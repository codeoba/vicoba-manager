<?php
/**
 * VICOBA REST API Controller
 * Custom API endpoints under /wp-json/vicoba/v1/
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_REST_API {

    private static $namespace = 'vicoba/v1';

    public static function register_routes() {
        // 1. Auth Login
        register_rest_route(self::$namespace, '/auth/login', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_login'),
            'permission_callback'=> '__return_true',
        ));

        // 2. Register Group
        register_rest_route(self::$namespace, '/auth/register-group', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_register_group'),
            'permission_callback'=> '__return_true',
        ));

        // 3. Shares Record
        register_rest_route(self::$namespace, '/shares/record', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_record_shares'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 4. Loans Apply
        register_rest_route(self::$namespace, '/loans/apply', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_apply_loan'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 5. Loan Guarantor Response
        register_rest_route(self::$namespace, '/loans/guarantor-respond', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_guarantor_response'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 6. Loan Disburse
        register_rest_route(self::$namespace, '/loans/disburse', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_disburse_loan'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 7. Loan Repay
        register_rest_route(self::$namespace, '/loans/repay', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_repay_loan'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 8. Fines Issue
        register_rest_route(self::$namespace, '/fines/issue', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_issue_fine'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 9. Fines Pay
        register_rest_route(self::$namespace, '/fines/pay', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_pay_fine'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 10. Meetings Attendance
        register_rest_route(self::$namespace, '/meetings/attendance', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_record_attendance'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 11. Social Fund Request
        register_rest_route(self::$namespace, '/social-fund/request', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_social_request'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 12. Social Fund Approve
        register_rest_route(self::$namespace, '/social-fund/approve', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_social_approve'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 13. Expense Entry
        register_rest_route(self::$namespace, '/ledger/expense', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_add_expense'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));

        // 14. Shareout Finalize
        register_rest_route(self::$namespace, '/shareout/finalize', array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_finalize_shareout'),
            'permission_callback'=> array(__CLASS__, 'check_logged_in'),
        ));
    }

    public static function check_logged_in() {
        return is_user_logged_in();
    }

    public static function handle_login($request) {
        $params = $request->get_json_params();
        $username = sanitize_text_field($params['username'] ?? '');
        $password = $params['password'] ?? '';

        $user = VICOBA_Auth::process_login($username, $password);
        if (is_wp_error($user)) {
            return new WP_REST_Response(array('success' => false, 'message' => $user->get_error_message()), 400);
        }

        return new WP_REST_Response(array(
            'success'      => true,
            'message'      => 'Imefanikiwa kuingia!',
            'redirect_url' => home_url('/dashboard/'),
        ), 200);
    }

    public static function handle_register_group($request) {
        $params = $request->get_json_params();

        $res = VICOBA_Auth::register_group_and_admin($params['group'], $params['admin']);
        if (is_wp_error($res)) {
            return new WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 400);
        }

        return new WP_REST_Response(array(
            'success'      => true,
            'message'      => 'Kikundi na akaunti ya Mwenyekiti vimesajiliwa kikamilifu!',
            'redirect_url' => home_url('/dashboard/'),
        ), 200);
    }

    public static function handle_record_shares($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $member_id = intval($params['member_id']);
        $meeting_id = intval($params['meeting_id'] ?? 0);
        $share_count = intval($params['share_count']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Shares::record_share_purchase($group_id, $member_id, $meeting_id, $share_count, $method, get_current_user_id());
        if (is_wp_error($res)) {
            return new WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 400);
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Hisa zimeingizwa kikamilifu!'), 200);
    }

    public static function handle_apply_loan($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $member_id = intval($params['member_id']);
        $principal = floatval($params['principal_amount']);
        $months = intval($params['repayment_period_months']);
        $purpose = sanitize_textarea_field($params['purpose'] ?? '');
        $guarantors = array_map('intval', $params['guarantor_ids'] ?? array());

        $res = VICOBA_Loans::apply_for_loan($group_id, $member_id, $principal, $months, $purpose, $guarantors);
        if (is_wp_error($res)) {
            return new WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 400);
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Ombi la mkopo limetumwa kikamilifu!'), 200);
    }

    public static function handle_guarantor_response($request) {
        $params = $request->get_json_params();
        $g_id = intval($params['guarantor_id']);
        $status = sanitize_text_field($params['status']);
        $comments = sanitize_textarea_field($params['comments'] ?? '');

        $res = VICOBA_Loans::respond_guarantor($g_id, $status, $comments);
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Majibu ya udhamini yamehifadhiwa!'), 200);
    }

    public static function handle_disburse_loan($request) {
        $params = $request->get_json_params();
        $loan_id = intval($params['loan_id']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Loans::approve_and_disburse_loan($loan_id, get_current_user_id(), $method);
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Mkopo umeidhinishwa na kutolewa kikamilifu!'), 200);
    }

    public static function handle_repay_loan($request) {
        $params = $request->get_json_params();
        $loan_id = intval($params['loan_id']);
        $amount = floatval($params['amount']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Loans::record_repayment($loan_id, $amount, $method, get_current_user_id());
        if (is_wp_error($res)) {
            return new WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 400);
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Rejesho la mkopo limeingizwa kikamilifu!'), 200);
    }

    public static function handle_issue_fine($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $member_id = intval($params['member_id']);
        $type_id = intval($params['fine_type_id']);
        $amount = floatval($params['amount']);
        $reason = sanitize_text_field($params['reason']);

        $res = VICOBA_Fines::issue_fine($group_id, $member_id, $type_id, $amount, $reason, 0, get_current_user_id());
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Faini imewekwa kwa mwanachama!'), 200);
    }

    public static function handle_pay_fine($request) {
        $params = $request->get_json_params();
        $fine_id = intval($params['fine_id']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Fines::pay_fine($fine_id, $method, get_current_user_id());
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Malipo ya faini yamepokelewa!'), 200);
    }

    public static function handle_record_attendance($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $meeting_id = intval($params['meeting_id']);
        $attendance = $params['attendance'];
        $auto_fine = (bool)($params['auto_fine'] ?? true);

        $res = VICOBA_Meetings::record_attendance($group_id, $meeting_id, $attendance, get_current_user_id(), $auto_fine);
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Mahudhurio yamehifadhiwa!'), 200);
    }

    public static function handle_social_request($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $member_id = intval($params['member_id']);
        $amount = floatval($params['amount']);
        $category = sanitize_text_field($params['category']);
        $desc = sanitize_textarea_field($params['description']);

        $res = VICOBA_Social_Fund::request_emergency_payout($group_id, $member_id, $amount, $category, $desc);
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Maombi ya Mfuko wa Jamii yametumwa!'), 200);
    }

    public static function handle_social_approve($request) {
        $params = $request->get_json_params();
        $req_id = intval($params['request_id']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Social_Fund::approve_emergency_payout($req_id, get_current_user_id(), $method);
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Msaada wa Mfuko wa Jamii umeidhinishwa!'), 200);
    }

    public static function handle_add_expense($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);
        $amount = floatval($params['amount']);
        $desc = sanitize_text_field($params['description']);
        $method = sanitize_text_field($params['payment_method'] ?? 'cash');

        $res = VICOBA_Ledger::record_expense($group_id, $amount, $desc, $method, get_current_user_id());
        return new WP_REST_Response(array('success' => (bool)$res, 'message' => 'Gharama ya uendeshaji imerekodiwa!'), 200);
    }

    public static function handle_finalize_shareout($request) {
        $params = $request->get_json_params();
        $group_id = intval($params['group_id']);

        $res = VICOBA_Shareout::finalize_shareout($group_id, get_current_user_id());
        if (is_wp_error($res)) {
            return new WP_REST_Response(array('success' => false, 'message' => $res->get_error_message()), 400);
        }

        return new WP_REST_Response(array('success' => true, 'message' => 'Mgawanyo wa Share-Out umekamilika na kufungwa kikamilifu!'), 200);
    }
}
