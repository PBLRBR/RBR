<?php
require_once __DIR__ . '/../../helpers/input_validation.php';
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus PUT.'
    ]);
    exit;
}

wajibAdmin();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format JSON tidak valid.'
        ]);
        exit;
    }

    $id = filter_var(
        $input['id_karya'] ?? null,
        FILTER_VALIDATE_INT
    );

    foreach (['judul_karya', 'isi_karya', 'deskripsi'] as $field) {
        if (isset($input[$field]) && !is_string($input[$field])) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => "Kolom {$field} harus berupa teks."
            ]);
            exit;
        }
    }
    $judul = trim($input['judul_karya'] ?? '');
    $isi = trim($input['isi_karya'] ?? '');

    $deskripsi = trim($input['deskripsi'] ?? '');
    $tahun = $input['tahun_publikasi'] ?? null;
    $id_kategori = $input['id_kategori'] ?? null;

    if (!$id || $id < 1 || $judul === '' || $isi === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'ID, judul, dan isi karya wajib valid.'
        ]);
        exit;
    }

    if ($tahun !== null &&
        (!filter_var($tahun, FILTER_VALIDATE_INT) ||
         $tahun < 1 || $tahun > (int) date('Y'))) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Tahun publikasi tidak valid.'
        ]);
        exit;
    }

    if ($id_kategori !== null &&
        (!filter_var($id_kategori, FILTER_VALIDATE_INT) || $id_kategori < 1)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'ID kategori tidak valid.'
        ]);
        exit;
    }

    simartValidateText($judul, 'judul_karya', 200);
    simartValidateText($isi, 'isi_karya');
    simartValidateText($deskripsi, 'deskripsi');

    $pdo = require __DIR__ . '/../../config/database.php';
    $stmt = $pdo->prepare(
        'UPDATE karya
         SET id_kategori = :id_kategori,
             judul_karya = :judul,
             tahun_publikasi = :tahun,
             deskripsi = :deskripsi,
             isi_karya = :isi
         WHERE id_karya = :id
         RETURNING id_karya, id_kategori, judul_karya,
                   tahun_publikasi, deskripsi, isi_karya'
    );

    $stmt->execute([
        ':id_kategori' => $id_kategori,
        ':judul' => $judul,
        ':tahun' => $tahun,
        ':deskripsi' => $deskripsi !== '' ? $deskripsi : null,
        ':isi' => $isi,
        ':id' => $id
    ]);

    $karya = $stmt->fetch();

    if (!$karya) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Karya tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Karya berhasil diperbarui.',
        'data' => $karya
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($e instanceof PDOException && (string) $e->getCode() === '23503') {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Kategori karya tidak ditemukan.']);
        exit;
    }
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal memperbarui karya.'
    ]);
}
