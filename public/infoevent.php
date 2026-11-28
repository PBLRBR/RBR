<?php
// Halaman Info Event (PHP + HTML dalam 1 file).
$pageTitle = 'Info Event | SIMART';
$events = require __DIR__ . '/_events.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="/images/rbr.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261128-public-qa">
    <link rel="stylesheet" href="/css/responsive.css?v=20261128-public-qa">
</head>

<body>
    <!-- navbar -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">Rumah Budaya Ratna</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <a class="active" href="/infoevent.php">Info Event</a>
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <?php require __DIR__ . '/_nav-auth.php'; ?>
        </div>
    </header>

    <main class="container page-content">
        <h2>Info Event Rumah Budaya Ratna</h2>
        <p class="subtitle">Contoh agenda kegiatan literasi, seni, dan budaya. Jadwal dapat disesuaikan.</p>

        <!-- daftar event -->
        <div class="book-grid">
            <?php foreach ($events as $index => [$ikon, $kategori, $judul, $jadwal, $isi]): ?>
            <article class="book-card">
                <div class="book-cover"><span><?= $ikon ?></span></div>
                <div class="book-info">
                    <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                    <h3><?= htmlspecialchars($judul) ?></h3>
                    <p class="author"><?= htmlspecialchars($jadwal) ?></p>
                    <p><?= htmlspecialchars($isi) ?></p>
                    <div class="book-footer">
                        <span class="price">Rumah Budaya Ratna</span>
                        <a href="/detail-event.php?id=<?= $index ?>" class="btn-primary btn-sm">Detail Event</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- footer -->
    <?php require __DIR__ . '/_footer.php'; ?>
    <?php require __DIR__ . '/_auth-modal.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js?v=20261128-auth-modal"></script>
    <script src="/js/login.js"></script>
    <script src="/js/phone-validation.js"></script>
</body>
</html>
