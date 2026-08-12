<?php namespace Models;

class Loans {

    public static function getByGroup(int $group_id): array {
        return \Database::all(
            'SELECT l.*, m.full_name as member_name, m.member_number, m.phone as member_phone
             FROM ' . \Database::t('loans') . ' l
             JOIN ' . \Database::t('members') . ' m ON m.id = l.member_id
             WHERE l.group_id = ? ORDER BY l.created_at DESC',
            [$group_id]
        );
    }

    public static function apply(int $group_id, array $data, int $by): int|array {
        if (empty($data['member_id']))       return ['error' => 'Mwanachama anahitajika.'];
        if (empty($data['principal_amount'])) return ['error' => 'Kiasi cha mkopo kinahitajika.'];

        $group    = \Database::get('SELECT * FROM ' . \Database::t('groups') . ' WHERE id=?', [$group_id]);
        $member   = \Database::get('SELECT * FROM ' . \Database::t('members') . ' WHERE id=? AND group_id=?', [(int)$data['member_id'], $group_id]);
        if (!$member) return ['error' => 'Mwanachama hakupatikana.'];

        $principal = (float)$data['principal_amount'];

        // Max loan = multiplier * member shares
        $member_shares = Shares::getMemberTotal($group_id, $member->id);
        $share_price   = (float)$group->share_price;
        $max_loan      = $member_shares * $share_price * (int)$group->max_loan_multiplier;
        if ($principal > $max_loan) {
            return ['error' => "Mkopo unaomba (TZS " . number_format($principal) . ") unazidi kiwango chako (TZS " . number_format($max_loan) . ")."];
        }

        // No active loans allowed
        $active = \Database::scalar(
            "SELECT COUNT(*) FROM " . \Database::t('loans') . " WHERE member_id=? AND group_id=? AND status IN ('active','overdue','pending_guarantors','pending_treasurer','pending_chairman')",
            [$member->id, $group_id]
        );
        if ($active > 0) return ['error' => 'Mwanachama bado ana mkopo wa sasa. Lazima ulipe kwanza.'];

        $rate    = !empty($data['interest_rate'])   ? (float)$data['interest_rate']   : (float)$group->loan_interest_rate;
        $type    = !empty($data['interest_type'])   ? $data['interest_type']           : $group->loan_interest_type;
        $months  = !empty($data['repayment_months']) ? (int)$data['repayment_months']  : (int)$group->max_loan_period;
        $sched   = calculate_loan_schedule($principal, $rate, $type, $months);
        $loan_code = generate_code('LN');

        $loan_id = \Database::insert('loans', [
            'loan_code'               => $loan_code,
            'group_id'                => $group_id,
            'member_id'               => $member->id,
            'principal_amount'        => $principal,
            'interest_rate'           => $rate,
            'interest_type'           => $type,
            'repayment_period_months' => $months,
            'monthly_installment'     => $sched['monthly_installment'],
            'total_payable'           => $sched['total_payable'],
            'balance_remaining'       => $sched['total_payable'],
            'purpose'                 => \sanitize($data['purpose'] ?? ''),
            'status'                  => 'pending_guarantors',
        ]);

        // Add guarantors if provided
        if (!empty($data['guarantors']) && is_array($data['guarantors'])) {
            foreach (array_slice($data['guarantors'], 0, 2) as $gid) {
                \Database::insert('loan_guarantors', [
                    'loan_id'             => $loan_id,
                    'guarantor_member_id' => (int)$gid,
                    'status'              => 'pending',
                ]);
            }
        }

        Audit::log($group_id, $by, 'loan_applied', 'loan', $loan_id, null, "TZS " . number_format($principal));
        Notifications::create($group_id, $member->user_id, 'Mkopo Umewasilishwa', "Ombi lako la mkopo wa TZS " . number_format($principal) . " limewasilishwa. Subiri idhini.", 'loan_applied');
        return $loan_id;
    }

