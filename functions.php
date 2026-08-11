<?php
/**
 * Vicoba Manager Theme Functions & Main Bootstrap File
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include required core OOP classes
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

// Initialize Core Setup
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

// Setup DB, Roles, and Router on Init (Priority 5)
add_action('init', function() {
    VICOBA_DB::init_db();
    VICOBA_Roles::setup_roles();
    VICOBA_Router::init();
}, 5);

// Register REST API Routes
add_action('rest_api_init', function() {
    VICOBA_REST_API::register_routes();
});

// Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('vicoba-theme-style', get_stylesheet_uri(), array(), '1.0.1');
    wp_enqueue_style('vicoba-custom-css', get_template_directory_uri() . '/assets/css/vicoba-style.css', array(), '1.0.1');

    wp_enqueue_script('vicoba-app-js', get_template_directory_uri() . '/assets/js/vicoba-app.js', array('jquery'), '1.0.1', true);

    $current_user = wp_get_current_user();
    $member = VICOBA_Members::get_member_by_user_id($current_user->ID);

    wp_localize_script('vicoba-app-js', 'vicobaData', array(
        'root'      => esc_url_raw(rest_url()),
        'nonce'     => wp_create_nonce('wp_rest'),
        'user'      => array(
            'id'        => $current_user->ID,
            'name'      => $current_user->display_name,
            'roles'     => $current_user->roles,
            'member_id' => $member ? $member->id : 0,
            'group_id'  => $member ? $member->group_id : 0,
        )
    ));
});

// Flush rewrite rules on theme activation
add_action('after_switch_theme', function() {
    VICOBA_Router::add_rewrite_rules();
    flush_rewrite_rules();
});
