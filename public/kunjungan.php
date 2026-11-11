<?php
require_once __DIR__ . '/_data.php';
startAppSession();
if (empty($_SESSION['visit_csrf'])) $_SESSION['visit_csrf'] = bin2hex(random_bytes(32));
$pageTitle = 'Buku Tamu | SIMART';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/responsive.css">
    <style>.visit-panel{max-width:600px;margin:36px auto;padding:28px;border:1px solid #ddd5c9;border-radius:16px;background:#fff}.visit-panel .form-group{margin-bottom:16px}.visit-message{margin-top:18px;padding:14px;border-radius:8px;background:#edf6ec}.visit-error{background:#ffeded;color:#8c231b}</style>
</head>
<body>
<header class="navbar"><div class="container nav-container">
    <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">SIMART</span></a>
    <div class="nav-auth"><?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><a class="btn-secondary" href="/admin/">Dashboard Admin</a><?php endif; ?></div>
</div></header>
<main class="container page-content">
    <div class="visit-panel">
        <span class="sub-title">Kunjungan langsung</span>
        <h1>Form Buku Tamu</h1>
        <p class="subtitle">Isi saat datang ke Rumah Budaya Ratna. Form ini <strong>bukan reservasi ruang</strong>, dan tidak memerlukan akun.</p>
        <form id="visit-form" data-csrf="<?= esc($_SESSION['visit_csrf']) ?>">
            <div class="form-group"><label for="visit-name">Nama Pengunjung</label><input id="visit-name" type="text" maxlength="100" required autocomplete="name"></div>
            <div class="form-group"><label for="visit-phone">Nomor WhatsApp</label><input id="visit-phone" type="tel" maxlength="20" pattern="(?:08[1-9][0-9]{7,10}|(?:\+?62)8[1-9][0-9]{7,10})" title="Contoh 081234567890 atau +6281234567890" placeholder="081234567890" required></div>
            <div class="form-group"><label for="visit-purpose">Tujuan Kunjungan</label>
                <select id="visit-purpose" required><option value="">Pilih tujuan</option><option value="Membaca buku">Membaca buku</option><option value="Mengikuti kegiatan">Mengikuti kegiatan</option><option value="Berkunjung ke toko buku">Berkunjung ke toko buku</option><option value="Mengunjungi Rumah Budaya Ratna">Kunjungan umum</option><option value="Diskusi atau komunitas">Diskusi atau komunitas</option></select>
            </div>
            <button type="submit" class="btn-primary full-width" id="visit-submit">Catat Kunjungan</button>
        </form>
        <p id="visit-message" role="status" class="visit-message" hidden></p>
        <p class="subtitle" style="margin-top:18px">Data digunakan untuk pencatatan kunjungan. Mohon hanya mengisi untuk kunjungan yang benar-benar dilakukan.</p>
    </div>
</main>
<footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
<script src="/js/api.js"></script><script src="/js/main.js"></script><script src="/js/kunjungan.js"></script>
</body></html>
