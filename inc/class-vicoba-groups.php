<?php
/**
 * VICOBA Groups Manager
 * Multi-Tenancy & Group Configuration Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Groups {

    public static function create_group($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';

        $inserted = $wpdb->insert($table, array(
            'name'                     => sanitize_text_field($data['name']),
            'registration_number'      => sanitize_text_field($data['registration_number'] ?? ''),
            'region'                  => sanitize_text_field($data['region']),
            'district'                => sanitize_text_field($data['district']),
            'established_date'        => sanitize_text_field($data['established_date'] ?? date('Y-m-d')),
            'currency'                => sanitize_text_field($data['currency'] ?? 'TZS'),
            'share_price'             => floatval($data['share_price'] ?? 10000.00),
            'min_shares_per_cycle'    => intval($data['min_shares_per_cycle'] ?? 1),
            'max_shares_per_cycle'    => intval($data['max_shares_per_cycle'] ?? 5),
            'interest_rate'           => floatval($data['interest_rate'] ?? 5.00),
            'interest_type'           => sanitize_text_field($data['interest_type'] ?? 'reducing'),
            'loan_multiplier'         => floatval($data['loan_multiplier'] ?? 3.00),
            'social_fund_contribution'=> floatval($data['social_fund_contribution'] ?? 2000.00),
            'status'                  => 'active',
            'created_at'              => current_time('mysql'),
        ));

        if ($inserted) {
            $group_id = $wpdb->insert_id;
            
            // Seed default fine types
            self::seed_default_fine_types($group_id);
            
            return $group_id;
        }

        return false;
    }

    public static function get_group($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $group_id));
    }

    public static function get_all_groups() {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    }

    public static function update_group($group_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';

        return $wpdb->update(
            $table,
            array(
                'name'                     => sanitize_text_field($data['name']),
                'registration_number'      => sanitize_text_field($data['registration_number']),
                'region'                  => sanitize_text_field($data['region']),
                'district'                => sanitize_text_field($data['district']),
                'share_price'             => floatval($data['share_price']),
                'min_shares_per_cycle'    => intval($data['min_shares_per_cycle']),
                'max_shares_per_cycle'    => intval($data['max_shares_per_cycle']),
                'interest_rate'           => floatval($data['interest_rate']),
                'interest_type'           => sanitize_text_field($data['interest_type']),
                'loan_multiplier'         => floatval($data['loan_multiplier']),
                'social_fund_contribution'=> floatval($data['social_fund_contribution']),
                'constitution_text'       => sanitize_textarea_field($data['constitution_text'] ?? ''),
            ),
            array('id' => $group_id)
        );
    }

    public static function update_status($group_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';
        return $wpdb->update($table, array('status' => sanitize_text_field($status)), array('id' => $group_id));
    }

    private static function seed_default_fine_types($group_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_fine_types';
        
        $defaults = array(
            array('name' => 'Kuchelewa Mkutano (Late arrival)', 'amount' => 1000.00, 'desc' => 'Kufika mkutanoni baada ya muda uliopangwa.'),
            array('name' => 'Kutohudhuria Mkutano (Missed meeting)', 'amount' => 2000.00, 'desc' => 'Kukosa mkutano bila udhuru rasmi.'),
            array('name' => 'Kuchelewa Malipo ya Mkopo (Overdue Loan)', 'amount' => 5000.00, 'desc' => 'Kuchelewesha marejesho ya mkopo kwa tarehe husika.'),
            array('name' => 'Kusababisha Kelele / Simu Kulia (Disturbance)', 'amount' => 500.00, 'desc' => 'Simu kulia au kutoheshimu utaratibu wa mkutano.'),
        );

        foreach ($defaults as $item) {
            $wpdb->insert($table, array(
                'group_id'       => $group_id,
                'name'           => $item['name'],
                'default_amount' => $item['amount'],
                'description'    => $item['desc'],
                'created_at'     => current_time('mysql'),
            ));
        }
    }
}
