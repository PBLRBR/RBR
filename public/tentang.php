<?php
// Halaman Tentang Kami (PHP + HTML dalam 1 file).
$pageTitle = 'Tentang Kami | SIMART';

// kartu nilai yang dirawat
$values = [
    ['01', 'Literasi', 'Menghubungkan pembaca dengan buku, gagasan, dan percakapan yang membuka wawasan.'],
    ['02', 'Seni & budaya', 'Memberi panggung bagi ekspresi, tradisi, dan karya komunitas lokal.'],
    ['03', 'Ruang bersama', 'Menyediakan tempat hangat untuk belajar, berkolaborasi, dan saling mengenal.'],
];
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
                <a class="active" href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <?php require __DIR__ . '/_nav-auth.php'; ?>
        </div>
    </header>

    <main class="container page-content">
        <!-- hero -->
        <section class="editorial-hero">
            <span class="eyebrow">Ruang temu literasi dan kebudayaan</span>
            <h1>Warisan Ratna Indraswari Ibrahim, ruang hidup bagi sastra dan budaya.</h1>
            <p>Rumah Budaya Ratna adalah ruang budaya berbasis komunitas yang menghidupkan kembali rumah peninggalan Ratna sebagai tempat aman dan terbuka bagi generasi muda untuk membaca, bertukar gagasan, mengembangkan kreativitas, serta mengenal sastra dan kebudayaan.</p>
        </section>

        <!-- sosok di balik Rumah Budaya Ratna -->
        <section class="tentang-container">
            <div class="tentang-image">
                <img src="/images/ratna-indraswari-ibrahim.jpg" alt="Ratna Indraswari Ibrahim bersama buku-bukunya" loading="lazy" style="width:100%;height:480px;object-fit:cover;object-position:center 38%;border-radius:16px">
            </div>
            <div class="tentang-content">
                <span class="sub-title">Sosok di balik layar</span>
                <h2>Ratna Indraswari <em>Ibrahim</em></h2>
                <p>Lahir di Malang pada 24 April 1949, Ratna adalah anak keenam dari sebelas bersaudara. Ayahnya, Saleh Ibrahim, seorang penulis dan aktivis berdarah Padang. Ratna pernah berkata bahwa lahir di keluarga itu membuatnya merasa seperti anak ikan yang bertemu dengan air. Ayahnya berpulang pada 1967, saat Ratna baru berusia 18 tahun.</p>
                <p>Polio menyerangnya pada usia 12 tahun, dan sejak itu ia menjalani hari-hari di atas kursi roda. Dari kursi roda itulah Ratna menulis ratusan cerpen yang dimuat media lokal, nasional, hingga internasional.</p>
                <p>Ratna juga dikenal sebagai pejuang hak difabel. Ia menolak difabel dipandang sebagai objek untuk dikasihani, dan terus mengingatkan bahwa mereka adalah subjek dan warga negara yang setara. Ratna berpulang di Malang pada 28 Maret 2011.</p>
            </div>
        </section>

        <!-- yang dirawat -->
        <section style="padding:64px 0">
            <div class="section-header">
                <div>
                    <span class="sub-title">Yang kami rawat</span>
                    <h2>Budaya hidup melalui kebersamaan</h2>
                </div>
            </div>
            <div class="story-grid">
                <?php foreach ($values as [$no, $judul, $isi]): ?>
                <article class="story-card">
                    <span class="story-mark"><?= $no ?></span>
                    <h3><?= htmlspecialchars($judul) ?></h3>
                    <p><?= htmlspecialchars($isi) ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- footer -->
    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
