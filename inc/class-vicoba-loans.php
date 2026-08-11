<?php
/**
 * VICOBA Loans Manager
 * Loan applications, guarantor digital approval, reducing/fixed interest calculation,
 * repayment schedules, and ledger integration.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Loans {

    public static function calculate_loan_schedule($principal, $interest_rate, $interest_type, $months) {
        $p = floatval($principal);
        $rate = floatval($interest_rate) / 100;
        $m = intval($months);
        $schedule = array();

        if ($interest_type === 'fixed') {
            // Total fixed interest = P * rate * months
            $total_interest = $p * $rate * ($m / 12);
            $total_payable = $p + $total_interest;
            $monthly_installment = $total_payable / $m;

            $principal_per_month = $p / $m;
            $interest_per_month = $total_interest / $m;

            $balance = $total_payable;
            for ($i = 1; $i <= $m; $i++) {
                $balance -= $monthly_installment;
                $schedule[] = array(
                    'month'              => $i,
                    'monthly_installment'=> round($monthly_installment, 2),
                    'principal_portion'  => round($principal_per_month, 2),
                    'interest_portion'   => round($interest_per_month, 2),
                    'remaining_balance'  => max(0, round($balance, 2)),
                );
            }

            return array(
                'total_interest'      => round($total_interest, 2),
                'total_payable'       => round($total_payable, 2),
                'monthly_installment' => round($monthly_installment, 2),
                'schedule'            => $schedule,
            );
        } else {
            // Reducing balance method (Amortization style)
            $monthly_rate = $rate / 12;
            if ($monthly_rate <= 0) {
                $monthly_installment = $p / $m;
            } else {
                $monthly_installment = ($p * $monthly_rate * pow(1 + $monthly_rate, $m)) / (pow(1 + $monthly_rate, $m) - 1);
            }

            $current_principal = $p;
            $total_interest_accumulated = 0;
            $total_payable_accumulated = 0;

            for ($i = 1; $i <= $m; $i++) {
                $interest_for_month = $current_principal * $monthly_rate;
                $principal_for_month = $monthly_installment - $interest_for_month;
                $current_principal -= $principal_for_month;
                if ($current_principal < 0) $current_principal = 0;

                $total_interest_accumulated += $interest_for_month;
                $total_payable_accumulated += $monthly_installment;

                $schedule[] = array(
                    'month'              => $i,
                    'monthly_installment'=> round($monthly_installment, 2),
                    'principal_portion'  => round($principal_for_month, 2),
                    'interest_portion'   => round($interest_for_month, 2),
                    'remaining_balance'  => round($current_principal, 2),
                );
            }

            return array(
                'total_interest'      => round($total_interest_accumulated, 2),
                'total_payable'       => round($total_payable_accumulated, 2),
                'monthly_installment' => round($monthly_installment, 2),
                'schedule'            => $schedule,
            );
        }
    }

    public static function apply_for_loan($group_id, $member_id, $principal, $months, $purpose, $guarantor_ids = array()) {
        global $wpdb;

        $group = VICOBA_Groups::get_group($group_id);
        if (!$group) {
            return new WP_Error('invalid_group', __('Kikundi hakikupatikana.', 'vicoba-manager'));
        }

        // Validate max loan limit based on member shares
        $shares = VICOBA_Shares::get_member_total_shares($group_id, $member_id);
        $max_allowed = $shares['value'] * floatval($group->loan_multiplier);

        if ($principal > $max_allowed) {
            return new WP_Error('exceeds_limit', sprintf(
                __('Kiasi cha mkopo (TZS %s) kinazidi kikomo chako cha Hisa (Mara %s = TZS %s).', 'vicoba-manager'),
                number_format($principal),
                $group->loan_multiplier,
                number_format($max_allowed)
            ));
        }

        $calc = self::calculate_loan_schedule($principal, $group->interest_rate, $group->interest_type, $months);

        $loan_code = 'LCN-' . strtoupper(wp_generate_password(6, false));
        $table_loans = $wpdb->prefix . 'vicoba_loans';

        $inserted = $wpdb->insert($table_loans, array(
            'group_id'                => $group_id,
            'member_id'               => $member_id,
            'loan_code'               => $loan_code,
            'principal_amount'        => floatval($principal),
            'interest_rate'           => floatval($group->interest_rate),
            'interest_type'           => sanitize_text_field($group->interest_type),
            'interest_amount'         => $calc['total_interest'],
            'total_amount'            => $calc['total_payable'],
            'balance_remaining'       => $calc['total_payable'],
            'repayment_period_months' => intval($months),
            'monthly_installment'     => $calc['monthly_installment'],
            'purpose'                 => sanitize_textarea_field($purpose),
            'status'                  => !empty($guarantor_ids) ? 'pending_guarantors' : 'pending_treasurer',
            'application_date'        => date('Y-m-d'),
            'created_at'              => current_time('mysql'),
        ));

        if (!$inserted) {
            return new WP_Error('db_error', __('Imeshindikana kuomba mkopo.', 'vicoba-manager'));
        }

        $loan_id = $wpdb->insert_id;

        // Create Guarantors records
        if (!empty($guarantor_ids)) {
            $guarantor_share = floatval($principal) / count($guarantor_ids);
            $table_g = $wpdb->prefix . 'vicoba_guarantors';

            foreach ($guarantor_ids as $g_member_id) {
                $wpdb->insert($table_g, array(
                    'loan_id'              => $loan_id,
                    'guarantor_member_id'  => intval($g_member_id),
                    'amount_guaranteed'    => round($guarantor_share, 2),
                    'status'               => 'pending',
                ));
            }
        }

        return $loan_id;
    }

    public static function respond_guarantor($guarantor_id, $status, $comments = '') {
        global $wpdb;
        $table_g = $wpdb->prefix . 'vicoba_guarantors';

        $updated = $wpdb->update(
            $table_g,
            array(
                'status'        => sanitize_text_field($status),
                'response_date' => current_time('mysql'),
                'comments'      => sanitize_textarea_field($comments),
            ),
            array('id' => $guarantor_id)
        );

        if ($updated) {
            // Check if all guarantors for this loan have approved
            $g_record = $wpdb->get_row($wpdb->prepare("SELECT loan_id FROM $table_g WHERE id = %d", $guarantor_id));
            if ($g_record) {
                $pending_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_g WHERE loan_id = %d AND status != 'approved'",
                    $g_record->loan_id
                ));

                if (intval($pending_count) === 0) {
                    // Advance loan status to pending_treasurer
                    $table_l = $wpdb->prefix . 'vicoba_loans';
                    $wpdb->update($table_l, array('status' => 'pending_treasurer'), array('id' => $g_record->loan_id));
                }
            }
        }

        return $updated;
    }

    public static function approve_loan_treasurer($loan_id, $approved_by) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_loans';
        return $wpdb->update($table, array('status' => 'pending_chairman'), array('id' => $loan_id));
    }

    public static function approve_and_disburse_loan($loan_id, $disbursed_by, $payment_method = 'cash') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_loans';

        $loan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $loan_id));
        if (!$loan) return false;

        $due_date = date('Y-m-d', strtotime('+' . $loan->repayment_period_months . ' months'));

        $updated = $wpdb->update($table, array(
            'status'       => 'active',
            'approved_at'  => current_time('mysql'),
            'disbursed_at' => current_time('mysql'),
            'due_date'     => $due_date,
        ), array('id' => $loan_id));

        if ($updated) {
            // Log disbursement in single source of truth financial ledger (Outflow)
            $member = VICOBA_Members::get_member($loan->member_id);
            $member_name = $member ? $member->full_name : 'Mwanachama';

            VICOBA_Ledger::record_transaction(array(
                'group_id'       => $loan->group_id,
                'member_id'      => $loan->member_id,
                'type'           => 'loan_disbursement',
                'amount'         => $loan->principal_amount,
                'payment_method' => $payment_method,
                'description'    => sprintf('Utoaji wa Mkopo %s (TZS %s) kwa %s', $loan->loan_code, number_format($loan->principal_amount), $member_name),
                'created_by'     => $disbursed_by,
            ));
        }

        return $updated;
    }

    public static function record_repayment($loan_id, $amount, $payment_method = 'cash', $verified_by = 0, $meeting_id = 0) {
        global $wpdb;
        $table_l = $wpdb->prefix . 'vicoba_loans';
        $loan = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_l WHERE id = %d", $loan_id));

        if (!$loan) {
            return new WP_Error('invalid_loan', __('Mkopo haukupatikana.', 'vicoba-manager'));
        }

        $paid = floatval($amount);
        if ($paid <= 0) {
            return new WP_Error('invalid_amount', __('Kiasi cha marejesho lazima kiwe zaidi ya 0.', 'vicoba-manager'));
        }

        // Breakdown: allocate to interest portion first, then principal
        $interest_portion = min($paid * 0.2, $paid); // 20% estimated interest component
        $principal_portion = $paid - $interest_portion;

        $table_r = $wpdb->prefix . 'vicoba_loan_repayments';
        $wpdb->insert($table_r, array(
            'group_id'       => $loan->group_id,
            'loan_id'        => $loan_id,
            'member_id'      => $loan->member_id,
            'meeting_id'     => $meeting_id,
            'amount_paid'    => $paid,
            'principal_paid' => $principal_portion,
            'interest_paid'  => $interest_portion,
            'payment_date'   => date('Y-m-d'),
            'payment_method' => $payment_method,
            'status'         => 'verified',
            'verified_by'    => $verified_by,
            'created_at'     => current_time('mysql'),
        ));

        // Update remaining loan balance
        $new_balance = max(0, $loan->balance_remaining - $paid);
        $new_status = ($new_balance <= 0) ? 'closed' : 'active';

        $wpdb->update($table_l, array(
            'balance_remaining' => $new_balance,
            'status'            => $new_status,
        ), array('id' => $loan_id));

        // Log repayment in single source of truth financial ledger (Inflow)
        $member = VICOBA_Members::get_member($loan->member_id);
        $member_name = $member ? $member->full_name : 'Mwanachama';

        VICOBA_Ledger::record_transaction(array(
            'group_id'       => $loan->group_id,
            'member_id'      => $loan->member_id,
            'meeting_id'     => $meeting_id,
            'type'           => 'loan_repayment',
            'amount'         => $paid,
            'payment_method' => $payment_method,
            'description'    => sprintf('Rejesho la Mkopo %s (TZS %s) kutoka kwa %s', $loan->loan_code, number_format($paid), $member_name),
            'created_by'     => $verified_by,
        ));

        return true;
    }

    public static function get_loans_by_group($group_id, $status = null) {
        global $wpdb;
        $table_l = $wpdb->prefix . 'vicoba_loans';
        $table_m = $wpdb->prefix . 'vicoba_members';

        if ($status) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT l.*, m.full_name, m.member_number FROM $table_l l JOIN $table_m m ON l.member_id = m.id WHERE l.group_id = %d AND l.status = %s ORDER BY l.created_at DESC",
                $group_id, $status
            ));
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, m.full_name, m.member_number FROM $table_l l JOIN $table_m m ON l.member_id = m.id WHERE l.group_id = %d ORDER BY l.created_at DESC",
            $group_id
        ));
    }

    public static function get_member_loans($member_id) {
        global $wpdb;
        $table_l = $wpdb->prefix . 'vicoba_loans';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_l WHERE member_id = %d ORDER BY created_at DESC", $member_id));
    }
}
