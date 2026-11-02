
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

wajibLogin();

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Hanya admin yang dapat menghapus reservasi.'
    ]);
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $id = filter_var(
        is_array($input) ? ($input['id_reservasi'] ?? null) : null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID reservasi tidak valid.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'DELETE FROM reservasi
         WHERE id_reservasi = :id
         RETURNING id_reservasi'
    );
    $stmt->execute(['id' => $id]);

    $deleted = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deleted) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Reservasi tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil dihapus.',
        'data' => $deleted
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus reservasi.'
    ]);
}
