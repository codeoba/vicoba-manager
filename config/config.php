<?php
/**
 * VICOBA Application Configuration
 */

return [
    // ── Database ──────────────────────────────────────────────
    'db' => [
        'host'    => getenv('DB_HOST') ?: 'localhost',
        'port'    => (int)(getenv('DB_PORT') ?: 3306),
        'name'    => getenv('DB_NAME') ?: 'sql_vikoba_mdand',
        'user'    => getenv('DB_USER') ?: 'sql_vikoba_mdand',
        'pass'    => getenv('DB_PASS') ?: 'a115df733e479',
        'prefix'  => 'vc_',
        'charset' => 'utf8mb4',
    ],

    // ── Application ───────────────────────────────────────────
    'app' => [
        'name'      => 'VICOBA Manager Pro',
        'url'       => 'https://vikoba.mdandu.com',
        'debug'     => false,
        'timezone'  => 'Africa/Dar_es_Salaam',
    ],

    // ── Session ────────────────────────────────────────────────
    'session' => [
        'name'     => 'vicoba_sess',
        'lifetime' => 7200,
        'secure'   => false, // Set false to ensure sessions work seamlessly across proxies
        'httponly' => true,
    ],

    // ── Security ──────────────────────────────────────────────
    'security' => [
        'bcrypt_cost'        => 12,
        'nida_encrypt_key'   => '4a8f9c2d1e0b3a7f5e6d8c9b0a1f2e3d',
    ],

    // ── SMS Gateway ───────────────────────────────────────────
    'sms' => [
        'enabled'   => false,
        'provider'  => 'beem',
        'api_key'   => '',
        'api_secret'=> '',
        'sender_id' => 'VICOBA',
    ],
];
