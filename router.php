
<?php
// Jalankan lokal:
// php -S localhost:8000 -t public router.php

if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/__router-check') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ROUTER_SIMART_AKTIF';
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ========================================
// 1. ROUTING API (KODE LAMA)
// ========================================

// Entry point fisik untuk frontend dan Wasmer.
if ($path === '/api/index.php') {
    return false;
}

// Hanya endpoint dari grup yang dikenal.
if (preg_match(
    '~^/api/(auth|buku|karya|kategori|kunjungan|laporan|pengunjung|reservasi|tempat|transaksi)/([a-z_]+\.php)$~',
    $path,
    $m
)) {
    $file = __DIR__ . '/backend/api/' . $m[1] . '/' . $m[2];

    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'API tidak ditemukan.'
        ]);
        exit;
    }

    // Perlindungan CSRF untuk request yang mengubah data.
    require_once __DIR__ . '/backend/config/csrf.php';
    csrfStartSession();
    csrfRequireValid();

    require $file;
    exit;
}

// Tolak endpoint API yang tidak dikenal.
if (str_starts_with($path, '/api/')) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint tidak dikenal.'
    ]);
    exit;
}

// ========================================
// 2. CLEAN URL (KODE BARU)
// ========================================

// Daftar halaman publik yang diizinkan.
$pages = [
    '/'                => 'index.php',
    '/tentang'         => 'tentang.php',
    '/katalog'         => 'katalog.php',
    '/karya'           => 'karya.php',
    '/detail-buku'     => 'detail-buku.php',
    '/detail-karya'    => 'detail-karya.php',
    '/reservasi'       => 'reservasi.php',
    '/riwayat'         => 'riwayat.php',
    '/kunjungan'       => 'kunjungan.php',
    '/profil'          => 'profil.php',
    '/infoevent'       => 'infoevent.php',
    '/lupa-password'   => 'lupa-password.php',
    '/reset-password'  => 'reset-password.php',
    '/admin'           => 'admin/index.php',
];

// Normalisasi trailing slash.
$cleanPath = rtrim($path, '/') ?: '/';

// Hanya terima GET dan HEAD untuk routing halaman.
if (
    in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)
    && isset($pages[$cleanPath])
) {
    $file = __DIR__ . '/public/' . $pages[$cleanPath];

    if (is_file($file)) {
        require $file;
        exit;
    }

    http_response_code(404);
    echo 'Halaman tidak ditemukan.';
    exit;
}

// ========================================
// 3. FILE STATIS DAN ROUTING DEFAULT
// ========================================

// Biarkan PHP built-in server menangani
// file fisik seperti CSS, JS, gambar,
// serta URL .php lama.
return false;
