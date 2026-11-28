<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus GET.']);
    exit;
}

wajibAdmin();

try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $rows = $pdo->query('WITH penjualan_buku AS (
        SELECT b.id_buku, b.judul, SUM(d.jumlah_beli) AS total_terjual, SUM(d.subtotal) AS pendapatan
        FROM detail_transaksi_penjualan d
        JOIN buku b ON b.id_buku = d.id_buku
        GROUP BY b.id_buku, b.judul
        HAVING SUM(d.jumlah_beli) > 0
    ) SELECT judul, total_terjual, pendapatan FROM penjualan_buku
      ORDER BY total_terjual DESC, pendapatan DESC')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil buku terlaris.']);
}
