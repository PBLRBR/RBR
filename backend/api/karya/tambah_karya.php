
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

wajibAdmin();

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);

    $judul = trim($input['judul_karya'] ?? '');
    $isi = trim($input['isi_karya'] ?? '');

    $deskripsi = trim($input['deskripsi'] ?? '');
    $tahun = $input['tahun_publikasi'] ?? null;
    $id_kategori = $input['id_kategori'] ?? null;

    if ($judul === '' || $isi === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Judul dan isi karya wajib diisi.'
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

    $stmt = $pdo->prepare(
        'INSERT INTO karya
            (id_kategori, judul_karya, tahun_publikasi, deskripsi, isi_karya)
         VALUES
            (:id_kategori, :judul, :tahun, :deskripsi, :isi)
         RETURNING id_karya, id_kategori, judul_karya,
                   tahun_publikasi, deskripsi, isi_karya'
    );

    $stmt->execute([
        ':id_kategori' => $id_kategori,
        ':judul' => $judul,
        ':tahun' => $tahun,
        ':deskripsi' => $deskripsi !== '' ? $deskripsi : null,
        ':isi' => $isi
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Karya berhasil ditambahkan.',
        'data' => $stmt->fetch()
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan karya.'
    ]);
}
