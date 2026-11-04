<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_require_role('member');
require_once dirname(__DIR__, 3) . '/runtime/models/Reservation.php';
$reservations = Reservation::forMember((int) simart_current_user()['id_user']);
$memberHeaderOptions = [
    'variant' => 'history',
    'active_history_nav' => 'riwayat',
];

require dirname(__DIR__, 2) . '/views/member/riwayat-reservasi.php';
