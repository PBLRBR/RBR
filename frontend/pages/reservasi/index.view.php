<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Ruang | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <?php if ($currentUser === null): ?>
        <?php
        $componentOptions = $guestHeaderOptions;
        require dirname(__DIR__, 2) . '/components/header.php';
        ?>
    <?php else: ?>
        <?php require_once dirname(__DIR__, 2) . '/components/navbar.php'; ?>
    <?php endif; ?>

    <main class="container page-content">
        <h2>Jadwal & Formulir Reservasi Ruang</h2>
        <p class="subtitle">Periksa ketersediaan jadwal pada kalender sebelum mengisi form pengajuan reservasi.</p>

        <div class="reservation-layout">
            <!-- Kolom Kiri: Kalender Jadwal -->
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
                    <div class="calendar-days" id="calendar-days" data-booked-dates="<?= htmlspecialchars(json_encode($bookedDates), ENT_QUOTES, 'UTF-8') ?>">
                        <!-- Render hari otomatis lewat JavaScript -->
                    </div>
                    <div class="calendar-legend">
                        <span class="legend-item"><span class="dot available"></span>Tersedia</span>
                        <span class="legend-item"><span class="dot booked"></span>Terisi (Full)</span>
                    </div>
                </div>

                <!-- Detail Jadwal Terisi pada Tanggal Pilihan -->
                <div class="schedule-info-box">
                    <h4>Jadwal yang Disetujui:</h4>
                    <ul id="schedule-list">
                        <?php foreach ($schedule as $entry): ?>
                            <li><strong><?= htmlspecialchars($entry['nama_tempat'], ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($entry['nama_acara'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(substr((string) $entry['jam_mulai'], 0, 5) . '–' . substr((string) $entry['jam_selesai'], 0, 5), ENT_QUOTES, 'UTF-8') ?>)</li>
                        <?php endforeach; ?>
                        <?php if ($schedule === []): ?><li>Belum ada jadwal yang disetujui.</li><?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Kolom Kanan: Form Reservasi -->
            <!-- Kolom Kanan: Informasi & Tombol Reservasi via WA -->
            <div class="form-section">
                <div class="reservation-form">
                    <h3>Alur Reservasi Ruangan</h3>
                    <?php if ($reservationNotice === 'saved'): ?>
                        <p class="subtitle" role="status">Permintaan tersimpan dengan status menunggu. Reservasi belum dikonfirmasi.</p>
                        <?php if (is_string($reservationWhatsApp)): ?>
                            <a class="btn-primary full-width" target="_blank" rel="noopener" href="<?= htmlspecialchars($reservationWhatsApp, ENT_QUOTES, 'UTF-8') ?>">Lanjutkan komunikasi melalui WhatsApp</a>
                        <?php else: ?>
                            <p class="subtitle">Kontak WhatsApp admin belum dikonfigurasi. Permintaan tetap tersimpan dan menunggu ditinjau.</p>
                        <?php endif; ?>
                    <?php elseif ($reservationNotice === 'error'): ?>
                        <p class="subtitle" role="alert">Permintaan belum dapat disimpan. Silakan periksa isian dan coba lagi.</p>
                    <?php endif; ?>
                    <p class="guest-reservation-intro" style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 20px;">
                        <?php if ($currentUser === null): ?>
                            Tamu dapat melihat informasi ruang. Masuk atau daftar sebagai anggota untuk mengirim permintaan reservasi.
                        <?php else: ?>
                            Permintaan akan tersimpan terlebih dahulu. Setelah itu, Anda dapat melanjutkan komunikasi melalui WhatsApp. Status menunggu bukan berarti reservasi sudah disetujui.
                        <?php endif; ?>
                    </p>

                    <?php if ($currentUser === null): ?>
                        <section class="guest-room-section" aria-labelledby="guest-room-heading">
                            <h4 id="guest-room-heading">Ruang yang dapat dipesan</h4>
                            <ul class="guest-room-list">
                                <?php foreach ($rooms as $room): ?>
                                    <li>
                                        <span><?= htmlspecialchars($room['nama_tempat'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="guest-room-capacity"><?= (int) $room['kapasitas'] ?> orang</span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($rooms === []): ?><li class="guest-room-empty">Belum ada ruang yang tersedia untuk reservasi.</li><?php endif; ?>
                            </ul>
                        </section>
                        <?php $reservationReturnTo = rawurlencode('/pages/reservasi/index.php'); ?>
                        <div class="guest-reservation-actions">
                            <a class="btn-secondary full-width" href="<?= htmlspecialchars(simart_url('/index.php?auth=login&return_to=' . $reservationReturnTo), ENT_QUOTES, 'UTF-8') ?>">Masuk untuk mengajukan reservasi</a>
                            <a class="btn-primary full-width" href="<?= htmlspecialchars(simart_url('/index.php?auth=register&return_to=' . $reservationReturnTo), ENT_QUOTES, 'UTF-8') ?>">Daftar sebagai anggota</a>
                        </div>
                    <?php else: ?>
                    <form id="form-wa-reservasi" method="post" action="<?= htmlspecialchars(simart_url('/pages/reservasi/submit.php'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= simart_csrf_field() ?>
                        <div class="form-group">
                            <label>Pilih Ruangan</label>
                            <select name="id_tempat" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= (int) $room['id_tempat'] ?>"><?= htmlspecialchars($room['nama_tempat'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $room['kapasitas'] ?> orang)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal acara</label>
                            <input type="date" name="tanggal_acara" min="<?= (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group row-time">
                            <div><label>Jam mulai</label><input type="time" name="jam_mulai" required></div>
                            <div><label>Jam selesai</label><input type="time" name="jam_selesai" required></div>
                        </div>
                        <div class="form-group">
                            <label>Nama acara</label>
                            <input type="text" name="nama_acara" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label>Penanggung jawab</label>
                            <input type="text" name="penanggung_jawab" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor WhatsApp</label>
                            <input type="tel" name="kontak_wa" maxlength="20" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi acara (opsional)</label>
                            <textarea name="deskripsi_acara" maxlength="5000" rows="4"></textarea>
                        </div>

                        <button type="submit" class="btn-primary full-width btn-large" style="background-color: var(--color-accent-green); color: var(--color-text-light);">
                            Simpan permintaan reservasi
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/reservasi.js"></script>
    <script src="../../assets/js/auth.js"></script>
</body>
</html>
