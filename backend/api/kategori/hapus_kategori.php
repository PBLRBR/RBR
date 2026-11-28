<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus DELETE.'
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
        $input['id_kategori'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'ID kategori tidak valid.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
        'DELETE FROM kategori
         WHERE id_kategori = :id
         RETURNING id_kategori'
    );

    $stmt->execute([':id' => $id]);
    $kategori = $stmt->fetch();

    if (!$kategori) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Kategori tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Kategori berhasil dihapus.',
        'data' => $kategori
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    if ($e instanceof PDOException && (string) $e->getCode() === '23503') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Kategori masih digunakan. Pindahkan buku atau karya yang terkait ke kategori lain sebelum menghapusnya.',
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus kategori.'
    ]);
}
