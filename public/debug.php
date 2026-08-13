<?php
/**
 * VICOBA Debug Script
 * Tumia hii kujua tatizo halisi la server
 * FUTA faili hii baada ya kumaliza debug!
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "<style>body{font-family:monospace;background:#0d1117;color:#e6edf3;padding:2rem;line-height:1.8}
.ok{color:#3fb950}.err{color:#f85149}.warn{color:#d29922}
h2{color:#58a6ff;border-bottom:1px solid #30363d;padding-bottom:.5rem;margin:1.5rem 0 .75rem}
pre{background:#161b22;padding:1rem;border-radius:.5rem;overflow-x:auto;border:1px solid #30363d}
</style>";

echo "<h1 style='color:#58a6ff'>🔍 VICOBA Debug Check</h1>";

// 1. PHP Version
echo "<h2>1. PHP Version</h2>";
$ver = phpversion();
echo "<pre>" . $ver . "</pre>";
if (version_compare($ver, '8.0', '<')) {
    echo "<p class='err'>❌ PHP 8.0+ inahitajika! Version yako: $ver</p>";
} else {
    echo "<p class='ok'>✅ PHP version iko sawa</p>";
}

// 2. Config file
echo "<h2>2. Config File</h2>";
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
$config_file = CONFIG_PATH . '/config.php';
if (!file_exists($config_file)) {
    echo "<p class='err'>❌ config.php HAIKUPATIKANA: $config_file</p>";
} else {
    echo "<p class='ok'>✅ config.php ipo</p>";
    $cfg = require $config_file;
    echo "<pre>";
    echo "DB Host: " . ($cfg['db']['host'] ?? '?') . "\n";
    echo "DB Name: " . ($cfg['db']['name'] ?? '?') . "\n";
    echo "DB User: " . ($cfg['db']['user'] ?? '?') . "\n";
    echo "DB Pass: " . (empty($cfg['db']['pass']) ? '(EMPTY!)' : '***set***') . "\n";
    echo "App URL: " . ($cfg['app']['url'] ?? '?') . "\n";
    echo "Debug:   " . (($cfg['app']['debug'] ?? false) ? 'ON' : 'off') . "\n";
    echo "Session secure: " . (($cfg['session']['secure'] ?? false) ? 'true (HTTPS required)' : 'false') . "\n";
    echo "</pre>";
    
    // Check for CHANGE_ME
    if (($cfg['db']['pass'] ?? '') === 'CHANGE_ME' || ($cfg['db']['pass'] ?? '') === '') {
        echo "<p class='err'>❌ DB password bado ni CHANGE_ME au iko wazi! Rekebisha config.php</p>";
    } else {
        echo "<p class='ok'>✅ DB password imewekwa</p>";
    }
}

// 3. Database Connection
echo "<h2>3. Database Connection</h2>";
if (isset($cfg)) {
    try {
        $dsn = "mysql:host={$cfg['db']['host']};port={$cfg['db']['port']};dbname={$cfg['db']['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<p class='ok'>✅ Database imeunganishwa vizuri!</p>";
        
        // Check tables
        $prefix = $cfg['db']['prefix'] ?? 'vc_';
        $tables = ['users','groups','members','loans','shares','fines','meetings','transactions','notifications'];
        echo "<pre>";
        foreach ($tables as $tbl) {
            $full = $prefix . $tbl;
            try {
                $cnt = $pdo->query("SELECT COUNT(*) FROM `$full`")->fetchColumn();
                echo "✅ $full — rekodi: $cnt\n";
            } catch (Exception $e) {
                echo "❌ $full — HAIKUPATIKANA!\n";
            }
        }
        echo "</pre>";
        
        // Check admin user
        $adminUser = $pdo->query("SELECT id,username,role,status FROM {$prefix}users WHERE username='admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($adminUser) {
            echo "<p class='ok'>✅ Admin user ipo: " . print_r($adminUser, true) . "</p>";
        } else {
            echo "<p class='warn'>⚠️ Admin user 'admin' haikupatikana kwenye DB</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='err'>❌ Database ERROR: " . $e->getMessage() . "</p>";
    }
}

// 4. Session test
echo "<h2>4. Session Test</h2>";
try {
    if (isset($cfg['session'])) {
        session_name($cfg['session']['name'] ?? 'vicoba_sess');
        // Don't set secure here for testing
        session_start();
        $_SESSION['test'] = 'ok_' . time();
        echo "<p class='ok'>✅ Session inafanya kazi. ID: " . session_id() . "</p>";
        session_destroy();
    }
} catch (Throwable $e) {
    echo "<p class='err'>❌ Session ERROR: " . $e->getMessage() . "</p>";
}

// 5. File permissions
echo "<h2>5. Key Files Check</h2>";
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/Views');
$files_to_check = [
    APP_PATH . '/helpers.php',
    APP_PATH . '/Core/Database.php',
    APP_PATH . '/Core/Auth.php',
    APP_PATH . '/Core/Router.php',
    APP_PATH . '/Core/Response.php',
    APP_PATH . '/Controllers/AuthController.php',
    VIEW_PATH . '/auth/login.php',
    VIEW_PATH . '/auth/register.php',
    VIEW_PATH . '/dashboard/layout.php',
    VIEW_PATH . '/errors/404.php',
];
echo "<pre>";
foreach ($files_to_check as $f) {
    $rel = str_replace(ROOT_PATH, '', $f);
    if (file_exists($f)) {
        echo "✅ $rel (" . filesize($f) . " bytes)\n";
    } else {
        echo "❌ $rel — HAIKUPATIKANA!\n";
    }
}
echo "</pre>";

// 6. PHP Extensions
echo "<h2>6. Required PHP Extensions</h2>";
$required = ['pdo','pdo_mysql','openssl','mbstring','json','session'];
echo "<pre>";
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext\n";
    } else {
        echo "❌ $ext — HAIPO!\n";
    }
}
echo "</pre>";

// 7. Try to load the actual login page
echo "<h2>7. Login Page PHP Syntax Check</h2>";
$login_file = VIEW_PATH . '/auth/login.php';
if (file_exists($login_file)) {
    $output = shell_exec("php -l " . escapeshellarg($login_file) . " 2>&1");
    if (str_contains($output, 'No syntax errors')) {
        echo "<p class='ok'>✅ login.php syntax iko sawa</p>";
    } else {
        echo "<p class='err'>❌ login.php syntax ERROR:<br><pre>$output</pre></p>";
    }
} else {
    echo "<p class='err'>❌ login.php HAIKUPATIKANA</p>";
}

echo "<hr style='border-color:#30363d;margin:2rem 0'>";
echo "<p style='color:#8b949e;font-size:.8rem'>⚠️ KUMBUKA: Futa faili hii <code>/public/debug.php</code> baada ya kumaliza debug!</p>";
