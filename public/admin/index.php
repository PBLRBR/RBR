<?php
require_once __DIR__ . '/../_data.php';
requireAppUser(true);
$pdo = db();
$pageTitle = 'Admin Dashboard | SIMART RBR';
$h = fn($v) => esc($v);
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tglLabel = fn($d) => $hari[date('w', strtotime($d))] . ', ' . date('j', strtotime($d)) . ' ' . $bulan[date('n', strtotime($d))-1] . ' ' . date('Y', strtotime($d));
$kategoriList = $pdo->query('SELECT id_kategori,nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll(PDO::FETCH_ASSOC);
$dbBooks = $pdo->query("SELECT *, COALESCE(nama_kategori,'Tanpa Kategori') AS kategori_tampil FROM v_katalog_buku ORDER BY id_buku DESC")->fetchAll(PDO::FETCH_ASSOC);
$books = [];
foreach ($dbBooks as $b) {
    $sale = boolPg($b['bisa_dijual']);
    $books[] = [$b['judul'],$b['penulis'],$b['kategori_tampil'],(float)($b['harga'] ?? 0),(int)($b['stok'] ?? 0),$b['kondisi'] ?? 'koleksi','bk-blue',(int)$b['id_buku'],(int)$b['id_kategori'],$b['deskripsi'] ?? '',$b['penerbit'] ?? '',$b['tahun_terbit'] ?? '',$sale,$b['foto_sampul'] ?? ''];
}
$resRows = $pdo->query("SELECT r.id_reservasi,r.id_tempat,r.nama_acara,r.penanggung_jawab,r.kontak_wa,r.deskripsi_acara,r.tanggal_acara,r.jam_mulai,r.jam_selesai,r.status_reservasi,t.nama_tempat FROM reservasi r JOIN tempat t ON r.id_tempat=t.id_tempat ORDER BY r.id_reservasi DESC")->fetchAll(PDO::FETCH_ASSOC);
$metaStatus = ['menunggu'=>'pending','disetujui'=>'approved','ditolak'=>'rejected'];
$reservations = [];
foreach ($resRows as $r) {
    $reservations[] = [$r['penanggung_jawab'],$r['nama_acara'],$r['nama_tempat'],$r['tanggal_acara'],substr($r['jam_mulai'],0,5),substr($r['jam_selesai'],0,5),0,$r['kontak_wa'],'—',$metaStatus[$r['status_reservasi']] ?? 'pending',false,(int)$r['id_reservasi'],(int)$r['id_tempat'],$r['deskripsi_acara'] ?? ''];
}
$statusMeta = ['pending'=>['Menunggu Review','badge-warning'],'approved'=>['Disetujui','badge-success'],'rejected'=>['Ditolak','badge-danger']];
$rsvCount = array_count_values(array_column($reservations,9));
$metrics = $pdo->query('SELECT total_pendapatan AS total, jumlah_transaksi AS transaksi, jumlah_buku_terjual FROM v_dashboard_penjualan')->fetch(PDO::FETCH_ASSOC);
$terjual = (int)$metrics['jumlah_buku_terjual'];
$bulanIni = $pdo->query("SELECT * FROM fn_ringkasan_penjualan(date_trunc('month', CURRENT_DATE)::DATE, CURRENT_DATE)")->fetch(PDO::FETCH_ASSOC);
// Contoh nyata CTE, JOIN, GROUP BY, dan HAVING untuk dashboard.
$bukuTerlaris = $pdo->query('WITH penjualan_buku AS (
    SELECT b.id_buku, b.judul, SUM(d.jumlah_beli) AS total_terjual, SUM(d.subtotal) AS pendapatan
    FROM detail_transaksi_penjualan d
    JOIN buku b ON b.id_buku = d.id_buku
    GROUP BY b.id_buku, b.judul
    HAVING SUM(d.jumlah_beli) > 0
) SELECT judul, total_terjual, pendapatan FROM penjualan_buku
  ORDER BY total_terjual DESC, pendapatan DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
$totalPengunjung = (int)$pdo->query('SELECT COUNT(*) FROM buku_tamu')->fetchColumn();
$latest = $pdo->query('SELECT t.kode_invoice, t.tanggal_transaksi, t.metode_transaksi, t.total_bayar, COALESCE(SUM(d.jumlah_beli),0) AS jumlah_buku FROM transaksi_penjualan t LEFT JOIN detail_transaksi_penjualan d ON d.id_transaksi=t.id_transaksi GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/admin.css">
</head>

<body class="admin-body">
    <!-- top admin bar -->
    <header class="admin-topbar">
        <div class="logo">
            <img src="/images/rbr.png" alt="Logo" class="logo-img">
            <span class="logo-text">SIMART ADMIN</span>
        </div>
        <div class="admin-user-info">
            <button class="btn-sm btn-secondary" type="button" data-logout>Logout</button>
        </div>
    </header>
    <div class="admin-container">
        <!-- sidebar -->
        <aside class="admin-sidebar">
            <nav class="sidebar-menu">
                <button class="sidebar-btn active" data-tab="analytics">📊 Analytics Dashboard</button>
                <button class="sidebar-btn" data-tab="buku">🗂️ Master Buku</button>
                <button class="sidebar-btn" data-tab="pos">🛒 Kasir Penjualan (POS)</button>
                <button class="sidebar-btn" data-tab="reservasi">
                    <span>📅 Persetujuan Reservasi</span>
                    <span class="nav-count"><?= $rsvCount['pending'] ?? 0 ?></span>
                </button>
            </nav>
        </aside>

        <!-- pages -->
        <main class="admin-content">
            <!-- analytics -->
            <section id="tab-analytics" class="tab-content active">
                <div class="section-header">
                    <div>
                        <h2>Dashboard Analitik</h2>
                        <p class="subtitle">Ringkasan performa penjualan, reservasi, dan kunjungan Rumah Budaya Ratna.</p>
                    </div>
                    <div class="period-filters" aria-label="Filter periode analitik">
                        <button class="period-filter">Hari Ini</button>
                        <button class="period-filter">7 Hari Terakhir</button>
                        <button class="period-filter active">Bulan Ini</button>
                        <button class="period-filter">Tahun Ini</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Penjualan Buku</h4>
                        <p class="stat-number"><?= $rp($metrics['total']) ?></p>
                        <span class="stat-trend neutral">Bulan ini: <?= $rp($bulanIni['total_pendapatan']) ?> · Total seluruh periode di atas</span>
                    </div>
                    <div class="stat-card">
                        <h4>Buku Terjual</h4>
                        <p class="stat-number"><?= $terjual ?></p>
                        <span class="stat-trend neutral">Seluruh periode</span>
                    </div>
                    <div class="stat-card">
                        <h4>Pengajuan Reservasi</h4>
                        <p class="stat-number"><?= count($reservations) ?></p>
                        <span class="stat-trend warning"><?= $rsvCount['pending'] ?? 0 ?> perlu review</span>
                    </div>
                    <div class="stat-card">
                        <h4>Total Pengunjung</h4>
                        <p class="stat-number"><?= $totalPengunjung ?></p>
                        <span class="stat-trend neutral">Total catatan buku tamu</span>
                    </div>
                </div>

                <div class="admin-card margin-top-30">
                    <h3>Aktivitas Transaksi Terbaru</h3>
                    <table class="data-table margin-top-15">
                        <thead>
                            <tr><th>ID Transaksi</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Metode</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest as $t): ?>
                            <tr><td><?= $h($t['kode_invoice']) ?></td><td><?= $h($t['tanggal_transaksi']) ?></td><td><?= (int)$t['jumlah_buku'] ?> buku</td><td><?= $rp($t['total_bayar']) ?></td><td><?= $h($t['metode_transaksi']) ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$latest): ?><tr><td colspan="5">Belum ada transaksi.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="admin-card margin-top-30">
                    <h3>5 Buku Terlaris (Semua Periode)</h3>
                    <table class="data-table margin-top-15">
                        <thead><tr><th>Judul Buku</th><th>Total Terjual</th><th>Nilai Penjualan</th></tr></thead>
                        <tbody>
                            <?php foreach ($bukuTerlaris as $b): ?>
                            <tr><td><?= $h($b['judul']) ?></td><td><?= (int)$b['total_terjual'] ?></td><td><?= $rp($b['pendapatan']) ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$bukuTerlaris): ?><tr><td colspan="3">Belum ada penjualan.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- master buku -->
            <section id="tab-buku" class="tab-content">
                <div class="section-header">
                    <div>
                        <h2>Kelola Master Buku</h2>
                        <p class="subtitle">Atur koleksi buku yang dijual dan dipinjamkan di Rumah Budaya Ratna.</p>
                    </div>
                </div>

                <!-- tabel buku -->
                <div class="master-panel active" id="master-buku">
                    <div class="admin-card">
                        <div class="table-toolbar">
                            <input type="text" id="book-search" class="search-input" placeholder="🔍 Cari judul atau penulis">
                            <span class="table-count" id="book-count">Menampilkan <?= count($books) ?> buku</span>
                            <button class="btn-primary" id="btn-add-book">+ Tambah Buku Baru</button>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr><th>Judul Buku</th><th>Penulis</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Kondisi</th><th>Aksi</th></tr>
                            </thead>
                            <tbody id="admin-book-list">
                                <?php foreach ($books as [$judul, $penulis, $kategori, $harga, $stok, $kondisi, $warna, $idBuku, $idKategori, $deskripsi, $penerbit, $tahun, $sale, $foto]): ?>
                                <tr data-id="<?= $idBuku ?>" data-judul="<?= $h($judul) ?>" data-penulis="<?= $h($penulis) ?>" data-kategori="<?= $idKategori ?>"
                                    data-harga="<?= $harga ?>" data-stok="<?= $stok ?>" data-kondisi="<?= $h($kondisi) ?>" data-deskripsi="<?= $h($deskripsi) ?>" data-penerbit="<?= $h($penerbit) ?>" data-tahun="<?= $h($tahun) ?>" data-sale="<?= $sale ? '1':'0' ?>" data-foto="<?= $h($foto) ?>">
                                    <td><strong><?= $h($judul) ?></strong></td>
                                    <td><?= $h($penulis) ?></td>
                                    <td><?= $h($kategori) ?></td>
                                    <td><?= $rp($harga) ?></td>
                                    <td><?php if ($stok === 0): ?><span class="stock-out">Habis</span><?php elseif ($stok <= 2): ?><span class="stock-low"><?= $stok ?></span><?php else: echo $stok; endif; ?></td>
                                    <td><span class="badge"><?= $h($kondisi) ?></span></td>
                                    <td>
                                        <button class="btn-sm btn-secondary btn-edit-book">Edit</button>
                                        <button class="btn-sm btn-danger btn-delete-book">Hapus</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- kasir -->
            <section id="tab-pos" class="tab-content">
                <div class="section-header">
                    <div>
                        <h2>POS Terminal Kasir</h2>
                        <p class="subtitle">Pilih buku dari katalog, atur jumlahnya di keranjang, lalu proses pembayaran dan cetak invoice.</p>
                    </div>
                </div>

                <div class="pos-layout">
                    <!-- katalog -->
                    <div class="pos-catalog">
                        <input type="text" id="pos-search" class="search-input" placeholder="🔍 Cari judul atau penulis">
                        <p class="pos-catalog-meta"><span id="pos-count">Menampilkan <?= count($books) ?> buku</span> · klik buku untuk menambahkan ke keranjang</p>

                        <div class="pos-items-grid">
                            <?php foreach ($books as [$judul, $penulis, $kategori, $harga, $stok, $kondisi, $warna, $idBuku, $idKategori, $desc, $penerbit, $tahun, $sale]): ?>
                            <?php if (!$sale) continue; ?>
                            <div class="pos-item-card<?= $stok === 0 ? ' out-of-stock' : '' ?>" data-id="<?= $idBuku ?>" data-judul="<?= $h($judul) ?>"
                                data-harga="<?= $harga ?>" data-stok="<?= $stok ?>" data-kondisi="<?= $h($kondisi) ?>" data-warna="<?= $warna ?>">
                                <div class="pos-item-main">
                                    <span class="book-icon <?= $warna ?>"></span>
                                    <div class="pos-item-info">
                                        <h4><?= $h($judul) ?></h4>
                                        <p class="pos-item-author"><?= $h($penulis) ?></p>
                                        <div class="pos-item-tags"><span class="badge"><?= $h($kondisi) ?></span><span class="pos-stock<?= $stok <= 2 ? ' low' : '' ?>">Stok <?= $stok ?></span></div>
                                    </div>
                                </div>
                                <div class="pos-item-foot">
                                    <span class="pos-item-price"><?= $rp($harga) ?></span>
                                    <?php if ($stok === 0): ?><span class="stock-pill empty">Habis</span><?php else: ?><span class="in-cart-tag" hidden></span><?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- keranjang (diisi JS) -->
                    <div class="pos-cart-panel">
                        <div class="cart-head">
                            <div>
                                <h3>Keranjang Transaksi</h3>
                                <span class="invoice-no">Invoice dibuat saat transaksi disimpan</span>
                            </div>
                            <button type="button" class="btn-clear" id="cart-clear">Kosongkan</button>
                        </div>

                        <div class="cart-list" id="cart-items"></div>

                        <div class="cart-summary">
                            <div class="summary-row"><span>Subtotal (<span id="cart-qty">0</span> buku)</span><span id="cart-subtotal">Rp 0</span></div>
                            <div class="summary-row summary-total">
                                <span>Total Bayar</span>
                                <strong id="cart-total" class="total-price">Rp 0</strong>
                            </div>
                        </div>

                        <div class="cart-payment">
                            <span class="cart-label">Metode pembayaran</span>
                            <div class="pay-methods">
                                <button type="button" class="pay-method active" data-method="tunai">💵 Tunai</button>
                                <button type="button" class="pay-method" data-method="qris">📱 QRIS</button>
                            </div>
                            <div class="cash-box">
                                <label for="cash-received">Uang diterima</label>
                                <div class="cash-input">
                                    <span class="cash-prefix">Rp</span>
                                    <input type="text" id="cash-received" inputmode="numeric" placeholder="0">
                                </div>
                                <div class="cash-change"><span id="cash-change-label">Kembalian</span><strong id="cash-change">Rp 0</strong></div>
                            </div>
                        </div>

                        <div class="cart-actions">
                            <button id="btn-checkout" class="btn-primary full-width btn-large" type="button">Proses Transaksi</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- reservasi -->
            <section id="tab-reservasi" class="tab-content">
                <h2>Persetujuan Reservasi Ruang</h2>
                <p class="subtitle">
                    Permintaan reservasi yang masuk dari formulir web. Diskusikan detailnya lewat WhatsApp,
                    lalu <strong>edit</strong> jika ada perubahan atau langsung <strong>setujui</strong>
                    agar jadwal tampil di kalender publik.
                </p>

                <!-- ringkasan status -->
                <div class="rsv-summary">
                    <div class="rsv-summary-item"><span class="rsv-summary-num" data-count="pending"><?= $rsvCount['pending'] ?? 0 ?></span><span class="rsv-summary-label">Menunggu review</span></div>
                    <div class="rsv-summary-item"><span class="rsv-summary-num" data-count="approved"><?= $rsvCount['approved'] ?? 0 ?></span><span class="rsv-summary-label">Disetujui</span></div>
                    <div class="rsv-summary-item"><span class="rsv-summary-num" data-count="rejected"><?= $rsvCount['rejected'] ?? 0 ?></span><span class="rsv-summary-label">Ditolak</span></div>
                </div>

                <!-- filter -->
                <div class="rsv-filters">
                    <button class="rsv-filter active" data-filter="pending">Menunggu Review</button>
                    <button class="rsv-filter" data-filter="approved">Disetujui</button>
                    <button class="rsv-filter" data-filter="rejected">Ditolak</button>
                    <button class="rsv-filter" data-filter="all">Semua</button>
                </div>

                <!-- daftar permintaan -->
                <div class="rsv-list">
                    <?php foreach ($reservations as [$pemohon, $agenda, $ruangan, $tanggal, $mulai, $selesai, $peserta, $wa, $diajukan, $status, $bentrok, $idReservasi, $idTempat, $deskripsi]): ?>
                    <article class="rsv-card rsv-<?= $status ?>" data-id="<?= $idReservasi ?>" data-idtempat="<?= $idTempat ?>" data-status="<?= $status ?>"
                        data-pemohon="<?= $h($pemohon) ?>" data-agenda="<?= $h($agenda) ?>" data-ruangan="<?= $h($ruangan) ?>" data-wa="<?= $h($wa) ?>" data-deskripsi="<?= $h($deskripsi) ?>"
                        data-tanggal="<?= $h($tanggal) ?>" data-mulai="<?= $h($mulai) ?>" data-selesai="<?= $h($selesai) ?>">
                        <div class="rsv-card-head">
                            <div>
                                <h3 data-f="agenda"><?= $h($agenda) ?></h3>
                                <p class="rsv-by">Oleh <strong data-f="pemohon"><?= $h($pemohon) ?></strong> </p>
                            </div>
                            <span class="badge rsv-badge <?= $statusMeta[$status][1] ?>"><?= $statusMeta[$status][0] ?></span>
                        </div>
                        <dl class="rsv-details">
                            <div><dt>Ruangan</dt><dd data-f="ruangan"><?= $h($ruangan) ?></dd></div>
                            <div><dt>Tanggal</dt><dd data-f="tanggal"><?= $tglLabel($tanggal) ?></dd></div>
                            <div><dt>Waktu</dt><dd data-f="waktu"><?= $mulai ?> - <?= $selesai ?> WIB</dd></div>
                            
                            <div><dt>Kontak WA</dt><dd><?= $h($wa) ?></dd></div>
                        </dl>
                        <?php if ($bentrok): ?>
                        <div class="rsv-warning">⚠️ <strong>Bentrok jadwal:</strong> Workshop Puisi (10:00 - 14:00) sudah disetujui di ruangan &amp; tanggal yang sama. Edit jadwalnya sebelum menyetujui.</div>
                        <?php endif; ?>
                        <!-- tombol tampil sesuai status kartu (diatur CSS) -->
                        <div class="rsv-actions">
                            <a class="btn-sm btn-secondary" href="https://wa.me/<?= preg_replace('/\D/', '', $wa) ?>" target="_blank" rel="noopener">💬 Chat WA</a>
                            <button class="btn-sm btn-secondary hide-rejected" data-action="edit">✏️ Edit</button>
                            <button class="btn-sm btn-danger only-pending" data-action="reject">Tolak</button>
                            <button class="btn-sm btn-approve only-pending" data-action="approve">✔ Setujui</button>
                            <button class="btn-sm btn-secondary only-approved" data-action="reset">Batalkan Persetujuan</button>
                            <button class="btn-sm btn-secondary only-rejected" data-action="reset">Kembalikan ke Review</button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                    <p class="subtitle" id="rsv-empty" hidden>Tidak ada permintaan reservasi pada filter ini.</p>
                </div>
            </section>
        </main>
    </div>

    <!-- popup tambah / edit buku -->
    <div class="popup-overlay" id="book-popup" hidden>
        <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="book-popup-title">
            <div class="popup-header">
                <h3 id="book-popup-title">Tambah Buku Baru</h3>
                <button type="button" class="popup-close" data-popup-close aria-label="Tutup">✕</button>
            </div>
            <form id="form-add-book">
                <div class="form-group">
                    <label for="book-judul">Judul Buku</label>
                    <input type="text" id="book-judul" placeholder="Contoh: Bumi Manusia" required>
                </div>
                <div class="form-group">
                    <label for="book-penulis">Penulis</label>
                    <input type="text" id="book-penulis" placeholder="Contoh: Pramoedya Ananta Toer" required>
                </div>
                <div class="form-group row-time">
                    <div>
                        <label for="book-kategori">Kategori</label>
                        <select id="book-kategori" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($kategoriList as $kategori): ?>
                            <option value="<?= (int)$kategori['id_kategori'] ?>"><?= $h($kategori['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="book-kondisi">Kondisi</label>
                        <select id="book-kondisi" required><option value="baru">Baru</option><option value="bekas">Bekas</option></select>
                    </div>
                </div>
                <div class="form-group"><label><input id="book-sale" type="checkbox" checked> Buku untuk dijual</label></div>
                <div class="form-group row-time">
                    <div><label for="book-penerbit">Penerbit (opsional)</label><input id="book-penerbit" type="text"></div>
                    <div><label for="book-tahun">Tahun terbit (opsional)</label><input id="book-tahun" type="number" min="0" max="9999"></div>
                </div>
                <div class="form-group row-time">
                    <div><label for="book-harga">Harga (Rp)</label><input type="number" id="book-harga" min="0" placeholder="85000" required></div>
                    <div><label for="book-stok">Stok</label><input type="number" id="book-stok" min="0" placeholder="10" required></div>
                </div>
                <div class="form-group">
                    <label for="book-deskripsi">Deskripsi Singkat <span class="label-optional">(opsional)</span></label>
                    <textarea id="book-deskripsi" rows="3" placeholder="Sinopsis atau catatan kondisi buku"></textarea>
                </div>
                <div class="form-group">
                    <label for="book-cover">URL Foto Sampul (opsional)</label>
                    <input type="url" id="book-cover" placeholder="https://...">
                </div>
                <div class="popup-actions">
                    <button type="button" class="btn-secondary" data-popup-close>Batal</button>
                    <button type="submit" class="btn-primary">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <!-- popup edit reservasi -->
    <div class="popup-overlay" id="rsv-popup" hidden>
        <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="rsv-popup-title">
            <div class="popup-header">
                <h3 id="rsv-popup-title">Edit Permintaan Reservasi</h3>
                <button type="button" class="popup-close" data-popup-close aria-label="Tutup">✕</button>
            </div>
            <form id="form-edit-reservation">
                <div class="form-group row-time">
                    <div>
                        <label for="edit-ruangan">Ruangan</label>
                        <select id="edit-ruangan" required>
                            <?php foreach ($pdo->query('SELECT id_tempat,nama_tempat FROM tempat ORDER BY nama_tempat') as $tempat): ?>
                            <option value="<?= (int)$tempat['id_tempat'] ?>"><?= $h($tempat['nama_tempat']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label for="edit-tanggal">Tanggal</label><input type="date" id="edit-tanggal" required></div>
                </div>
                <div class="form-group row-time">
                    <div><label for="edit-jam-mulai">Jam Mulai</label><input type="time" id="edit-jam-mulai" required></div>
                    <div><label for="edit-jam-selesai">Jam Selesai</label><input type="time" id="edit-jam-selesai" required></div>
                </div>
                <div class="form-group"><label for="edit-pemohon">Nama Pemohon / Komunitas</label><input type="text" id="edit-pemohon" required></div>
                <div class="form-group"><label for="edit-agenda">Agenda / Keperluan</label><input type="text" id="edit-agenda" required></div>
                
                <div class="popup-actions">
                    <button type="button" class="btn-secondary" data-popup-close>Batal</button>
                    <button type="submit" class="btn-secondary btn-outline-primary">Simpan Perubahan</button>
                    <button type="button" class="btn-primary" id="btn-save-approve">Simpan &amp; Setujui</button>
                </div>
            </form>
        </div>
    </div>

    <!-- popup konfirmasi (hapus buku, setujui & tolak reservasi) -->
    <div class="popup-overlay" id="confirm-popup" hidden>
        <div class="popup-box popup-sm" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="popup-header">
                <h3 id="confirm-title">Konfirmasi</h3>
                <button type="button" class="popup-close" data-popup-close aria-label="Tutup">✕</button>
            </div>
            <p class="confirm-message" id="confirm-message"></p>
            <div class="popup-actions">
                <button type="button" class="btn-secondary" data-popup-close>Batal</button>
                <button type="button" class="btn-primary" id="confirm-ok">Ya</button>
            </div>
        </div>
    </div>

    <script src="/js/api.js"></script>
    <script src="/js/admin.js"></script>
</body>
</html>