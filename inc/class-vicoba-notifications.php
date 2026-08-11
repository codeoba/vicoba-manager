<?php
/**
 * VICOBA Notifications Manager
 * In-app alerts, SMS dispatch (Africa's Talking / Twilio API integration mock)
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Notifications {

    public static function create_notification($group_id, $user_id, $title, $message, $type = 'system') {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_notifications';

        return $wpdb->insert($table, array(
            'group_id'   => intval($group_id),
            'user_id'    => intval($user_id),
            'title'      => sanitize_text_field($title),
            'message'    => sanitize_textarea_field($message),
            'type'       => sanitize_text_field($type),
            'is_read'    => 0,
            'created_at' => current_time('mysql'),
        ));
    }

    public static function get_user_notifications($user_id, $unread_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_notifications';
        if ($unread_only) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d AND is_read = 0 ORDER BY created_at DESC", $user_id));
        }
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 50", $user_id));
    }

    public static function mark_as_read($notification_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_notifications';
        return $wpdb->update($table, array('is_read' => 1), array('id' => $notification_id, 'user_id' => $user_id));
    }

    /**
     * Send SMS Notification (Africa's Talking / Twilio Mock)
     */
    public static function send_sms($phone, $message) {
        // In production, invoke Africa's Talking API / Twilio HTTP endpoint
        // Logs for development & verification
        error_log(sprintf('[VICOBA SMS DISPATCH] To: %s | Message: %s', $phone, $message));
        return true;
    }
}
