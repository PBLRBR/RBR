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
    if (empty($_SESSION['user']) || ($admin && ($_SESSION['user']['role'] ?? '') !== 'admin')) {
        header('Location: /?auth=login', true, 302);
        exit;
    }
}
