<?php
/**
 * VICOBA Auth — Session-based Authentication
 * Replaces WordPress user system entirely
 */

class Auth
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) return;

        $cfg = config('session');
        session_name($cfg['name']);
        session_set_cookie_params([
            'lifetime' => $cfg['lifetime'],
            'path'     => '/',
            'secure'   => $cfg['secure'],
            'httponly' => $cfg['httponly'],
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /** Log in a user — stores user ID and role in session */
    public static function login(object $user): void
    {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id']   = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['group_id']  = $user->group_id;
        $_SESSION['logged_in'] = true;
    }

    /** Log out — destroy session */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /** Check if a user is logged in */
    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    /** Get the current authenticated user (from DB) */
    public static function user(): ?object
    {
        if (!self::check()) return null;
        return Database::get(
            'SELECT u.*, m.id as member_id, m.member_number, m.phone FROM ' . Database::t('users') . ' u
             LEFT JOIN ' . Database::t('members') . ' m ON m.user_id = u.id
             WHERE u.id = ? AND u.status = ?',
            [$_SESSION['user_id'], 'active']
        );
    }

    /** Require authentication — redirect to login if not logged in */
    public static function require(): object
    {
        if (!self::check()) {
            Response::redirect('/login');
        }
        $user = self::user();
        if (!$user) {
            self::logout();
            Response::redirect('/login');
        }
        return $user;
    }

    /** Check if current user has a given role or higher */
    public static function hasRole(string ...$roles): bool
    {
        return in_array($_SESSION['user_role'] ?? '', $roles, true);
    }

    /** Require a specific role — 403 if not authorized */
    public static function requireRole(string ...$roles): void
    {
        if (!self::hasRole(...$roles)) {
            Response::json(['success' => false, 'message' => 'Huna ruhusa ya kufanya kitendo hiki.'], 403);
        }
    }

    /** Get current user's group_id from session */
    public static function groupId(): int
    {
        return (int)($_SESSION['group_id'] ?? 0);
    }

    /** Role hierarchy helper */
    public static function canManage(): bool
    {
        return self::hasRole('super_admin', 'group_admin', 'treasurer', 'secretary');
    }

    // ── CSRF Protection ───────────────────────────────────────

    public static function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // ── Password Helpers ──────────────────────────────────────

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => config('security.bcrypt_cost', 12)]);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /** Attempt login — returns user object or null on failure */
    public static function attempt(string $username, string $password): ?object
    {
        $user = Database::get(
            'SELECT * FROM ' . Database::t('users') . ' WHERE (username = ? OR email = ?) AND status = ?',
            [$username, $username, 'active']
        );

        if (!$user) return null;
        if (!self::verifyPassword($password, $user->password)) return null;

        return $user;
    }
}
