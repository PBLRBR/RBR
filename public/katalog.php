<?php
require_once __DIR__ . '/_data.php';
$pageTitle = 'Katalog Buku | SIMART';
$rows = db()->query('SELECT id_buku, judul, penulis, bisa_dijual, harga, stok, deskripsi, foto_sampul, COALESCE(nama_kategori, \'Tanpa Kategori\') AS nama_kategori FROM v_katalog_buku ORDER BY id_buku DESC')->fetchAll(PDO::FETCH_ASSOC);
$books = [];
foreach ($rows as $r) {
    $books[(int)$r['id_buku']] = [$r['judul'], $r['penulis'], $r['nama_kategori'], boolPg($r['bisa_dijual']) ? 'dijual' : 'koleksi', (float)($r['harga'] ?? 0), $r['deskripsi'] ?? '', $r['foto_sampul'], $r['stok']];
}
$categories = array_unique(array_column($books, 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/responsive.css">
    <style>
        .book-grid { grid-template-columns:repeat(auto-fill, minmax(185px, 1fr)); gap:16px; }
        .book-card-link { cursor:pointer; }
        .book-card-link:focus-visible { outline:3px solid var(--ochre); outline-offset:3px; }
        .book-card-link .book-cover { height:150px; }
        .book-card-link .book-info { padding:12px; }
        .book-card-link .book-title { font-size:1rem; }
        .book-card-link .book-footer { gap:8px; font-size:.84rem; }
        @media(max-width:480px) { .book-grid { grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; } .book-card-link .book-cover { height:125px; } .book-card-link .book-info { padding:10px; } }
    </style>
    <style>
        .book-footer .detail-link { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:9px 16px; border:1px solid var(--terracotta); border-radius:999px; background:var(--terracotta); color:#fff; font-size:.82rem; font-weight:700; letter-spacing:.02em; box-shadow:0 5px 14px rgba(145,74,51,.18); transition:transform .2s ease, background .2s ease, box-shadow .2s ease; }
        .book-footer .detail-link::after { content:'→'; font-size:1rem; transition:transform .2s ease; }
        .book-footer .detail-link:hover { background:var(--espresso); border-color:var(--espresso); box-shadow:0 8px 18px rgba(48,39,32,.2); transform:translateY(-2px); }
        .book-footer .detail-link:hover::after { transform:translateX(3px); }
        .book-footer .detail-link:focus-visible { outline:3px solid var(--ochre); outline-offset:3px; }
        @media(max-width:480px) { .book-footer .detail-link { padding:8px 12px; font-size:.78rem; } }
    </style>
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
        <h2>Katalog Buku</h2>
        <p class="subtitle">Jelajahi buku koleksi dan buku yang tersedia untuk dibeli langsung di kasir.</p>

        <!-- pencarian & filter kategori -->
        <div class="filter-panel">
            <input type="text" id="search-input" class="search-input" placeholder="🔍 Cari judul buku atau penulis...">
            <div class="filter-tags" id="filter-tags">
                <button class="tag active" data-category="semua">Semua</button>
                <?php foreach ($categories as $kategori): ?>
                <button class="tag" data-category="<?= htmlspecialchars($kategori) ?>"><?= htmlspecialchars($kategori) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- daftar buku -->
        <div class="book-grid">
            <?php foreach ($books as $id => [$judul, $penulis, $kategori, $jenis, $harga, $deskripsi, $fotoSampul, $stok]): ?>
            <article class="book-card book-card-link" data-category="<?= htmlspecialchars($kategori) ?>" tabindex="0" role="link" aria-label="Lihat detail buku: <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" onclick="if (!event.target.closest('a')) window.location.href='/detail-buku.php?id=<?= $id ?>'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='/detail-buku.php?id=<?= $id ?>'; }">
                <div class="book-cover<?= $fotoSampul ? ' has-image' : '' ?>">
                    <?php if ($fotoSampul): ?>
                        <img src="<?= htmlspecialchars($fotoSampul, ENT_QUOTES, 'UTF-8') ?>" alt="Sampul buku <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <?php else: ?><span>Cover Buku</span><?php endif; ?>
                </div>
                <div class="book-info">
                    <span class="category-badge"><?= htmlspecialchars($kategori) ?></span>
                    <h3 class="book-title"><?= htmlspecialchars($judul) ?></h3>
                    <p class="author"><?= htmlspecialchars($penulis) ?></p>
                    <div class="book-footer">
                        <span class="price"><?= $jenis === 'dijual' ? ($stok > 0 ? 'Rp ' . number_format($harga, 0, ',', '.') . ' · Stok ' . (int)$stok : 'Habis') : 'Koleksi baca' ?></span>

                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <p id="no-result" class="text-center margin-top-30" style="display:none;color:var(--color-text-muted)">Buku tidak ditemukan.</p>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
