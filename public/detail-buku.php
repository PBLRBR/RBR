<?php
require_once __DIR__ . '/_data.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Buku tidak ditemukan.'); }
$stmt = db()->prepare('SELECT b.*, COALESCE(k.nama_kategori, \'Tanpa Kategori\') AS nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.id_buku = :id');
$stmt->execute(['id' => $id]);
$b = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$b) { http_response_code(404); exit('Buku tidak ditemukan.'); }
$judul = $b['judul']; $penulis = $b['penulis']; $kategori = $b['nama_kategori'];
$jenis = boolPg($b['bisa_dijual']) ? 'dijual' : 'koleksi';
$harga = (float)($b['harga'] ?? 0); $stok = $b['stok'];
$deskripsi = $b['deskripsi'] ?? ''; $fotoSampul = $b['foto_sampul'];
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
                <!-- <a href="/infoevent.php">Info Event</a> -->
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
        <a href="/katalog.php" class="book-back-link">← <span>Kembali ke katalog</span></a>

        <!-- detail buku -->
        <section class="book-detail-layout">
            <div class="book-detail-cover<?= $fotoSampul ? ' has-image' : '' ?>" aria-label="Sampul <?= htmlspecialchars($judul) ?>">
                <?php if ($fotoSampul): ?>
                    <img src="<?= htmlspecialchars($fotoSampul, ENT_QUOTES, 'UTF-8') ?>" alt="Sampul buku <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <span class="cover-kicker">Koleksi SIMART</span><strong><?= htmlspecialchars($judul) ?></strong><span class="cover-author"><?= htmlspecialchars($penulis) ?></span>
                <?php endif; ?>
            </div>
            <div class="book-detail-content">
                <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                <p class="book-detail-eyebrow">Detail buku</p>
                <h1><?= htmlspecialchars($judul) ?></h1>
                <p class="book-author">oleh <?= htmlspecialchars($penulis) ?></p>
                <p class="book-price"><?= $jenis === 'dijual' ? 'Rp ' . number_format($harga, 0, ',', '.') . ' · Stok ' . (int)$stok : 'Baca di tempat' ?></p>
                <p class="book-description"><?= htmlspecialchars($deskripsi) ?></p>
                <div class="book-facts"><div><span>Status</span><strong><?= $jenis === 'dijual' ? 'Dijual di kasir' : 'Koleksi Rumah Budaya Ratna' ?></strong></div><div><span>Kategori</span><strong><?= htmlspecialchars($kategori) ?></strong></div></div>
                <?php if ($jenis === 'dijual'): ?>
                <p class="book-note">Pembelian buku dilayani langsung oleh kasir di Rumah Budaya Ratna.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
