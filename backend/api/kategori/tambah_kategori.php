<?php
require_once __DIR__ . '/../../helpers/input_validation.php';
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
        echo json_encode([
            'success' => false,
            'message' => 'Format JSON tidak valid.'
        ]);
        exit;
    }

    foreach (['nama_kategori', 'deskripsi'] as $field) {
        if (isset($input[$field]) && !is_string($input[$field])) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => "Kolom {$field} harus berupa teks."
            ]);
            exit;
        }
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

    simartValidateText($nama, 'nama_kategori', 100);
    simartValidateText($deskripsi, 'deskripsi');

    $pdo = require __DIR__ . '/../../config/database.php';
    $pdo->beginTransaction();
    // Serialize category writes without changing the database schema.
    $pdo->exec('LOCK TABLE kategori IN SHARE ROW EXCLUSIVE MODE');
    $duplicate = $pdo->prepare(
        'SELECT 1 FROM kategori WHERE lower(btrim(nama_kategori)) = lower(:nama) LIMIT 1'
    );
    $duplicate->execute(['nama' => $nama]);
    if ($duplicate->fetchColumn() !== false) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nama kategori sudah digunakan.']);
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

    $kategori = $stmt->fetch();
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil ditambahkan.',
        'data' => $kategori
    ]);

} catch (InvalidArgumentException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
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
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
