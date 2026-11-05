<?php
// Halaman Katalog Buku (PHP + HTML dalam 1 file).
$pageTitle = 'Katalog Buku | SIMART';

// data dummy buku: judul, penulis, kategori, jenis (dijual/koleksi), harga, deskripsi
$books = [
    1 => ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Sastra', 'dijual', 85000, 'Novel sejarah tentang Minke di masa kolonial.', null],
    2 => ['Laskar Pelangi', 'Andrea Hirata', 'Sastra', 'dijual', 70000, 'Kisah sepuluh anak Belitung dan semangat belajar mereka.', null],
    3 => ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 'dijual', 120000, 'Ringkasan sejarah singkat umat manusia.', null],
    4 => ['Filosofi Teras', 'Henry Manampiring', 'Filsafat', 'dijual', 88000, 'Pengantar filsafat Stoa untuk kehidupan sehari-hari.', null],
    5 => ['Cantik Itu Luka', 'Eka Kurniawan', 'Sastra', 'koleksi', 0, 'Koleksi baca di tempat, Rumah Budaya Ratna.', null],
    6 => ['Cosmos', 'Carl Sagan', 'Sains', 'koleksi', 0, 'Koleksi baca di tempat, Rumah Budaya Ratna.', null],
];
$categories = array_unique(array_column($books, 2)); // daftar kategori untuk tombol filter
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
            <?php foreach ($books as $id => [$judul, $penulis, $kategori, $jenis, $harga, $deskripsi, $fotoSampul]): ?>
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
                        <span class="price"><?= $jenis === 'dijual' ? 'Rp ' . number_format($harga, 0, ',', '.') : 'Koleksi baca' ?></span>
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
