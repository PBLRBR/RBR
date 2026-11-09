<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');
wajibAdmin();
$id = filter_input(INPUT_GET,'id_transaksi',FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'ID tidak valid.']); exit; }
try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $stmt=$pdo->prepare('SELECT t.*, u.nama AS nama_kasir FROM transaksi_penjualan t JOIN users u ON u.id_user=t.id_user WHERE t.id_transaksi=:id');
    $stmt->execute(['id'=>$id]); $transaction=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$transaction) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Transaksi tidak ditemukan.']);exit; }
    $stmt=$pdo->prepare('SELECT d.id_buku,b.judul,d.jumlah_beli,d.subtotal FROM detail_transaksi_penjualan d JOIN buku b ON b.id_buku=d.id_buku WHERE d.id_transaksi=:id ORDER BY d.id_detail');
    $stmt->execute(['id'=>$id]);
    echo json_encode(['success'=>true,'data'=>['transaksi'=>$transaction,'detail'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]]);
} catch(Throwable $e) {error_log($e->getMessage());http_response_code(500);echo json_encode(['success'=>false,'message'=>'Gagal membaca detail transaksi.']);}
