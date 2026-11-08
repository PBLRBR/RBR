
<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $id = filter_input(INPUT_GET, 'id_karya', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID karya tidak valid.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
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
         WHERE k.id_karya = :id_karya'
    );

    $stmt->execute([':id_karya' => $id]);
    $karya = $stmt->fetch();

    if (!$karya) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Karya tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $karya
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil detail karya.'
    ]);
}
