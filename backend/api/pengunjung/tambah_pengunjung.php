
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

    $nama = trim($input['nama_pengunjung'] ?? '');
    $no_hp = trim($input['no_hp'] ?? '');

    if ($nama === '' || $no_hp === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama dan nomor HP wajib diisi.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO pengunjung (id_user, nama_pengunjung, no_hp)
         VALUES (NULL, :nama, :no_hp)
         RETURNING id_pengunjung, nama_pengunjung, no_hp'
    );

    $stmt->execute([
        ':nama' => $nama,
        ':no_hp' => $no_hp
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Pengunjung berhasil ditambahkan.',
        'data' => $stmt->fetch()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === '23505') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Nomor HP sudah terdaftar.'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan pengunjung.'
    ]);
}
