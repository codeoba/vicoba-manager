<?php
/**
 * VICOBA Frontend Router
 * Intercepts /dashboard, /login, /register early in WP lifecycle (parse_request)
 * Completely eliminates WP canonical redirects to /wp-admin/
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Router {

    public static function init() {
        // Intercept routes on parse_request & init
        add_action('parse_request', array(__CLASS__, 'intercept_custom_routes'), 1);
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));
        add_action('admin_init', array(__CLASS__, 'restrict_admin_access'));
        
        // Remove WP default canonical redirect for custom frontend routes
        remove_action('template_redirect', 'redirect_canonical');
        add_filter('redirect_canonical', '__return_false');
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule('^login/?$', 'index.php?vicoba_route=login', 'top');
        add_rewrite_rule('^register/?$', 'index.php?vicoba_route=register', 'top');
        add_rewrite_rule('^dashboard/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=overview', 'top');
        add_rewrite_rule('^dashboard/([a-zA-Z0-9_-]+)/?$', 'index.php?vicoba_route=dashboard&vicoba_subroute=$matches[1]', 'top');
    }

    public static function intercept_custom_routes() {
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');
        $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');

        if (!empty($site_path) && strpos($request_path, $site_path) === 0) {
            $request_path = trim(substr($request_path, strlen($site_path)), '/');
        }

        $segments = explode('/', $request_path);
        $first_segment = strtolower($segments[0] ?? '');

        if ($first_segment === 'dashboard' || $first_segment === 'login' || $first_segment === 'register') {
            
            if ($first_segment === 'login') {
                if (is_user_logged_in()) {
                    wp_redirect(home_url('/dashboard/'));
                    exit;
                }
                include get_template_directory() . '/template-parts/auth/login.php';
                exit;
            }

            if ($first_segment === 'register') {
                if (is_user_logged_in()) {
                    wp_redirect(home_url('/dashboard/'));
                    exit;
                }
                include get_template_directory() . '/template-parts/auth/register.php';
                exit;
            }

            if ($first_segment === 'dashboard') {
                if (!is_user_logged_in()) {
                    wp_redirect(home_url('/login/'));
                    exit;
                }

                $subroute = isset($segments[1]) && !empty($segments[1]) ? sanitize_text_field($segments[1]) : 'overview';
                set_query_var('vicoba_subroute', $subroute);

                include get_template_directory() . '/template-parts/dashboard/layout.php';
                exit;
            }
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

        // Only redirect non-admins away from wp-admin
        if (!in_array('administrator', $current_user->roles) && !in_array('super_admin', $current_user->roles)) {
            wp_redirect(home_url('/dashboard/'));
            exit;
        }
    }
}
