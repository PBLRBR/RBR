<?php
require_once __DIR__ . '/_data.php';
$stories = json_decode(file_get_contents(__DIR__ . '/data/cerpen-ratna.json'), true) ?: [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || !isset($stories[$id])) {
    http_response_code(404);
    exit('Cerpen tidak ditemukan.');
}
$story = $stories[$id];
$pageTitle = $story['title'] . ' | Karya Bu Ratna';
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
                <a href="/katalog.php">Katalog Buku</a>
                <a class="active" href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <?php require __DIR__ . '/_nav-auth.php'; ?>
        </div>
    </header>

    <main class="container page-content">
        <a href="/karya.php" class="book-back-link">← <span>Kembali ke karya</span></a>

        <section class="book-detail-layout story-detail-layout">
            <div class="book-detail-content">
                <span class="category-badge">Cerpen</span>
                <p class="book-detail-eyebrow">Detail karya</p>
                <h1><?= htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="book-author">oleh Ratna Indraswari Ibrahim</p>
                <p class="book-description">Baca cerpen lengkap dari arsip karya Ratna Indraswari Ibrahim yang dihimpun Rumah Budaya Ratna.</p>
                <div class="book-facts">
                    <div><span>Jenis karya</span><strong>Cerpen</strong></div>
                    <div><span>Arsip</span><strong>Rumah Budaya Ratna</strong></div>
                </div>
            </div>
        </section>

        <article class="story-full-text">
            <h2><?= htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($story['paragraphs'] as $paragraph): ?>
                <p><?= nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endforeach; ?>
        </article>
    </main>

    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
