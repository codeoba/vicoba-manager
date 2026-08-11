<?php
/**
 * VICOBA Authentication & Registration
 * Frontend Login, Group Registration, Member Onboarding, and 2FA OTP simulation
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Auth {

    public static function process_login($username, $password) {
        $creds = array(
            'user_login'    => sanitize_text_field($username),
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            return $user;
        }

        // Return logged in user
        return $user;
    }

    public static function register_group_and_admin($group_data, $admin_data) {
        // 1. Create Group first
        $group_id = VICOBA_Groups::create_group($group_data);
        if (!$group_id) {
            return new WP_Error('group_creation_failed', __('Imeshindikana kusajili kikundi.', 'vicoba-manager'));
        }

        // 2. Check if username or email exists
        $user_login = sanitize_user($admin_data['username']);
        $email = sanitize_email($admin_data['email']);

        if (username_exists($user_login)) {
            return new WP_Error('user_exists', __('Jina la mtumiaji (username) limeshatumika.', 'vicoba-manager'));
        }

        if (email_exists($email)) {
            return new WP_Error('email_exists', __('Barua pepe (email) imeshatumika.', 'vicoba-manager'));
        }

        // 3. Create WP User with group_admin role
        $user_id = wp_create_user($user_login, $admin_data['password'], $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user = new WP_User($user_id);
        $user->set_role('group_admin');

        // 4. Create Member Record for Chairman/Admin
        $member_id = VICOBA_Members::add_member(array(
            'group_id'          => $group_id,
            'user_id'           => $user_id,
            'member_number'     => 'MEM-001',
            'full_name'         => sanitize_text_field($admin_data['full_name']),
            'phone'             => sanitize_text_field($admin_data['phone']),
            'nida_number'       => sanitize_text_field($admin_data['nida_number'] ?? ''),
            'emergency_contact' => sanitize_text_field($admin_data['emergency_contact'] ?? ''),
            'status'            => 'active',
            'role'              => 'group_admin',
            'joined_date'       => date('Y-m-d'),
        ));

        // Auto login
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        VICOBA_Audit::log('group_registered', $group_id, 'group', $group_id, '', $group_data['name']);

        return array(
            'group_id'  => $group_id,
            'user_id'   => $user_id,
            'member_id' => $member_id,
        );
    }

    public static function register_member_by_admin($group_id, $member_data) {
        $user_login = sanitize_user($member_data['username']);
        $email = sanitize_email($member_data['email']);

        if (username_exists($user_login)) {
            return new WP_Error('user_exists', __('Jina la mtumiaji limeshatumika.', 'vicoba-manager'));
        }

        $user_id = wp_create_user($user_login, $member_data['password'], $email);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $role = sanitize_text_field($member_data['role'] ?? 'member');
        $user = new WP_User($user_id);
        $user->set_role($role);

        // Generate sequential member number
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE group_id = %d", $group_id));
        $m_num = 'MEM-' . str_pad(intval($count) + 1, 3, '0', STR_PAD_LEFT);

        $member_id = VICOBA_Members::add_member(array(
            'group_id'          => $group_id,
            'user_id'           => $user_id,
            'member_number'     => $m_num,
            'full_name'         => sanitize_text_field($member_data['full_name']),
            'phone'             => sanitize_text_field($member_data['phone']),
            'nida_number'       => sanitize_text_field($member_data['nida_number'] ?? ''),
            'emergency_contact' => sanitize_text_field($member_data['emergency_contact'] ?? ''),
            'status'            => 'active',
            'role'              => $role,
            'joined_date'       => date('Y-m-d'),
        ));

        VICOBA_Audit::log('member_registered', $group_id, 'member', $member_id, '', $member_data['full_name']);

        return $member_id;
    }
}
