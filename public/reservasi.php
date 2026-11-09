<?php
require_once __DIR__ . '/_data.php';
startAppSession();
$pageTitle = 'Reservasi Ruang | SIMART';
$pdo = db();
$approved = $pdo->query("SELECT r.tanggal_acara, r.jam_mulai, r.jam_selesai, r.nama_acara, t.nama_tempat FROM reservasi r JOIN tempat t ON t.id_tempat = r.id_tempat WHERE r.status_reservasi = 'disetujui' AND r.tanggal_acara >= CURRENT_DATE ORDER BY r.tanggal_acara, r.jam_mulai")->fetchAll(PDO::FETCH_ASSOC);
$bookedDates = array_values(array_unique(array_column($approved, 'tanggal_acara')));
$schedule = [];
foreach (array_slice($approved, 0, 12) as $r) $schedule[] = [$r['nama_tempat'], $r['nama_acara'], substr($r['jam_mulai'],0,5) . '–' . substr($r['jam_selesai'],0,5) . ' (' . $r['tanggal_acara'] . ')'];
$rooms = $pdo->query("SELECT id_tempat, nama_tempat, kapasitas FROM tempat WHERE status = 'aktif' ORDER BY nama_tempat")->fetchAll(PDO::FETCH_ASSOC);
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
                <a href="/profil.php" class="user-profile"><img src="/images/rbr.png" alt="Foto Profil" class="profile-avatar"><span class="user-name"><?= esc($_SESSION['user']['nama'] ?? 'Masuk') ?></span></a>
                <?php if (!empty($_SESSION['user'])): ?><button type="button" class="btn-secondary" data-logout>Keluar</button><?php else: ?><a class="btn-secondary" href="/?auth=login">Masuk</a><?php endif; ?>
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
                        <span class="legend-item"><span class="dot booked"></span>Ada jadwal disetujui</span>
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
                    <form id="reservation-request-form">
                        <div class="form-group">
                            <label>Pilih Ruangan</label>
                            <select id="reserve-place" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?= (int)$room['id_tempat'] ?>"><?= esc($room['nama_tempat']) ?> (<?= (int)$room['kapasitas'] ?> orang)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal acara</label>
                            <input id="reserve-date" type="date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Jam mulai</label><input id="reserve-start" type="time" required></div>
                            <div><label>Jam selesai</label><input id="reserve-end" type="time" required></div>
                        </div>
                        <div class="form-group"><label>Nama acara</label><input id="reserve-name" type="text" maxlength="150" required></div>
                        <div class="form-group"><label>Penanggung jawab</label><input id="reserve-pic" type="text" maxlength="100" required></div>
                        <div class="form-group"><label>Nomor WhatsApp</label><input id="reserve-phone" type="tel" maxlength="20" required></div>
                        <div class="form-group"><label>Deskripsi acara (opsional)</label><textarea id="reserve-desc" rows="4"></textarea></div>
                        <button type="submit" class="btn-primary full-width btn-large">Simpan permintaan reservasi</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/reservasi.js"></script>
</body>
</html>
