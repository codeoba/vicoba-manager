<?php
/**
 * VICOBA Share-Out Engine
 * Pure math distribution calculator, simulation, and cycle finalization
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Shareout {

    /**
     * Pure calculation function for Share-Out simulation
     */
    public static function calculate_shareout_distribution($group_id) {
        global $wpdb;

        // 1. Total Shares pool
        $shares_data = VICOBA_Shares::get_group_total_shares($group_id);
        $total_shares_count = $shares_data['count'];
        $total_shares_pool = $shares_data['value'];

        if ($total_shares_count <= 0) {
            return new WP_Error('no_shares', __('Hakuna hisa zilizonunuliwa katika mzunguko huu.', 'vicoba-manager'));
        }

        // 2. Total Interest Profit from Repayments
        $table_r = $wpdb->prefix . 'vicoba_loan_repayments';
        $interest_profit = $wpdb->get_var($wpdb->prepare("SELECT SUM(interest_paid) FROM $table_r WHERE group_id = %d", $group_id));
        $total_interest_profit = floatval($interest_profit ?? 0.00);

        // 3. Total Fines Collected
        $table_f = $wpdb->prefix . 'vicoba_fines';
        $fines_collected = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_f WHERE group_id = %d AND status = 'paid'", $group_id));
        $total_fines = floatval($fines_collected ?? 0.00);

        // 4. Total Expenses
        $table_t = $wpdb->prefix . 'vicoba_transactions';
        $expenses = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM $table_t WHERE group_id = %d AND type = 'group_expense'", $group_id));
        $total_expenses = floatval($expenses ?? 0.00);

        // 5. Total Bad Debt (Overdue active loans balance)
        $table_l = $wpdb->prefix . 'vicoba_loans';
        $bad_debt = $wpdb->get_var($wpdb->prepare("SELECT SUM(balance_remaining) FROM $table_l WHERE group_id = %d AND status = 'overdue'", $group_id));
        $total_bad_debt = floatval($bad_debt ?? 0.00);

        // Net Distributable Pool
        $net_distributable = $total_shares_pool + $total_interest_profit + $total_fines - $total_expenses - $total_bad_debt;
        $dividend_per_share = $net_distributable / $total_shares_count;

        // Individual member distribution breakdown
        $members = VICOBA_Members::get_members_by_group($group_id, 'active');
        $member_payouts = array();

        foreach ($members as $member) {
            $m_shares = VICOBA_Shares::get_member_total_shares($group_id, $member->id);
            $m_count = $m_shares['count'];
            $ratio = $total_shares_count > 0 ? ($m_count / $total_shares_count) : 0;
            $gross_payout = $m_count * $dividend_per_share;

            // Check if member has active loans to offset
            $active_loan_balance = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(balance_remaining) FROM $table_l WHERE group_id = %d AND member_id = %d AND status IN ('active', 'overdue')",
                $group_id, $member->id
            ));
            $loan_deduction = floatval($active_loan_balance ?? 0.00);
            $net_payout = max(0, $gross_payout - $loan_deduction);

            $member_payouts[] = array(
                'member_id'             => $member->id,
                'member_number'         => $member->member_number,
                'full_name'             => $member->full_name,
                'total_member_shares'   => $m_count,
                'share_ratio'           => round($ratio, 6),
                'gross_payout'          => round($gross_payout, 2),
                'active_loan_deduction' => round($loan_deduction, 2),
                'net_payout'            => round($net_payout, 2),
            );
        }

        return array(
            'total_shares_count'    => $total_shares_count,
            'total_shares_pool'     => $total_shares_pool,
            'total_interest_profit' => $total_interest_profit,
            'total_fines_collected' => $total_fines,
            'total_expenses'        => $total_expenses,
            'total_bad_debt'        => $total_bad_debt,
            'net_distributable'     => round($net_distributable, 2),
            'dividend_per_share'    => round($dividend_per_share, 2),
            'member_payouts'        => $member_payouts,
        );
    }

    public static function finalize_shareout($group_id, $finalized_by) {
        global $wpdb;

        $calc = self::calculate_shareout_distribution($group_id);
        if (is_wp_error($calc)) {
            return $calc;
        }

        $table_s = $wpdb->prefix . 'vicoba_shareouts';
        $inserted = $wpdb->insert($table_s, array(
            'group_id'              => $group_id,
            'shareout_date'         => date('Y-m-d'),
            'total_shares_pool'     => $calc['total_shares_pool'],
            'total_interest_profit' => $calc['total_interest_profit'],
            'total_fines_collected' => $calc['total_fines_collected'],
            'total_expenses'        => $calc['total_expenses'],
            'total_bad_debt'        => $calc['total_bad_debt'],
            'net_distributable'     => $calc['net_distributable'],
            'status'                => 'finalized',
            'finalized_by'          => $finalized_by,
            'created_at'            => current_time('mysql'),
        ));

        if (!$inserted) {
            return new WP_Error('db_error', __('Kosa la kuhifadhi Share-Out.', 'vicoba-manager'));
        }

        $shareout_id = $wpdb->insert_id;
        $table_d = $wpdb->prefix . 'vicoba_shareout_details';

        foreach ($calc['member_payouts'] as $payout) {
            $wpdb->insert($table_d, array(
                'shareout_id'           => $shareout_id,
                'member_id'             => $payout['member_id'],
                'total_member_shares'   => $payout['total_member_shares'],
                'share_ratio'           => $payout['share_ratio'],
                'gross_payout'          => $payout['gross_payout'],
                'active_loan_deduction' => $payout['active_loan_deduction'],
                'net_payout'            => $payout['net_payout'],
                'payment_status'        => 'paid',
                'paid_at'               => current_time('mysql'),
            ));

            // Log Share-Out Payout in financial ledger
            VICOBA_Ledger::record_transaction(array(
                'group_id'       => $group_id,
                'member_id'      => $payout['member_id'],
                'type'           => 'shareout',
                'amount'         => $payout['net_payout'],
                'payment_method' => 'cash',
                'description'    => sprintf('Mgawanyo wa Share-Out (Net: TZS %s) kwa %s', number_format($payout['net_payout']), $payout['full_name']),
                'created_by'     => $finalized_by,
            ));
        }

        return $shareout_id;
    }
}
