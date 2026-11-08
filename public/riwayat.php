<?php
require_once __DIR__ . '/_data.php';
requireAppUser();
$pageTitle = 'Riwayat Reservasi | SIMART';
$stmt = db()->prepare('SELECT r.tanggal_acara, r.jam_mulai, r.jam_selesai, r.nama_acara, r.status_reservasi, r.alasan_penolakan, t.nama_tempat FROM reservasi r JOIN tempat t ON t.id_tempat = r.id_tempat WHERE r.id_user = :id ORDER BY r.id_reservasi DESC');
$stmt->execute(['id' => $_SESSION['user']['id_user']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$badges = ['disetujui' => 'badge-success', 'ditolak' => 'badge-danger', 'menunggu' => 'badge-warning'];
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
    <!-- navbar (tampilan anggota, dummy) -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">SIMART</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a class="active" href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <div class="nav-auth">
                <a href="/profil.php" class="user-profile"><img src="/images/rbr.png" alt="Foto Profil" class="profile-avatar"><span class="user-name"><?= esc($_SESSION['user']['nama']) ?></span></a>
                <button class="btn-secondary" type="button" data-logout>Keluar</button>
            </div>
        </div>
    </header>

    <main class="container page-content">
        <h1>Riwayat Reservasi</h1>
        <p class="subtitle">Pantau status pengajuan penggunaan ruang budaya Anda.</p>

        <!-- tabel riwayat -->
        <div class="admin-card" style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr><th>Tanggal acara</th><th>Ruang</th><th>Waktu kegiatan</th><th>Agenda</th><th>Status</th><th>Alasan penolakan</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= esc($r['tanggal_acara']) ?></td>
                        <td><?= esc($r['nama_tempat']) ?></td>
                        <td><?= esc(substr($r['jam_mulai'],0,5) . '–' . substr($r['jam_selesai'],0,5)) ?></td>
                        <td><?= esc($r['nama_acara']) ?></td>
                        <td><span class="badge <?= $badges[$r['status_reservasi']] ?? '' ?>"><?= esc(ucfirst($r['status_reservasi'])) ?></span></td>
                        <td><?= esc($r['alasan_penolakan'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$reservations): ?><tr><td colspan="6">Belum ada reservasi.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
