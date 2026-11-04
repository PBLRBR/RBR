<?php
require_once dirname(__DIR__) . '/pages/functions.php';
$currentUser = simart_current_user();
$active = $memberHeaderOptions['active_nav'] ?? $componentOptions['active'] ?? '';
$navItems = [
    'beranda' => ['Beranda', '/index.php'],
    'tentang' => ['Tentang', '/pages/karya/tentang.php'],
    'event' => ['Info Event', '/pages/karya/infoevent.php'],
    'katalog' => ['Katalog Buku', '/pages/buku/index.php'],
    'karya' => ['Karya Bu Ratna', '/pages/karya/index.php'],
    'reservasi' => ['Reservasi Ruang', '/pages/reservasi/index.php'],
];
?>
<header class="navbar">
    <div class="container nav-container">
        <a class="logo" href="<?= htmlspecialchars(simart_url('/index.php'), ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars(simart_url('/assets/images/rbr.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo Rumah Budaya Ratna" class="logo-image">
            <span class="logo-text">SIMART</span>
        </a>
        <nav class="nav-menu" aria-label="Navigasi utama">
            <?php foreach ($navItems as $key => [$label, $path]): ?>
                <a<?= $active === $key ? ' class="active"' : '' ?> href="<?= htmlspecialchars(simart_url($path), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </nav>
        <?php if ($currentUser !== null): ?>
            <?php simart_render_account_menu($currentUser, [
                'title' => $memberHeaderOptions['profile_title'] ?? 'Profil Saya',
                'active' => $memberHeaderOptions['profile_active'] ?? false,
                'avatar_id' => $memberHeaderOptions['avatar_id'] ?? null,
                'user_name_id' => $memberHeaderOptions['user_name_id'] ?? null,
            ]); ?>
        <?php else: ?>
            <div class="nav-auth">
                <a class="btn-secondary" href="<?= htmlspecialchars(simart_url('/index.php?auth=login'), ENT_QUOTES, 'UTF-8') ?>">Masuk</a>
                <a class="btn-primary" href="<?= htmlspecialchars(simart_url('/index.php?auth=register'), ENT_QUOTES, 'UTF-8') ?>">Daftar</a>
            </div>
        <?php endif; ?>
    </div>
</header>
