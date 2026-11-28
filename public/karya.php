<?php
require_once __DIR__ . '/_data.php';
$pageTitle = 'Karya Bu Ratna | SIMART';
$storiesFile = __DIR__ . '/data/cerpen-ratna.json';
$stories = json_decode(file_get_contents($storiesFile), true) ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261125-reservation-popup">
    <link rel="stylesheet" href="/css/responsive.css?v=20261125-reservation-popup">
</head>

<body>
    <!-- navbar -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">Rumah Budaya Ratna</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a href="/katalog.php">Katalog Buku</a>
                <a class="active" href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <?php require __DIR__ . '/_nav-auth.php'; ?>
        </div>
    </header>

    <main class="container page-content">
        <!-- hero -->
        <section class="editorial-hero">
            <span class="eyebrow">Cerpen pilihan</span>
            <h1>Menelusuri dunia dalam cerita Ratna.</h1>
            <p>Empat cerpen Ratna Indraswari Ibrahim dari arsip yang dihimpun Rumah Budaya Ratna. Pilih “Baca cerpen lengkap” untuk membuka ceritanya.</p>
        </section>

        <!-- daftar karya -->
        <div class="story-grid">
            <?php foreach ($stories as $index => $story): ?>
            <?php
                $intro = $story['paragraphs'][0] ?? 'Baca cerpen lengkap karya Ratna Indraswari Ibrahim.';
                preg_match('/^.{1,220}/us', $intro, $excerptMatch);
                $excerpt = $excerptMatch[0] ?? $intro;
                if (strlen($excerpt) < strlen($intro)) $excerpt .= '…';
            ?>
            <a class="story-card story-card-link" href="/detail-karya.php?id=<?= $index ?>" aria-label="Baca cerpen: <?= htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="category-badge">Cerpen</span>
                <h2><?= htmlspecialchars($story['title']) ?></h2>
                <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                <span class="story-card-action">Baca cerpen lengkap <span aria-hidden="true">→</span></span>
            </a>
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
