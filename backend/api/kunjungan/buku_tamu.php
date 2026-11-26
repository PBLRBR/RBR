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
    $tanggal = $_GET['tanggal'] ?? null;

    if (
        $tanggal !== null && (
            !is_string($tanggal) ||
            !preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $tanggal) ||
            !checkdate(
                (int) substr($tanggal, 5, 2),
                (int) substr($tanggal, 8, 2),
                (int) substr($tanggal, 0, 4)
            )
        )
    ) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Tanggal kunjungan harus valid dengan format YYYY-MM-DD.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';
    $filterTanggal = $tanggal === null ? '' : 'WHERE bt.tanggal_kunjungan = :tanggal';

    $stmt = $pdo->prepare(
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
         ' . $filterTanggal . '
         ORDER BY bt.tanggal_kunjungan DESC,
                  bt.jam_masuk DESC,
                  bt.id_buku_tamu DESC'
    );

    $stmt->execute($tanggal === null ? [] : ['tanggal' => $tanggal]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $index => $row) {
        if ($row['jam_masuk'] !== null) {
            $rows[$index]['jam_masuk'] = substr($row['jam_masuk'], 0, 8);
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar buku tamu.'
    ]);
}
