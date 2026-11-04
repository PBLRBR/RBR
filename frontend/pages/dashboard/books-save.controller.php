<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
require_once dirname(__DIR__, 2) . '/pages/database.php';
simart_require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();
$action = (string) ($_POST['action'] ?? 'save');
$db = simart_db();
$adminUrl = simart_url('/pages/dashboard/index.php');

try {
    if ($action === 'archive') {
        $bookId = filter_var($_POST['id_buku'] ?? null, FILTER_VALIDATE_INT);
        if (!$bookId || $bookId < 1) {
            header('Location: ' . $adminUrl . '?error=1#tab-buku');
            exit;
        }
        $bookColumns = $db->query("SELECT column_name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'buku'")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('is_archived', $bookColumns, true)) {
            $archive = $db->prepare('UPDATE buku SET is_archived = TRUE WHERE id_buku = :id AND is_archived = FALSE');
        } else {
            $archive = $db->prepare('DELETE FROM buku WHERE id_buku = :id');
        }
        $archive->execute([':id' => $bookId]);
        header('Location: ' . $adminUrl . ($archive->rowCount() === 1 ? '?saved=1' : '?error=1') . '#tab-buku');
        exit;
    }

    if ($action === 'category_create') {
        $categoryName = trim((string) ($_POST['nama_kategori'] ?? ''));
        $categoryDescription = trim((string) ($_POST['deskripsi_kategori'] ?? ''));
        if ($categoryName === '' || mb_strlen($categoryName) > 100 || mb_strlen($categoryDescription) > 1000) {
            header('Location: ' . $adminUrl . '?error=1#tab-buku');
            exit;
        }
        $createCategory = $db->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (:nama, :deskripsi)');
        $createCategory->execute([
            ':nama' => $categoryName,
            ':deskripsi' => $categoryDescription !== '' ? $categoryDescription : null,
        ]);
        header('Location: ' . $adminUrl . '?saved=1#tab-buku');
        exit;
    }

    if ($action === 'category_update') {
        $categoryId = filter_var($_POST['id_kategori'] ?? null, FILTER_VALIDATE_INT);
        $categoryName = trim((string) ($_POST['nama_kategori'] ?? ''));
        $categoryDescription = trim((string) ($_POST['deskripsi_kategori'] ?? ''));
        if (!$categoryId || $categoryId < 1 || $categoryName === '' || mb_strlen($categoryName) > 100
            || mb_strlen($categoryDescription) > 1000) {
            header('Location: ' . $adminUrl . '?error=1#tab-buku');
            exit;
        }
        $updateCategory = $db->prepare(
            'UPDATE kategori SET nama_kategori = :nama, deskripsi = :deskripsi WHERE id_kategori = :id'
        );
        $updateCategory->execute([
            ':nama' => $categoryName,
            ':deskripsi' => $categoryDescription !== '' ? $categoryDescription : null,
            ':id' => $categoryId,
        ]);
        header('Location: ' . $adminUrl . ($updateCategory->rowCount() === 1 ? '?saved=1' : '?error=1') . '#tab-buku');
        exit;
    }

    if ($action !== 'save') {
        http_response_code(400);
        exit('Aksi tidak dikenal.');
    }

    $bookId = filter_var($_POST['id_buku'] ?? '0', FILTER_VALIDATE_INT);
    $categoryId = filter_var($_POST['id_kategori'] ?? null, FILTER_VALIDATE_INT);
    $title = trim((string) ($_POST['judul'] ?? ''));
    $author = trim((string) ($_POST['penulis'] ?? ''));
    $publisher = trim((string) ($_POST['penerbit'] ?? ''));
    $yearText = trim((string) ($_POST['tahun_terbit'] ?? ''));
    $type = (string) ($_POST['jenis_buku'] ?? '');
    $condition = (string) ($_POST['kondisi'] ?? '');
    $priceText = trim((string) ($_POST['harga'] ?? ''));
    $stockText = trim((string) ($_POST['stok'] ?? ''));
    $description = trim((string) ($_POST['deskripsi'] ?? ''));
    $coverPath = trim((string) ($_POST['foto_sampul'] ?? ''));
    $coverValid = $coverPath === '' || preg_match('#^assets/images/[A-Za-z0-9_-]+\.(?:png|jpe?g|webp)$#i', $coverPath) === 1;
    if ($coverValid && $coverPath !== '') {
        $coverFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $coverPath);
        $coverValid = is_file($coverFile);
    }

    $validPrice = $priceText === '' || preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $priceText) === 1;
    $price = $priceText === '' ? null : $priceText;
    $stock = filter_var($stockText, FILTER_VALIDATE_INT);
    $year = $yearText === '' ? null : filter_var($yearText, FILTER_VALIDATE_INT);
    $validCondition = $condition === '' || in_array($condition, ['baru', 'bekas'], true);
    $validBook = $bookId !== false && $bookId >= 0 && $categoryId !== false && $categoryId > 0
        && $title !== '' && mb_strlen($title) <= 200 && $author !== '' && mb_strlen($author) <= 150
        && mb_strlen($publisher) <= 150 && ($year === null || ($year !== false && $year > 0))
        && in_array($type, ['koleksi', 'dijual'], true) && $validCondition && $validPrice
        && $stock !== false && $stock >= 0 && mb_strlen($description) <= 10000 && $coverValid;
    if ($type === 'dijual' && ($price === null || !$validCondition || $condition === '')) {
        $validBook = false;
    }
    if (!$validBook) {
        header('Location: ' . $adminUrl . '?error=1#tab-buku');
        exit;
    }

    $priceValue = $type === 'dijual' ? $price : null;
    $conditionValue = $condition !== '' ? $condition : null;
    $bookColumns = $db->query("SELECT column_name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = 'buku'")->fetchAll(PDO::FETCH_COLUMN);
    $hasJenis = in_array('jenis_buku', $bookColumns, true);
    $typeColumn = $hasJenis ? 'jenis_buku' : 'bisa_dijual';
    $typeValue = $hasJenis ? $type : ($type === 'dijual');
    $activeClause = in_array('is_archived', $bookColumns, true) ? ' AND is_archived = FALSE' : '';
    if ($bookId > 0) {
        $save = $db->prepare(
            'UPDATE buku SET id_kategori = :kategori, judul = :judul, penulis = :penulis, '
            . 'penerbit = :penerbit, tahun_terbit = :tahun, ' . $typeColumn . ' = :jenis, kondisi = :kondisi, '
            . 'harga = :harga, stok = :stok, deskripsi = :deskripsi, foto_sampul = :foto_sampul '
            . 'WHERE id_buku = :id' . $activeClause
        );
        $save->execute([
            ':kategori' => $categoryId, ':judul' => $title, ':penulis' => $author,
            ':penerbit' => $publisher !== '' ? $publisher : null, ':tahun' => $year,
            ':jenis' => $typeValue, ':kondisi' => $conditionValue, ':harga' => $priceValue,
            ':stok' => $stock, ':deskripsi' => $description !== '' ? $description : null,
            ':foto_sampul' => $coverPath !== '' ? $coverPath : null,
            ':id' => $bookId,
        ]);
        if ($save->rowCount() !== 1) {
            header('Location: ' . $adminUrl . '?error=1#tab-buku');
            exit;
        }
    } else {
        $save = $db->prepare(
            'INSERT INTO buku (id_kategori, judul, penulis, penerbit, tahun_terbit, ' . $typeColumn . ', kondisi, harga, stok, deskripsi, foto_sampul) '
            . 'VALUES (:kategori, :judul, :penulis, :penerbit, :tahun, :jenis, :kondisi, :harga, :stok, :deskripsi, :foto_sampul)'
        );
        $save->execute([
            ':kategori' => $categoryId, ':judul' => $title, ':penulis' => $author,
            ':penerbit' => $publisher !== '' ? $publisher : null, ':tahun' => $year,
            ':jenis' => $typeValue, ':kondisi' => $conditionValue, ':harga' => $priceValue,
            ':stok' => $stock, ':deskripsi' => $description !== '' ? $description : null,
            ':foto_sampul' => $coverPath !== '' ? $coverPath : null,
        ]);
    }

    header('Location: ' . $adminUrl . '?saved=1#tab-buku');
    exit;
} catch (Throwable $exception) {
    error_log('SIMART book administration error: ' . $exception->getMessage());
    header('Location: ' . $adminUrl . '?error=1#tab-buku');
    exit;
}
