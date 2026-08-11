<?php
/**
 * VICOBA Shares Manager
 * Handles purchasing of shares, contribution tracking, min/max validations,
 * and single-source-of-truth ledger auto-posting.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Shares {

    public static function record_share_purchase($group_id, $member_id, $meeting_id, $share_count, $payment_method = 'cash', $recorded_by = 0) {
        global $wpdb;

        $group = VICOBA_Groups::get_group($group_id);
        if (!$group) {
            return new WP_Error('invalid_group', __('Group not found', 'vicoba-manager'));
        }

        $count = intval($share_count);
        if ($count < $group->min_shares_per_cycle || $count > $group->max_shares_per_cycle) {
            return new WP_Error('invalid_share_count', sprintf(
                __('Idadi ya hisa lazima iwe kati ya %d na %d kwa mzunguko.', 'vicoba-manager'),
                $group->min_shares_per_cycle,
                $group->max_shares_per_cycle
            ));
        }

        $unit_price = floatval($group->share_price);
        $total_amount = $count * $unit_price;

        $table_shares = $wpdb->prefix . 'vicoba_shares';
        $inserted = $wpdb->insert($table_shares, array(
            'group_id'     => $group_id,
            'member_id'    => $member_id,
            'meeting_id'   => $meeting_id,
            'share_count'  => $count,
            'unit_price'   => $unit_price,
            'total_amount' => $total_amount,
            'payment_date' => date('Y-m-d'),
            'recorded_by'  => $recorded_by,
            'created_at'   => current_time('mysql'),
        ));

        if (!$inserted) {
            return new WP_Error('db_error', __('Kosa la kuhifadhi hisa kwenye database.', 'vicoba-manager'));
        }

        $share_id = $wpdb->insert_id;

        // Automatically log to single-source-of-truth financial ledger
        $member = VICOBA_Members::get_member($member_id);
        $member_name = $member ? $member->full_name : 'Mwanachama';

        VICOBA_Ledger::record_transaction(array(
            'group_id'       => $group_id,
            'member_id'      => $member_id,
            'meeting_id'     => $meeting_id,
            'type'           => 'share_purchase',
            'amount'         => $total_amount,
            'payment_method' => $payment_method,
            'description'    => sprintf('Uwekaji wa Hisa %d (TZS %s) kwa %s', $count, number_format($total_amount), $member_name),
            'created_by'     => $recorded_by,
        ));

        return $share_id;
    }

    public static function get_member_total_shares($group_id, $member_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_shares';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(share_count) as total_shares, SUM(total_amount) as total_value FROM $table WHERE group_id = %d AND member_id = %d",
            $group_id,
            $member_id
        ));

        return array(
            'count' => intval($res->total_shares ?? 0),
            'value' => floatval($res->total_value ?? 0.00),
        );
    }

    public static function get_group_total_shares($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_shares';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(share_count) as total_shares, SUM(total_amount) as total_value FROM $table WHERE group_id = %d",
            $group_id
        ));

        return array(
            'count' => intval($res->total_shares ?? 0),
            'value' => floatval($res->total_value ?? 0.00),
        );
    }

    public static function get_shares_by_group($group_id, $limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_shares';
        $table_members = $wpdb->prefix . 'vicoba_members';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, m.full_name, m.member_number FROM $table s 
             JOIN $table_members m ON s.member_id = m.id 
             WHERE s.group_id = %d ORDER BY s.created_at DESC LIMIT %d",
            $group_id, $limit
        ));
    }
}
