<?php
require_once __DIR__ . '/_data.php';
requireAppUser();
$pageTitle = 'Profil Saya | SIMART';
$stmt = db()->prepare('SELECT nama, email, no_hp FROM users WHERE id_user=:id');
$stmt->execute(['id'=>$_SESSION['user']['id_user']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profile) { http_response_code(404); exit('Akun tidak ditemukan.'); }
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
    <!-- navbar akun login -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span class="logo-text">SIMART</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <div class="nav-auth">
                <a href="/profil.php" class="user-profile active"><img src="/images/rbr.png" alt="Foto Profil" class="profile-avatar"><span class="user-name"><?= htmlspecialchars($profile['nama']) ?></span></a>
                <button class="btn-secondary" type="button" data-logout>Keluar</button>
            </div>
        </div>
    </header>

    <main class="container page-content">
        <h2>Pengaturan Profil</h2>
        <p class="subtitle">Informasi akun dari database. Perubahan profil belum diaktifkan pada versi integrasi awal.</p>

        <div class="profile-layout">
            <!-- kartu ringkasan profil -->
            <div class="profile-card text-center">
                <div class="avatar-upload-wrapper">
                    <img src="/images/rbr.png" alt="Foto Profil" class="avatar-large">
                </div>
                <h3><?= htmlspecialchars($profile['nama']) ?></h3>
                <p class="user-email-text"><?= htmlspecialchars($profile['email']) ?></p>
                <span class="role-badge">Anggota Komunitas</span>
            </div>

            <!-- form edit profil -->
            <div class="profile-form-section">
                <form class="reservation-form" data-dummy>
                    <h3>Informasi Pribadi</h3>
                    <div class="form-group">
                        <label>Nama Tampilan</label>
                        <input type="text" value="<?= htmlspecialchars($profile['nama']) ?>" maxlength="100" required>
                    </div>
                    <div class="form-group row-time">
                        <div><label>Alamat Email</label><input type="email" value="<?= htmlspecialchars($profile['email']) ?>" required></div>
                        <div><label>Nomor WhatsApp</label><input type="tel" value="<?= htmlspecialchars($profile['no_hp']) ?>" maxlength="20"></div>
                    </div>

                    <hr class="form-divider">
                    <h3>Ubah Kata Sandi (opsional)</h3>
                    <div class="form-group"><label>Kata sandi saat ini</label><input type="password"></div>
                    <div class="form-group row-time">
                        <div><label>Kata sandi baru</label><input type="password" minlength="8"></div>
                        <div><label>Ulangi kata sandi baru</label><input type="password" minlength="8"></div>
                    </div>
                    <div class="profile-actions">
                        <button type="submit" class="btn-primary">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- footer -->
    <footer class="footer"><div class="container"><h3>SIMART</h3><p>Rumah Budaya Ratna · Malang</p></div></footer>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
