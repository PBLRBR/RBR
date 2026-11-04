<?php
require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
simart_require_role('member');
require_once dirname(__DIR__, 2) . '/pages/reservasi/Reservation.php';
$reservations = Reservation::forMember((int) simart_current_user()['id_user']);
$memberHeaderOptions = [
    'variant' => 'history',
    'active_history_nav' => 'riwayat',
];

require __DIR__ . '/riwayat.view.php';
