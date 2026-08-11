<?php
/**
 * VICOBA Audit Logger
 * Records administrative and operational activities for transparency
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Audit {

    public static function log($action, $group_id = 0, $target_type = '', $target_id = 0, $old_value = '', $new_value = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_audit_log';

        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return $wpdb->insert($table, array(
            'group_id'    => intval($group_id),
            'user_id'     => intval($user_id),
            'action'      => sanitize_text_field($action),
            'target_type' => sanitize_text_field($target_type),
            'target_id'   => intval($target_id),
            'old_value'   => is_array($old_value) ? json_encode($old_value) : sanitize_text_field($old_value),
            'new_value'   => is_array($new_value) ? json_encode($new_value) : sanitize_text_field($new_value),
            'ip_address'  => sanitize_text_field($ip),
            'user_agent'  => sanitize_text_field($ua),
            'created_at'  => current_time('mysql'),
        ));
    }

    public static function get_logs($group_id = 0, $limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_audit_log';
        if ($group_id > 0) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d ORDER BY created_at DESC LIMIT %d", $group_id, $limit));
        }
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY created_at DESC LIMIT %d", $limit));
    }
}
