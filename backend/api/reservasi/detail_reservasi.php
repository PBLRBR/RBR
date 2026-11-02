
<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus GET.'
    ]);
    exit;
}

$id = filter_input(INPUT_GET, 'id_reservasi', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID reservasi tidak valid.'
    ]);
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
        'SELECT
            r.id_reservasi,
            r.id_user,
            r.id_tempat,
            r.nama_acara,
            r.deskripsi_acara,
            r.penanggung_jawab,
            r.kontak_wa,
            r.tanggal_acara,
            r.jam_mulai,
            r.jam_selesai,
            r.poster_acara,
            r.tautan_info,
            r.status_reservasi,
            r.alasan_penolakan,
            u.nama AS nama_pemesan,
            u.email AS email_pemesan,
            t.nama_tempat
         FROM reservasi r
         JOIN users u ON r.id_user = u.id_user
         JOIN tempat t ON r.id_tempat = t.id_tempat
         WHERE r.id_reservasi = :id'
    );

    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Reservasi tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Detail reservasi berhasil diambil.',
        'data' => $data
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil detail reservasi.'
    ]);
}
