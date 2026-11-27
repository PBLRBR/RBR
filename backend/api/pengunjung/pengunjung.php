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
    $limit = filter_var(
        $_GET['limit'] ?? '50',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 100]]
    );

    $sebelumId = isset($_GET['sebelum_id'])
        ? filter_var(
            $_GET['sebelum_id'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : null;

    if ($limit === false || $sebelumId === false) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Limit harus 1–100 dan sebelum_id harus berupa ID positif.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $sql = "
        SELECT
            p.id_pengunjung,
            COALESCE(p.nama_pengunjung, u.nama) AS nama_pengunjung,
            COALESCE(p.no_hp, u.no_hp) AS no_hp,
            p.id_user,
            CASE
                WHEN p.id_user IS NOT NULL THEN 'member'
                ELSE 'non-member'
            END AS jenis_pengunjung
        FROM pengunjung p
        LEFT JOIN users u ON p.id_user = u.id_user
    ";

    if ($sebelumId !== null) {
        $sql .= ' WHERE p.id_pengunjung < :sebelum_id';
    }

    $sql .= ' ORDER BY p.id_pengunjung DESC LIMIT :limit';

    $stmt = $pdo->prepare($sql);
    if ($sebelumId !== null) {
        $stmt->bindValue(':sebelum_id', $sebelumId, PDO::PARAM_INT);
    }

    // Ambil satu baris ekstra untuk mengetahui apakah masih ada halaman berikutnya.
    $stmt->bindValue(':limit', $limit + 1, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $adaBerikutnya = count($rows) > $limit;
    if ($adaBerikutnya) {
        array_pop($rows);
    }

    echo json_encode([
        'success' => true,
        'data' => $rows,
        'paginasi' => [
            'limit' => $limit,
            'sebelum_id_berikutnya' => $adaBerikutnya
                ? (int) $rows[count($rows) - 1]['id_pengunjung']
                : null,
        ],
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar pengunjung.'
    ]);
}
