<?php

if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/__routing-check') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'SIMART ROUTER ACTIVE';
    exit;
}

/** SIMART: clean URL halaman + routing API lama. */
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
if (!is_string($path)) {
    http_response_code(400);
    exit('URL tidak valid.');
}

// API tetap memakai pintu masuk fisik di Wasmer.
if ($path === '/api/index.php') {
    return false;
}
if (preg_match('~^/api/(auth|buku|karya|kategori|kunjungan|laporan|pengunjung|reservasi|tempat|transaksi)/([a-z_]+\.php)$~', $path, $m)) {
    $file = __DIR__ . '/backend/api/' . $m[1] . '/' . $m[2];
    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'API tidak ditemukan.']);
        exit;
    }
    require_once __DIR__ . '/backend/config/csrf.php';
    csrfStartSession();
    csrfRequireValid();
    require $file;
    exit;
}
if (str_starts_with($path, '/api/')) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Endpoint tidak dikenal.']);
    exit;
}

// Whitelist eksplisit. Jangan pernah include file berdasarkan path pengguna secara langsung.
// Routing halaman frontend menggunakan clean URL.
$pages = [
    '/' => 'index.php',
    '/tentang' => 'tentang.php',
    '/katalog' => 'katalog.php',
    '/karya' => 'karya.php',
    '/detail-buku' => 'detail-buku.php',
    '/detail-karya' => 'detail-karya.php',
    '/reservasi' => 'reservasi.php',
    '/kunjungan' => 'kunjungan.php',
    '/profil' => 'profil.php',
    '/riwayat' => 'riwayat.php',
    '/lupa-password' => 'lupa-password.php',
    '/reset-password' => 'reset-password.php',
    '/admin' => 'admin/index.php',
    '/admin/' => 'admin/index.php',
];

if (isset($pages[$path])) {
    $file = __DIR__ . '/public/' . $pages[$path];

    if (!is_file($file)) {
        http_response_code(404);
        exit('Halaman tidak ditemukan.');
    }

    require $file;
    exit;
}

// Kembalikan 404 untuk URL halaman yang tidak dikenal.
$publicFile = realpath(__DIR__ . '/public' . $path);
$publicRoot = realpath(__DIR__ . '/public');

$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

$staticExtensions = [
    'css', 'js', 'png', 'jpg', 'jpeg',
    'gif', 'svg', 'webp', 'ico',
    'woff', 'woff2', 'ttf', 'pdf'
];

if (
    in_array($extension, $staticExtensions, true) &&
    $publicFile !== false &&
    $publicRoot !== false &&
    str_starts_with($publicFile, $publicRoot . DIRECTORY_SEPARATOR) &&
    is_file($publicFile)
) {
    return false;
}

http_response_code(404);
exit('Halaman tidak ditemukan.');