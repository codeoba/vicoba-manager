<?php
/**
 * Auto-matching Data Migration Script
 * Automatically inspects table schemas, finds common columns between WP tables and VC tables,
 * and migrates data safely without column errors.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require APP_PATH . '/helpers.php';
require APP_PATH . '/Core/Database.php';

echo "=== VICOBA Auto-Matching Data Migration ===\n\n";

$pdo = Database::connect();

// Map WP tables -> VC tables
$map = [
    'wp_4fec8c_vicoba_groups'           => 'vc_groups',
    'wp_4fec8c_vicoba_members'          => 'vc_members',
    'wp_4fec8c_vicoba_shares'           => 'vc_shares',
    'wp_4fec8c_vicoba_loans'            => 'vc_loans',
    'wp_4fec8c_vicoba_loan_repayments'  => 'vc_loan_repayments',
    'wp_4fec8c_vicoba_guarantors'       => 'vc_loan_guarantors',
    'wp_4fec8c_vicoba_fine_types'       => 'vc_fine_types',
    'wp_4fec8c_vicoba_fines'            => 'vc_fines',
    'wp_4fec8c_vicoba_meetings'         => 'vc_meetings',
    'wp_4fec8c_vicoba_attendance'       => 'vc_meeting_attendance',
    'wp_4fec8c_vicoba_social_fund'      => 'vc_social_fund',
    'wp_4fec8c_vicoba_transactions'     => 'vc_transactions',
    'wp_4fec8c_vicoba_shareouts'        => 'vc_shareouts',
    'wp_4fec8c_vicoba_shareout_details' => 'vc_shareout_details',
    'wp_4fec8c_vicoba_notifications'    => 'vc_notifications',
    'wp_4fec8c_vicoba_audit_log'        => 'vc_audit_log',
];

foreach ($map as $source => $target) {
    try {
        // Get source columns
        $stmt = $pdo->query("SHOW COLUMNS FROM `$source`");
        if (!$stmt) continue;
        $src_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get target columns
        $stmt = $pdo->query("SHOW COLUMNS FROM `$target`");
        if (!$stmt) continue;
        $tgt_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Find common columns
        $common = array_intersect($src_cols, $tgt_cols);
        if (empty($common)) continue;

        $col_list = implode('`, `', $common);
        $sql = "INSERT IGNORE INTO `$target` (`$col_list`) SELECT `$col_list` FROM `$source`";
        
        $affected = $pdo->exec($sql);
        echo "  ✅ Migrated $source → $target ($affected rows)\n";

    } catch (\Exception $e) {
        echo "  ⚠️ Skipping $source → $target: " . $e->getMessage() . "\n";
    }
}

// Migrate WP Users -> vc_users
echo "\n👥 Migrating Users...\n";
try {
    $sql = "INSERT IGNORE INTO vc_users (id, username, email, password, display_name, role, group_id, status)
            SELECT 
                u.ID,
                u.user_login,
                u.user_email,
                '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                u.display_name,
                'member',
                (SELECT group_id FROM vc_members WHERE user_id=u.ID LIMIT 1),
                'active'
            FROM wp_4fec8c_users u";
    $affected = $pdo->exec($sql);
    echo "  ✅ Migrated $affected users to vc_users\n";
} catch (\Exception $e) {
    echo "  ⚠️ Users migration: " . $e->getMessage() . "\n";
}

// Ensure Admin account is super_admin
$pdo->exec("UPDATE vc_users SET role='super_admin', password='$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username='admin'");
echo "  ✅ Admin user updated\n";

echo "\n📊 Data Verification:\n";
$tables = ['vc_groups', 'vc_members', 'vc_shares', 'vc_loans', 'vc_fines', 'vc_users'];
foreach ($tables as $t) {
    $cnt = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "  • $t: $cnt rows\n";
}

echo "\n✨ Migration Completed Successfully!\n";
