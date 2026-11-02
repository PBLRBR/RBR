
<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        'SELECT
            r.id_reservasi,
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
            u.nama AS nama_pemesan,
            t.nama_tempat
         FROM reservasi r
         JOIN users u ON r.id_user = u.id_user
         JOIN tempat t ON r.id_tempat = t.id_tempat
         ORDER BY r.id_reservasi DESC'
    );

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar reservasi.'
    ]);
}
