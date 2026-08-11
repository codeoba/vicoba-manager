<?php
/**
 * Main Index Template File
 */

if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
} else {
    wp_redirect(home_url('/login/'));
    exit;
}
