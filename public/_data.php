<?php
// Berkas server-side khusus halaman; bukan API publik.
function db(): PDO {
    return require __DIR__ . '/../backend/config/database.php';
}
function esc($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function boolPg($value): bool {
    return $value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true';
}
function startAppSession(): void {
    require_once __DIR__ . '/../backend/config/csrf.php';
    csrfStartSession();
}
function requireAppUser(bool $admin = false): void {
    startAppSession();
    header('Cache-Control: no-store, private');
    if (empty($_SESSION['user']) || ($admin && ($_SESSION['user']['role'] ?? '') !== 'admin')) {
        $target = $admin ? '/admin/' : (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
        $params = ['return_to' => $target];
        if (!empty($_SESSION['session_expired']) && empty($_SESSION['user'])) {
            $params['session'] = 'expired';
        } else {
            $params['auth'] = 'login';
        }
        header('Location: /?' . http_build_query($params), true, 302);
        exit;
    }
}
