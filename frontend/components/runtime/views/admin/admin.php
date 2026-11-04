<?php require_once dirname(__DIR__, 3) . '/runtime/support/functions.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SIMART RBR</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body class="admin-body">

    <!-- Top Admin Bar -->
    <header class="admin-topbar">
        <div class="logo">
            <span class="logo-badge">RBR</span>
            <span class="logo-text">SIMART ADMIN</span>
        </div>
        <?php simart_render_account_menu($adminUser, ['href' => simart_url('/pages/dashboard/index.php'), 'title' => 'Panel Admin']); ?>
    </header>

    <div class="admin-container">
        <?php require dirname(__DIR__, 3) . '/sidebar.php'; ?>

        <!-- Main Content Panel -->
        <main class="admin-content">

            <!-- TAB 1: DASHBOARD ANALYTICS -->
            <section id="tab-analytics" class="tab-content active">
                <h2>Dashboard Analitik</h2>
                <p class="subtitle">Ringkasan performa penjualan dan reservasi Rumah Budaya Ratna.</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Penjualan Buku</h4>
                        <p class="stat-number">Rp <?= number_format((float) $dashboard['total_bulan'], 0, ',', '.') ?></p>
                        <span class="stat-trend positive">Transaksi lunas bulan ini</span>
                    </div>
                    <div class="stat-card">
                        <h4>Buku Terjual</h4>
                        <p class="stat-number"><?= $unitsThisMonth ?> Eks</p>
                        <span class="stat-trend neutral">Terjual bulan ini</span>
                    </div>
                    <div class="stat-card">
                        <h4>Pengajuan Reservasi</h4>
                        <p class="stat-number"><?= count($reservations) ?> Agenda</p>
                        <span class="stat-trend warning"><?= $pendingReservations ?> Perlu Review</span>
                    </div>
                </div>

                <div class="admin-card margin-top-30">
                    <h3>Aktivitas Transaksi Terbaru</h3>
                    <table class="data-table margin-top-15">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSales as $recentSale): ?>
                            <tr>
                                <td><?= htmlspecialchars($recentSale['kode_invoice'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(date('d M Y H:i', strtotime($recentSale['tanggal_transaksi'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $recentSale['items'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>Rp <?= number_format((float) $recentSale['total_bayar'], 0, ',', '.') ?></td>
                                <td><span class="badge badge-success"><?= htmlspecialchars(strtoupper($recentSale['metode_transaksi']), ENT_QUOTES, 'UTF-8') ?> · Lunas</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($recentSales === []): ?><tr><td colspan="5">Belum ada transaksi penjualan.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- TAB 2: MASTER DATA BUKU -->
            <section id="tab-buku" class="tab-content">
                <div class="section-header">
                    <div>
                        <h2>Kelola Master Data Buku</h2>
                        <p class="subtitle">Tambah, edit, atau hapus koleksi buku di toko.</p>
                    </div>
                    <a class="btn-primary" href="index.php#tab-buku">+ Tambah Buku Baru</a>
                </div>

                <?php if (isset($_GET['saved'])): ?><p class="subtitle" role="status">Data buku berhasil disimpan.</p><?php elseif (isset($_GET['error'])): ?><p class="subtitle" role="alert">Data buku belum tersimpan. Periksa kategori dan semua isian.</p><?php endif; ?>

                <div class="admin-card margin-top-15">
                    <h3><?= $editBook ? 'Edit Buku' : 'Tambah Buku' ?></h3>
                    <form method="post" action="books-save.php" class="margin-top-15">
                        <?= simart_csrf_field() ?>
                        <input type="hidden" name="id_buku" value="<?= (int) ($editBook['id_buku'] ?? 0) ?>">
                        <div class="form-group row-time">
                            <div><label>Judul</label><input name="judul" maxlength="200" required value="<?= htmlspecialchars((string) ($editBook['judul'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div><label>Penulis</label><input name="penulis" maxlength="150" required value="<?= htmlspecialchars((string) ($editBook['penulis'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Kategori</label><select name="id_kategori" required><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id_kategori'] ?>"<?= (int) ($editBook['id_kategori'] ?? 0) === (int) $category['id_kategori'] ? ' selected' : '' ?>><?= htmlspecialchars($category['nama_kategori'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                            <div><label>Jenis buku</label><select name="jenis_buku" required><option value="koleksi"<?= ($editBook['jenis_buku'] ?? '') === 'koleksi' ? ' selected' : '' ?>>Koleksi baca</option><option value="dijual"<?= ($editBook['jenis_buku'] ?? '') === 'dijual' ? ' selected' : '' ?>>Dijual</option></select></div>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Penerbit</label><input name="penerbit" maxlength="150" value="<?= htmlspecialchars((string) ($editBook['penerbit'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div><label>Tahun terbit</label><input type="number" name="tahun_terbit" min="1" value="<?= htmlspecialchars((string) ($editBook['tahun_terbit'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Kondisi</label><select name="kondisi"><option value="">Tidak ditentukan</option><option value="baru"<?= ($editBook['kondisi'] ?? '') === 'baru' ? ' selected' : '' ?>>Baru</option><option value="bekas"<?= ($editBook['kondisi'] ?? '') === 'bekas' ? ' selected' : '' ?>>Bekas</option></select></div>
                            <div><label>Harga (wajib untuk buku jual)</label><input type="number" name="harga" min="0" step="0.01" value="<?= htmlspecialchars((string) ($editBook['harga'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div><label>Stok</label><input type="number" name="stok" min="0" required value="<?= htmlspecialchars((string) ($editBook['stok'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"></div>
                        </div>
                        <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="4"><?= htmlspecialchars((string) ($editBook['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        <div class="form-group"><label>Path sampul dari folder assets/images (opsional)</label><input name="foto_sampul" maxlength="255" placeholder="assets/images/nama-file.jpg" value="<?= htmlspecialchars((string) ($editBook['foto_sampul'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <button class="btn-primary" type="submit">Simpan Buku</button>
                        <?php if ($editBook): ?><a class="btn-sm btn-secondary" href="index.php#tab-buku">Batal edit</a><?php endif; ?>
                    </form>
                </div>

                <div class="admin-card margin-top-15">
                    <h3>Tambah Kategori</h3>
                    <form method="post" action="books-save.php" class="form-group row-time margin-top-15">
                        <?= simart_csrf_field() ?><input type="hidden" name="action" value="category_create">
                        <div><label>Nama kategori</label><input name="nama_kategori" maxlength="100" required></div>
                        <div><label>Deskripsi (opsional)</label><input name="deskripsi_kategori" maxlength="1000"></div>
                        <button class="btn-secondary" type="submit">Tambah kategori</button>
                    </form>
                    <div class="margin-top-15">
                        <?php foreach ($categories as $category): ?>
                            <form method="post" action="books-save.php" class="form-group row-time">
                                <?= simart_csrf_field() ?><input type="hidden" name="action" value="category_update"><input type="hidden" name="id_kategori" value="<?= (int) $category['id_kategori'] ?>">
                                <div><label>Nama kategori</label><input name="nama_kategori" maxlength="100" required value="<?= htmlspecialchars($category['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>"></div>
                                <div><label>Deskripsi</label><input name="deskripsi_kategori" maxlength="1000" value="<?= htmlspecialchars((string) ($category['deskripsi'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                                <button class="btn-sm btn-secondary" type="submit">Perbarui</button>
                            </form>
                        <?php endforeach; ?>
                        <?php if ($categories === []): ?><p class="subtitle">Belum ada kategori.</p><?php endif; ?>
                    </div>
                </div>

                <div class="admin-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Kondisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="admin-book-list">
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($book['penulis'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($book['nama_kategori'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $book['harga'] !== null ? 'Rp ' . number_format((float) $book['harga'], 0, ',', '.') : '—' ?></td>
                                <td><?= (int) $book['stok'] ?></td>
                                <td><?= htmlspecialchars((string) ($book['kondisi'] ?? '—'), ENT_QUOTES, 'UTF-8') ?> · <?= $book['jenis_buku'] === 'dijual' ? 'Dijual' : 'Koleksi' ?></td>
                                <td><a class="btn-sm btn-secondary" href="index.php?edit_book=<?= (int) $book['id_buku'] ?>#tab-buku">Edit</a>
                                    <form method="post" action="books-save.php" style="display:inline">
                                        <?= simart_csrf_field() ?><input type="hidden" name="action" value="archive"><input type="hidden" name="id_buku" value="<?= (int) $book['id_buku'] ?>">
                                        <button class="btn-sm btn-danger" type="submit">Arsipkan</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($books === []): ?><tr><td colspan="7">Belum ada buku. Tambahkan kategori lalu masukkan buku.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- TAB 3: KASIR / POS (Sesuai Layout PDF Wireframe) -->
            <section id="tab-pos" class="tab-content">
                <h2>POS Terminal Kasir</h2>
                <p class="subtitle">Catat penjualan langsung setelah pembayaran diterima. Hanya buku jual dengan stok tersedia yang ditampilkan.</p>
                <?php if (isset($_GET['sale_error'])): ?><p class="subtitle" role="alert">Transaksi belum disimpan. Periksa keranjang dan stok buku, lalu coba lagi.</p><?php endif; ?>

                <div class="pos-layout margin-top-15">
                    <!-- Sisi Kiri: Katalog Produk Kasir -->
                    <div class="pos-catalog">
                        <input type="text" class="search-input" placeholder="🔍 Scan barcode atau cari judul buku...">
                        <div class="pos-items-grid">
                            <?php foreach ($saleBooks as $saleBook): ?>
                                <button type="button" class="pos-item-card" data-book-id="<?= (int) $saleBook['id_buku'] ?>" data-title="<?= htmlspecialchars($saleBook['judul'], ENT_QUOTES, 'UTF-8') ?>" data-price="<?= htmlspecialchars((string) $saleBook['harga'], ENT_QUOTES, 'UTF-8') ?>" onclick="addToCart(<?= (int) $saleBook['id_buku'] ?>, this.dataset.title, Number(this.dataset.price))">
                                    <h4><?= htmlspecialchars($saleBook['judul'], ENT_QUOTES, 'UTF-8') ?></h4>
                                    <p>Rp <?= number_format((float) $saleBook['harga'], 0, ',', '.') ?> | Stok: <?= (int) $saleBook['stok'] ?></p>
                                </button>
                            <?php endforeach; ?>
                            <?php if ($saleBooks === []): ?><p>Belum ada buku jual yang tersedia.</p><?php endif; ?>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Keranjang Transaksi -->
                    <div class="pos-cart-panel">
                        <h3>Keranjang Transaksi</h3>
                        <span class="invoice-no">Nomor invoice dibuat saat transaksi disimpan</span>

                        <form id="form-pos-sale" method="post" action="sales-save.php">
                            <?= simart_csrf_field() ?>
                            <input type="hidden" name="idempotency_key" value="<?= htmlspecialchars($saleToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="items_json" id="sale-items-json" value="[]">
                            <div class="form-group"><label>Nama pembeli (opsional untuk walk-in)</label><input name="nama_pembeli" maxlength="100"></div>
                            <div class="form-group"><label>Metode pembayaran</label><select name="metode_transaksi" required><option value="cash">Tunai</option><option value="qris">QRIS</option></select></div>

                        <div class="cart-table-wrapper margin-top-15">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Buku</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items">
                                    <!-- Dynamic items -->
                                </tbody>
                            </table>
                        </div>

                        <div class="cart-summary margin-top-15">
                            <div class="summary-row">
                                <span>Total Bayar:</span>
                                <strong id="cart-total" class="total-price">Rp 0</strong>
                            </div>
                        </div>

                            <button type="submit" class="btn-primary full-width margin-top-15 btn-large">Simpan transaksi lunas</button>
                        </form>
                    </div>
                </div>
            </section>

<section id="tab-reservasi" class="tab-content">
    <h2>Tempat &amp; Reservasi</h2>
    <p class="subtitle">Tambahkan tempat yang bisa dipilih anggota, lalu tinjau permintaan reservasi. Perubahan status dicatat di SIMART.</p>
    <?php if (isset($_GET['reservation_error'])): ?><p class="subtitle" role="alert">Status tidak dapat diperbarui. Muat ulang daftar dan coba lagi.</p><?php elseif (isset($_GET['reservation_saved'])): ?><p class="subtitle" role="status">Status reservasi berhasil diperbarui.</p><?php endif; ?>
    <?php if (($_GET['place_error'] ?? '') === 'duplicate'): ?><p class="subtitle" role="alert">Nama tempat tersebut sudah terdaftar.</p><?php elseif (isset($_GET['place_error'])): ?><p class="subtitle" role="alert">Tempat belum dapat disimpan. Periksa nama dan kapasitasnya.</p><?php elseif (isset($_GET['place_saved'])): ?><p class="subtitle" role="status">Tempat berhasil ditambahkan dan kini tersedia untuk reservasi.</p><?php endif; ?>

    <div class="admin-card place-management-card margin-top-15">
        <div>
            <h3>Tempat Reservasi</h3>
            <p>Tempat baru langsung aktif dan dapat dipilih anggota pada halaman reservasi.</p>
        </div>
        <form method="post" action="places-save.php" class="place-create-form">
            <?= simart_csrf_field() ?>
            <div class="form-group">
                <label for="nama-tempat">Nama tempat</label>
                <input id="nama-tempat" name="nama_tempat" type="text" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="kapasitas-tempat">Kapasitas (orang)</label>
                <input id="kapasitas-tempat" name="kapasitas" type="number" min="1" max="10000" required>
            </div>
            <div class="form-group place-description-field">
                <label for="deskripsi-tempat">Deskripsi (opsional)</label>
                <textarea id="deskripsi-tempat" name="deskripsi" maxlength="2000" rows="2"></textarea>
            </div>
            <button class="btn-primary" type="submit">Tambah tempat</button>
        </form>

        <div class="place-list-wrap">
            <h4>Tempat terdaftar</h4>
            <div class="admin-card place-list-card">
                <?php if ($places === []): ?>
                    <p>Belum ada tempat. Tambahkan tempat pertama melalui formulir di atas.</p>
                <?php else: ?>
                    <ul class="place-list">
                        <?php foreach ($places as $place): ?>
                            <li>
                                <span><strong><?= htmlspecialchars($place['nama_tempat'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($place['deskripsi']): ?><small><?= htmlspecialchars($place['deskripsi'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                                </span>
                                <span class="place-list-meta"><?= (int) $place['kapasitas'] ?> orang · <?= $place['status'] === 'aktif' ? 'Aktif' : 'Tidak aktif' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h3 class="reservation-requests-heading">Permintaan masuk</h3>
    <div class="admin-card margin-top-15" style="overflow-x:auto">
        <table class="data-table">
            <thead><tr><th>Diajukan</th><th>Acara</th><th>Anggota</th><th>Tempat &amp; waktu</th><th>Kontak</th><th>Status / aksi</th></tr></thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($reservation['dibuat_pada'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><strong><?= htmlspecialchars($reservation['nama_acara'], ENT_QUOTES, 'UTF-8') ?></strong><br><?= htmlspecialchars($reservation['penanggung_jawab'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($reservation['nama_member'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($reservation['nama_tempat'], ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars(date('d M Y', strtotime($reservation['tanggal_acara'])) . ' · ' . substr((string) $reservation['jam_mulai'], 0, 5) . '–' . substr((string) $reservation['jam_selesai'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($reservation['kontak_wa'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(ucfirst($reservation['status_reservasi']), ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($reservation['status_reservasi'] === 'menunggu'): ?>
                        <form method="post" action="reservation-update.php" style="margin-top:8px">
                            <?= simart_csrf_field() ?><input type="hidden" name="id_reservasi" value="<?= (int) $reservation['id_reservasi'] ?>">
                            <button class="btn-sm btn-primary" type="submit" name="status" value="disetujui">Setujui</button>
                            <button class="btn-sm btn-danger" type="submit" name="status" value="ditolak">Tolak</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($reservations === []): ?><tr><td colspan="6">Belum ada permintaan reservasi.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

        </main>
    </div>

    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/buku.js"></script>
    <script src="../../assets/js/kategori.js"></script>
    <script src="../../assets/js/karya.js"></script>
    <script src="../../assets/js/reservasi.js"></script>
    <script src="../../assets/js/laporan.js"></script>
    <script src="../../assets/js/transaksi.js"></script>
</body>
</html>
