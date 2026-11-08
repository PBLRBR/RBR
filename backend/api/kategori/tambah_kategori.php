
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

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format JSON tidak valid.'
        ]);
        exit;
    }

    $nama = trim($input['nama_kategori'] ?? '');
    $deskripsi = trim($input['deskripsi'] ?? '');

    if ($nama === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama kategori wajib diisi.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO kategori (nama_kategori, deskripsi)
         VALUES (:nama, :deskripsi)
         RETURNING id_kategori, nama_kategori, deskripsi'
    );

    $stmt->execute([
        ':nama' => $nama,
        ':deskripsi' => $deskripsi !== '' ? $deskripsi : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan.',
        'data' => $stmt->fetch()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === '23505') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Nama kategori sudah digunakan.'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan kategori.'
    ]);
}
