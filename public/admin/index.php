<?php
require_once __DIR__ . '/../../src/auth.php'; // autentikasi
require_admin();                           // blokir hacker!

$pageTitle = 'Admin Dashboard | SIMART RBR';

// helper tampilan
$h  = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
$hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglLabel = fn($d) => $hari[date('w', strtotime($d))] . ', ' . date('j', strtotime($d)) . ' ' . $bulan[date('n', strtotime($d)) - 1] . ' ' . date('Y', strtotime($d));

// data dummy buku: judul, penulis, kategori, harga, stok, kondisi, warna ikon, jumlah di keranjang
$books = [
    ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Sastra', 85000, 12, 'Baru', 'bk-red'],
    ['Laskar Pelangi', 'Andrea Hirata', 'Sastra', 50000, 5, 'Bekas', 'bk-red'],
    ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 95000, 8, 'Baru', 'bk-amber'],
    ['Filosofi Teras', 'Henry Manampiring', 'Filsafat', 78000, 15, 'Baru', 'bk-green'],
    ['Cantik Itu Luka', 'Eka Kurniawan', 'Sastra', 65000, 2, 'Bekas', 'bk-red'],
    ['Atomic Habits', 'James Clear', 'Lainnya', 110000, 6, 'Baru', 'bk-brown'],
    ['Cosmos', 'Carl Sagan', 'Sains', 70000, 3, 'Bekas', 'bk-blue'],
    ['Negeri 5 Menara', 'Ahmad Fuadi', 'Sastra', 45000, 0, 'Bekas', 'bk-red'],
];

