<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            Riwayat Pembelian | SIMART
        </title>
        <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    </head>
    <body>
        <?php require_once dirname(__DIR__, 3) . '/navbar.php'; ?>
        <main class="container page-content">
            <h1>
                Riwayat Pembelian
            </h1>
            <p class="subtitle">
                SIMART tidak menyediakan checkout atau riwayat pembelian online. Penjualan buku dicatat oleh Admin/kasir di Rumah Budaya Ratna.
            </p>
            <a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary">Kembali ke katalog</a>
        </main>
        <?php
        require_once dirname(__DIR__, 3) . '/runtime/support/functions.php';
        simart_public_footer('compact');
        ?>
    </body>
</html>
