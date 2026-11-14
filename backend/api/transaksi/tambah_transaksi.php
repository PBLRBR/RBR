<?php
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus POST.']);
    exit;
}
wajibAdmin();

$pdo = null;
try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new InvalidArgumentException('Format JSON tidak valid.');
    }

    // Satu UUID harus dipertahankan FE saat mencoba ulang checkout yang sama.
    $checkoutKey = $input['idempotency_key'] ?? null;
    if (!is_string($checkoutKey) || !preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
        $checkoutKey
    )) {
        throw new InvalidArgumentException('ID checkout tidak valid. Pastikan JavaScript POS sudah diperbarui.');
    }

    $metode = $input['metode_transaksi'] ?? '';
    if (!in_array($metode, ['cash', 'qris'], true)) {
        throw new InvalidArgumentException('Metode pembayaran harus cash atau qris.');
    }
    if (!is_string($input['nama_pembeli'] ?? '')) {
        throw new InvalidArgumentException('Nama pembeli harus teks.');
    }
    $pembeli = trim($input['nama_pembeli'] ?? '');
    if (strlen($pembeli) > 100) {
        throw new InvalidArgumentException('Nama pembeli terlalu panjang.');
    }

    $rawItems = $input['items'] ?? [];
    if (!is_array($rawItems) || count($rawItems) < 1 || count($rawItems) > 100) {
        throw new InvalidArgumentException('Keranjang harus berisi 1–100 item.');
    }
    $items = [];
    foreach ($rawItems as $row) {
        if (!is_array($row)) throw new InvalidArgumentException('Item keranjang tidak valid.');
        $id = filter_var($row['id_buku'] ?? null, FILTER_VALIDATE_INT);
        $qty = filter_var($row['jumlah_beli'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1 || !$qty || $qty < 1) {
            throw new InvalidArgumentException('ID buku atau jumlah tidak valid.');
        }
        $items[$id] = ($items[$id] ?? 0) + $qty;
        if ($items[$id] > 10000) throw new InvalidArgumentException('Jumlah terlalu banyak.');
    }
    ksort($items, SORT_NUMERIC);

    // Fingerprint mencegah UUID checkout dipakai ulang untuk isi keranjang berbeda.
    $canonicalItems = [];
    foreach ($items as $id => $qty) {
        $canonicalItems[] = ['id_buku' => (int)$id, 'jumlah_beli' => (int)$qty];
    }
    $fingerprint = hash('sha256', json_encode([
        'metode_transaksi' => $metode,
        'nama_pembeli' => $pembeli,
        'items' => $canonicalItems,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

    $pdo = require __DIR__ . '/../../config/database.php';
    $pdo->beginTransaction();

    // INSERT dibuat SEBELUM penguncian stok. UNIQUE index mengantrekan
    // dua request dengan key sama; yang kedua hanya menerima transaksi pertama.
    $invoice = 'INV-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $reserve = $pdo->prepare(
        'INSERT INTO transaksi_penjualan
            (id_user, kode_invoice, tanggal_transaksi, nama_pembeli,
             metode_transaksi, total_bayar, idempotency_key, request_hash)
         VALUES (:kasir, :invoice, CURRENT_TIMESTAMP, :pembeli,
                 :metode, 0, :checkout_key, :request_hash)
         ON CONFLICT (id_user, idempotency_key) DO NOTHING
         RETURNING id_transaksi, kode_invoice, total_bayar'
    );
    $kasir = (int)$_SESSION['user']['id_user'];
    $reserve->execute([
        'kasir' => $kasir,
        'invoice' => $invoice,
        'pembeli' => $pembeli !== '' ? $pembeli : null,
        'metode' => $metode,
        'checkout_key' => $checkoutKey,
        'request_hash' => $fingerprint,
    ]);
    $transaksi = $reserve->fetch(PDO::FETCH_ASSOC);

    if (!$transaksi) {
        $existingQuery = $pdo->prepare(
            'SELECT id_transaksi, kode_invoice, total_bayar, request_hash
             FROM transaksi_penjualan
             WHERE id_user = :kasir AND idempotency_key = :checkout_key'
        );
        $existingQuery->execute(['kasir' => $kasir, 'checkout_key' => $checkoutKey]);
        $existing = $existingQuery->fetch(PDO::FETCH_ASSOC);
        if (!$existing) throw new RuntimeException('Transaksi duplikat tidak dapat ditemukan.');

        if (!is_string($existing['request_hash']) || !hash_equals($existing['request_hash'], $fingerprint)) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'ID checkout sudah digunakan untuk isi transaksi berbeda. Muat ulang POS dan periksa riwayat transaksi.'
            ]);
            exit;
        }

        $details = $pdo->prepare(
            'SELECT d.id_buku, d.jumlah_beli, d.subtotal, b.judul
             FROM detail_transaksi_penjualan d
             JOIN buku b ON b.id_buku = d.id_buku
             WHERE d.id_transaksi = :id
             ORDER BY d.id_buku'
        );
        $details->execute(['id' => $existing['id_transaksi']]);
        $lines = $details->fetchAll(PDO::FETCH_ASSOC);
        unset($existing['request_hash']);
        $pdo->commit();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Checkout ini sudah tercatat sebelumnya; tidak dibuat transaksi baru.',
            'data' => [
                'transaksi' => $existing,
                'items' => $lines,
                'idempotent_replay' => true,
            ]
        ]);
        exit;
    }

    // Untuk setiap item, validasi stok dan harga terbaru di database.
    $lock = $pdo->prepare(
        'SELECT id_buku, judul, harga, stok, bisa_dijual
         FROM buku WHERE id_buku = :id AND active = TRUE FOR UPDATE'
    );
    $lines = [];
    $total = 0;
    foreach ($items as $id => $qty) {
        $lock->execute(['id' => $id]);
        $book = $lock->fetch(PDO::FETCH_ASSOC);
        if (!$book) throw new InvalidArgumentException("Buku ID $id tidak ditemukan atau tidak aktif.");
        if (!in_array($book['bisa_dijual'], [true, 1, '1', 't', 'true'], true)) {
            throw new InvalidArgumentException('Buku ' . $book['judul'] . ' tidak untuk dijual.');
        }
        if ((int)$book['stok'] < $qty) {
            throw new InvalidArgumentException('Stok buku ' . $book['judul'] . ' tidak cukup.');
        }
        $harga = (float)$book['harga'];
        $subtotal = round($harga * $qty, 2);
        $total += $subtotal;
        $lines[] = [
            'id_buku' => (int)$id,
            'jumlah_beli' => (int)$qty,
            'subtotal' => $subtotal,
            'judul' => $book['judul'],
        ];
    }
    $total = round($total, 2);

    $detailInsert = $pdo->prepare(
        'INSERT INTO detail_transaksi_penjualan
         (id_transaksi, id_buku, jumlah_beli, subtotal)
         VALUES (:transaksi, :buku, :qty, :subtotal)'
    );
    $stockUpdate = $pdo->prepare(
        'UPDATE buku SET stok = stok - :qty WHERE id_buku = :id'
    );
    foreach ($lines as $line) {
        $detailInsert->execute([
            'transaksi' => $transaksi['id_transaksi'],
            'buku' => $line['id_buku'],
            'qty' => $line['jumlah_beli'],
            'subtotal' => $line['subtotal'],
        ]);
        $stockUpdate->execute(['qty' => $line['jumlah_beli'], 'id' => $line['id_buku']]);
    }

    $finish = $pdo->prepare(
        'UPDATE transaksi_penjualan SET total_bayar = :total
         WHERE id_transaksi = :id
         RETURNING id_transaksi, kode_invoice, total_bayar'
    );
    $finish->execute(['id' => $transaksi['id_transaksi'], 'total' => $total]);
    $transaksi = $finish->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi tersimpan.',
        'data' => [
            'transaksi' => $transaksi,
            'items' => $lines,
            'idempotent_replay' => false,
        ]
    ]);
} catch (InvalidArgumentException $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi.']);
}
