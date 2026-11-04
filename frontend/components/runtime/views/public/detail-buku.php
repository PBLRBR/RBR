<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            Detail Buku | SIMART
        </title>
        <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    </head>
    <body>
        <?php require_once dirname(__DIR__, 3) . '/header.php'; ?>
        <main class="container page-content">
            <a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-outline">
                ← Kembali ke katalog
            </a>
            <section class="tentang-container" style="margin-top:32px;align-items:start">
                <div class="book-cover" style="height:420px;border-radius:16px">
                    <?php if ($book['foto_sampul']): ?><img src="<?= htmlspecialchars(simart_url('/' . ltrim($book['foto_sampul'], '/')), ENT_QUOTES, 'UTF-8') ?>" alt="Sampul <?= htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span>Area sampul buku</span><?php endif; ?>
                </div>
                <div class="tentang-content">
                    <span class="category-badge">
                        <?= htmlspecialchars($book['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <h1 style="font-family:Georgia,serif;font-size:clamp(2rem,4vw,3rem)">
                        <?= htmlspecialchars($book['judul'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <p class="subtitle">
                        <?= htmlspecialchars($book['penulis'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <?php if ($book['jenis_buku'] === 'dijual'): ?><h2>Rp <?= number_format((float) $book['harga'], 0, ',', '.') ?></h2><?php else: ?><h2>Koleksi baca</h2><?php endif; ?>
                    <p>
                        <?= nl2br(htmlspecialchars((string) ($book['deskripsi'] ?? 'Belum ada deskripsi.'), ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                    <p><strong>Jenis:</strong> <?= $book['jenis_buku'] === 'dijual' ? 'Buku untuk dijual' : 'Buku koleksi' ?></p>
                    <?php if ($book['jenis_buku'] === 'dijual'): ?>
                        <p><strong>Ketersediaan:</strong> <?= (int) $book['stok'] > 0 ? 'Tersedia' : 'Stok habis' ?></p>
                        <p class="subtitle">Pembelian buku dilayani langsung oleh kasir di Rumah Budaya Ratna.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        <?php
        require_once dirname(__DIR__, 3) . '/runtime/support/functions.php';
        simart_public_footer('compact');
        ?>
    </body>
</html>
