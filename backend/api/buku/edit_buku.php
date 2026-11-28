<?php
require_once __DIR__ . '/../../helpers/input_validation.php';
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus PUT.'
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

    $idKategori = filter_var(
        $input['id_kategori'] ?? null,
        FILTER_VALIDATE_INT
    );

    foreach (['judul', 'penulis', 'penerbit', 'deskripsi', 'foto_sampul'] as $field) {
        if (isset($input[$field]) && !is_string($input[$field])) {
            throw new InvalidArgumentException("Kolom {$field} harus berupa teks.");
        }
    }

    $judul = trim($input['judul'] ?? '');
    $penulis = trim($input['penulis'] ?? '');

    if (!$idBuku || $idBuku < 1) {
        throw new InvalidArgumentException('ID buku wajib berupa bilangan bulat positif.');
    }

    if (!$idKategori || $idKategori < 1 || $judul === '' || $penulis === '') {
        throw new InvalidArgumentException(
            'ID kategori, judul, dan penulis wajib diisi.'
        );
    }

    $penerbit = trim($input['penerbit'] ?? '');
    $tahunTerbit = $input['tahun_terbit'] ?? null;
    $bisaDijual = $input['bisa_dijual'] ?? false;
    $kondisi = $input['kondisi'] ?? null;
    $harga = $input['harga'] ?? null;
    $stok = $input['stok'] ?? null;
    // Wajib kirim stok yang terlihat saat form dibuka. Mencegah menimpa stok POS.
    if (!array_key_exists('expected_stok', $input)) {
        throw new InvalidArgumentException('Stok awal wajib disertakan. Muat ulang halaman admin.');
    }
    $expectedStok = $input['expected_stok'];
    if ($expectedStok !== null) {
        $expectedStok = filter_var($expectedStok, FILTER_VALIDATE_INT);
        if ($expectedStok === false || $expectedStok < 0 || $expectedStok > 2147483647) {
            throw new InvalidArgumentException('Stok awal tidak valid.');
        }
    }
    $deskripsi = trim($input['deskripsi'] ?? '');
    $fotoSampul = trim($input['foto_sampul'] ?? '');

    foreach (['judul' => 200, 'penulis' => 150, 'penerbit' => 150, 'foto_sampul' => 255, 'deskripsi' => null] as $field => $max) {
        simartValidateText($input[$field] ?? null, $field, $max);
    }

    if (!is_bool($bisaDijual)) {
        throw new InvalidArgumentException(
            'bisa_dijual harus bernilai true atau false.'
        );
    }

    if ($tahunTerbit !== null && $tahunTerbit !== '') {
        $tahunTerbit = filter_var($tahunTerbit, FILTER_VALIDATE_INT);

        if ($tahunTerbit === false || $tahunTerbit < 1 || $tahunTerbit > (int) date('Y')) {
            throw new InvalidArgumentException('Tahun terbit tidak valid.');
        }
    } else {
        $tahunTerbit = null;
    }

    if ($bisaDijual) {
        if (
            !in_array($kondisi, ['baru', 'bekas'], true) ||
            !is_numeric($harga) ||
            !is_numeric($stok) ||
            (float) $harga < 0 ||
            !is_finite((float) $harga) ||
            round((float) $harga, 2) > 9999999999.99 ||
            filter_var($stok, FILTER_VALIDATE_INT) === false ||
            (int) $stok < 0 || (int) $stok > 2147483647
        ) {
            throw new InvalidArgumentException(
                'Buku yang dijual wajib memiliki kondisi baru/bekas, harga valid, dan stok bilangan bulat nonnegatif.'
            );
        }

        $harga = (float) $harga;
        $stok = (int) $stok;
    } else {
        $kondisi = null;
        $harga = null;
        $stok = null;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    // Pastikan buku yang akan diperbarui memang ada.
    $cek = $pdo->prepare(
        'SELECT id_buku FROM buku WHERE id_buku = :id_buku AND active = TRUE'
    );
    $cek->execute(['id_buku' => $idBuku]);

    if (!$cek->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Buku tidak ditemukan.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE buku SET
            id_kategori = :id_kategori,
            judul = :judul,
            penulis = :penulis,
            penerbit = :penerbit,
            tahun_terbit = :tahun_terbit,
            bisa_dijual = :bisa_dijual,
            kondisi = :kondisi,
            harga = :harga,
            stok = :stok,
            deskripsi = :deskripsi,
            foto_sampul = :foto_sampul
         WHERE id_buku = :id_buku AND active = TRUE
           AND stok IS NOT DISTINCT FROM :expected_stok
         RETURNING id_buku, id_kategori, judul, penulis, penerbit,
                   tahun_terbit, bisa_dijual, kondisi, harga, stok,
                   deskripsi, foto_sampul'
    );

    $stmt->execute([
        'id_buku' => $idBuku,
        'expected_stok' => $expectedStok,
        'id_kategori' => $idKategori,
        'judul' => $judul,
        'penulis' => $penulis,
        'penerbit' => $penerbit !== '' ? $penerbit : null,
        'tahun_terbit' => $tahunTerbit,
        'bisa_dijual' => $bisaDijual ? 'true' : 'false',
        'kondisi' => $kondisi,
        'harga' => $harga,
        'stok' => $stok,
        'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
        'foto_sampul' => $fotoSampul !== '' ? $fotoSampul : null
    ]);

    $updatedBook = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$updatedBook) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Stok buku sudah berubah sejak form dibuka. Muat ulang halaman lalu coba lagi.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil diperbarui.',
        'data' => $updatedBook
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

} catch (PDOException $e) {
    if ((string) $e->getCode() === '23503') {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Kategori buku tidak ditemukan.'
        ]);
        exit;
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memperbarui buku. Periksa kategori dan data buku.'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
