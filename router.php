<?php
// Jalankan: php -S localhost:8000 -t public router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Hanya endpoint PHP di grup yang dikenal boleh diakses melalui /api.
if (preg_match('~^/api/(auth|buku|karya|kategori|kunjungan|laporan|pengunjung|reservasi|tempat|transaksi)/([a-z_]+\.php)$~', $path, $m)) {
    $file = __DIR__ . '/backend/api/' . $m[1] . '/' . $m[2];
    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'API tidak ditemukan.']);
        exit;
    }
    require $file;
    exit;
}
if (str_starts_with($path, '/api/')) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Endpoint tidak dikenal.']);
    exit;
}
return false;
