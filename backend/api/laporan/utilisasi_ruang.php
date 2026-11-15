<?php

declare(strict_types=1);

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../../helpers/periode_laporan.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus GET.']);
    exit;
}

wajibAdmin();

try {
    $rentang = rentangPeriodeLaporan($_GET);
    $pdo = require __DIR__ . '/../../config/database.php';

    // LEFT JOIN mempertahankan ruangan dengan 0 reservasi, termasuk ruangan nonaktif
    // yang mungkin pernah digunakan secara historis.
    $stmt = $pdo->prepare(
        "SELECT t.id_tempat, t.nama_tempat, t.status,
                COUNT(r.id_reservasi)::integer AS jumlah_reservasi,
                COALESCE(
                    ROUND(SUM(EXTRACT(EPOCH FROM (r.jam_selesai - r.jam_mulai)) / 3600)::numeric, 2),
                    0
                ) AS total_jam_terpakai
         FROM tempat t
         LEFT JOIN reservasi r
                ON r.id_tempat = t.id_tempat
               AND r.status_reservasi = 'disetujui'
               AND r.tanggal_acara BETWEEN CAST(:mulai AS date) AND CAST(:selesai AS date)
         GROUP BY t.id_tempat, t.nama_tempat, t.status
         ORDER BY jumlah_reservasi DESC, t.nama_tempat ASC"
    );
    $stmt->execute(['mulai' => $rentang['mulai'], 'selesai' => $rentang['selesai']]);

    $data = [];
    $totalReservasi = 0;
    $totalJam = 0.0;

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        $jumlah = (int) $baris['jumlah_reservasi'];
        $jam = (float) $baris['total_jam_terpakai'];
        $data[] = [
            'id_tempat' => (int) $baris['id_tempat'],
            'nama_tempat' => $baris['nama_tempat'],
            'status_tempat' => $baris['status'],
            'jumlah_reservasi' => $jumlah,
            'total_jam_terpakai' => $jam,
        ];
        $totalReservasi += $jumlah;
        $totalJam += $jam;
    }

    echo json_encode([
        'success' => true,
        'periode' => $rentang,
        'ringkasan' => [
            'total_reservasi_disetujui' => $totalReservasi,
            'total_jam_terpakai' => round($totalJam, 2),
        ],
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil utilisasi ruangan.']);
}
