<?php
require_once __DIR__ . '/../../helpers/input_validation.php';
require_once __DIR__ . '/../../helpers/phone.php';
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format JSON tidak valid.']);
        exit;
    }

    $namaInput = $input['nama_pengunjung'] ?? null;
    $nama = is_string($namaInput) ? trim($namaInput) : '';
    $no_hp = normalizeIndonesianMobile($input['no_hp'] ?? null);

    if ($nama === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama wajib diisi.'
        ]);
        exit;
    }

    if ($no_hp === null) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Nomor HP harus 10-13 digit angka berawalan 08, 62, atau +62.']);
        exit;
    }

    simartValidateText($nama, 'nama_pengunjung', 100);

    $pdo = require __DIR__ . '/../../config/database.php';
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

} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan pengunjung.']);
}
