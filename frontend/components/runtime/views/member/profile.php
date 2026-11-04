<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | SIMART</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
</head>
<body>
    <!-- Navbar -->
    <?php require_once dirname(__DIR__, 3) . '/navbar.php'; ?>

    <!-- Main Content -->
    <main class="container page-content">
        <h2>Pengaturan Profil</h2>
        <p class="subtitle">Kelola informasi pribadi yang tersimpan pada akun Anda.</p>

        <div class="profile-layout">
            <!-- Sidebar / Card Upload Foto Profil -->
            <div class="profile-card text-center">
                <div class="avatar-upload-wrapper">
                    <img src="https://via.placeholder.com/150" alt="Foto Profil" id="profile-preview-img" class="avatar-large">
                </div>
                <h3 id="display-name-text"><?= htmlspecialchars($profile['nama'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="user-email-text" id="display-email-text"><?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="role-badge">Anggota Komunitas</span>
            </div>

            <!-- Form Edit Profil -->
            <div class="profile-form-section">
                <?php if ($profileNotice === '1'): ?><p class="subtitle" role="status">Profil berhasil diperbarui.</p><?php elseif ($profileNotice === 'error'): ?><p class="subtitle" role="alert">Perubahan belum tersimpan. Periksa kembali isian Anda.</p><?php endif; ?>
                <form class="reservation-form" id="form-edit-profile" method="post" action="profile-update.php">
                    <?= simart_csrf_field() ?>
                    <h3>Informasi Pribadi</h3>
                    
                    <div class="form-group">
                        <label>Nama Tampilan (Display Name)</label>
                        <input type="text" name="nama" id="input-display-name" value="<?= htmlspecialchars($profile['nama'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
                    </div>

                    <div class="form-group row-time">
                        <div>
                            <label>Alamat Email</label>
                            <input type="email" name="email" id="input-email" value="<?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>
                        </div>
                        <div>
                            <label>Nomor WhatsApp</label>
                            <input type="tel" name="no_hp" id="input-phone" value="<?= htmlspecialchars((string) ($profile['no_hp'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="20">
                        </div>
                    </div>

                    <hr class="form-divider">
                    <h3>Ubah Kata Sandi (opsional)</h3>
                    <div class="form-group"><label>Kata sandi saat ini</label><input type="password" name="current_password" autocomplete="current-password"></div>
                    <div class="form-group row-time">
                        <div><label>Kata sandi baru</label><input type="password" name="new_password" minlength="8" autocomplete="new-password"></div>
                        <div><label>Ulangi kata sandi baru</label><input type="password" name="confirm_password" minlength="8" autocomplete="new-password"></div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn-primary">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../../assets/js/auth.js"></script>
</body>
</html>
