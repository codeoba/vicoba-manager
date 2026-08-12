<?php
/**
 * VICOBA Global Helper Functions
 * Replaces WordPress helper functions (esc_html, sanitize_text_field, etc.)
 */

// ── Configuration ─────────────────────────────────────────────────────────────

function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require CONFIG_PATH . '/config.php';
    }

    // Support dot-notation: config('db.host')
    $parts = explode('.', $key);
    $value = $cfg;
    foreach ($parts as $part) {
        if (!isset($value[$part])) return $default;
        $value = $value[$part];
    }
    return $value;
}

// ── Security & Sanitization ───────────────────────────────────────────────────

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize(string $value): string
{
    return trim(strip_tags($value));
}

function sanitize_email_addr(string $value): string
{
    return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
}

function valid_email(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize_int(mixed $value): int
{
    return (int)$value;
}

function sanitize_float(mixed $value): float
{
    return (float)str_replace(',', '', $value);
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function today(): string
{
    return date('Y-m-d');
}

// ── URL helpers ───────────────────────────────────────────────────────────────

function url(string $path = '/', array $params = []): string
{
    return Router::url($path, $params);
}

function asset(string $path): string
{
    return rtrim(config('app.url'), '/') . '/assets/' . ltrim($path, '/');
}

function redirect(string $path, int $code = 302): never
{
    Response::redirect($path, $code);
}

// ── Request helpers ───────────────────────────────────────────────────────────

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

function get_param(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
}

// ── Number & Currency ─────────────────────────────────────────────────────────

function money(float $amount, string $currency = 'TZS'): string
{
    return $currency . ' ' . number_format($amount, 0, '.', ',');
}

function percent(float $value, int $decimals = 1): string
{
    return number_format($value, $decimals) . '%';
}

// ── Date & Time ───────────────────────────────────────────────────────────────

function format_date(string $date, string $format = 'd/m/Y'): string
{
    if (!$date || $date === '0000-00-00') return 'N/A';
    return date($format, strtotime($date));
}

function format_datetime(string $date): string
{
    return format_date($date, 'd/m/Y H:i');
}

function days_ago(string $date): int
{
    return (int)floor((time() - strtotime($date)) / 86400);
}

// ── Unique Code Generator ─────────────────────────────────────────────────────

function generate_code(string $prefix, int $length = 8): string
{
    return strtoupper($prefix . '-' . bin2hex(random_bytes(3)) . date('ymd'));
}

// ── Flash Messages ────────────────────────────────────────────────────────────

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// ── Role Helpers ──────────────────────────────────────────────────────────────

function role_label(string $role): string
{
    return match($role) {
        'super_admin'  => 'Super Admin',
        'group_admin'  => 'Mwenyekiti',
        'secretary'    => 'Katibu',
        'treasurer'    => 'Mweka Hazina',
        'member'       => 'Mwanachama',
        default        => ucfirst($role),
    };
}

function role_badge_class(string $role): string
{
    return match($role) {
        'super_admin'  => 'bg-purple-100 text-purple-800 border-purple-200',
        'group_admin'  => 'bg-blue-100 text-blue-800 border-blue-200',
        'secretary'    => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        'treasurer'    => 'bg-amber-100 text-amber-800 border-amber-200',
        'member'       => 'bg-slate-100 text-slate-700 border-slate-200',
        default        => 'bg-gray-100 text-gray-700',
    };
}

// ── Encryption (NIDA / sensitive data) ───────────────────────────────────────

function encrypt_data(string $data): string
{
    $key = hex2bin(config('security.nida_encrypt_key'));
    $iv  = random_bytes(16);
    $enc = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $enc);
}

function decrypt_data(string $data): string
{
    $key     = hex2bin(config('security.nida_encrypt_key'));
    $decoded = base64_decode($data);
    $iv      = substr($decoded, 0, 16);
    $enc     = substr($decoded, 16);
    return openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
}

// ── Loan Calculations (pure PHP, no WP dependency) ───────────────────────────

function calculate_loan_schedule(float $principal, float $rate_percent, string $type, int $months): array
{
    $monthly_rate = ($rate_percent / 100) / 12;
    $schedule     = [];
    $balance      = $principal;

    if ($type === 'flat') {
        $total_interest   = $principal * ($rate_percent / 100) * ($months / 12);
        $monthly_interest = $total_interest / $months;
        $monthly_principal = $principal / $months;
        $installment      = $monthly_principal + $monthly_interest;

        for ($i = 1; $i <= $months; $i++) {
            $balance -= $monthly_principal;
            $schedule[] = [
                'period'    => $i,
                'principal' => round($monthly_principal, 2),
                'interest'  => round($monthly_interest, 2),
                'total'     => round($installment, 2),
                'balance'   => round(max(0, $balance), 2),
            ];
        }
        return [
            'monthly_installment' => round($installment, 2),
            'total_payable'       => round($principal + $total_interest, 2),
            'total_interest'      => round($total_interest, 2),
            'schedule'            => $schedule,
        ];
    }

    // Reducing balance
    if ($monthly_rate > 0) {
        $installment = ($principal * $monthly_rate * pow(1 + $monthly_rate, $months))
                     / (pow(1 + $monthly_rate, $months) - 1);
    } else {
        $installment = $principal / $months;
    }

    $total_paid = 0;
    for ($i = 1; $i <= $months; $i++) {
        $interest  = $balance * $monthly_rate;
        $principal_part = $installment - $interest;
        $balance  -= $principal_part;
        $total_paid += $installment;
        $schedule[] = [
            'period'    => $i,
            'principal' => round($principal_part, 2),
            'interest'  => round($interest, 2),
            'total'     => round($installment, 2),
            'balance'   => round(max(0, $balance), 2),
        ];
    }

    return [
        'monthly_installment' => round($installment, 2),
        'total_payable'       => round($total_paid, 2),
        'total_interest'      => round($total_paid - $principal, 2),
        'schedule'            => $schedule,
    ];
}
