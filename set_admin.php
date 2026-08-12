<?php
/**
 * Set Admin Password Utility
 * Generates a clean BCRYPT password hash for 'Admin@1234' using PHP password_hash()
 */

define('ROOT_PATH', __DIR__);
define('APP_PATH',  ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require APP_PATH . '/helpers.php';
require APP_PATH . '/Core/Database.php';
require APP_PATH . '/Core/Auth.php';

$hash = Auth::hashPassword('Admin@1234');

Database::query(
    "UPDATE " . Database::t('users') . " SET password = ?, status = 'active', role = 'super_admin' WHERE username = 'admin'",
    [$hash]
);

echo "✅ Admin password reset to 'Admin@1234' with hash: $hash\n";
