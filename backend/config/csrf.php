<?php
/**
 * Session dan proteksi CSRF untuk seluruh endpoint /api yang dilewati router.php.
 * Token tidak mengandalkan database dan berlaku juga bagi pengunjung tanpa akun.
 */
const SIMART_IDLE_TIMEOUT = 3600;
const SIMART_ADMIN_MAX_SESSION = 43200;
const SIMART_MEMBER_MAX_SESSION = 86400;

function simartSessionProbe(): bool
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $route = $path === '/api/index.php' ? ($_GET['route'] ?? '') : $path;
    return in_array($route, ['auth/me.php', 'auth/csrf.php', '/api/auth/me.php', '/api/auth/csrf.php'], true);
}

function simartSessionTouch(): void
{
    if (isset($_SESSION['user'])) {
        $_SESSION['last_activity_at'] = time();
    }
}

function simartSessionCheck(): void
{
    if (!isset($_SESSION['user'])) return;

    $now = time();
    // Sesi lama mendapat timestamp saat pertama kali memakai kebijakan baru.
    $started = (int) ($_SESSION['login_at'] ?? $now);
    $last = (int) ($_SESSION['last_activity_at'] ?? $now);
    $max = ($_SESSION['user']['role'] ?? '') === 'admin'
        ? SIMART_ADMIN_MAX_SESSION : SIMART_MEMBER_MAX_SESSION;

    if ($now - $last >= SIMART_IDLE_TIMEOUT || $now - $started >= $max) {
        // Hapus identitas autentikasi. Token CSRF anonim tetap bisa dipakai.
        unset($_SESSION['user'], $_SESSION['login_at'], $_SESSION['last_activity_at']);
        $_SESSION['session_expired'] = true;
        return;
    }

    $_SESSION['login_at'] = $started;
    $_SESSION['last_activity_at'] = $last;
}

function csrfStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
        || getenv('APP_FORCE_HTTPS') === '1';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    // Jangan biarkan GC default (24 menit) menghapus sesi sebelum kebijakan aplikasi.
    if ((int) ini_get('session.gc_maxlifetime') < SIMART_MEMBER_MAX_SESSION) {
        ini_set('session.gc_maxlifetime', (string) SIMART_MEMBER_MAX_SESSION);
    }
    session_start();
    simartSessionCheck();
    if (!simartSessionProbe() && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        simartSessionTouch();
    }
}

function csrfToken(): string
{
    csrfStartSession();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfRequireValid(): void
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        return;
    }

    csrfStartSession();
    $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    $expected = $_SESSION['csrf_token'] ?? null;

    if (!is_string($submitted) || !is_string($expected)
        || strlen($submitted) !== 64
        || !hash_equals($expected, $submitted)) {
        http_response_code(419);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'success' => false,
            'message' => 'Token keamanan tidak valid atau sesi berakhir. Muat ulang halaman dan coba lagi.',
            'code' => 'CSRF_INVALID',
        ]);
        exit;
    }
    if (!simartSessionProbe()) simartSessionTouch();
}
