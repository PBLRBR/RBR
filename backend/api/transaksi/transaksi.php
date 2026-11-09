<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');
wajibAdmin();
try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $rows = $pdo->query('SELECT t.id_transaksi,t.kode_invoice,t.tanggal_transaksi,t.nama_pembeli,t.metode_transaksi,t.total_bayar,u.nama AS nama_kasir,COALESCE(SUM(d.jumlah_beli),0) AS jumlah_item FROM transaksi_penjualan t JOIN users u ON u.id_user=t.id_user LEFT JOIN detail_transaksi_penjualan d ON d.id_transaksi=t.id_transaksi GROUP BY t.id_transaksi,u.nama ORDER BY t.tanggal_transaksi DESC')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows]);
} catch (Throwable $e) { error_log($e->getMessage()); http_response_code(500); echo json_encode(['success'=>false,'message'=>'Gagal membaca transaksi.']); }
