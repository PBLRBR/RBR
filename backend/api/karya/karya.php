
<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        'SELECT
            k.id_karya,
            k.judul_karya,
            k.tahun_publikasi,
            k.deskripsi,
            k.isi_karya,
            k.id_kategori,
            c.nama_kategori
         FROM karya k
         LEFT JOIN kategori c ON k.id_kategori = c.id_kategori
         ORDER BY k.id_karya DESC'
    );

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar karya.'
    ]);
}
    