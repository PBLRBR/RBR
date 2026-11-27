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
    $tanggalMulai = $_GET['tanggal_mulai'] ?? null;
    $tanggalSelesai = $_GET['tanggal_selesai'] ?? null;
    $pakaiFilter = $tanggalMulai !== null || $tanggalSelesai !== null;

    $tanggalValid = static function ($tanggal): bool {
        if (!is_string($tanggal) || !preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $tanggal)) {
            return false;
        }

        [$tahun, $bulan, $hari] = explode('-', $tanggal);
        return checkdate((int) $bulan, (int) $hari, (int) $tahun);
    };

    if (
        $pakaiFilter && (
            !$tanggalValid($tanggalMulai) ||
            !$tanggalValid($tanggalSelesai) ||
            $tanggalMulai > $tanggalSelesai
        )
    ) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Isi kedua tanggal dengan format YYYY-MM-DD. Tanggal mulai tidak boleh setelah tanggal selesai.'
        ]);
        exit;
    }

    $limit = filter_var($_GET['limit'] ?? 50, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 100],
    ]);
    $halaman = filter_var($_GET['halaman'] ?? 1, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => intdiv(PHP_INT_MAX, 100)],
    ]);

    if ($limit === false || $halaman === false) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Limit harus 1–100 dan halaman harus berupa bilangan bulat positif.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $sql = 'SELECT * FROM v_laporan_kunjungan';
    $params = [];

    if ($pakaiFilter) {
        $sql .= ' WHERE tanggal_kunjungan BETWEEN :tanggal_mulai AND :tanggal_selesai';
        $params = [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai
        ];
    }

    $sql .= ' ORDER BY tanggal_kunjungan DESC, jam_masuk DESC, id_buku_tamu DESC
          LIMIT :limit OFFSET :offset';

    $stmt = $pdo->prepare($sql);

    foreach ($params as $nama => $nilai) {
        $stmt->bindValue(':' . $nama, $nilai, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit + 1, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($halaman - 1) * $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $adaBerikutnya = count($rows) > $limit;

    if ($adaBerikutnya) {
        array_pop($rows);
    }

    foreach ($rows as $index => $row) {
        if ($row['jam_masuk'] !== null) {
            $rows[$index]['jam_masuk'] = substr($row['jam_masuk'], 0, 8);
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $rows,
        'paginasi' => [
            'halaman' => $halaman,
            'limit' => $limit,
            'ada_berikutnya' => $adaBerikutnya,
        ],
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil laporan kunjungan.'
    ]);
}
