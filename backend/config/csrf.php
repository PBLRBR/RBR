<?php
/**
 * Session dan proteksi CSRF untuk seluruh endpoint /api yang dilewati router.php.
 * Token tidak mengandalkan database dan berlaku juga bagi pengunjung tanpa akun.
 */
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
    session_start();
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
}
