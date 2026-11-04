<?php
require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
$signedInUser = simart_require_role('member');
require_once dirname(__DIR__, 2) . '/pages/auth/Member.php';
$profile = Member::findProfile((int) $signedInUser['id_user']);
if (!$profile) {
    http_response_code(404);
    exit('Akun tidak ditemukan.');
}
$profileNotice = (string) ($_GET['updated'] ?? '');
$memberHeaderOptions = [
    'active_nav' => 'event',
    'profile_active' => true,
    'avatar_id' => 'nav-avatar-img',
    'user_name_id' => 'nav-user-name',
];

require __DIR__ . '/profile.view.php';
