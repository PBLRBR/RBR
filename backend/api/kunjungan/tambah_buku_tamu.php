
<?php

require_once __DIR__ . '/../auth/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus POST.'
    ]);
    exit;
}

// Endpoint ini menerima ID pengunjung dari klien; batasi ke admin.
// Pengunjung mandiri tetap memakai daftar_kunjungan.php.
wajibAdmin();

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format data tidak valid.'
        ]);
        exit;
    }

    $id_pengunjung = filter_var(
        $input['id_pengunjung'] ?? null,
        FILTER_VALIDATE_INT
    );

    $keperluan = trim($input['keperluan'] ?? '');

    if (!$id_pengunjung || $id_pengunjung < 1 || $keperluan === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID pengunjung dan keperluan wajib diisi.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO buku_tamu (id_pengunjung, keperluan)
         VALUES (:id_pengunjung, :keperluan)
         RETURNING id_buku_tamu, id_pengunjung,
                   tanggal_kunjungan, jam_masuk, keperluan'
    );

    $stmt->execute([
        'id_pengunjung' => $id_pengunjung,
        'keperluan' => $keperluan
    ]);

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Catatan kunjungan berhasil ditambahkan.',
        'data' => $stmt->fetch(PDO::FETCH_ASSOC)
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan catatan kunjungan.'
    ]);
}
