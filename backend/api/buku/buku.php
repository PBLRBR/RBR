<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method harus GET.']);
    exit;
}
try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $stmt = $pdo->query('SELECT id_buku, id_kategori, nama_kategori, judul, penulis, penerbit,
        tahun_terbit, bisa_dijual, kondisi, harga, stok, deskripsi, foto_sampul
        FROM v_katalog_buku ORDER BY id_buku DESC');
    echo json_encode(['success'=>true,'data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal mengambil daftar buku.']);
}
