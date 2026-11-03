<?php
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
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        throw new InvalidArgumentException('Format JSON tidak valid.');
    }

    $idKategori = filter_var(
        $input['id_kategori'] ?? null,
        FILTER_VALIDATE_INT
    );

    $judul = trim($input['judul'] ?? '');
    $penulis = trim($input['penulis'] ?? '');
    $penerbit = trim($input['penerbit'] ?? '');
    $tahunTerbit = $input['tahun_terbit'] ?? null;
    $bisaDijual = $input['bisa_dijual'] ?? false;
    $kondisi = $input['kondisi'] ?? null;
    $harga = $input['harga'] ?? null;
    $stok = $input['stok'] ?? null;
    $deskripsi = trim($input['deskripsi'] ?? '');
    $fotoSampul = trim($input['foto_sampul'] ?? '');

    if (!$idKategori || $idKategori < 1 || $judul === '' || $penulis === '') {
        throw new InvalidArgumentException(
            'ID kategori, judul, dan penulis wajib diisi.'
        );
    }

    if (!is_bool($bisaDijual)) {
        throw new InvalidArgumentException(
            'bisa_dijual harus bernilai true atau false.'
        );
    }

    if ($tahunTerbit !== null && $tahunTerbit !== '') {
        $tahunTerbit = filter_var($tahunTerbit, FILTER_VALIDATE_INT);

        if ($tahunTerbit === false || $tahunTerbit < 0) {
            throw new InvalidArgumentException(
                'Tahun terbit harus berupa angka yang valid.'
            );
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
            filter_var($stok, FILTER_VALIDATE_INT) === false ||
            (int) $stok < 0
        ) {
            throw new InvalidArgumentException(
                'Buku yang dijual wajib memiliki kondisi baru/bekas, harga valid, dan stok berupa bilangan bulat nonnegatif.'
            );
        }

        $harga = (float) $harga;
        $stok = (int) $stok;
    } else {
        $kondisi = null;
        $harga = null;
        $stok = null;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO buku (
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
        ) VALUES (
            :id_kategori,
            :judul,
            :penulis,
            :penerbit,
            :tahun_terbit,
            :bisa_dijual,
            :kondisi,
            :harga,
            :stok,
            :deskripsi,
            :foto_sampul
        )
        RETURNING id_buku, id_kategori, judul, penulis, penerbit,
                  tahun_terbit, bisa_dijual, kondisi, harga, stok,
                  deskripsi, foto_sampul'
    );

    $stmt->execute([
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

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Buku berhasil ditambahkan.',
        'data' => $stmt->fetch(PDO::FETCH_ASSOC)
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
        'message' => 'Gagal menyimpan buku. Periksa kategori dan data buku.'
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
