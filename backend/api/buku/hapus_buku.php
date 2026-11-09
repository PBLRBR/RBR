
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        throw new InvalidArgumentException('Format JSON tidak valid.');
    }

    $idBuku = filter_var(
        $input['id_buku'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($idBuku === false || $idBuku === null || $idBuku < 1) {
        throw new InvalidArgumentException(
            'ID buku wajib berupa bilangan bulat positif.'
        );
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
        'DELETE FROM buku
         WHERE id_buku = :id_buku
         RETURNING id_buku, judul'
    );

    $stmt->execute([
        'id_buku' => $idBuku
    ]);

    $buku = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$buku) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil dihapus.',
        'data' => $buku
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Buku gagal dihapus. Data mungkin masih digunakan oleh transaksi.'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
