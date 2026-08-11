<?php
/**
 * Vicoba Manager Theme Functions & Main Bootstrap File
 * Version: 2.1 — Full feature set with Cron, Export, NIDA Encryption, Notifications Bell
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================
// 1. INCLUDE ALL CORE OOP CLASSES
// ============================================================
require_once get_template_directory() . '/inc/class-vicoba-db.php';
require_once get_template_directory() . '/inc/class-vicoba-roles.php';
require_once get_template_directory() . '/inc/class-vicoba-groups.php';
require_once get_template_directory() . '/inc/class-vicoba-members.php';
require_once get_template_directory() . '/inc/class-vicoba-shares.php';
require_once get_template_directory() . '/inc/class-vicoba-loans.php';
require_once get_template_directory() . '/inc/class-vicoba-fines.php';
require_once get_template_directory() . '/inc/class-vicoba-meetings.php';
require_once get_template_directory() . '/inc/class-vicoba-social-fund.php';
require_once get_template_directory() . '/inc/class-vicoba-ledger.php';
require_once get_template_directory() . '/inc/class-vicoba-shareout.php';
require_once get_template_directory() . '/inc/class-vicoba-reports.php';
require_once get_template_directory() . '/inc/class-vicoba-audit.php';
require_once get_template_directory() . '/inc/class-vicoba-notifications.php';
require_once get_template_directory() . '/inc/class-vicoba-auth.php';
require_once get_template_directory() . '/inc/class-vicoba-router.php';
require_once get_template_directory() . '/inc/class-vicoba-rest-api.php';
require_once get_template_directory() . '/inc/class-vicoba-cron.php';
require_once get_template_directory() . '/inc/class-vicoba-export.php';

// ============================================================
// 2. THEME SUPPORT
// ============================================================
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

// ============================================================
// 3. INIT — DB + ROLES + ROUTER + CRON + EXPORT
// ============================================================
add_action('init', function() {
    VICOBA_DB::init_db();
    VICOBA_Roles::setup_roles();
    VICOBA_Router::init();
    VICOBA_Cron::init();

    // Handle CSV export download requests
    VICOBA_Export::handle_download_request();

    // Block wp-admin access for non-super-admins
    if (is_admin() && !wp_doing_ajax()) {
        $user = wp_get_current_user();
        if ($user->ID && !in_array('administrator', $user->roles) && !in_array('super_admin', $user->roles)) {
            wp_redirect(VICOBA_Router::get_url('dashboard'));
            exit;
        }
    }
}, 5);

// ============================================================
// 4. REST API ROUTES
// ============================================================
add_action('rest_api_init', function() {
    VICOBA_REST_API::register_routes();
});

// ============================================================
// 5. ENQUEUE SCRIPTS & STYLES
// ============================================================
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('vicoba-theme-style', get_stylesheet_uri(), [], '2.1.0');
    wp_enqueue_style('vicoba-custom-css', get_template_directory_uri() . '/assets/css/vicoba-style.css', [], '2.1.0');
    wp_enqueue_script('vicoba-app-js', get_template_directory_uri() . '/assets/js/vicoba-app.js', ['jquery'], '2.1.0', true);

    $current_user = wp_get_current_user();
    $member       = VICOBA_Members::get_member_by_user_id($current_user->ID);
    $group        = $member ? VICOBA_Groups::get_group($member->group_id) : null;

    // Unread notifications count
    $unread_count = 0;
    if ($current_user->ID) {
        $unread_notifs = VICOBA_Notifications::get_user_notifications($current_user->ID, true);
        $unread_count  = count($unread_notifs);
    }

    wp_localize_script('vicoba-app-js', 'vicobaData', [
        'root'          => esc_url_raw(rest_url()),
        'nonce'         => wp_create_nonce('wp_rest'),
        'ajax_url'      => admin_url('admin-ajax.php'),
        'export_url'    => add_query_arg(['vicoba_export' => ''], home_url('/')),
        'unread_count'  => $unread_count,
        'user'          => [
            'id'        => $current_user->ID,
            'name'      => $current_user->display_name,
            'roles'     => $current_user->roles,
            'member_id' => $member ? $member->id        : 0,
            'group_id'  => $member ? $member->group_id  : 0,
            'currency'  => $group  ? $group->currency   : 'TZS',
        ],
        'group'         => $group ? [
            'id'             => $group->id,
            'name'           => $group->name,
            'share_price'    => $group->share_price,
            'interest_rate'  => $group->interest_rate ?? $group->loan_interest_rate ?? 10,
            'loan_multiplier'=> $group->loan_multiplier ?? $group->max_loan_multiplier ?? 3,
        ] : null,
    ]);
});

// ============================================================
// 6. REWRITE RULES — flush on theme activation
// ============================================================
add_action('after_switch_theme', function() {
    VICOBA_Router::add_rewrite_rules();
    flush_rewrite_rules();
    // Seed default fine types for any existing group
    global $wpdb;
    $groups = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}vicoba_groups");
    foreach ($groups as $g) {
        VICOBA_Fines::seed_default_fine_types($g->id);
    }
});

// ============================================================
// 7. CLEAN UP CRON ON THEME DEACTIVATION
// ============================================================
add_action('switch_theme', function() {
    VICOBA_Cron::deactivate();
});

// ============================================================
// 8. LOGIN PAGE REDIRECT — block wp-login for non-admins
// ============================================================
add_filter('login_url', function($login_url) {
    return VICOBA_Router::get_url('login');
});
