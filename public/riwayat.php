<?php
// Halaman Riwayat Reservasi (PHP + HTML dalam 1 file).
$pageTitle = 'Riwayat Reservasi | SIMART';

// data dummy: tanggal pengajuan, ruang, waktu kegiatan, agenda, status
$reservations = [
    ['8 Okt 2026', 'Pendopo Utama', '15 Okt 2026 · 13:00–16:00', 'Rapat Persiapan Festival Desa', 'menunggu'],
    ['5 Okt 2026', 'Pendopo Utama', '15 Okt 2026 · 10:00–14:00', 'Workshop Puisi', 'disetujui'],
    ['7 Okt 2026', 'Ruang Diskusi & Lesehan', '12 Okt 2026 · 19:00–22:00', 'Ulang tahun pribadi', 'ditolak'],
];
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
                <a href="/profil.php" class="user-profile"><img src="/images/rbr.png" alt="Foto Profil" class="profile-avatar"><span class="user-name">Budi Santoso</span></a>
                <a class="btn-secondary" href="/">Keluar</a>
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
                    <tr><th>Tanggal pengajuan</th><th>Ruang</th><th>Waktu kegiatan</th><th>Agenda</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as [$dibuat, $ruang, $waktu, $agenda, $status]): ?>
                    <tr>
                        <td><?= $dibuat ?></td>
                        <td><?= htmlspecialchars($ruang) ?></td>
                        <td><?= $waktu ?></td>
                        <td><?= htmlspecialchars($agenda) ?></td>
                        <td><span class="badge <?= $badges[$status] ?>"><?= ucfirst($status) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
