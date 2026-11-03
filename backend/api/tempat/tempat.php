
<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus GET.'
    ]);
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->query(
        "SELECT id_tempat, nama_tempat, deskripsi, kapasitas, status
         FROM tempat
         ORDER BY nama_tempat ASC"
    );

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll()
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil daftar tempat.'
    ]);
}
