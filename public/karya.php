<?php
require_once __DIR__ . '/_data.php';
$pageTitle = 'Karya Bu Ratna | SIMART';
$rows = db()->query('SELECT judul_karya, deskripsi, isi_karya, COALESCE(nama_kategori, \'KARYA\') AS kategori FROM v_katalog_karya ORDER BY id_karya DESC')->fetchAll(PDO::FETCH_ASSOC);
$karya = [];
foreach ($rows as $r) $karya[] = [$r['kategori'], $r['judul_karya'], $r['deskripsi'] ?? '', $r['isi_karya'] ?? '', 'Baca ringkasan'];
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
                <a href="/katalog.php">Katalog Buku</a>
                <a class="active" href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <div class="nav-auth">
                <a class="btn-secondary" href="/?auth=login">Masuk</a>
                <a class="btn-primary" href="/?auth=register">Daftar</a>
            </div>
        </div>
    </header>

    <main class="container page-content">
        <!-- hero -->
        <section class="editorial-hero">
            <span class="eyebrow">Arsip dan warisan</span>
            <h1>Karya yang menyimpan jejak zaman.</h1>
            <p>Ruang untuk mengenal karya, pemikiran, dan perjalanan Sutrisni Ratna Artsanti. Daftar karya dan arsip yang tercatat di SIMART.</p>
        </section>

        <!-- daftar karya -->
        <div class="story-grid">
            <?php foreach ($karya as $index => [$kategori, $judul, $isi, $ringkasan, $tombol]): ?>
            <article class="story-card">
                <span class="story-mark">✦</span>
                <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                <h2><?= htmlspecialchars($judul) ?></h2>
                <p><?= htmlspecialchars($isi) ?></p>
                <button class="btn-outline story-toggle" type="button" aria-expanded="false" aria-controls="story-detail-<?= $index ?>"><?= htmlspecialchars($tombol) ?> <span aria-hidden="true">↓</span></button>
                <div class="story-detail" id="story-detail-<?= $index ?>" hidden><h3>Ringkasan</h3><p><?= htmlspecialchars($ringkasan) ?></p></div>
            </article>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
