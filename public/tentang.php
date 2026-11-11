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
                <a class="active" href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
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
        <!-- hero -->
        <section class="editorial-hero">
            <span class="eyebrow">Ruang temu literasi dan kebudayaan</span>
            <h1>Warisan Ratna Indraswari Ibrahim, ruang hidup bagi sastra dan budaya.</h1>
            <p>Rumah Budaya Ratna adalah ruang budaya berbasis komunitas yang menghidupkan kembali rumah peninggalan Ratna sebagai tempat aman dan terbuka bagi generasi muda untuk membaca, bertukar gagasan, mengembangkan kreativitas, serta mengenal sastra dan kebudayaan.</p>
        </section>

        <!-- profil bu ratna -->
        <section class="tentang-container">
            <div class="tentang-image">
                <img src="/images/rbrfront.jpeg" alt="Bagian depan Rumah Budaya Ratna" style="width:100%;height:340px;object-fit:cover;border-radius:16px">
            </div>
            <div class="tentang-content">
                <span class="sub-title">Sosok di balik layar</span>
                <h2>Ratna Indraswari Ibrahim</h2>
                <p>Ratna Indraswari Ibrahim (24 April 1949–28 Maret 2011) adalah sastrawati dan aktivis asal Malang. Rumah yang dahulu menjadi tempat tinggal sekaligus ruang berkarya beliau kini dihidupkan kembali sebagai Rumah Budaya Ratna oleh keluarga dan sahabatnya, sebagai ruang bagi literasi, seni, dan komunitas.</p>
                <p>SIMART hadir untuk memudahkan masyarakat mengenal koleksi, mengikuti kegiatan, dan mengajukan penggunaan ruang budaya.</p>
                <!-- <a href="/infoevent.php" class="btn-primary">Jelajahi kegiatan</a> -->
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
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
