<?php
// Halaman Info Event (PHP + HTML dalam 1 file).
$pageTitle = 'Info Event | SIMART';

// data dummy event: ikon, kategori, judul, jadwal, deskripsi
$events = [
    ['📚', 'LITERASI', 'Diskusi Buku: Sastra dan Kehidupan', 'Sabtu, 24 Oktober 2026 · 14.00 WIB', 'Ruang berbagi cerita dan gagasan bersama pembaca serta pegiat literasi.'],
    ['🎨', 'SENI & BUDAYA', 'Lokakarya Seni untuk Semua', 'Minggu, 1 November 2026 · 10.00 WIB', 'Kenali ragam ekspresi seni melalui sesi kreatif yang terbuka untuk umum.'],
    ['🎭', 'PERTUNJUKAN', 'Apresiasi Karya Komunitas', 'Sabtu, 14 November 2026 · 19.00 WIB', 'Malam pertunjukan dan temu komunitas untuk merayakan karya lokal.'],
];
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
                <a class="active" href="/infoevent.php">Info Event</a>
                <a href="/katalog.php">Katalog Buku</a>
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
        <h2>Info Event Rumah Budaya Ratna</h2>
        <p class="subtitle">Contoh agenda kegiatan literasi, seni, dan budaya. Jadwal dapat disesuaikan.</p>

        <!-- daftar event -->
        <div class="book-grid">
            <?php foreach ($events as [$ikon, $kategori, $judul, $jadwal, $isi]): ?>
            <article class="book-card">
                <div class="book-cover"><span><?= $ikon ?></span></div>
                <div class="book-info">
                    <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                    <h3><?= htmlspecialchars($judul) ?></h3>
                    <p class="author"><?= htmlspecialchars($jadwal) ?></p>
                    <p><?= htmlspecialchars($isi) ?></p>
                    <div class="book-footer">
                        <span class="price">Rumah Budaya Ratna</span>
                        <a href="#" class="btn-primary btn-sm">Detail Event</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
