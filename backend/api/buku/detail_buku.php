
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
    $idBuku = filter_var(
        $_GET['id_buku'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($idBuku === false || $idBuku === null || $idBuku < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID buku wajib berupa bilangan bulat positif.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    $stmt = $pdo->prepare(
        'SELECT
            id_buku,
            id_kategori,
            judul,
            penulis,
            penerbit,
            tahun_terbit,
            bisa_dijual,
            kondisi,
            harga,
            stok,
            deskripsi,
            foto_sampul
         FROM buku
         WHERE id_buku = :id_buku AND active = TRUE'
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
        'message' => 'Detail buku berhasil diambil.',
        'data' => $buku
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil detail buku.'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