    public static function guarantorRespond(int $loan_id, int $user_id, string $response): int {
        $member = \Database::get('SELECT * FROM ' . \Database::t('members') . ' WHERE user_id = ?', [$user_id]);
        if (!$member) return 0;

        $updated = \Database::update('loan_guarantors',
            ['status' => $response === 'approve' ? 'approved' : 'declined', 'responded_at' => \now()],
            ['loan_id' => $loan_id, 'guarantor_member_id' => $member->id]
        );

        // Auto-advance if all guarantors approved
        $pending = \Database::scalar('SELECT COUNT(*) FROM ' . \Database::t('loan_guarantors') . ' WHERE loan_id = ? AND status = ?', [$loan_id, 'pending']);
        $declined = \Database::scalar('SELECT COUNT(*) FROM ' . \Database::t('loan_guarantors') . ' WHERE loan_id = ? AND status = ?', [$loan_id, 'declined']);

        if ($declined > 0) {
            \Database::update('loans', ['status' => 'rejected'], ['id' => $loan_id]);
        } elseif ($pending == 0) {
            \Database::update('loans', ['status' => 'pending_treasurer'], ['id' => $loan_id]);
        }

        return $updated;
    }

    public static function disburse(int $loan_id, array $data, int $by): int|array {
        $loan = \Database::get('SELECT * FROM ' . \Database::t('loans') . ' WHERE id = ?', [$loan_id]);
        if (!$loan) return ['error' => 'Mkopo haukupatikana.'];
        if (!in_array($loan->status, ['pending_treasurer','pending_chairman'])) {
            return ['error' => 'Mkopo huu hauko katika hali ya kusubiri kutolewa.'];
        }

        $due_date = date('Y-m-d', strtotime('+' . $loan->repayment_period_months . ' months'));

        \Database::update('loans', [
            'status'       => 'active',
            'disbursed_at' => \now(),
            'disbursed_by' => $by,
            'due_date'     => $due_date,
        ], ['id' => $loan_id]);

        Ledger::record($loan->group_id, $loan->member_id, 'loan_disbursement', (float)$loan->principal_amount, $data['payment_method'] ?? 'cash', "Mkopo: {$loan->loan_code}", $by);
        Audit::log($loan->group_id, $by, 'loan_disbursed', 'loan', $loan_id, $loan->status, 'active');
        Notifications::create($loan->group_id, \Database::scalar('SELECT user_id FROM ' . \Database::t('members') . ' WHERE id=?', [$loan->member_id]), 'Mkopo Umetolewa!', "Mkopo wako {$loan->loan_code} wa TZS " . number_format($loan->principal_amount) . " umetolewa. Due: " . format_date($due_date), 'loan_disbursed');
        return 1;
    }

    public static function repay(int $loan_id, array $data, int $by): int|array {
        $loan = \Database::get('SELECT * FROM ' . \Database::t('loans') . ' WHERE id = ?', [$loan_id]);
        if (!$loan) return ['error' => 'Mkopo haukupatikana.'];
        if (!in_array($loan->status, ['active','overdue'])) return ['error' => 'Mkopo huu hauwezi kulipwa kwa sasa.'];

        $amount  = (float)($data['amount_paid'] ?? 0);
        if ($amount <= 0) return ['error' => 'Kiasi cha kulipa lazima kiwe zaidi ya sifuri.'];

        $balance = max(0, (float)$loan->balance_remaining - $amount);

        \Database::insert('loan_repayments', [
            'loan_id'       => $loan_id,
            'payment_date'  => $data['payment_date'] ?? \today(),
            'amount_paid'   => $amount,
            'balance_after' => $balance,
            'payment_method'=> $data['payment_method'] ?? 'cash',
            'payment_reference' => $data['payment_reference'] ?? null,
            'received_by'   => $by,
        ]);

        $new_status = $balance <= 0 ? 'completed' : $loan->status;
        \Database::update('loans', [
            'amount_paid'      => (float)$loan->amount_paid + $amount,
            'balance_remaining'=> $balance,
            'last_payment_date'=> \today(),
            'status'           => $new_status,
        ], ['id' => $loan_id]);

        Ledger::record($loan->group_id, $loan->member_id, 'loan_repayment', $amount, $data['payment_method'] ?? 'cash', "Rejesho: {$loan->loan_code}", $by);
        Audit::log($loan->group_id, $by, 'loan_repaid', 'loan', $loan_id, null, "TZS " . number_format($amount));
        return 1;
    }
}
