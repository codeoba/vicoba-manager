<?php
/**
 * VICOBA Fines Manager
 * Fine rules, issuing fines, processing payments, and ledger posting
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Fines {

    public static function get_fine_types($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_fine_types';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d ORDER BY name ASC", $group_id));
    }

    public static function create_fine_type($group_id, $name, $amount, $description = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_fine_types';
        return $wpdb->insert($table, array(
            'group_id'       => $group_id,
            'name'           => sanitize_text_field($name),
            'default_amount' => floatval($amount),
            'description'    => sanitize_textarea_field($description),
            'created_at'     => current_time('mysql'),
        ));
    }

    public static function issue_fine($group_id, $member_id, $fine_type_id, $amount, $reason, $meeting_id = 0, $recorded_by = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_fines';

        $inserted = $wpdb->insert($table, array(
            'group_id'     => $group_id,
            'member_id'    => $member_id,
            'meeting_id'   => $meeting_id,
            'fine_type_id' => $fine_type_id,
            'amount'       => floatval($amount),
            'reason'       => sanitize_text_field($reason),
            'status'       => 'unpaid',
            'recorded_by'  => $recorded_by,
            'created_at'   => current_time('mysql'),
        ));

        return $inserted ? $wpdb->insert_id : false;
    }

    public static function pay_fine($fine_id, $payment_method = 'cash', $received_by = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_fines';
        $fine = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $fine_id));

        if (!$fine || $fine->status === 'paid') {
            return false;
        }

        $updated = $wpdb->update($table, array(
            'status'  => 'paid',
            'paid_at' => current_time('mysql'),
        ), array('id' => $fine_id));

        if ($updated) {
            // Post directly to the financial ledger (Inflow)
            $member = VICOBA_Members::get_member($fine->member_id);
            $member_name = $member ? $member->full_name : 'Mwanachama';

            VICOBA_Ledger::record_transaction(array(
                'group_id'       => $fine->group_id,
                'member_id'      => $fine->member_id,
                'meeting_id'     => $fine->meeting_id,
                'type'           => 'fine_payment',
                'amount'         => $fine->amount,
                'payment_method' => $payment_method,
                'description'    => sprintf('Malipo ya Faini: %s (TZS %s) kutoka kwa %s', $fine->reason, number_format($fine->amount), $member_name),
                'created_by'     => $received_by,
            ));
        }

        return $updated;
    }

    public static function get_fines_by_group($group_id, $status = null) {
        global $wpdb;
        $table_f = $wpdb->prefix . 'vicoba_fines';
        $table_m = $wpdb->prefix . 'vicoba_members';

        if ($status) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT f.*, m.full_name, m.member_number FROM $table_f f JOIN $table_m m ON f.member_id = m.id WHERE f.group_id = %d AND f.status = %s ORDER BY f.created_at DESC",
                $group_id, $status
            ));
        }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT f.*, m.full_name, m.member_number FROM $table_f f JOIN $table_m m ON f.member_id = m.id WHERE f.group_id = %d ORDER BY f.created_at DESC",
            $group_id
        ));
    }
}
