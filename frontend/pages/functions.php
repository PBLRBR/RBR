<?php

require_once __DIR__ . '/auth/auth.php';

function simart_render_account_menu(?array $user = null, array $options = []): void
{
    $user ??= simart_current_user();
    if ($user === null) {
        return;
    }

    $role = (string) ($user['role'] ?? 'member');
    $profileHref = $options['href'] ?? simart_url('/pages/auth/profile.php');
    $profileTitle = $options['title'] ?? 'Ke Pengaturan Profil';
    $profileClass = 'user-profile' . (!empty($options['active']) ? ' active' : '');
    $avatarId = $options['avatar_id'] ?? null;
    $userNameId = $options['user_name_id'] ?? null;
    $logoutHref = simart_url('/pages/auth/submit.php');

    echo '<div class="nav-auth simart-account-menu">';
    echo '<a href="' . htmlspecialchars($profileHref, ENT_QUOTES, 'UTF-8') . '" class="'
        . htmlspecialchars($profileClass, ENT_QUOTES, 'UTF-8') . '" title="'
        . htmlspecialchars($profileTitle, ENT_QUOTES, 'UTF-8') . '">';
    echo '<img src="https://via.placeholder.com/40" alt="Foto Profil" class="profile-avatar"'
        . ($avatarId ? ' id="' . htmlspecialchars((string) $avatarId, ENT_QUOTES, 'UTF-8') . '"' : '') . '>';
    echo '<span class="user-name"'
        . ($userNameId ? ' id="' . htmlspecialchars((string) $userNameId, ENT_QUOTES, 'UTF-8') . '"' : '')
        . '>' . htmlspecialchars((string) ($user['nama'] ?? 'Pengguna'), ENT_QUOTES, 'UTF-8') . '</span></a>';
    echo '<form method="post" action="' . htmlspecialchars($logoutHref, ENT_QUOTES, 'UTF-8') . '">'
        . simart_csrf_field() . '<input type="hidden" name="action" value="logout">'
        . '<button type="submit" class="btn-secondary">Keluar</button></form>';
    echo '</div>';
}

function simart_public_header(array $options = []): void
{
    $active = $options['active'] ?? 'tentang';
    $logoHref = simart_url('/index.php');
    $showReservation = $options['show_reservation'] ?? false;
    $authReturnTo = $options['auth_return_to'] ?? '';
    $linkedLogo = $options['linked_logo'] ?? true;
    $currentUser = simart_current_user();

    $items = [
        'beranda' => ['Beranda', simart_url('/index.php')],
        'tentang' => ['Tentang', simart_url('/pages/karya/tentang.php')],
        'event' => ['Info Event', simart_url('/pages/karya/infoevent.php')],
        'katalog' => ['Katalog Buku', simart_url('/pages/buku/index.php')],
        'karya' => ['Karya Bu Ratna', simart_url('/pages/karya/index.php')],
    ];

    echo '<header class="navbar">';
    echo '<div class="container nav-container">';
    echo $linkedLogo
        ? '<a class="logo" href="' . htmlspecialchars($logoHref, ENT_QUOTES, 'UTF-8') . '">'
        : '<div class="logo">';
    echo '<img src="' . htmlspecialchars(simart_url('/assets/images/rbr.png'), ENT_QUOTES, 'UTF-8') . '" alt="Logo Rumah Budaya Ratna" class="logo-image">';
    echo '<span class="logo-text">SIMART</span>' . ($linkedLogo ? '</a>' : '</div>');
    echo '<nav class="nav-menu">';

    foreach ($items as $key => [$label, $href]) {
        $class = $key === $active ? ' class="active"' : '';
        echo '<a' . $class . ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
    }

    if ($showReservation) {
        echo '<a href="' . htmlspecialchars(simart_url('/pages/reservasi/index.php'), ENT_QUOTES, 'UTF-8') . '">Reservasi Ruang</a>';
    }

    echo '</nav>';

    if ($currentUser !== null) {
        simart_render_account_menu($currentUser);
    } else {
        echo '<div class="nav-auth">';
        $authPage = simart_url('/index.php');
        $returnQuery = $authReturnTo !== '' ? '&return_to=' . rawurlencode($authReturnTo) : '';
        echo '<a class="btn-secondary" href="' . htmlspecialchars($authPage . '?auth=login' . $returnQuery, ENT_QUOTES, 'UTF-8') . '">Masuk</a>';
        echo '<a class="btn-primary" href="' . htmlspecialchars($authPage . '?auth=register' . $returnQuery, ENT_QUOTES, 'UTF-8') . '">Daftar</a>';
        echo '</div>';
    }

    echo '</div></header>';
}

