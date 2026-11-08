
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

    $id = filter_var(
        $input['id_karya'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'ID karya tidak valid.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'DELETE FROM karya
         WHERE id_karya = :id
         RETURNING id_karya'
    );

    $stmt->execute([':id' => $id]);
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
        'message' => 'Karya berhasil dihapus.',
        'data' => $karya
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus karya. Data mungkin masih digunakan.'
    ]);
}
