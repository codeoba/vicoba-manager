<?php
/**
 * VICOBA Frontend Router
 * Bulletproof routing supporting clean URLs (/dashboard, /login, /register)
 * with automatic fallback to query parameter routing (?vicoba_route=dashboard)
 */

if (!defined('ABSPATH')) {
    exit;
}

class VICOBA_Router {

    public static function init() {
        add_action('init', array(__CLASS__, 'add_rewrite_rules'), 5);
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

    public static function get_url($route = 'dashboard', $subroute = '') {
        $using_permalinks = (bool) get_option('permalink_structure');
        
        if ($using_permalinks) {
            $url = home_url('/' . $route . '/');
            if (!empty($subroute) && $subroute !== 'overview') {
                $url = home_url('/' . $route . '/' . $subroute . '/');
            }
            return $url;
        }

        $url = home_url('/?vicoba_route=' . $route);
        if (!empty($subroute)) {
            $url .= '&vicoba_subroute=' . $subroute;
        }
        return $url;
    }

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
        $request_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
        $site_path = trim(parse_url(home_url(), PHP_URL_PATH) ?? '', '/');

        if (!empty($site_path) && strpos($request_path, $site_path) === 0) {
            $request_path = trim(substr($request_path, strlen($site_path)), '/');
        }

        $route = get_query_var('vicoba_route');
        $subroute = get_query_var('vicoba_subroute');

        // Fallback to GET parameters
        if (empty($route) && isset($_GET['vicoba_route'])) {
            $route = sanitize_text_field($_GET['vicoba_route']);
        }
        if (empty($subroute) && isset($_GET['vicoba_subroute'])) {
            $subroute = sanitize_text_field($_GET['vicoba_subroute']);
        }

        // Direct URI matching fallback
        if (empty($route) && !empty($request_path)) {
            $segments = explode('/', $request_path);
            $first_segment = strtolower($segments[0] ?? '');

            if ($first_segment === 'dashboard' || $first_segment === 'login' || $first_segment === 'register') {
                $route = $first_segment;
                $subroute = isset($segments[1]) && !empty($segments[1]) ? sanitize_text_field($segments[1]) : 'overview';
            }
        }

        if (empty($route)) {
            return;
        }

        if ($route === 'login') {
            if (is_user_logged_in()) {
                wp_redirect(self::get_url('dashboard'));
                exit;
            }
            include get_template_directory() . '/template-parts/auth/login.php';
            exit;
        }

        if ($route === 'register') {
            if (is_user_logged_in()) {
                wp_redirect(self::get_url('dashboard'));
                exit;
            }
            include get_template_directory() . '/template-parts/auth/register.php';
            exit;
        }

        if ($route === 'dashboard') {
            if (!is_user_logged_in()) {
                wp_redirect(self::get_url('login'));
                exit;
            }
            
            if (empty($subroute)) {
                $subroute = 'overview';
            }
            
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

        // Only redirect non-admins away from wp-admin
        if (!in_array('administrator', $current_user->roles) && !in_array('super_admin', $current_user->roles)) {
            wp_redirect(self::get_url('dashboard'));
            exit;
        }
    }
}