function simart_public_footer(string $variant = 'about'): void
{
    if ($variant === 'compact') {
        ?>
        <footer class="footer">
            <div class="container">
                <h3>SIMART</h3>
                <p>Rumah Budaya Ratna · Malang</p>
            </div>
        </footer>
        <?php
        return;
    }

    if ($variant === 'landing') {
        ?>
        <footer class="footer">
            <div class="container footer-container">
                <div class="footer-col brand-col">
                    <h3>SIMART</h3>
                    <p>Platform manajemen literasi dan reservasi ruang kebudayaan di Rumah Budaya Ratna Malang.</p>
                </div>
                <div class="footer-col">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="<?= htmlspecialchars(simart_url('/index.php'), ENT_QUOTES, 'UTF-8') ?>">Beranda Utama</a></li>
                        <li><a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>">Katalog Buku Lengkap</a></li>
                        <li><a href="<?= htmlspecialchars(simart_url('/pages/reservasi/index.php'), ENT_QUOTES, 'UTF-8') ?>">Reservasi Ruang</a></li>
                        <li><a href="#tentang">Tentang Rumah Budaya</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Hubungi Kami</h4>
                    <p>Jl. Cokroaminoto No.6, Klojen, Kec. Klojen, Kota Malang, Jawa Timur 65111</p>
                    <p>info@rumahbudayaratna.org</p>
                    <p>+62 341 555123</p>
                </div>
                <div class="footer-col map-col">
                    <h4>Lokasi Kami</h4>
                    <div class="map-wrapper">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3375.373829808816!2d112.63107834774493!3d-7.970008297222715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6290052a77475%3A0xa948270dfe439e48!2sRumah%20Budaya%20Ratna!5e0!3m2!1sen!2sid!4v1790146759693!5m2!1sen!2sid" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 SIMART Rumah Budaya Ratna. All rights reserved.</p>
            </div>
        </footer>
        <?php
        return;
    }

    if ($variant === 'karya') {
        ?>
        <footer class="footer">
            <div class="container footer-container">
                <div>
                    <h3>SIMART</h3>
                    <p>Merawat literasi dan kebudayaan bersama.</p>
                </div>
                <div>
                    <h4>Tautan cepat</h4>
                    <p><a href="<?= htmlspecialchars(simart_url('/pages/karya/tentang.php'), ENT_QUOTES, 'UTF-8') ?>">Tentang kami</a></p>
                    <p><a href="<?= htmlspecialchars(simart_url('/pages/karya/infoevent.php'), ENT_QUOTES, 'UTF-8') ?>">Info event</a></p>
                </div>
                <div>
                    <h4>Kontak</h4>
                    <p>Rumah Budaya Ratna · Malang</p>
                </div>
            </div>
        </footer>
        <?php
        return;
    }

    ?>
    <footer class="footer">
        <div class="container footer-container">
            <div>
                <h3>SIMART</h3>
                <p>Platform literasi dan kebudayaan Rumah Budaya Ratna, Malang.</p>
            </div>
            <div>
                <h4>Jelajahi</h4>
                <p><a href="<?= htmlspecialchars(simart_url('/pages/buku/index.php'), ENT_QUOTES, 'UTF-8') ?>">Katalog buku</a></p>
                <p><a href="<?= htmlspecialchars(simart_url('/pages/karya/infoevent.php'), ENT_QUOTES, 'UTF-8') ?>">Info event</a></p>
            </div>
            <div>
                <h4>Kunjungi</h4>
                <p>Jl. Cokroaminoto No. 6, Klojen, Malang</p>
            </div>
        </div>
    </footer>
    <?php
}