// data dummy reservasi: pemohon, agenda, ruangan, tanggal, mulai, selesai, peserta, WA, waktu diajukan, status, bentrok
$reservations = [
    ['Karang Taruna Desa Sukorejo', 'Rapat Persiapan Festival Desa', 'Pendopo Utama', '2026-10-15', '13:00', '16:00', 25, '+62 812-3456-7802', '8 Okt 2026, 14.20', 'pending', true],
    ['Rina Wulandari', 'Bedah Buku "Bumi Manusia"', 'Ruang Diskusi & Lesehan', '2026-10-18', '15:00', '17:30', 15, '+62 812-3456-7803', '9 Okt 2026, 08.40', 'pending', false],
    ['Komunitas Fotografi Blitar', 'Pameran Foto Mini', 'Pendopo Utama', '2026-10-24', '08:00', '12:00', 40, '+62 812-3456-7804', '9 Okt 2026, 19.05', 'pending', false],
    ['Komunitas Sastra Malang', 'Workshop Puisi', 'Pendopo Utama', '2026-10-15', '10:00', '14:00', 30, '+62 812-3456-7801', '5 Okt 2026, 09.15', 'approved', false],
    ['Dimas Pratama', 'Ulang tahun pribadi', 'Ruang Diskusi & Lesehan', '2026-10-12', '19:00', '22:00', 20, '+62 812-3456-7805', '7 Okt 2026, 21.10', 'rejected', false],
];
$statusMeta = ['pending' => ['Menunggu Review', 'badge-warning'], 'approved' => ['Disetujui', 'badge-success'], 'rejected' => ['Ditolak', 'badge-danger']];
$rsvCount = array_count_values(array_column($reservations, 9));

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
            <a href="/" class="btn-sm btn-secondary">Keluar Panel</a>
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
                        <p class="stat-number">Rp 4.850.000</p>
                        <span class="stat-trend positive">+12% bulan ini</span>
                    </div>
                    <div class="stat-card">
                        <h4>Buku Terjual</h4>
                        <p class="stat-number">64</p>
                        <span class="stat-trend neutral">Stok aman</span>
                    </div>
                    <div class="stat-card">
                        <h4>Pengajuan Reservasi</h4>
                        <p class="stat-number">12</p>
                        <span class="stat-trend warning">3 Perlu Review</span>
                    </div>
                    <div class="stat-card">
                        <h4>Total Pengunjung</h4>
                        <p class="stat-number">87</p>
                        <span class="stat-trend neutral">62 via QR · 25 buku tamu manual</span>
                    </div>
                </div>

                <div class="admin-card margin-top-30">
                    <h3>Aktivitas Transaksi Terbaru</h3>
                    <table class="data-table margin-top-15">
                        <thead>
                            <tr><th>ID Transaksi</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Metode</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#INV-202609-001</td>
                                <td>10 Sep 2026</td>
                                <td>Bumi Manusia (2), Sapiens (1)</td>
                                <td>Rp 343.000</td>
                                <td><span class="badge badge-success">Kasir</span></td>
                            </tr>
                            <tr>
                                <td>#INV-202609-002</td>
                                <td>10 Sep 2026</td>
                                <td>Filosofi Teras (1)</td>
                                <td>Rp 88.000</td>
                                <td><span class="badge badge-info">Ambil di Tempat</span></td>
                            </tr>
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
                                <?php foreach ($books as [$judul, $penulis, $kategori, $harga, $stok, $kondisi]): ?>
                                <tr data-judul="<?= $h($judul) ?>" data-penulis="<?= $h($penulis) ?>" data-kategori="<?= $h($kategori) ?>"
                                    data-harga="<?= $harga ?>" data-stok="<?= $stok ?>" data-kondisi="<?= $h($kondisi) ?>" data-deskripsi="">
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
                            <?php foreach ($books as $i => [$judul, $penulis, $kategori, $harga, $stok, $kondisi, $warna]): ?>
                            <div class="pos-item-card<?= $stok === 0 ? ' out-of-stock' : '' ?>" data-id="<?= $i ?>" data-judul="<?= $h($judul) ?>"
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
                                <span class="invoice-no">INV-202610-014</span>
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
                            <button class="btn-primary full-width btn-large">🖨️ Proses Transaksi &amp; Cetak Invoice</button>
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
                    <?php foreach ($reservations as [$pemohon, $agenda, $ruangan, $tanggal, $mulai, $selesai, $peserta, $wa, $diajukan, $status, $bentrok]): ?>
                    <article class="rsv-card rsv-<?= $status ?>" data-status="<?= $status ?>"
                        data-pemohon="<?= $h($pemohon) ?>" data-agenda="<?= $h($agenda) ?>" data-ruangan="<?= $h($ruangan) ?>"
                        data-tanggal="<?= $tanggal ?>" data-mulai="<?= $mulai ?>" data-selesai="<?= $selesai ?>" data-peserta="<?= $peserta ?>">
                        <div class="rsv-card-head">
                            <div>
                                <h3 data-f="agenda"><?= $h($agenda) ?></h3>
                                <p class="rsv-by">Oleh <strong data-f="pemohon"><?= $h($pemohon) ?></strong> · diajukan <?= $diajukan ?></p>
                            </div>
                            <span class="badge rsv-badge <?= $statusMeta[$status][1] ?>"><?= $statusMeta[$status][0] ?></span>
                        </div>
                        <dl class="rsv-details">
                            <div><dt>Ruangan</dt><dd data-f="ruangan"><?= $h($ruangan) ?></dd></div>
                            <div><dt>Tanggal</dt><dd data-f="tanggal"><?= $tglLabel($tanggal) ?></dd></div>
                            <div><dt>Waktu</dt><dd data-f="waktu"><?= $mulai ?> - <?= $selesai ?> WIB</dd></div>
                            <div><dt>Peserta</dt><dd data-f="peserta"><?= $peserta ?> orang</dd></div>
                            <div><dt>Kontak WA</dt><dd><?= $wa ?></dd></div>
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
                            <option>Sastra</option><option>Sejarah</option><option>Filsafat</option>
                            <option>Sains</option><option>Anak</option><option>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="book-kondisi">Kondisi</label>
                        <select id="book-kondisi" required><option>Baru</option><option>Bekas</option></select>
                    </div>
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
                    <label for="book-cover">Foto Sampul <span class="label-optional">(opsional)</span></label>
                    <input type="file" id="book-cover" accept="image/*">
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
                            <option value="Pendopo Utama">Pendopo Utama</option>
                            <option value="Ruang Diskusi &amp; Lesehan">Ruang Diskusi &amp; Lesehan</option>
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
                <div class="form-group"><label for="edit-peserta">Perkiraan Jumlah Peserta</label><input type="number" id="edit-peserta" min="1" required></div>
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

    <script src="/js/admin.js"></script>
</body>
</html>