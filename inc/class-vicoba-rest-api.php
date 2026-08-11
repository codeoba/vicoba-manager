<?php
/**
 * VICOBA REST API Controller - Complete
 * All endpoints: auth, members, shares, loans, fines, meetings, social fund, ledger, shareout, settings
 */

if (!defined('ABSPATH')) exit;

class VICOBA_REST_API {

    private static $namespace = 'vicoba/v1';

    public static function register_routes() {
        // --- AUTH ---
        register_rest_route(self::$namespace, '/auth/login',            ['methods'=>'POST','callback'=>[__CLASS__,'handle_login'],'permission_callback'=>'__return_true']);
        register_rest_route(self::$namespace, '/auth/register-group',   ['methods'=>'POST','callback'=>[__CLASS__,'handle_register_group'],'permission_callback'=>'__return_true']);

        // --- MEMBERS ---
        register_rest_route(self::$namespace, '/members/add',           ['methods'=>'POST','callback'=>[__CLASS__,'handle_add_member'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/members/update',        ['methods'=>'POST','callback'=>[__CLASS__,'handle_update_member'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/members/update-status', ['methods'=>'POST','callback'=>[__CLASS__,'handle_update_member_status'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/members/update-role',   ['methods'=>'POST','callback'=>[__CLASS__,'handle_update_member_role'],'permission_callback'=>[__CLASS__,'check_admin']]);

        // --- SHARES ---
        register_rest_route(self::$namespace, '/shares/record',         ['methods'=>'POST','callback'=>[__CLASS__,'handle_record_shares'],'permission_callback'=>[__CLASS__,'check_logged_in']]);

        // --- LOANS ---
        register_rest_route(self::$namespace, '/loans/apply',           ['methods'=>'POST','callback'=>[__CLASS__,'handle_apply_loan'],'permission_callback'=>[__CLASS__,'check_logged_in']]);
        register_rest_route(self::$namespace, '/loans/guarantor-respond',['methods'=>'POST','callback'=>[__CLASS__,'handle_guarantor_response'],'permission_callback'=>[__CLASS__,'check_logged_in']]);
        register_rest_route(self::$namespace, '/loans/disburse',        ['methods'=>'POST','callback'=>[__CLASS__,'handle_disburse_loan'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/loans/repay',           ['methods'=>'POST','callback'=>[__CLASS__,'handle_repay_loan'],'permission_callback'=>[__CLASS__,'check_logged_in']]);

        // --- FINES ---
        register_rest_route(self::$namespace, '/fines/issue',           ['methods'=>'POST','callback'=>[__CLASS__,'handle_issue_fine'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/fines/pay',             ['methods'=>'POST','callback'=>[__CLASS__,'handle_pay_fine'],'permission_callback'=>[__CLASS__,'check_logged_in']]);

        // --- MEETINGS ---
        register_rest_route(self::$namespace, '/meetings/create',       ['methods'=>'POST','callback'=>[__CLASS__,'handle_create_meeting'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/meetings/attendance',   ['methods'=>'POST','callback'=>[__CLASS__,'handle_record_attendance'],'permission_callback'=>[__CLASS__,'check_admin']]);

        // --- SOCIAL FUND ---
        register_rest_route(self::$namespace, '/social-fund/request',   ['methods'=>'POST','callback'=>[__CLASS__,'handle_social_request'],'permission_callback'=>[__CLASS__,'check_logged_in']]);
        register_rest_route(self::$namespace, '/social-fund/approve',   ['methods'=>'POST','callback'=>[__CLASS__,'handle_social_approve'],'permission_callback'=>[__CLASS__,'check_admin']]);
        register_rest_route(self::$namespace, '/social-fund/contribute',['methods'=>'POST','callback'=>[__CLASS__,'handle_social_contribute'],'permission_callback'=>[__CLASS__,'check_logged_in']]);

        // --- LEDGER ---
        register_rest_route(self::$namespace, '/ledger/expense',        ['methods'=>'POST','callback'=>[__CLASS__,'handle_add_expense'],'permission_callback'=>[__CLASS__,'check_admin']]);

        // --- SHAREOUT ---
        register_rest_route(self::$namespace, '/shareout/finalize',     ['methods'=>'POST','callback'=>[__CLASS__,'handle_finalize_shareout'],'permission_callback'=>[__CLASS__,'check_admin']]);

        // --- SETTINGS ---
        register_rest_route(self::$namespace, '/settings/update',       ['methods'=>'POST','callback'=>[__CLASS__,'handle_update_settings'],'permission_callback'=>[__CLASS__,'check_admin']]);

        // --- SUPER ADMIN ---
        register_rest_route(self::$namespace, '/superadmin/groups',     ['methods'=>'GET','callback'=>[__CLASS__,'handle_get_groups'],'permission_callback'=>[__CLASS__,'check_super_admin']]);
        register_rest_route(self::$namespace, '/superadmin/group-status',['methods'=>'POST','callback'=>[__CLASS__,'handle_group_status'],'permission_callback'=>[__CLASS__,'check_super_admin']]);
    }

    /* ============ PERMISSION CHECKS ============ */

    public static function check_logged_in() {
        return is_user_logged_in();
    }

    public static function check_admin() {
        if (!is_user_logged_in()) return false;
        $user = wp_get_current_user();
        return !empty(array_intersect($user->roles, ['super_admin','group_admin','secretary','treasurer','administrator']));
    }

    public static function check_super_admin() {
        if (!is_user_logged_in()) return false;
        $user = wp_get_current_user();
        return !empty(array_intersect($user->roles, ['super_admin','administrator']));
    }

    /* ============ AUTH ============ */

    public static function handle_login($request) {
        $p = $request->get_json_params();
        $user = VICOBA_Auth::process_login(
            sanitize_text_field($p['username'] ?? ''),
            $p['password'] ?? ''
        );
        if (is_wp_error($user)) {
            return new WP_REST_Response(['success'=>false,'message'=>$user->get_error_message()], 400);
        }
        return new WP_REST_Response([
            'success'      => true,
            'message'      => 'Imefanikiwa kuingia!',
            'redirect_url' => home_url('/?vicoba_route=dashboard'),
        ], 200);
    }

    public static function handle_register_group($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Auth::register_group_and_admin($p['group'] ?? [], $p['admin'] ?? []);
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response([
            'success'      => true,
            'message'      => 'Kikundi na akaunti ya Mwenyekiti vimesajiliwa!',
            'redirect_url' => home_url('/?vicoba_route=login'),
        ], 200);
    }

    /* ============ MEMBERS ============ */

    public static function handle_add_member($request) {
        $p = $request->get_json_params();

        // 1. Create WP User
        $user_id = wp_create_user(
            sanitize_text_field($p['username'] ?? ''),
            $p['password'] ?? wp_generate_password(),
            sanitize_email($p['email'] ?? '')
        );
        if (is_wp_error($user_id)) {
            return new WP_REST_Response(['success'=>false,'message'=>$user_id->get_error_message()], 400);
        }

        // Assign WP role
        $wp_user = new WP_User($user_id);
        $wp_user->set_role(sanitize_text_field($p['role'] ?? 'member'));

        // 2. Get current user's group
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        // 3. Generate member number
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vicoba_members WHERE group_id = %d", $group_id
        ));
        $member_number = 'MEM-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        // 4. Insert member record
        $member_id = VICOBA_Members::add_member([
            'group_id'      => $group_id,
            'user_id'       => $user_id,
            'member_number' => $member_number,
            'full_name'     => sanitize_text_field($p['full_name'] ?? ''),
            'phone'         => sanitize_text_field($p['phone'] ?? ''),
            'nida_number'   => sanitize_text_field($p['nida_number'] ?? ''),
            'role'          => sanitize_text_field($p['role'] ?? 'member'),
            'status'        => 'active',
            'joined_date'   => date('Y-m-d'),
        ]);

        if (!$member_id) {
            wp_delete_user($user_id);
            return new WP_REST_Response(['success'=>false,'message'=>'Imeshindwa kuongeza mwanachama kwenye database.'], 500);
        }

        return new WP_REST_Response(['success'=>true,'message'=>'Mwanachama ameongezwa kikamilifu! Namba: ' . $member_number], 200);
    }

    public static function handle_update_member($request) {
        $p = $request->get_json_params();
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_members';

        $wpdb->update($table, [
            'full_name' => sanitize_text_field($p['full_name'] ?? ''),
            'phone'     => sanitize_text_field($p['phone'] ?? ''),
            'role'      => sanitize_text_field($p['role'] ?? 'member'),
        ], ['id' => intval($p['member_id'])]);

        return new WP_REST_Response(['success'=>true,'message'=>'Taarifa za mwanachama zimesasishwa!'], 200);
    }

    public static function handle_update_member_status($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Members::update_status(intval($p['member_id']), sanitize_text_field($p['status']));
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Hali ya mwanachama imebadilishwa!'], 200);
    }

    public static function handle_update_member_role($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Members::update_role(intval($p['member_id']), sanitize_text_field($p['role']));
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Jukumu la mwanachama limebadilishwa!'], 200);
    }

    /* ============ SHARES ============ */

    public static function handle_record_shares($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Shares::record_share_purchase(
            $group_id,
            intval($p['member_id']),
            intval($p['meeting_id'] ?? 0),
            intval($p['share_count']),
            sanitize_text_field($p['payment_method'] ?? 'cash'),
            get_current_user_id()
        );
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response(['success'=>true,'message'=>'Hisa zimeingizwa kikamilifu!'], 200);
    }

    /* ============ LOANS ============ */

    public static function handle_apply_loan($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Loans::apply_for_loan(
            $group_id,
            intval($p['member_id']),
            floatval($p['principal_amount']),
            intval($p['repayment_period_months']),
            sanitize_textarea_field($p['purpose'] ?? ''),
            array_map('intval', $p['guarantor_ids'] ?? [])
        );
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response(['success'=>true,'message'=>'Ombi la mkopo limetumwa! Linasubiri idhini ya wadhamini.'], 200);
    }

    public static function handle_guarantor_response($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Loans::respond_guarantor(
            intval($p['guarantor_id']),
            sanitize_text_field($p['status']),
            sanitize_textarea_field($p['comments'] ?? '')
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Jibu la udhamini limehifadhiwa!'], 200);
    }

    public static function handle_disburse_loan($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Loans::approve_and_disburse_loan(
            intval($p['loan_id']),
            get_current_user_id(),
            sanitize_text_field($p['payment_method'] ?? 'cash')
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Mkopo umeidhinishwa na kutolewa!'], 200);
    }

    public static function handle_repay_loan($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Loans::record_repayment(
            intval($p['loan_id']),
            floatval($p['amount']),
            sanitize_text_field($p['payment_method'] ?? 'cash'),
            get_current_user_id()
        );
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response(['success'=>true,'message'=>'Rejesho la mkopo limeingizwa!'], 200);
    }

    /* ============ FINES ============ */

    public static function handle_issue_fine($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Fines::issue_fine(
            $group_id,
            intval($p['member_id']),
            intval($p['fine_type_id'] ?? 0),
            floatval($p['amount']),
            sanitize_text_field($p['reason']),
            intval($p['meeting_id'] ?? 0),
            get_current_user_id()
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Faini imewekwa kwa mwanachama!'], 200);
    }

    public static function handle_pay_fine($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Fines::pay_fine(
            intval($p['fine_id']),
            sanitize_text_field($p['payment_method'] ?? 'cash'),
            get_current_user_id()
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Malipo ya faini yamepokelewa!'], 200);
    }

    /* ============ MEETINGS ============ */

    public static function handle_create_meeting($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Meetings::create_meeting([
            'group_id'          => $group_id,
            'meeting_date'      => sanitize_text_field($p['meeting_date']),
            'meeting_type'      => sanitize_text_field($p['meeting_type'] ?? 'regular'),
            'venue'             => sanitize_text_field($p['venue'] ?? ''),
            'agenda'            => sanitize_textarea_field($p['agenda'] ?? ''),
            'recorded_by'       => get_current_user_id(),
        ]);
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response(['success'=>true,'message'=>'Mkutano umeandikishwa!','meeting_id'=>$res], 200);
    }

    public static function handle_record_attendance($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Meetings::record_attendance(
            $group_id,
            intval($p['meeting_id']),
            $p['attendance'] ?? [],
            get_current_user_id(),
            (bool)($p['auto_fine'] ?? true)
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Mahudhurio yamehifadhiwa!'], 200);
    }

    /* ============ SOCIAL FUND ============ */

    public static function handle_social_request($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Social_Fund::request_emergency_payout(
            $group_id,
            intval($p['member_id']),
            floatval($p['amount']),
            sanitize_text_field($p['category']),
            sanitize_textarea_field($p['description'] ?? '')
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Maombi ya Mfuko wa Jamii yametumwa kwa idhini!'], 200);
    }

    public static function handle_social_approve($request) {
        $p = $request->get_json_params();
        $res = VICOBA_Social_Fund::approve_emergency_payout(
            intval($p['request_id']),
            get_current_user_id(),
            sanitize_text_field($p['payment_method'] ?? 'cash')
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Msaada wa Mfuko wa Jamii umeidhinishwa na kutolewa!'], 200);
    }

    public static function handle_social_contribute($request) {
        $p = $request->get_json_params();
        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_social_fund_contributions';
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : 1;

        $res = $wpdb->insert($table, [
            'group_id'       => $group_id,
            'member_id'      => intval($p['member_id']),
            'amount'         => floatval($p['amount']),
            'payment_method' => sanitize_text_field($p['payment_method'] ?? 'cash'),
            'payment_date'   => date('Y-m-d'),
            'recorded_by'    => get_current_user_id(),
        ]);
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Michango ya Mfuko wa Jamii imehifadhiwa!'], 200);
    }

    /* ============ LEDGER ============ */

    public static function handle_add_expense($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Ledger::record_expense(
            $group_id,
            floatval($p['amount']),
            sanitize_text_field($p['description']),
            sanitize_text_field($p['payment_method'] ?? 'cash'),
            get_current_user_id()
        );
        return new WP_REST_Response(['success'=>(bool)$res,'message'=>'Gharama imeingizwa kwenye Ledger!'], 200);
    }

    /* ============ SHAREOUT ============ */

    public static function handle_finalize_shareout($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        $res = VICOBA_Shareout::finalize_shareout($group_id, get_current_user_id());
        if (is_wp_error($res)) {
            return new WP_REST_Response(['success'=>false,'message'=>$res->get_error_message()], 400);
        }
        return new WP_REST_Response(['success'=>true,'message'=>'Mgawanyo wa Share-Out umekamilika kikamilifu!'], 200);
    }

    /* ============ SETTINGS ============ */

    public static function handle_update_settings($request) {
        $p = $request->get_json_params();
        $current_member = VICOBA_Members::get_member_by_user_id(get_current_user_id());
        $group_id = $current_member ? $current_member->group_id : intval($p['group_id'] ?? 1);

        global $wpdb;
        $table = $wpdb->prefix . 'vicoba_groups';
        $res = $wpdb->update($table, [
            'name'                  => sanitize_text_field($p['name'] ?? ''),
            'description'           => sanitize_textarea_field($p['description'] ?? ''),
            'share_price'           => floatval($p['share_price'] ?? 10000),
            'min_shares_per_cycle'  => intval($p['min_shares_per_cycle'] ?? 1),
            'max_shares_per_cycle'  => intval($p['max_shares_per_cycle'] ?? 5),
            'loan_interest_rate'    => floatval($p['loan_interest_rate'] ?? 10),
            'loan_interest_type'    => sanitize_text_field($p['loan_interest_type'] ?? 'flat'),
            'max_loan_multiplier'   => floatval($p['max_loan_multiplier'] ?? 3),
            'social_fund_per_cycle' => floatval($p['social_fund_per_cycle'] ?? 2000),
            'meeting_fine_amount'   => floatval($p['meeting_fine_amount'] ?? 1000),
            'currency'              => sanitize_text_field($p['currency'] ?? 'TZS'),
            'cycle_start_date'      => sanitize_text_field($p['cycle_start_date'] ?? date('Y-01-01')),
        ], ['id' => $group_id]);

        return new WP_REST_Response(['success'=>(false !== $res),'message'=>'Mipangilio ya kikundi imehifadhiwa!'], 200);
    }

    /* ============ SUPER ADMIN ============ */

    public static function handle_get_groups() {
        global $wpdb;
        $groups = $wpdb->get_results("SELECT g.*, COUNT(m.id) as member_count FROM {$wpdb->prefix}vicoba_groups g LEFT JOIN {$wpdb->prefix}vicoba_members m ON g.id = m.group_id GROUP BY g.id ORDER BY g.created_at DESC");
        return new WP_REST_Response(['success'=>true,'groups'=>$groups], 200);
    }

    public static function handle_group_status($request) {
        $p = $request->get_json_params();
        global $wpdb;
        $res = $wpdb->update(
            $wpdb->prefix . 'vicoba_groups',
            ['status' => sanitize_text_field($p['status'])],
            ['id' => intval($p['group_id'])]
        );
        return new WP_REST_Response(['success'=>(false !== $res),'message'=>'Hali ya kikundi imebadilishwa!'], 200);
    }
}
