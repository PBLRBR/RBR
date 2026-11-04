<?php

declare(strict_types=1);

function simart_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isSecure,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function simart_current_user(): ?array
{
    simart_start_session();
    $user = $_SESSION['simart_user'] ?? $_SESSION['user'] ?? null;
    if (!is_array($user)) {
        return null;
    }

    // The PBLReal API uses the `user` session key; keep both apps in sync.
    $_SESSION['simart_user'] = $user;
    $_SESSION['user'] = $user;

    return $user;
}

function simart_url(string $path): string
{
    $script = parse_url((string) ($_SERVER['SCRIPT_NAME'] ?? ''), PHP_URL_PATH);
    $script = is_string($script) ? str_replace('\\', '/', $script) : '';
    $frontendMarker = '/frontend/';
    $frontendPosition = strpos($script, $frontendMarker);
    if ($frontendPosition !== false) {
        $basePath = substr($script, 0, $frontendPosition) . '/frontend';
    } else {
        $marker = '/pages/';
        $position = strpos($script, $marker);
        $basePath = $position === false ? '' : substr($script, 0, $position);
    }
    return $basePath . '/' . ltrim($path, '/');
}

function simart_require_role(string $role): array
{
    $user = simart_current_user();
    if ($user === null) {
        $returnTo = $_SERVER['REQUEST_URI'] ?? '/index.php';
        header('Location: ' . simart_url('/index.php')
            . '?auth=login&return_to=' . rawurlencode($returnTo));
        exit;
    }

    if (($user['role'] ?? null) !== $role) {
        http_response_code(403);
        echo 'Akses ditolak.';
        exit;
    }

    return $user;
}

function simart_csrf_token(): string
{
    simart_start_session();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function simart_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(simart_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function simart_verify_csrf(): void
{
    simart_start_session();
    $submitted = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($known) || $known === ''
        || !hash_equals($known, $submitted)) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

function simart_safe_return_path(mixed $value, string $role): string
{
    if (!is_string($value) || $value === '' || str_starts_with($value, '//')
        || str_contains($value, "\r") || str_contains($value, "\n")) {
        return simart_url($role === 'admin' ? '/pages/dashboard/index.php' : '/pages/auth/profile.php');
    }

    $allowed = [
        '/pages/auth/profile.php' => ['member', 'admin'],
        '/pages/reservasi/index.php' => ['member', 'admin'],
        '/pages/reservasi/riwayat.php' => ['member', 'admin'],
        '/pages/transaksi/riwayat.php' => ['member', 'admin'],
        '/pages/dashboard/index.php' => ['admin'],
    ];

    $path = parse_url($value, PHP_URL_PATH);
    $allowedPath = null;
    if (is_string($path)) {
        foreach ($allowed as $candidate => $roles) {
            if (str_ends_with($path, $candidate) && in_array($role, $roles, true)) {
                $allowedPath = $candidate;
                break;
            }
        }
    }

    return simart_url($allowedPath ?? ($role === 'admin' ? '/pages/dashboard/index.php' : '/pages/auth/profile.php'));
}
