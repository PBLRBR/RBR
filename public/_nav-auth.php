<?php
require_once __DIR__ . '/_data.php';
startAppSession();
$user = $_SESSION['user'] ?? null;
$authReturn = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$authQuery = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
if (is_string($authQuery) && $authQuery !== '') $authReturn .= '?' . $authQuery;
if (!str_starts_with($authReturn, '/') || str_starts_with($authReturn, '//')) $authReturn = '/';
?>
<div class="nav-auth">
    <?php if ($user): ?>
        <a class="btn-secondary user-profile" href="<?= ($user['role'] ?? '') === 'admin' ? '/admin/' : '/profil.php' ?>"><img src="/images/rbr.png" alt="" class="profile-avatar"><span class="nav-account-name"><?= ($user['role'] ?? '') === 'admin' ? 'Admin' : esc($user['nama'] ?? 'Akun') ?></span></a>
        <button type="button" class="btn-secondary" data-logout>Keluar</button>
    <?php else: ?>
        <a class="btn-secondary" href="/?auth=login&amp;next=<?= rawurlencode($authReturn) ?>">Masuk</a>
        <a class="btn-primary" href="/?auth=register&amp;next=<?= rawurlencode($authReturn) ?>">Daftar</a>
    <?php endif; ?>
</div>
