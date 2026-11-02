
<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus DELETE.'
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

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === '23503') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Kategori masih digunakan oleh buku atau karya.'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus kategori.'
    ]);
}
