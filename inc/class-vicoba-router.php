<?php
/**
 * VICOBA Frontend Router
 * Manages clean URLs, rewrite rules, template dispatching, and prevents WP canonical redirects to wp-admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Router {

    public static function init() {
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
        add_action('template_redirect', array(__CLASS__, 'dispatch_templates'), 1);
        add_action('admin_init', array(__CLASS__, 'restrict_admin_access'));
        add_filter('redirect_canonical', array(__CLASS__, 'prevent_canonical_redirect'), 10, 2);
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule('^login/?$', 'index.php?vicoba_route=login', 'top');
        add_rewrite_rule('^register/?$', 'index.php?vicoba_route=register', 'top');
        add_rewrite_rule('^dashboard/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=overview', 'top');
        add_rewrite_rule('^dashboard/([a-zA-Z0-9_-]+)/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=$matches[1]', 'top');
    }

    /**
     * Prevent WordPress canonical redirect from redirecting /dashboard to /wp-admin
     */
    public static function prevent_canonical_redirect($redirect_url, $requested_url) {
        $path = parse_url($requested_url, PHP_URL_PATH);
        if ($path && (strpos($path, '/dashboard') !== false || strpos($path, '/login') !== false || strpos($path, '/register') !== false)) {
            return false;
        }
        return $redirect_url;
    }

    public static function add_query_vars($vars) {
        $vars[] = 'vicoba_route';
        $vars[] = 'vicoba_subroute';
        return $vars;
    }

    public static function dispatch_templates() {
        $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        $route = get_query_var('vicoba_route');
        $subroute = get_query_var('vicoba_subroute');

        // Direct URI inspection fallback to guarantee 100% routing match
        if (empty($route)) {
            if ($request_path === 'login' || strpos($request_path, 'login') === 0) {
                $route = 'login';
            } elseif ($request_path === 'register' || strpos($request_path, 'register') === 0) {
                $route = 'register';
            } elseif ($request_path === 'dashboard' || strpos($request_path, 'dashboard') === 0) {
                $route = 'dashboard';
                $parts = explode('/', $request_path);
                $subroute = isset($parts[1]) && !empty($parts[1]) ? sanitize_text_field($parts[1]) : 'overview';
            }
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
            
            if (empty($subroute)) {
                $subroute = 'overview';
            }
            
            // Set query var for template inclusion
            set_query_var('vicoba_subroute', $subroute);

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

        // Allow Super Admin or standard WP administrator to access wp-admin ONLY if they explicitly navigate to /wp-admin/
        // Non-admin users attempting to access wp-admin are redirected to frontend dashboard
        if (!in_array('administrator', $current_user->roles) && !in_array('super_admin', $current_user->roles)) {
            wp_redirect(home_url('/dashboard/'));
            exit;
        }
    }
}
