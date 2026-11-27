
<?php

$path = parse_url(
    (string) ($_SERVER['REQUEST_URI'] ?? '/'),
    PHP_URL_PATH
);

if (!is_string($path)) {
    http_response_code(400);
    exit('URL tidak valid.');
}

// Tes untuk memastikan router aktif di Wasmer.
if ($path === '/__routing-check') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'SIMART ROUTER ACTIVE';
    exit;
}

// API fisik tetap ditangani oleh public/api/index.php.
if ($path === '/api/index.php') {
    return false;
}

// Routing endpoint API lama.
if (preg_match(
    '~^/api/(auth|buku|karya|kategori|kunjungan|laporan|pengunjung|reservasi|tempat|transaksi)/([a-z_]+\.php)$~',
    $path,
    $matches
)) {
    $file = __DIR__
        . '/backend/api/'
        . $matches[1]
        . '/'
        . $matches[2];

    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'API tidak ditemukan.'
        ]);
        exit;
    }

    require_once __DIR__ . '/backend/config/csrf.php';

    csrfStartSession();
    csrfRequireValid();

    require $file;
    exit;
}

// Endpoint API yang tidak dikenal.
if (str_starts_with($path, '/api/')) {
    http_response_code(404);
    header('Content-Type: application/json');

    echo json_encode([
        'success' => false,
        'message' => 'Endpoint tidak dikenal.'
    ]);
    exit;
}

// Daftar halaman frontend yang diizinkan.
$pages = [
    '/' => 'index.php',
    '/index.php' => 'index.php',

    '/tentang' => 'tentang.php',
    '/tentang.php' => 'tentang.php',

    '/katalog' => 'katalog.php',
    '/katalog.php' => 'katalog.php',

    '/karya' => 'karya.php',
    '/karya.php' => 'karya.php',

    '/detail-buku' => 'detail-buku.php',
    '/detail-buku.php' => 'detail-buku.php',

    '/detail-karya' => 'detail-karya.php',
    '/detail-karya.php' => 'detail-karya.php',

    '/infoevent' => 'infoevent.php',
    '/infoevent.php' => 'infoevent.php',

    '/reservasi' => 'reservasi.php',
    '/reservasi.php' => 'reservasi.php',

    '/kunjungan' => 'kunjungan.php',
    '/kunjungan.php' => 'kunjungan.php',

    '/profil' => 'profil.php',
    '/profil.php' => 'profil.php',

    '/riwayat' => 'riwayat.php',
    '/riwayat.php' => 'riwayat.php',

    '/lupa-password' => 'lupa-password.php',
    '/lupa-password.php' => 'lupa-password.php',

    '/reset-password' => 'reset-password.php',
    '/reset-password.php' => 'reset-password.php',

    '/admin' => 'admin/index.php',
    '/admin/' => 'admin/index.php',
    '/admin/index.php' => 'admin/index.php',
];

// Jalankan halaman sesuai mapping.
if (isset($pages[$path])) {
    $file = __DIR__ . '/public/' . $pages[$path];

    if (!is_file($file)) {
        http_response_code(404);
        exit('File halaman tidak ditemukan.');
    }

    require $file;
    exit;
}

// File statis tetap dilayani server PHP.
$publicRoot = realpath(__DIR__ . '/public');
$requestedFile = realpath(__DIR__ . '/public' . $path);

$staticExtensions = [
    'css', 'js', 'png', 'jpg', 'jpeg',
    'webp', 'gif', 'svg', 'ico',
    'woff', 'woff2', 'ttf', 'pdf',
    'txt', 'json', 'map'
];

$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (
    $publicRoot !== false &&
    $requestedFile !== false &&
    str_starts_with(
        $requestedFile,
        $publicRoot . DIRECTORY_SEPARATOR
    ) &&
    is_file($requestedFile) &&
    in_array($extension, $staticExtensions, true)
) {
    return false;
}

// URL lainnya menghasilkan 404.
http_response_code(404);
exit('Halaman tidak ditemukan.');
