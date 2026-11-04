<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku | SIMART</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(simart_url('/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require dirname(__DIR__, 2) . '/components/header.php'; ?>

    <main class="container page-content">
        <h2>Katalog Buku</h2>
        <p class="subtitle">Jelajahi buku koleksi dan buku yang tersedia untuk dibeli langsung di kasir.</p>

        <div class="filter-panel">
            <input type="text" id="search-input" class="search-input" placeholder="🔍 Cari judul buku atau penulis...">
            <div class="filter-tags" id="filter-tags">
                <button class="tag active" data-category="semua">Semua</button>
                <?php foreach ($categories as $category): ?>
                    <button class="tag" data-category="<?= (int) $category['id_kategori'] ?>"><?= htmlspecialchars($category['nama_kategori'], ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="book-grid" id="book-grid">
            <?php foreach ($books as $book): ?>
                <article class="book-card" data-category="<?= (int) $book['id_kategori'] ?>">
                    <div class="book-cover">
                        <?php if ($book['foto_sampul']): ?>
                            <img src="<?= htmlspecialchars(simart_url('/' . ltrim($book['foto_sampul'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="Sampul <?= htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php else: ?><span>Cover Buku</span><?php endif; ?>
                    </div>
                    <div class="book-info">
                        <span class="category-badge"><?= htmlspecialchars($book['nama_kategori'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3 class="book-title"><?= htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="author"><?= htmlspecialchars($book['penulis'], ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="book-footer">
                            <span class="price"><?= $book['jenis_buku'] === 'dijual' && $book['harga'] !== null ? 'Rp ' . number_format((float) $book['harga'], 0, ',', '.') : 'Koleksi baca' ?></span>
                            <a class="btn-primary btn-sm" href="<?= htmlspecialchars(simart_url('/pages/buku/detail.php?id=' . (int) $book['id_buku']), ENT_QUOTES, 'UTF-8') ?>">Lihat detail</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if ($books === []): ?><p class="subtitle">Belum ada buku yang dipublikasikan di katalog.</p><?php endif; ?>
        </div>

        <p id="no-result" class="text-center margin-top-30" style="display: none; color: var(--color-text-muted);">Buku tidak ditemukan.</p>
    </main>

    <script src="<?= htmlspecialchars(simart_url('/assets/js/api.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(simart_url('/assets/js/buku.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(simart_url('/assets/js/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
