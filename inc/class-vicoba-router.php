<?php
/**
 * VICOBA Frontend Router
 * Manages clean URLs, rewrite rules, template dispatching, and wp-admin redirection
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Router {

    public static function init() {
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        add_action('template_redirect', array(__CLASS__, 'dispatch_templates'));
        add_action('admin_init', array(__CLASS__, 'restrict_admin_access'));
        add_action('wp_loaded', array(__CLASS__, 'auto_flush_rules'));
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule('^login/?$', 'index.php?vicoba_route=login', 'top');
        add_rewrite_rule('^register/?$', 'index.php?vicoba_route=register', 'top');
        add_rewrite_rule('^dashboard/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=overview', 'top');
        add_rewrite_rule('^dashboard/([a-zA-Z0-9_-]+)/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=$matches[1]', 'top');
    }

    public static function auto_flush_rules() {
        $rules = get_option('rewrite_rules');
        if (!isset($rules['^dashboard/?$']) || !isset($rules['^login/?$'])) {
            self::add_rewrite_rules();
            flush_rewrite_rules(false);
        }
    }

    public static function add_query_vars($vars) {
        $vars[] = 'vicoba_route';
        $vars[] = 'vicoba_subroute';
        return $vars;
    }

    public static function dispatch_templates() {
        $route = get_query_var('vicoba_route');
        $subroute = get_query_var('vicoba_subroute');

        // Fallback to $_GET parameters if query_var is empty (e.g. before rewrite rules flush)
        if (empty($route) && isset($_GET['vicoba_route'])) {
            $route = sanitize_text_field($_GET['vicoba_route']);
        }
        if (empty($subroute) && isset($_GET['vicoba_subroute'])) {
            $subroute = sanitize_text_field($_GET['vicoba_subroute']);
        }

        if (empty($route)) {
            return;
        }

        if ($route === 'login') {
            if (is_user_logged_in()) {
                wp_redirect(home_url('/dashboard/'));
                exit;
            }
            include get_template_directory() . '/template-parts/auth/login.php';
            exit;
        }

        if ($route === 'register') {
            if (is_user_logged_in()) {
                wp_redirect(home_url('/dashboard/'));
                exit;
            }
            include get_template_directory() . '/template-parts/auth/register.php';
            exit;
        }

        if ($route === 'dashboard') {
            if (!is_user_logged_in()) {
                wp_redirect(home_url('/login/'));
                exit;
            }
            
            // Set default subroute if empty
            if (empty($subroute)) {
                $subroute = 'overview';
            }
            
            include get_template_directory() . '/template-parts/dashboard/layout.php';
            exit;
        }
    }

    public static function restrict_admin_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->ID) {
            return;
        }

        // Allow Super Admin or standard WP administrator to access wp-admin
        if (in_array('administrator', $current_user->roles) || in_array('super_admin', $current_user->roles)) {
            return;
        }

        // Redirect all regular VICOBA members, treasurers, secretaries, group admins to frontend dashboard
        wp_redirect(home_url('/dashboard/'));
        exit;
    }
}
