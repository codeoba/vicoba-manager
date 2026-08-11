<?php
/**
 * Main Index Template File
 */

if (is_user_logged_in()) {
    wp_redirect(VICOBA_Router::get_url('dashboard'));
    exit;
} else {
    wp_redirect(VICOBA_Router::get_url('login'));
    exit;
}
