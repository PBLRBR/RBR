<?php
require_once __DIR__ . '/_data.php';
startAppSession();
$user = $_SESSION['user'] ?? null;
?>
<div class="nav-auth">
    <?php if ($user): ?>
        <a class="btn-secondary user-profile" href="<?= ($user['role'] ?? '') === 'admin' ? '/admin/' : '/profil' ?>"><img src="/images/rbr.png" alt="" class="profile-avatar"><span class="nav-account-name"><?= ($user['role'] ?? '') === 'admin' ? 'Admin' : esc($user['nama'] ?? 'Akun') ?></span></a>
        <button type="button" class="btn-secondary" data-logout>Keluar</button>
    <?php else: ?>
        <a class="btn-secondary" href="/?auth=login">Masuk</a>
        <a class="btn-primary" href="/?auth=register">Daftar</a>
    <?php endif; ?>
</div>
