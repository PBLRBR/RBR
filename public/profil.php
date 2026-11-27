<?php
require_once __DIR__ . '/_data.php';
requireAppUser();
$pageTitle = 'Profil Saya | SIMART';
$stmt = db()->prepare('SELECT nama, email, no_hp FROM users WHERE id_user=:id');
$stmt->execute(['id' => $_SESSION['user']['id_user']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profile) {
    http_response_code(404);
    exit('Akun tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20261125-reservation-popup">
    <link rel="stylesheet" href="/css/responsive.css?v=20261125-reservation-popup">
</head>

<body>
    <!-- navbar akun login -->
    <header class="navbar">
        <div class="container nav-container">
            <a class="logo" href="/"><img src="/images/rbr.png" alt="Logo" class="logo-image"><span
                    class="logo-text">Rumah Budaya Ratna</span></a>
            <nav class="nav-menu">
                <a href="/">Beranda</a>
                <a href="/tentang.php">Tentang</a>
                <!-- <a href="/infoevent.php">Info Event</a> -->
                <a href="/katalog.php">Katalog Buku</a>
                <a href="/karya.php">Karya Bu Ratna</a>
                <a href="/reservasi.php">Reservasi Ruang</a>
            </nav>
            <?php require __DIR__ . '/_nav-auth.php'; ?>
        </div>
    </header>

    <main class="container page-content">
        <h2>Pengaturan Profil</h2>
        <p class="subtitle">Perbarui nama tampilan dan nomor WhatsApp di halaman ini.</p>
        </p>

        <div class="profile-layout">
            <!-- kartu ringkasan profil -->
            <div class="profile-card text-center">
                <div class="avatar-upload-wrapper">
                    <img src="/images/rbr.png" alt="Foto Profil" class="avatar-large">
                </div>
                <h3 id="profile-display-name"><?= esc($profile['nama']) ?></h3>
                <p class="user-email-text"><?= htmlspecialchars($profile['email']) ?></p>
                <span class="role-badge">Anggota Komunitas</span>
                <p class="margin-top-30"><a class="btn-primary" href="/riwayat.php">Riwayat Reservasi</a></p>
            </div>

            <!-- form edit profil -->
            <div class="profile-form-section">
                <form id="profile-form" class="reservation-form" method="post">
                    <h3>Informasi Pribadi</h3>

                    <div class="form-group">
                        <label for="profile-name">Nama Tampilan</label>
                        <input id="profile-name" name="nama" type="text" value="<?= esc($profile['nama']) ?>"
                            maxlength="100" required>
                    </div>

                    <div class="form-group row-time">
                        <div>
                            <label for="profile-email">Alamat Email</label>
                            <input id="profile-email" type="email" value="<?= esc($profile['email']) ?>" readonly>
                        </div>
                        <div>
                            <label for="profile-phone">Nomor WhatsApp</label>
                            <input id="profile-phone" name="no_hp" type="tel"
                                value="<?= esc($profile['no_hp'] ?? '') ?>" maxlength="20">
                        </div>
                    </div>

                    <p id="profile-message" role="status" hidden></p>

                    <div class="profile-actions">
                        <button type="submit" class="btn-primary">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- footer -->
    <?php require __DIR__ . '/_footer.php'; ?>
    <script src="/js/api.js"></script>
    <script src="/js/main.js"></script>
</body>

</html>