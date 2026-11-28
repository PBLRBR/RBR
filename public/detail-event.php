<?php
require_once __DIR__ . '/_data.php';
$events = require __DIR__ . '/_events.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$event = $id === false || $id === null ? null : ($events[$id] ?? null);
$pageTitle = $event ? $event[2] . ' | Info Event' : 'Event tidak ditemukan | Info Event';
if (!$event) http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261125-reservation-popup">
    <link rel="stylesheet" href="/css/responsive.css?v=20261125-reservation-popup">
</head>
<body>
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
        <a href="/infoevent.php" class="book-back-link">← <span>Kembali ke info event</span></a>
        <?php if ($event): [$ikon, $kategori, $judul, $jadwal, $isi] = $event; ?>
            <section class="book-detail-layout event-detail-layout">
                <div class="event-detail-icon" aria-hidden="true"><?= htmlspecialchars($ikon, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="book-detail-content">
                    <span class="category-badge"><?= htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8') ?></span>
                    <p class="book-detail-eyebrow">Detail event</p>
                    <h1><?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="book-author"><?= htmlspecialchars($jadwal, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="book-description"><?= htmlspecialchars($isi, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="book-facts"><div><span>Tempat</span><strong>Rumah Budaya Ratna</strong></div><div><span>Kategori</span><strong><?= htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8') ?></strong></div></div>
                </div>
            </section>
        <?php else: ?>
            <section class="editorial-hero">
                <span class="eyebrow">Info event</span>
                <h1>Event tidak ditemukan.</h1>
                <p>Event yang Anda cari tidak tersedia. Silakan kembali ke daftar info event.</p>
            </section>
        <?php endif; ?>
    </main>
    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/main.js"></script>
</body>
</html>
