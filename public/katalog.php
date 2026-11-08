<?php
require_once __DIR__ . '/_data.php';
$pageTitle = 'Katalog Buku | SIMART';
$rows = db()->query('SELECT b.id_buku, b.judul, b.penulis, b.bisa_dijual, b.harga, b.stok, b.deskripsi, b.foto_sampul, COALESCE(k.nama_kategori, \'Tanpa Kategori\') AS nama_kategori FROM buku b LEFT JOIN kategori k ON k.id_kategori = b.id_kategori ORDER BY b.id_buku DESC')->fetchAll(PDO::FETCH_ASSOC);
$books = [];
foreach ($rows as $r) {
    $books[(int)$r['id_buku']] = [$r['judul'], $r['penulis'], $r['nama_kategori'], boolPg($r['bisa_dijual']) ? 'dijual' : 'koleksi', (float)($r['harga'] ?? 0), $r['deskripsi'] ?? '', $r['foto_sampul'], $r['stok']];
}
$categories = array_unique(array_column($books, 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/responsive.css">
</head>

<body>
    <!-- navbar -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">SIMART</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a class="active" href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <div class="nav-auth">
                <a class="btn-secondary" href="/?auth=login">Masuk</a>
                <a class="btn-primary" href="/?auth=register">Daftar</a>
            </div>
        </div>
    </header>

    <main class="container page-content">
        <h2>Katalog Buku</h2>
        <p class="subtitle">Jelajahi buku koleksi dan buku yang tersedia untuk dibeli langsung di kasir.</p>

        <!-- pencarian & filter kategori -->
        <div class="filter-panel">
            <input type="text" id="search-input" class="search-input" placeholder="🔍 Cari judul buku atau penulis...">
            <div class="filter-tags" id="filter-tags">
                <button class="tag active" data-category="semua">Semua</button>
                <?php foreach ($categories as $kategori): ?>
                <button class="tag" data-category="<?= htmlspecialchars($kategori) ?>"><?= htmlspecialchars($kategori) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- daftar buku -->
        <div class="book-grid">
            <?php foreach ($books as $id => [$judul, $penulis, $kategori, $jenis, $harga, $deskripsi, $fotoSampul, $stok]): ?>
            <article class="book-card" data-category="<?= htmlspecialchars($kategori) ?>">
                <div class="book-cover<?= $fotoSampul ? ' has-image' : '' ?>">
                    <?php if ($fotoSampul): ?>
                        <img src="<?= htmlspecialchars($fotoSampul, ENT_QUOTES, 'UTF-8') ?>" alt="Sampul buku <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <?php else: ?><span>Cover Buku</span><?php endif; ?>
                </div>
                <div class="book-info">
                    <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                    <h3 class="book-title"><?= htmlspecialchars($judul) ?></h3>
                    <p class="author"><?= htmlspecialchars($penulis) ?></p>
                    <div class="book-footer">
                        <span class="price"><?= $jenis === 'dijual' ? ($stok > 0 ? 'Rp ' . number_format($harga, 0, ',', '.') . ' · Stok ' . (int)$stok : 'Habis') : 'Koleksi baca' ?></span>
                        <a class="btn-primary btn-sm" href="/detail-buku.php?id=<?= $id ?>">Lihat detail</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <p id="no-result" class="text-center margin-top-30" style="display:none;color:var(--color-text-muted)">Buku tidak ditemukan.</p>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
