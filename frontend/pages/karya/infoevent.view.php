<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Event | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <?php require_once dirname(__DIR__, 2) . '/components/header.php'; ?>

    <main class="container page-content">
        <h2>Info Event Rumah Budaya Ratna</h2>
        <p class="subtitle">Contoh agenda kegiatan literasi, seni, dan budaya. Jadwal dapat disesuaikan.</p>

        <div class="book-grid">
            <article class="book-card">
                <div class="book-cover"><span>📚</span></div>
                <div class="book-info">
                    <span class="category-badge">LITERASI</span>
                    <h3>Diskusi Buku: Sastra dan Kehidupan</h3>
                    <p class="author">Sabtu, 24 Oktober 2026 · 14.00 WIB</p>
                    <p>Ruang berbagi cerita dan gagasan bersama pembaca serta pegiat literasi.</p>
                    <div class="book-footer">
                        <span class="price">Rumah Budaya Ratna</span>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/infoevent.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary btn-sm">Detail Event</a>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <div class="book-cover"><span>🎨</span></div>
                <div class="book-info">
                    <span class="category-badge">SENI &amp; BUDAYA</span>
                    <h3>Lokakarya Seni untuk Semua</h3>
                    <p class="author">Minggu, 1 November 2026 · 10.00 WIB</p>
                    <p>Kenali ragam ekspresi seni melalui sesi kreatif yang terbuka untuk umum.</p>
                    <div class="book-footer">
                        <span class="price">Rumah Budaya Ratna</span>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/infoevent.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary btn-sm">Detail Event</a>
                    </div>
                </div>
            </article>

            <article class="book-card">
                <div class="book-cover"><span>🎭</span></div>
                <div class="book-info">
                    <span class="category-badge">PERTUNJUKAN</span>
                    <h3>Apresiasi Karya Komunitas</h3>
                    <p class="author">Sabtu, 14 November 2026 · 19.00 WIB</p>
                    <p>Malam pertunjukan dan temu komunitas untuk merayakan karya lokal.</p>
                    <div class="book-footer">
                        <span class="price">Rumah Budaya Ratna</span>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/infoevent.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary btn-sm">Detail Event</a>
                    </div>
                </div>
            </article>
        </div>
    </main>
</body>
</html>
