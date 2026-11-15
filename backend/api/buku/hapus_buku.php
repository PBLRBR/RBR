<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method harus DELETE.']);
    exit;
}
wajibAdmin();
try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) throw new InvalidArgumentException('Format JSON tidak valid.');
    $id = filter_var($data['id_buku'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) throw new InvalidArgumentException('ID buku tidak valid.');
    $pdo = require __DIR__ . '/../../config/database.php';
    // Soft delete: pertahankan baris buku untuk foreign key riwayat penjualan.
    $stmt = $pdo->prepare('UPDATE buku SET active = FALSE
        WHERE id_buku = :id AND active = TRUE
        RETURNING id_buku, judul, active');
    $stmt->execute(['id'=>$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$book) {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Buku tidak ditemukan atau sudah nonaktif.']);
        exit;
    }
    echo json_encode(['success'=>true,'message'=>'Buku dinonaktifkan dari katalog dan kasir.','data'=>$book]);
} catch (InvalidArgumentException $e) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal menonaktifkan buku.']);
}
