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
$pages = [
    '/' => 'index.php',
    '/tentang' => 'tentang.php',
    '/katalog' => 'katalog.php',
    '/karya' => 'karya.php',
    '/detail-buku' => 'detail-buku.php',
    '/detail-karya' => 'detail-karya.php',
    '/infoevent' => 'infoevent.php',
    '/reservasi' => 'reservasi.php',
    '/kunjungan' => 'kunjungan.php',
    '/profil' => 'profil.php',
    '/riwayat' => 'riwayat.php',
    '/lupa-password' => 'lupa-password.php',
    '/reset-password' => 'reset-password.php',
    '/admin' => 'admin/index.php',
    '/admin/' => 'admin/index.php',
];

// Kanonisasi URL lama, terbatas pada GET/HEAD agar body POST tidak terbuang.
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (in_array($method, ['GET', 'HEAD'], true)) {
    foreach ($pages as $clean => $file) {
        if ($clean === '/admin/' || $clean === '/admin') {
            continue;
        }
        if ($path === '/' . $file) {
            $target = $clean;
            $query = $_SERVER['QUERY_STRING'] ?? '';
            if ($query !== '') {
                $target .= '?' . $query;
            }
            header('Location: ' . $target, true, 301);
            exit;
        }
    }
}
if ($path === '/admin') {
    header('Location: /admin/', true, 308);
    exit;
}
if (isset($pages[$path])) {
    require __DIR__ . '/public/' . $pages[$path];
    exit;
}

// Aset statis yang benar-benar ada tetap dilayani oleh web server.
$publicRoot = realpath(__DIR__ . '/public');
$requestedFile = realpath(__DIR__ . '/public' . $path);
if ($publicRoot !== false && $requestedFile !== false
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)) {
    return false;
}
http_response_code(404);
exit('Halaman tidak ditemukan.');
