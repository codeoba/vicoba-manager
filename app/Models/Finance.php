<?php namespace Models;

class Shareout {
    public static function finalize(int $group_id, array $data, int $by): int|array {
        $members = \Database::all("SELECT m.id, COALESCE(SUM(s.share_count),0) as total_shares FROM " . \Database::t('members') . " m LEFT JOIN " . \Database::t('shares') . " s ON s.member_id=m.id AND s.group_id=m.group_id WHERE m.group_id=? AND m.status='active' GROUP BY m.id", [$group_id]);
        if (empty($members)) return ['error' => 'Hakuna wanachama wa kikundi hiki.'];

        $total_shares_pool = (float)\Database::scalar("SELECT COALESCE(SUM(total_amount),0) FROM " . \Database::t('shares') . " WHERE group_id=?", [$group_id]);
        $total_interest    = (float)\Database::scalar("SELECT COALESCE(SUM(amount_paid - principal_amount),0) FROM " . \Database::t('loans') . " WHERE group_id=? AND status='completed'", [$group_id]);
        $total_fines       = (float)\Database::scalar("SELECT COALESCE(SUM(amount),0) FROM " . \Database::t('fines') . " WHERE group_id=? AND status='paid'", [$group_id]);
        $operating_costs   = (float)($data['operating_costs'] ?? 0);
        $bad_debt          = (float)($data['bad_debt_provision'] ?? 0);
        $net_distributable = $total_shares_pool + $total_interest + $total_fines - $operating_costs - $bad_debt;

        $total_share_units = array_sum(array_column($members, 'total_shares'));
        if ($total_share_units <= 0) return ['error' => 'Jumla ya hisa ni sifuri. Haiwezekani kufanya mgawanyo.'];

        \Database::beginTransaction();
        try {
            $shareout_id = \Database::insert('shareouts', [
                'group_id'             => $group_id,
                'shareout_date'        => $data['shareout_date'] ?? \today(),
                'total_shares_pool'    => $total_shares_pool,
                'total_interest_pool'  => $total_interest,
                'total_fines_pool'     => $total_fines,
                'operating_costs'      => $operating_costs,
                'bad_debt_provision'   => $bad_debt,
                'net_distributable'    => $net_distributable,
                'total_shares'         => $total_share_units,
                'status'               => 'finalized',
                'finalized_by'         => $by,
            ]);

            foreach ($members as $m) {
                $shares  = (int)$m->total_shares;
                $ratio   = $total_share_units > 0 ? $shares / $total_share_units : 0;
                $gross   = $net_distributable * $ratio;
                $active_loan = (float)\Database::scalar("SELECT COALESCE(SUM(balance_remaining),0) FROM " . \Database::t('loans') . " WHERE member_id=? AND group_id=? AND status IN ('active','overdue')", [$m->id, $group_id]);
                $net = max(0, $gross - $active_loan);

                \Database::insert('shareout_details', ['shareout_id' => $shareout_id, 'member_id' => $m->id, 'total_member_shares' => $shares, 'share_ratio' => $ratio, 'gross_payout' => $gross, 'active_loan_deduction' => $active_loan, 'net_payout' => $net]);
            }

            \Database::commit();
            Audit::log($group_id, $by, 'shareout_finalized', 'shareout', $shareout_id, null, "TZS " . number_format($net_distributable));
            return $shareout_id;
        } catch (\Throwable $e) {
            \Database::rollback();
            error_log($e->getMessage());
            return ['error' => 'Hitilafu ya ndani. Jaribu tena.'];
        }
    }
}

