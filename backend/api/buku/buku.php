<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        'SELECT b.id_buku, b.id_kategori, k.nama_kategori,
                b.judul, b.penulis, b.penerbit, b.tahun_terbit,
                b.bisa_dijual, b.kondisi, b.harga, b.stok,
                b.deskripsi, b.foto_sampul
         FROM buku AS b
         LEFT JOIN kategori AS k ON k.id_kategori = b.id_kategori
         ORDER BY b.id_buku DESC'
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