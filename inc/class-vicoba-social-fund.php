<?php
/**
 * VICOBA Social Fund Manager
 * Emergency assistance requests, contributions, and ledger integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Social_Fund {

    public static function record_contribution($group_id, $member_id, $meeting_id, $amount, $payment_method = 'cash', $recorded_by = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_social_fund';

        $val = floatval($amount);
        $inserted = $wpdb->insert($table, array(
            'group_id'         => $group_id,
            'member_id'        => $member_id,
            'meeting_id'       => $meeting_id,
            'transaction_type' => 'contribution',
            'amount'           => $val,
            'category'         => 'general',
            'description'      => 'Mchango wa Mfuko wa Jamii',
            'status'           => 'approved',
            'approved_by'      => $recorded_by,
            'created_at'       => current_time('mysql'),
        ));

        if ($inserted) {
            $member = VICOBA_Members::get_member($member_id);
            $member_name = $member ? $member->full_name : 'Mwanachama';

            // Post to ledger (Inflow)
            VICOBA_Ledger::record_transaction(array(
                'group_id'       => $group_id,
                'member_id'      => $member_id,
                'meeting_id'     => $meeting_id,
                'type'           => 'social_fund_in',
                'amount'         => $val,
                'payment_method' => $payment_method,
                'description'    => sprintf('Mchango wa Mfuko wa Jamii (TZS %s) kutoka kwa %s', number_format($val), $member_name),
                'created_by'     => $recorded_by,
            ));
        }

        return $inserted;
    }

    public static function request_emergency_payout($group_id, $member_id, $amount, $category, $description) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_social_fund';

        return $wpdb->insert($table, array(
            'group_id'         => $group_id,
            'member_id'        => $member_id,
            'meeting_id'       => 0,
            'transaction_type' => 'payout',
            'amount'           => floatval($amount),
            'category'         => sanitize_text_field($category),
            'description'      => sanitize_textarea_field($description),
            'status'           => 'pending',
            'approved_by'      => 0,
            'created_at'       => current_time('mysql'),
        ));
    }

    public static function approve_emergency_payout($request_id, $approved_by, $payment_method = 'cash') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_social_fund';
        $req = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $request_id));

        if (!$req || $req->status === 'approved') {
            return false;
        }

        $updated = $wpdb->update($table, array(
            'status'      => 'approved',
            'approved_by' => $approved_by,
        ), array('id' => $request_id));

        if ($updated) {
            $member = VICOBA_Members::get_member($req->member_id);
            $member_name = $member ? $member->full_name : 'Mwanachama';

            // Post to ledger (Outflow)
            VICOBA_Ledger::record_transaction(array(
                'group_id'       => $req->group_id,
                'member_id'      => $req->member_id,
                'type'           => 'social_fund_out',
                'amount'         => $req->amount,
                'payment_method' => $payment_method,
                'description'    => sprintf('Msaada wa Mfuko wa Jamii (%s: TZS %s) kwa %s', ucfirst($req->category), number_format($req->amount), $member_name),
                'created_by'     => $approved_by,
            ));
        }

        return $updated;
    }

    public static function get_records_by_group($group_id) {
        global $wpdb;
        $table_s = $wpdb->prefix . 'vicoba_social_fund';
        $table_m = $wpdb->prefix . 'vicoba_members';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, m.full_name, m.member_number FROM $table_s s JOIN $table_m m ON s.member_id = m.id WHERE s.group_id = %d ORDER BY s.created_at DESC",
            $group_id
        ));
    }
}
