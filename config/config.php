<?php
/**
 * VICOBA Application Configuration
 * Copy this file to config.php and fill in your server details
 */

return [
    // ── Database ──────────────────────────────────────────────
    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'vicoba_db',       // Your database name
        'user'    => 'vicoba_user',     // Your DB username
        'pass'    => 'CHANGE_ME',       // Your DB password
        'prefix'  => 'vc_',            // Table prefix (e.g. vc_groups, vc_loans)
        'charset' => 'utf8mb4',
    ],

    // ── Application ───────────────────────────────────────────
    'app' => [
        'name'      => 'VICOBA Manager',
        'url'       => 'https://vikoba.mdandu.com',  // No trailing slash
        'debug'     => false,    // Set to true during development only
        'timezone'  => 'Africa/Dar_es_Salaam',
    ],

    // ── Session ────────────────────────────────────────────────
    'session' => [
        'name'     => 'vicoba_sess',
        'lifetime' => 7200,         // 2 hours in seconds
        'secure'   => true,         // Only send cookie over HTTPS
        'httponly' => true,
    ],

    // ── Security ──────────────────────────────────────────────
    'security' => [
        'bcrypt_cost'        => 12,
        'nida_encrypt_key'   => 'CHANGE_TO_32_CHAR_RANDOM_STRING', // openssl_random_pseudo_bytes(32) in hex
    ],

    // ── SMS Gateway (optional) ────────────────────────────────
    'sms' => [
        'enabled'   => false,
        'provider'  => 'beem',              // beem | nexmo | africas_talking
        'api_key'   => '',
        'api_secret'=> '',
        'sender_id' => 'VICOBA',
    ],
];