class Reports {
    public static function getSummary(int $group_id, ?string $from = null, ?string $to = null): array {
        $t_loans = \Database::t('loans');
        $t_shares = \Database::t('shares');
        $t_fines = \Database::t('fines');
        $t_members = \Database::t('members');

        $date_cond = '';
        $params = [$group_id];
        if ($from) { $date_cond .= ' AND created_at >= ?'; $params[] = $from; }
        if ($to)   { $date_cond .= ' AND created_at <= ?'; $params[] = $to . ' 23:59:59'; }

        $total_shares  = (float)\Database::scalar("SELECT COALESCE(SUM(total_amount),0) FROM $t_shares WHERE group_id=?$date_cond", $params);
        $active_loans  = (float)\Database::scalar("SELECT COALESCE(SUM(balance_remaining),0) FROM $t_loans WHERE group_id=? AND status IN ('active','overdue')", [$group_id]);
        $total_borrowed= (float)\Database::scalar("SELECT COALESCE(SUM(principal_amount),0) FROM $t_loans WHERE group_id=? AND status IN ('active','overdue','completed')", [$group_id]);
        $total_repaid  = (float)\Database::scalar("SELECT COALESCE(SUM(amount_paid),0) FROM $t_loans WHERE group_id=? AND status IN ('active','overdue','completed')", [$group_id]);
        $fines_pending = (float)\Database::scalar("SELECT COALESCE(SUM(amount),0) FROM $t_fines WHERE group_id=? AND status='pending'", [$group_id]);
        $fines_paid    = (float)\Database::scalar("SELECT COALESCE(SUM(amount),0) FROM $t_fines WHERE group_id=? AND status='paid'", [$group_id]);
        $member_count  = (int)\Database::scalar("SELECT COUNT(*) FROM $t_members WHERE group_id=? AND status='active'", [$group_id]);
        $total_loan_count = (int)\Database::scalar("SELECT COUNT(*) FROM $t_loans WHERE group_id=? AND status IN ('active','overdue')", [$group_id]);
        $overdue_loans = (int)\Database::scalar("SELECT COUNT(*) FROM $t_loans WHERE group_id=? AND status='overdue'", [$group_id]);
        $balance = Ledger::getBalance($group_id);

        // Repayment Rate
        $repayment_rate = $total_borrowed > 0 ? round(($total_repaid / $total_borrowed) * 100, 1) : 100;

        // NPL Rate (Non-performing loan ratio)
        $npl_rate = $total_loan_count > 0 ? round(($overdue_loans / $total_loan_count) * 100, 1) : 0;

        // Calculate Financial Health Score (0 - 100)
        $health_score = 100;
        if ($npl_rate > 0) $health_score -= ($npl_rate * 1.5);
        if ($repayment_rate < 90) $health_score -= ((90 - $repayment_rate) * 0.8);
        if ($member_count < 5) $health_score -= 10;
        $health_score = max(10, min(100, (int)round($health_score)));

        $risk_level = match(true) {
            $health_score >= 80 => 'LOW',
            $health_score >= 50 => 'MEDIUM',
            default             => 'HIGH'
        };

        return compact('total_shares','active_loans','fines_pending','fines_paid','member_count','overdue_loans','balance','health_score','risk_level','npl_rate','repayment_rate');
    }
}

class Export {
    public static function streamLedgerCsv(int $group_id, ?string $from, ?string $to): never {
        $params = [$group_id];
        $where = '';
        if ($from) { $where .= ' AND t.created_at >= ?'; $params[] = $from; }
        if ($to)   { $where .= ' AND t.created_at <= ?'; $params[] = $to . ' 23:59:59'; }

        $rows = \Database::all(
            "SELECT t.transaction_code, t.created_at, t.type, m.full_name as member_name, t.amount, t.payment_method, t.description
             FROM " . \Database::t('transactions') . " t
             LEFT JOIN " . \Database::t('members') . " m ON m.id = t.member_id
             WHERE t.group_id=? $where ORDER BY t.created_at DESC",
            $params
        );

        \Response::csv("ledger_{$group_id}_" . date('Ymd') . ".csv", function($out) use ($rows) {
            fputcsv($out, ['Namba', 'Tarehe', 'Aina', 'Mwanachama', 'Kiasi', 'Njia ya Malipo', 'Maelezo']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->transaction_code, $r->created_at, $r->type, $r->member_name ?? '', $r->amount, $r->payment_method, $r->description]);
            }
        });
    }

    public static function streamStatementCsv(int $group_id, int $member_id): never {
        $member = \Database::get('SELECT full_name, member_number FROM ' . \Database::t('members') . ' WHERE id=? AND group_id=?', [$member_id, $group_id]);
        $rows = \Database::all(
            "SELECT t.transaction_code, t.created_at, t.type, t.amount, t.payment_method, t.description
             FROM " . \Database::t('transactions') . " t
             WHERE t.group_id=? AND t.member_id=? ORDER BY t.created_at DESC",
            [$group_id, $member_id]
        );
        $name = str_replace(' ', '_', $member->full_name ?? 'member');
        \Response::csv("statement_{$name}_" . date('Ymd') . ".csv", function($out) use ($rows, $member) {
            fputcsv($out, ['Taarifa ya: ' . ($member->full_name ?? ''), 'Namba: ' . ($member->member_number ?? '')]);
            fputcsv($out, ['Namba', 'Tarehe', 'Aina', 'Kiasi', 'Njia', 'Maelezo']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->transaction_code, $r->created_at, $r->type, $r->amount, $r->payment_method, $r->description]);
            }
        });
    }

    public static function streamShareoutCsv(int $shareout_id): never {
        $rows = \Database::all(
            "SELECT m.full_name, m.member_number, sd.total_member_shares, sd.gross_payout, sd.active_loan_deduction, sd.net_payout
             FROM " . \Database::t('shareout_details') . " sd
             JOIN " . \Database::t('members') . " m ON m.id = sd.member_id
             WHERE sd.shareout_id = ? ORDER BY m.full_name",
            [$shareout_id]
        );
        \Response::csv("shareout_{$shareout_id}_" . date('Ymd') . ".csv", function($out) use ($rows) {
            fputcsv($out, ['Jina', 'Namba', 'Jumla Hisa', 'Malipo Ghafi', 'Punguzo (Mkopo)', 'Malipo Halisi']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->full_name, $r->member_number, $r->total_member_shares, $r->gross_payout, $r->active_loan_deduction, $r->net_payout]);
            }
        });
    }
}
