<?php
/**
 * WordPress → VICOBA Standalone Migration Script
 * Run this ONCE to migrate data from WordPress database to new standalone tables
 * 
 * USAGE (on server):
 *   php migrate/from_wordpress.php
 * 
 * IMPORTANT: Backup your database before running!
 */

// Bootstrap
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');

require APP_PATH . '/helpers.php';
require APP_PATH . '/Core/Database.php';
require APP_PATH . '/Core/Auth.php';

echo "=== VICOBA Migration: WordPress → Standalone ===\n\n";

$cfg = config('db');
$wp_prefix = 'wp_'; // Your WordPress table prefix

// ── 1. Migrate existing vicoba_ tables ────────────────────────────────────────
// The data tables (vicoba_groups, vicoba_members, etc.) may already exist with
// WP prefix. We just need to rename them to use the new vc_ prefix.

echo "🔍 Checking existing tables...\n";

$pdo = Database::connect();

$tables_exist = [];
$existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($existing as $t) {
    $tables_exist[$t] = true;
}

// Map old WP table names → new standalone table names
$table_map = [
    $wp_prefix . 'vicoba_groups'           => 'vc_groups',
    $wp_prefix . 'vicoba_members'          => 'vc_members',
    $wp_prefix . 'vicoba_shares'           => 'vc_shares',
    $wp_prefix . 'vicoba_loans'            => 'vc_loans',
    $wp_prefix . 'vicoba_loan_guarantors'  => 'vc_loan_guarantors',
    $wp_prefix . 'vicoba_loan_repayments'  => 'vc_loan_repayments',
    $wp_prefix . 'vicoba_fine_types'       => 'vc_fine_types',
    $wp_prefix . 'vicoba_fines'            => 'vc_fines',
    $wp_prefix . 'vicoba_meetings'         => 'vc_meetings',
    $wp_prefix . 'vicoba_meeting_attendance'=> 'vc_meeting_attendance',
    $wp_prefix . 'vicoba_social_fund'      => 'vc_social_fund',
    $wp_prefix . 'vicoba_transactions'     => 'vc_transactions',
    $wp_prefix . 'vicoba_shareouts'        => 'vc_shareouts',
    $wp_prefix . 'vicoba_shareout_details' => 'vc_shareout_details',
    $wp_prefix . 'vicoba_notifications'    => 'vc_notifications',
    $wp_prefix . 'vicoba_audit_log'        => 'vc_audit_log',
];

// Option A: If the WP tables exist, rename them
foreach ($table_map as $old => $new) {
    if (isset($tables_exist[$old]) && !isset($tables_exist[$new])) {
        echo "  📋 Renaming $old → $new ... ";
        try {
            $pdo->exec("RENAME TABLE `$old` TO `$new`");
            echo "✅\n";
        } catch (\Exception $e) {
            echo "❌ " . $e->getMessage() . "\n";
        }
    } elseif (isset($tables_exist[$new])) {
        echo "  ✅ $new already exists — skipping\n";
    } else {
        echo "  ⚠️  $old not found — you may need to run install.sql first\n";
    }
}

echo "\n";

// ── 2. Migrate WordPress Users → vc_users ─────────────────────────────────────
echo "👥 Migrating WordPress users → vc_users...\n";

$wp_users_exist = isset($tables_exist[$wp_prefix . 'users']);
$vc_users_exist = isset($tables_exist['vc_users']);

if (!$wp_users_exist) {
    echo "  ⚠️  {$wp_prefix}users table not found. Skipping user migration.\n";
    echo "  ℹ️  Make sure to create admin user via install.sql\n";
} elseif (!$vc_users_exist) {
    echo "  ⚠️  vc_users table not found. Run install.sql first.\n";
} else {
    // Get WP user meta for vicoba roles
    $wp_users = $pdo->query("SELECT u.ID, u.user_login, u.user_email, u.user_pass, u.display_name FROM {$wp_prefix}users u")->fetchAll(PDO::FETCH_OBJ);
    
    $migrated = 0;
    foreach ($wp_users as $wu) {
        // Check if user already exists in vc_users
        $exists = Database::scalar('SELECT id FROM vc_users WHERE username = ? OR email = ?', [$wu->user_login, $wu->user_email]);
        if ($exists) { echo "  ⏭️  {$wu->user_login} already in vc_users\n"; continue; }

        // Get group from vc_members
        $member = Database::get('SELECT group_id, role FROM vc_members WHERE user_id = ?', [$wu->ID]) ??
                  Database::get('SELECT group_id, role FROM vc_members WHERE user_id = ?', [$wu->ID]);

        // Get role from WP user meta
        $wp_role_meta = $pdo->prepare("SELECT meta_value FROM {$wp_prefix}usermeta WHERE user_id = ? AND meta_key = '{$wp_prefix}capabilities'");
        $wp_role_meta->execute([$wu->ID]);
        $caps_raw = $wp_role_meta->fetchColumn();
        $wp_role = 'member';
        if ($caps_raw) {
            $caps = maybe_unserialize_compat($caps_raw);
            $vicoba_roles = ['super_admin','group_admin','treasurer','secretary','member'];
            foreach ($vicoba_roles as $vr) {
                if (!empty($caps[$vr])) { $wp_role = $vr; break; }
            }
        }
        $final_role = $member->role ?? $wp_role;

        // Insert into vc_users — NOTE: WP password hash won't work with password_verify()
        // We generate a temp password. Users will need to reset.
        $temp_pass = Auth::hashPassword('Vicoba@' . substr(md5($wu->user_login), 0, 6));

        Database::insert('users', [
            'username'     => $wu->user_login,
            'email'        => $wu->user_email,
            'password'     => $temp_pass, // ← Users need password reset!
            'display_name' => $wu->display_name,
            'role'         => $final_role,
            'group_id'     => $member->group_id ?? null,
            'status'       => 'active',
        ]);

        // Update vc_members to link to new user ID
        $new_uid = (int)Database::connect()->lastInsertId();
        Database::update('members', ['user_id' => $new_uid], ['user_id' => (int)$wu->ID]);
        Database::update('users', ['group_id' => $member->group_id ?? null], ['id' => $new_uid]);

        echo "  ✅ Migrated: {$wu->user_login} (role: $final_role)\n";
        $migrated++;
    }
    echo "  Total migrated: $migrated users\n";
}

echo "\n";
echo "⚠️  IMPORTANT: Migrated users have TEMPORARY PASSWORDS!\n";
echo "   Format: Vicoba + first 6 chars of MD5(username)\n";
echo "   Example for 'admin': Vicoba@" . substr(md5('admin'),0,6) . "\n";
echo "   Ask users to change passwords after first login.\n\n";

// ── 3. Verify table counts ─────────────────────────────────────────────────────
echo "📊 Verification:\n";
$check_tables = ['vc_groups','vc_members','vc_shares','vc_loans','vc_fines','vc_meetings','vc_users'];
foreach ($check_tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  ✅ $t: $count rows\n";
    } catch (\Exception $e) {
        echo "  ❌ $t: TABLE NOT FOUND\n";
    }
}

echo "\n✨ Migration complete!\n";
echo "Next steps:\n";
echo "  1. Update config/config.php with your database credentials\n";
echo "  2. Point nginx/apache document root to /public/\n";
echo "  3. Test login at https://yourdomain.com/login\n";
echo "  4. Inform users of their temporary passwords\n\n";

function maybe_unserialize_compat(string $data): mixed {
    if (@unserialize($data) !== false) return @unserialize($data);
    return json_decode($data, true) ?? $data;
}
