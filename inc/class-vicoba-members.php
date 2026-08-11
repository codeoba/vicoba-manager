<?php
/**
 * VICOBA Members Manager
 * Profile management, KYC encryption, member directory
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Members {

    // Simple reversible encryption for NIDA storage
    private static function encrypt_nida($nida) {
        if (empty($nida)) return '';
        $key = defined('AUTH_KEY') ? AUTH_KEY : 'vicoba_secret_key';
        return base64_encode(openssl_encrypt($nida, 'AES-128-ECB', $key));
    }

    public static function decrypt_nida($encrypted) {
        if (empty($encrypted)) return '';
        $key = defined('AUTH_KEY') ? AUTH_KEY : 'vicoba_secret_key';
        return openssl_decrypt(base64_decode($encrypted), 'AES-128-ECB', $key);
    }

    public static function add_member($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';

        $nida_enc = self::encrypt_nida($data['nida_number'] ?? '');

        $inserted = $wpdb->insert($table, array(
            'group_id'             => intval($data['group_id']),
            'user_id'              => intval($data['user_id']),
            'member_number'        => sanitize_text_field($data['member_number']),
            'full_name'            => sanitize_text_field($data['full_name']),
            'phone'                => sanitize_text_field($data['phone']),
            'nida_number_encrypted'=> $nida_enc,
            'photo_url'            => esc_url_raw($data['photo_url'] ?? ''),
            'emergency_contact'    => sanitize_text_field($data['emergency_contact'] ?? ''),
            'status'               => sanitize_text_field($data['status'] ?? 'active'),
            'role'                 => sanitize_text_field($data['role'] ?? 'member'),
            'joined_date'          => sanitize_text_field($data['joined_date'] ?? date('Y-m-d')),
            'created_at'           => current_time('mysql'),
        ));

        if ($inserted) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public static function get_member($member_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $member_id));
    }

    public static function get_member_by_user_id($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id = %d", $user_id));
    }

    public static function get_members_by_group($group_id, $status = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        if ($status) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d AND status = %s ORDER BY full_name ASC", $group_id, $status));
        }
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE group_id = %d ORDER BY full_name ASC", $group_id));
    }

    public static function update_status($member_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        return $wpdb->update($table, array('status' => sanitize_text_field($status)), array('id' => $member_id));
    }

    public static function update_role($member_id, $role) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        
        $member = self::get_member($member_id);
        if ($member) {
            // Update WP User role as well
            $user = new WP_User($member->user_id);
            $user->set_role(sanitize_text_field($role));
        }

        return $wpdb->update($table, array('role' => sanitize_text_field($role)), array('id' => $member_id));
    }
}
