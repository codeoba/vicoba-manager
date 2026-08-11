<?php
/**
 * VICOBA Meetings Manager
 * Meeting scheduler, agenda, attendance tracking with auto-fines, and minutes
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Meetings {

    public static function create_meeting($group_id, $date, $location, $agenda, $created_by) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_meetings';

        // Get next meeting number for group
        $last_num = $wpdb->get_var($wpdb->prepare("SELECT MAX(meeting_number) FROM $table WHERE group_id = %d", $group_id));
        $next_num = intval($last_num) + 1;

        $inserted = $wpdb->insert($table, array(
            'group_id'       => $group_id,
            'meeting_number' => $next_num,
            'meeting_date'   => sanitize_text_field($date),
            'location'       => sanitize_text_field($location),
            'agenda'         => sanitize_textarea_field($agenda),
            'status'         => 'scheduled',
            'created_by'     => $created_by,
            'created_at'     => current_time('mysql'),
        ));

        return $inserted ? $wpdb->insert_id : false;
    }

    public static function record_attendance($group_id, $meeting_id, $attendance_data, $recorded_by, $auto_fine_absent = true) {
        global $wpdb;
        $table_att = $wpdb->prefix . 'vicoba_attendance';

        // Get missed-meeting fine type ID if available
        $fine_type_id = 0;
        if ($auto_fine_absent) {
            $table_ft = $wpdb->prefix . 'vicoba_fine_types';
            $fine_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_ft WHERE group_id = %d AND name LIKE %s LIMIT 1", $group_id, '%Missed%'));
            if ($fine_type) {
                $fine_type_id = $fine_type->id;
                $fine_amount = $fine_type->default_amount;
            } else {
                $fine_amount = 2000.00; // fallback default
            }
        }

        foreach ($attendance_data as $item) {
            $member_id = intval($item['member_id']);
            $status = sanitize_text_field($item['status']); // present, absent, excused

            // Check if record exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_att WHERE meeting_id = %d AND member_id = %d",
                $meeting_id, $member_id
            ));

            $fine_applied = 0;
            if ($status === 'absent' && $auto_fine_absent) {
                $fine_applied = 1;
                VICOBA_Fines::issue_fine(
                    $group_id,
                    $member_id,
                    $fine_type_id,
                    $fine_amount,
                    sprintf('Kutohudhuria Mkutano #%d', $meeting_id),
                    $meeting_id,
                    $recorded_by
                );
            }

            if ($existing) {
                $wpdb->update($table_att, array(
                    'status'       => $status,
                    'fine_applied' => $fine_applied,
                    'recorded_by'  => $recorded_by,
                ), array('id' => $existing->id));
            } else {
                $wpdb->insert($table_att, array(
                    'group_id'     => $group_id,
                    'meeting_id'   => $meeting_id,
                    'member_id'    => $member_id,
                    'status'       => $status,
                    'fine_applied' => $fine_applied,
                    'recorded_by'  => $recorded_by,
                    'created_at'   => current_time('mysql'),
                ));
            }
        }

        return true;
    }

    public static function update_minutes($meeting_id, $minutes, $status = 'completed') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_meetings';
        return $wpdb->update($table, array(
            'minutes' => sanitize_textarea_field($minutes),
            'status'  => sanitize_text_field($status),
        ), array('id' => $meeting_id));
    }

    public static function get_meetings_by_group($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_meetings';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d ORDER BY meeting_date DESC", $group_id));
    }

    public static function get_meeting_attendance($meeting_id) {
        global $wpdb;
        $table_att = $wpdb->prefix . 'vicoba_attendance';
        $table_m = $wpdb->prefix . 'vicoba_members';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, m.full_name, m.member_number FROM $table_att a JOIN $table_m m ON a.member_id = m.id WHERE a.meeting_id = %d ORDER BY m.full_name ASC",
            $meeting_id
        ));
    }
}
