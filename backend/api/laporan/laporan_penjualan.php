
<?php
require_once __DIR__ . '/../auth/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus GET.'
    ]);
    exit;
}

wajibAdmin();

try {
    $tanggalMulai = $_GET['tanggal_mulai'] ?? '';
    $tanggalSelesai = $_GET['tanggal_selesai'] ?? '';

    $formatTanggal = '/^\d{4}-\d{2}-\d{2}$/';

    if (
        !preg_match($formatTanggal, $tanggalMulai) ||
        !preg_match($formatTanggal, $tanggalSelesai) ||
        !checkdate(
            (int) substr($tanggalMulai, 5, 2),
            (int) substr($tanggalMulai, 8, 2),
            (int) substr($tanggalMulai, 0, 4)
        ) ||
        !checkdate(
            (int) substr($tanggalSelesai, 5, 2),
            (int) substr($tanggalSelesai, 8, 2),
            (int) substr($tanggalSelesai, 0, 4)
        ) ||
        $tanggalMulai > $tanggalSelesai
    ) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Rentang tanggal tidak valid.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
        'SELECT * FROM fn_ringkasan_penjualan(
            CAST(:tanggal_mulai AS DATE),
            CAST(:tanggal_selesai AS DATE)
        )'
    );

    $stmt->execute([
        ':tanggal_mulai' => $tanggalMulai,
        ':tanggal_selesai' => $tanggalSelesai
    ]);

    echo json_encode([
        'success' => true,
        'periode' => [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai
        ],
        'data' => $stmt->fetchAll()
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil laporan penjualan.'
    ]);
}
