<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            Karya Bu Ratna | SIMART
        </title>
        <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    </head>
    <body>
        <?php require_once dirname(__DIR__, 2) . '/components/header.php'; ?>
        <main class="container page-content">
            <section class="editorial-hero">
                <span class="eyebrow">
                    Arsip dan warisan
                </span>
                <h1>
                    Karya yang menyimpan jejak zaman.
                </h1>
                <p>
                    Ruang untuk mengenal karya, pemikiran, dan perjalanan Sutrisni Ratna Artsanti. Konten berikut merupakan contoh tampilan dan dapat diganti dengan data karya terverifikasi.
                </p>
            </section>
            <div class="story-grid">
                <article class="story-card">
                    <span class="story-mark">
                        ✦
                    </span>
                    <span class="category-badge">
                        ESAI
                    </span>
                    <h2>
                        Catatan tentang ruang budaya
                    </h2>
                    <p>
                        Gagasan tentang rumah sebagai tempat bertukar pikiran, merawat ingatan, dan menumbuhkan kreativitas.
                    </p>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-outline">
                        Baca ringkasan →
                    </a>
                </article>
                <article class="story-card">
                    <span class="story-mark">
                        ✦
                    </span>
                    <span class="category-badge">
                        SASTRA
                    </span>
                    <h2>
                        Fragmen perjalanan
                    </h2>
                    <p>
                        Catatan pilihan dari perjalanan panjang mendampingi pendidikan, sastra, dan komunitas seni.
                    </p>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-outline">
                        Baca ringkasan →
                    </a>
                </article>
                <article class="story-card">
                    <span class="story-mark">
                        ✦
                    </span>
                    <span class="category-badge">
                        ARSIP
                    </span>
                    <h2>
                        Merawat ingatan bersama
                    </h2>
                    <p>
                        Dokumentasi kegiatan dan kolaborasi yang membentuk Rumah Budaya Ratna.
                    </p>
                    <a href="<?= htmlspecialchars(simart_url('/pages/karya/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-outline">
                        Lihat arsip →
                    </a>
                </article>
            </div>
            <p class="subtitle" style="margin-top:28px">
                Catatan: lengkapi judul, tahun, sampul, dan ringkasan setelah inventaris karya tersedia.
            </p>
        </main>
        <?php $footerVariant = 'karya'; require_once dirname(__DIR__, 2) . '/components/footer.php'; ?>
        <script src="../../assets/js/api.js"></script>
        <script src="../../assets/js/karya.js"></script>
    </body>
</html>
