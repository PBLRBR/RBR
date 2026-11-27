<?php
require_once __DIR__ . '/../auth/auth.php';
wajibAdmin();
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Gunakan metode GET.'
    ]);
    exit;
}
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

    $sql = 'SELECT
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
            JOIN tempat t ON r.id_tempat = t.id_tempat';

    if ($sebelumId !== null) {
        $sql .= ' WHERE r.id_reservasi < :sebelum_id';
    }

    $sql .= ' ORDER BY r.id_reservasi DESC LIMIT :limit';

    $stmt = $pdo->prepare($sql);
    if ($sebelumId !== null) {
        $stmt->bindValue(':sebelum_id', $sebelumId, PDO::PARAM_INT);
    }
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
                ? (int) $rows[count($rows) - 1]['id_reservasi']
                : null,
        ],
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar reservasi.'
    ]);
}
