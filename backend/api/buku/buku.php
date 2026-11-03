<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        'SELECT id_buku, judul, penulis, penerbit, tahun_terbit,
                bisa_dijual, kondisi, harga, stok, deskripsi, foto_sampul
         FROM buku
         ORDER BY id_buku DESC'
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
        'message' => 'Gagal mengambil daftar buku.'
    ]);
}