<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota <?= htmlspecialchars($sale['kode_invoice'], ENT_QUOTES, 'UTF-8') ?> | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body class="admin-body">
    <main class="admin-content" style="max-width:800px;margin:32px auto">
        <div class="admin-card">
            <h1>Nota Penjualan</h1>
            <p><strong>Invoice:</strong> <?= htmlspecialchars($sale['kode_invoice'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Tanggal:</strong> <?= htmlspecialchars(date('d-m-Y H:i', strtotime($sale['tanggal_transaksi'])), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Kasir Admin:</strong> <?= htmlspecialchars($sale['nama_admin'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Pembeli:</strong> <?= htmlspecialchars((string) ($sale['nama_pembeli'] ?: 'Pembeli walk-in'), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Pembayaran:</strong> <?= htmlspecialchars(strtoupper($sale['metode_transaksi']), ENT_QUOTES, 'UTF-8') ?> · Lunas</p>
            <table class="data-table">
                <thead><tr><th>Buku</th><th>Jumlah</th><th>Harga satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?= htmlspecialchars($line['judul'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $line['jumlah_beli'] ?></td>
                        <td>Rp <?= number_format((float) $line['harga_satuan'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format((float) $line['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th colspan="3">Total dibayar</th><th>Rp <?= number_format((float) $sale['total_bayar'], 0, ',', '.') ?></th></tr></tfoot>
            </table>
            <div style="margin-top:24px">
                <button class="btn-primary" type="button" onclick="window.print()">Cetak nota</button>
                <a class="btn-secondary" href="index.php#tab-pos">Kembali ke POS</a>
            </div>
        </div>
    </main>
</body>
</html>
