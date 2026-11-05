<?php
// Halaman Reservasi Ruang (PHP + HTML dalam 1 file).
$pageTitle = 'Reservasi Ruang | SIMART';

// data dummy: tanggal terisi, jadwal disetujui, daftar ruangan
$bookedDates = ['2026-11-14', '2026-11-20', '2026-11-28'];
$schedule = [
    ['Pendopo Utama', 'Workshop Puisi', '10:00–14:00'],
    ['Ruang Diskusi & Lesehan', 'Bedah Buku "Bumi Manusia"', '15:00–17:30'],
];
$rooms = [['Pendopo Utama', 50], ['Ruang Diskusi & Lesehan', 25]];
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
        <h2>Jadwal &amp; Formulir Reservasi Ruang</h2>
        <p class="subtitle">Periksa ketersediaan jadwal pada kalender sebelum mengisi form pengajuan reservasi.</p>

        <div class="reservation-layout">
            <!-- kolom kiri: kalender & jadwal -->
            <div class="calendar-section">
                <div class="calendar-card">
                    <div class="calendar-header">
                        <button id="prev-month" class="btn-sm btn-secondary">&lt;</button>
                        <h3 id="calendar-month-year"></h3>
                        <button id="next-month" class="btn-sm btn-secondary">&gt;</button>
                    </div>
                    <div class="calendar-weekdays">
                        <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                    </div>
                    <div class="calendar-days" id="calendar-days" data-booked-dates='<?= json_encode($bookedDates) ?>'></div>
                    <div class="calendar-legend">
                        <span class="legend-item"><span class="dot available"></span>Tersedia</span>
                        <span class="legend-item"><span class="dot booked"></span>Terisi (Full)</span>
                    </div>
                </div>
                <div class="schedule-info-box">
                    <h4>Jadwal yang Disetujui:</h4>
                    <ul>
                        <?php foreach ($schedule as [$ruang, $acara, $jam]): ?>
                        <li><strong><?= htmlspecialchars($ruang) ?>:</strong> <?= htmlspecialchars($acara) ?> (<?= $jam ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- kolom kanan: form reservasi -->
            <div class="form-section">
                <div class="reservation-form">
                    <h3>Ajukan Reservasi Ruangan</h3>
                    <p class="subtitle">Permintaan berstatus menunggu sampai disetujui admin. <a href="/riwayat.php">Lihat riwayat</a></p>
                    <form data-dummy>
                        <div class="form-group">
                            <label>Pilih Ruangan</label>
                            <select required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($rooms as [$nama, $kapasitas]): ?>
                                <option><?= htmlspecialchars($nama) ?> (<?= $kapasitas ?> orang)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal acara</label>
                            <input type="date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Jam mulai</label><input type="time" required></div>
                            <div><label>Jam selesai</label><input type="time" required></div>
                        </div>
                        <div class="form-group"><label>Nama acara</label><input type="text" maxlength="150" required></div>
                        <div class="form-group"><label>Penanggung jawab</label><input type="text" maxlength="100" required></div>
                        <div class="form-group"><label>Nomor WhatsApp</label><input type="tel" maxlength="20" required></div>
                        <div class="form-group"><label>Deskripsi acara (opsional)</label><textarea rows="4"></textarea></div>
                        <button type="submit" class="btn-primary full-width btn-large">Simpan permintaan reservasi</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/main.js"></script>
</body>
</html>
