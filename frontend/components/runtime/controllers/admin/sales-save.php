<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
require_once dirname(__DIR__, 3) . '/runtime/support/database.php';
$admin = simart_require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();

$token = (string) ($_POST['idempotency_key'] ?? '');
$buyerName = trim((string) ($_POST['nama_pembeli'] ?? ''));
$paymentMethod = (string) ($_POST['metode_transaksi'] ?? '');
$items = json_decode((string) ($_POST['items_json'] ?? ''), true);
$validToken = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $token) === 1;

if (!$validToken || mb_strlen($buyerName) > 100 || !in_array($paymentMethod, ['cash', 'qris'], true)
    || !is_array($items) || $items === [] || count($items) > 100) {
    header('Location: ' . simart_url('/pages/dashboard/index.php') . '?sale_error=invalid#tab-pos');
    exit;
}

$quantities = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        header('Location: ' . simart_url('/pages/dashboard/index.php') . '?sale_error=invalid#tab-pos');
        exit;
    }
    $bookId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
    if (!$bookId || $bookId < 1 || !$quantity || $quantity < 1 || $quantity > 10000
        || isset($quantities[$bookId])) {
        header('Location: ' . simart_url('/pages/dashboard/index.php') . '?sale_error=invalid#tab-pos');
        exit;
    }
    $quantities[$bookId] = $quantity;
}
ksort($quantities, SORT_NUMERIC);

$db = simart_db();
try {
    $db->beginTransaction();

    $existingSale = $db->prepare('SELECT id_transaksi FROM transaksi_penjualan WHERE idempotency_key = :key');
    $existingSale->execute([':key' => $token]);
    $existingId = $existingSale->fetchColumn();
    if ($existingId !== false) {
        $db->commit();
        header('Location: ' . simart_url('/pages/dashboard/receipt.php') . '?id=' . (int) $existingId);
        exit;
    }

    $adminRole = $db->prepare("SELECT role FROM users WHERE id_user = :id FOR SHARE");
    $adminRole->execute([':id' => $admin['id_user']]);
    if ($adminRole->fetchColumn() !== 'admin') {
        throw new RuntimeException('Admin account is no longer authorized.');
    }

    $placeholders = [];
    $parameters = [];
    foreach (array_keys($quantities) as $index => $bookId) {
        $placeholder = ':book_' . $index;
        $placeholders[] = $placeholder;
        $parameters[$placeholder] = $bookId;
    }
    $bookQuery = $db->prepare(
        "SELECT id_buku, judul, harga, stok FROM buku WHERE is_archived = FALSE "
        . "AND jenis_buku = 'dijual' AND id_buku IN (" . implode(', ', $placeholders) . ") "
        . 'ORDER BY id_buku FOR UPDATE'
    );
    $bookQuery->execute($parameters);
    $books = [];
    foreach ($bookQuery->fetchAll() as $book) {
        $books[(int) $book['id_buku']] = $book;
    }
    if (count($books) !== count($quantities)) {
        throw new DomainException('Buku jual tidak ditemukan.');
    }
    foreach ($quantities as $bookId => $quantity) {
        if ((int) $books[$bookId]['stok'] < $quantity) {
            throw new DomainException('Stok buku tidak mencukupi.');
        }
    }

    $invoiceNumber = (int) $db->query("SELECT nextval('simart_invoice_seq')")->fetchColumn();
    $invoiceDate = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    $invoiceCode = 'SIM-' . $invoiceDate->format('Ymd') . '-' . str_pad((string) $invoiceNumber, 6, '0', STR_PAD_LEFT);
    $createSale = $db->prepare(
        'INSERT INTO transaksi_penjualan (id_user, idempotency_key, kode_invoice, nama_pembeli, metode_transaksi) '
        . 'VALUES (:id_user, :idempotency_key, :invoice, :buyer, :method) RETURNING id_transaksi'
    );
    $createSale->execute([
        ':id_user' => $admin['id_user'],
        ':idempotency_key' => $token,
        ':invoice' => $invoiceCode,
        ':buyer' => $buyerName !== '' ? $buyerName : null,
        ':method' => $paymentMethod,
    ]);
    $transactionId = (int) $createSale->fetchColumn();

    $createLine = $db->prepare(
        'INSERT INTO detail_transaksi_penjualan (id_transaksi, id_buku, jumlah_beli, harga_satuan) '
        . 'VALUES (:transaction, :book, :quantity, :price)'
    );
    $decreaseStock = $db->prepare(
        "UPDATE buku SET stok = stok - :quantity WHERE id_buku = :book "
        . "AND jenis_buku = 'dijual' AND is_archived = FALSE AND stok >= :available_quantity"
    );
    foreach ($quantities as $bookId => $quantity) {
        $createLine->execute([
            ':transaction' => $transactionId,
            ':book' => $bookId,
            ':quantity' => $quantity,
            ':price' => $books[$bookId]['harga'],
        ]);
        $decreaseStock->execute([
            ':quantity' => $quantity,
            ':book' => $bookId,
            ':available_quantity' => $quantity,
        ]);
        if ($decreaseStock->rowCount() !== 1) {
            throw new DomainException('Stok buku berubah. Silakan muat ulang daftar kasir.');
        }
    }

    $db->commit();
    header('Location: ' . simart_url('/pages/dashboard/receipt.php') . '?id=' . $transactionId);
    exit;
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    if ($exception instanceof PDOException && $exception->getCode() === '23505') {
        $retry = $db->prepare('SELECT id_transaksi FROM transaksi_penjualan WHERE idempotency_key = :key');
        $retry->execute([':key' => $token]);
        $existingId = $retry->fetchColumn();
        if ($existingId !== false) {
            header('Location: ' . simart_url('/pages/dashboard/receipt.php') . '?id=' . (int) $existingId);
            exit;
        }
    }
    if (!$exception instanceof DomainException) {
        error_log('SIMART POS transaction failed: ' . $exception->getMessage());
    }
    $_SESSION['sale_error'] = 'save';
    header('Location: ' . simart_url('/pages/dashboard/index.php') . '?sale_error=save#tab-pos');
    exit;
}
