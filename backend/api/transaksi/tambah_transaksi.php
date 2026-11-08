<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method harus POST.']); exit;
}
wajibAdmin();
$pdo = null;
try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) throw new InvalidArgumentException('Format JSON tidak valid.');
    $metode = $input['metode_transaksi'] ?? '';
    if (!in_array($metode, ['cash','qris'], true)) throw new InvalidArgumentException('Metode harus cash atau qris.');
    $pembeli = trim((string)($input['nama_pembeli'] ?? ''));
    if (strlen($pembeli) > 100) throw new InvalidArgumentException('Nama pembeli terlalu panjang.');
    $rawItems = $input['items'] ?? [];
    if (!is_array($rawItems) || count($rawItems) < 1 || count($rawItems) > 100) {
        throw new InvalidArgumentException('Keranjang harus berisi buku.');
    }
    $items = [];
    foreach ($rawItems as $row) {
        if (!is_array($row)) throw new InvalidArgumentException('Item keranjang tidak valid.');
        $id = filter_var($row['id_buku'] ?? null, FILTER_VALIDATE_INT);
        $qty = filter_var($row['jumlah_beli'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1 || !$qty || $qty < 1) throw new InvalidArgumentException('ID buku atau jumlah tidak valid.');
        $items[$id] = ($items[$id] ?? 0) + $qty;
        if ($items[$id] > 10000) throw new InvalidArgumentException('Jumlah terlalu banyak.');
    }
    ksort($items, SORT_NUMERIC);
    $pdo = require __DIR__ . '/../../config/database.php';
    $pdo->beginTransaction();
    $lock = $pdo->prepare('SELECT id_buku, judul, harga, stok, bisa_dijual FROM buku WHERE id_buku = :id FOR UPDATE');
    $lines = []; $total = 0;
    foreach ($items as $id => $qty) {
        $lock->execute(['id'=>$id]);
        $book = $lock->fetch(PDO::FETCH_ASSOC);
        if (!$book) throw new InvalidArgumentException("Buku ID $id tidak ditemukan.");
        if (!in_array($book['bisa_dijual'], [true, 1, '1', 't', 'true'], true)) {
            throw new InvalidArgumentException('Buku ' . $book['judul'] . ' tidak untuk dijual.');
        }
        if ((int)$book['stok'] < $qty) throw new InvalidArgumentException('Stok buku ' . $book['judul'] . ' tidak cukup.');
        $price = (float)$book['harga'];
        $subtotal = round($price * $qty, 2);
        $total += $subtotal;
        $lines[] = ['id_buku'=>$id,'jumlah_beli'=>$qty,'subtotal'=>$subtotal,'judul'=>$book['judul']];
    }
    $total = round($total, 2);
    $invoice = 'INV-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $pdo->prepare('INSERT INTO transaksi_penjualan (id_user, kode_invoice, tanggal_transaksi, nama_pembeli, metode_transaksi, total_bayar) VALUES (:kasir,:invoice,CURRENT_TIMESTAMP,:pembeli,:metode,:total) RETURNING id_transaksi, kode_invoice, total_bayar');
    $stmt->execute(['kasir'=>$_SESSION['user']['id_user'],'invoice'=>$invoice,'pembeli'=>$pembeli ?: null,'metode'=>$metode,'total'=>$total]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    $details = $pdo->prepare('INSERT INTO detail_transaksi_penjualan (id_transaksi, id_buku, jumlah_beli, subtotal) VALUES (:transaksi,:buku,:qty,:subtotal)');
    $stock = $pdo->prepare('UPDATE buku SET stok = stok - :qty WHERE id_buku = :buku');
    foreach ($lines as $line) {
        $details->execute(['transaksi'=>$transaction['id_transaksi'],'buku'=>$line['id_buku'],'qty'=>$line['jumlah_beli'],'subtotal'=>$line['subtotal']]);
        $stock->execute(['buku'=>$line['id_buku'],'qty'=>$line['jumlah_beli']]);
    }
    $pdo->commit();
    http_response_code(201);
    echo json_encode(['success'=>true,'message'=>'Transaksi tersimpan.','data'=>['transaksi'=>$transaction,'items'=>$lines]]);
} catch (InvalidArgumentException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(422); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan transaksi.']);
}
