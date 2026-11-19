<?php
/** Front controller API untuk Apache. PHP built-in server tetap menggunakan router.php. */
$path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
if (!is_string($path) || !preg_match(
    '~^/api/(auth|buku|karya|kategori|kunjungan|laporan|pengunjung|reservasi|tempat|transaksi)/([a-z_]+\.php)$~',
    $path, $matches
)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Endpoint API tidak dikenal.']);
    exit;
}

$root = dirname(__DIR__, 2);
$file = $root . '/backend/api/' . $matches[1] . '/' . $matches[2];
if (!is_file($file)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Endpoint API tidak ditemukan.']);
    exit;
}

require_once $root . '/backend/config/csrf.php';
csrfStartSession();
csrfRequireValid();
require $file;
