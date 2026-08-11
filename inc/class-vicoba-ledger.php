<?php
/**
 * VICOBA Financial Ledger Engine
 * Immutable Single Source of Truth for all monetary transactions
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Ledger {

    public static function record_transaction($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_transactions';

        $code = 'TXN-' . strtoupper(wp_generate_password(8, false));

        $inserted = $wpdb->insert($table, array(
            'group_id'         => intval($data['group_id']),
            'member_id'        => intval($data['member_id'] ?? 0),
            'meeting_id'       => intval($data['meeting_id'] ?? 0),
            'transaction_code' => $code,
            'type'             => sanitize_text_field($data['type']),
            'amount'           => floatval($data['amount']),
            'payment_method'   => sanitize_text_field($data['payment_method'] ?? 'cash'),
            'description'      => sanitize_textarea_field($data['description']),
            'created_by'       => intval($data['created_by'] ?? get_current_user_id()),
            'created_at'       => current_time('mysql'),
        ));

        return $inserted ? $code : false;
    }

    public static function get_box_balance($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_transactions';

        $inflow_types = array('share_purchase', 'loan_repayment', 'fine_payment', 'social_fund_in');
        $outflow_types = array('loan_disbursement', 'social_fund_out', 'group_expense', 'shareout');

        $inflow_placeholders = implode("','", array_map('esc_sql', $inflow_types));
        $outflow_placeholders = implode("','", array_map('esc_sql', $outflow_types));

        $total_inflows = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM $table WHERE group_id = %d AND type IN ('$inflow_placeholders')",
            $group_id
        ));

        $total_outflows = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(amount) FROM $table WHERE group_id = %d AND type IN ('$outflow_placeholders')",
            $group_id
        ));

        $inflows = floatval($total_inflows ?? 0.00);
        $outflows = floatval($total_outflows ?? 0.00);
        $balance = $inflows - $outflows;

        // Breakdown by account method
        $cash = $wpdb->get_var($wpdb->prepare(
            "SELECT (SUM(CASE WHEN type IN ('$inflow_placeholders') THEN amount ELSE 0 END) - SUM(CASE WHEN type IN ('$outflow_placeholders') THEN amount ELSE 0 END)) FROM $table WHERE group_id = %d AND payment_method = 'cash'",
            $group_id
        ));

        $mobile = $wpdb->get_var($wpdb->prepare(
            "SELECT (SUM(CASE WHEN type IN ('$inflow_placeholders') THEN amount ELSE 0 END) - SUM(CASE WHEN type IN ('$outflow_placeholders') THEN amount ELSE 0 END)) FROM $table WHERE group_id = %d AND payment_method = 'mobile_money'",
            $group_id
        ));

        $bank = $wpdb->get_var($wpdb->prepare(
            "SELECT (SUM(CASE WHEN type IN ('$inflow_placeholders') THEN amount ELSE 0 END) - SUM(CASE WHEN type IN ('$outflow_placeholders') THEN amount ELSE 0 END)) FROM $table WHERE group_id = %d AND payment_method = 'bank'",
            $group_id
        ));

        return array(
            'total_inflows'  => $inflows,
            'total_outflows' => $outflows,
            'box_balance'    => $balance,
            'cash_balance'   => floatval($cash ?? 0.00),
            'mobile_balance' => floatval($mobile ?? 0.00),
            'bank_balance'   => floatval($bank ?? 0.00),
        );
    }

    public static function record_expense($group_id, $amount, $description, $payment_method = 'cash', $created_by = 0) {
        return self::record_transaction(array(
            'group_id'       => $group_id,
            'type'           => 'group_expense',
            'amount'         => floatval($amount),
            'payment_method' => $payment_method,
            'description'    => sprintf('Gharama za Uendeshaji: %s', sanitize_text_field($description)),
            'created_by'     => $created_by,
        ));
    }

    public static function get_ledger_transactions($group_id, $limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_transactions';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE group_id = %d ORDER BY created_at DESC LIMIT %d",
            $group_id, $limit
        ));
    }
}
