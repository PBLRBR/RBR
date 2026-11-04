<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
$signedInUser = simart_require_role('member');
require_once dirname(__DIR__, 3) . '/runtime/models/Member.php';
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

require dirname(__DIR__, 2) . '/views/member/profile.php';
