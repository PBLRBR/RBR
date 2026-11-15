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

    $mulai = new DateTimeImmutable($rentang['mulai']);
    $selesai = new DateTimeImmutable($rentang['selesai']);
    $harian = $mulai->diff($selesai)->days <= 31;
    $granularitas = $harian ? 'harian' : 'bulanan';

    // Pemilihan ekspresi SQL hanya berdasarkan nilai boolean internal, bukan input SQL pengguna.
    $tanggalSql = $harian
        ? 'tanggal_transaksi::date'
        : "date_trunc('month', tanggal_transaksi)::date";

    $stmt = $pdo->prepare(
        "SELECT $tanggalSql AS titik,
                COUNT(*)::integer AS jumlah_transaksi,
                COALESCE(SUM(total_bayar), 0) AS omzet
         FROM transaksi_penjualan
         WHERE tanggal_transaksi >= CAST(:mulai AS date)
           AND tanggal_transaksi < CAST(:selesai AS date) + INTERVAL '1 day'
         GROUP BY 1
         ORDER BY 1"
    );
    $stmt->execute(['mulai' => $rentang['mulai'], 'selesai' => $rentang['selesai']]);

    // Lengkapi tanggal/bulan tanpa transaksi agar grafik tidak melompat.
    $titikMulai = $harian ? $mulai : $mulai->modify('first day of this month');
    $step = $harian ? '+1 day' : '+1 month';
    $format = $harian ? 'Y-m-d' : 'Y-m';
    $bucket = [];

    for ($tanggal = $titikMulai; $tanggal <= $selesai; $tanggal = $tanggal->modify($step)) {
        $kunci = $tanggal->format($format);
        $bucket[$kunci] = [
            'periode' => $kunci,
            'omzet' => 0.0,
            'jumlah_transaksi' => 0,
        ];
    }

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $baris) {
        $kunci = substr((string) $baris['titik'], 0, $harian ? 10 : 7);
        if (isset($bucket[$kunci])) {
            $bucket[$kunci]['omzet'] = (float) $baris['omzet'];
            $bucket[$kunci]['jumlah_transaksi'] = (int) $baris['jumlah_transaksi'];
        }
    }

    // Gunakan function PostgreSQL yang memang sudah tersedia pada rancangan SIMART.
    $ringkasanStmt = $pdo->prepare(
        'SELECT total_pendapatan, jumlah_transaksi, jumlah_buku_terjual
         FROM fn_ringkasan_penjualan(CAST(:mulai AS date), CAST(:selesai AS date))'
    );
    $ringkasanStmt->execute(['mulai' => $rentang['mulai'], 'selesai' => $rentang['selesai']]);
    $ringkasan = $ringkasanStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    echo json_encode([
        'success' => true,
        'periode' => $rentang,
        'granularitas' => $granularitas,
        'ringkasan' => [
            'total_pendapatan' => (float) ($ringkasan['total_pendapatan'] ?? 0),
            'jumlah_transaksi' => (int) ($ringkasan['jumlah_transaksi'] ?? 0),
            'jumlah_buku_terjual' => (int) ($ringkasan['jumlah_buku_terjual'] ?? 0),
        ],
        'data' => array_values($bucket),
    ], JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil tren penjualan.']);
}
