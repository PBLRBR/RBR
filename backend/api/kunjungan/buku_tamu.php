
<?php

require_once __DIR__ . '/../auth/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus GET.'
    ]);
    exit;
}

// Data identitas/nomor HP hanya boleh dibaca admin.
wajibAdmin();

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        'SELECT
            bt.id_buku_tamu,
            bt.tanggal_kunjungan,
            bt.jam_masuk,
            bt.keperluan,
            p.id_pengunjung,
            COALESCE(p.nama_pengunjung, u.nama) AS nama_pengunjung,
            COALESCE(p.no_hp, u.no_hp) AS no_hp
         FROM buku_tamu bt
         JOIN pengunjung p
            ON bt.id_pengunjung = p.id_pengunjung
         LEFT JOIN users u
            ON p.id_user = u.id_user
         ORDER BY bt.tanggal_kunjungan DESC,
                  bt.jam_masuk DESC'
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
        'message' => 'Gagal mengambil daftar buku tamu.'
    ]);
}
