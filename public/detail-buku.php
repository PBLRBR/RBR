<?php
// Halaman Detail Buku (PHP + HTML dalam 1 file).
// data dummy buku: judul, penulis, kategori, jenis (dijual/koleksi), harga, deskripsi
$books = [
    1 => ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Sastra', 'dijual', 85000, 'Novel sejarah tentang Minke di masa kolonial.'],
    2 => ['Laskar Pelangi', 'Andrea Hirata', 'Sastra', 'dijual', 70000, 'Kisah sepuluh anak Belitung dan semangat belajar mereka.'],
    3 => ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 'dijual', 120000, 'Ringkasan sejarah singkat umat manusia.'],
    4 => ['Filosofi Teras', 'Henry Manampiring', 'Filsafat', 'dijual', 88000, 'Pengantar filsafat Stoa untuk kehidupan sehari-hari.'],
    5 => ['Cantik Itu Luka', 'Eka Kurniawan', 'Sastra', 'koleksi', 0, 'Koleksi baca di tempat, Rumah Budaya Ratna.'],
    6 => ['Cosmos', 'Carl Sagan', 'Sains', 'koleksi', 0, 'Koleksi baca di tempat, Rumah Budaya Ratna.'],
];
$id = (int) ($_GET['id'] ?? 1);
if (!isset($books[$id])) $id = 1; // fallback ke buku pertama
[$judul, $penulis, $kategori, $jenis, $harga, $deskripsi] = $books[$id];
$pageTitle = $judul . ' | SIMART';
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
                <a href="/infoevent.php">Info Event</a>
                <a class="active" href="/katalog.php">Katalog Buku</a>
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
        <a href="/katalog.php" class="btn-outline">← Kembali ke katalog</a>

        <!-- detail buku -->
        <section class="tentang-container" style="margin-top:32px;align-items:start">
            <div class="book-cover" style="height:420px;border-radius:16px"><span>Area sampul buku</span></div>
            <div class="tentang-content">
                <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                <h1 style="font-family:Georgia,serif;font-size:clamp(2rem,4vw,3rem)"><?= htmlspecialchars($judul) ?></h1>
                <p class="subtitle"><?= htmlspecialchars($penulis) ?></p>
                <h2><?= $jenis === 'dijual' ? 'Rp ' . number_format($harga, 0, ',', '.') : 'Koleksi baca' ?></h2>
                <p><?= htmlspecialchars($deskripsi) ?></p>
                <p><strong>Jenis:</strong> <?= $jenis === 'dijual' ? 'Buku untuk dijual' : 'Buku koleksi' ?></p>
                <?php if ($jenis === 'dijual'): ?>
                <p><strong>Ketersediaan:</strong> Tersedia</p>
                <p class="subtitle">Pembelian buku dilayani langsung oleh kasir di Rumah Budaya Ratna.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
