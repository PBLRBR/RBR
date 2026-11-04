<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
require_once dirname(__DIR__, 3) . '/runtime/models/Reservation.php';
$currentUser = simart_current_user();
$rooms = Reservation::activeRooms();
$schedule = Reservation::upcomingApproved();
$bookedDates = array_values(array_unique(array_column($schedule, 'tanggal')));
$reservationNotice = $_SESSION['reservation_notice'] ?? null;
unset($_SESSION['reservation_notice']);
$reservationWhatsApp = $_SESSION['reservation_whatsapp'] ?? null;
unset($_SESSION['reservation_whatsapp']);
$memberHeaderOptions = [
    'active_nav' => 'event',
    'reservation_active' => true,
    'profile_title' => 'Ke Pengaturan Profil',
];
$guestHeaderOptions = [
    'active' => 'event',
    'logo_href' => '../public/index.php',
    'about_href' => '../public/tentang.php',
    'show_reservation' => true,
    'auth_return_to' => '/pages/reservasi/index.php',
    'public_path' => '../public/',
    'member_path' => '../member/',
    'page_extension' => '.php',
    'page_links' => [
        'beranda' => '../public/index.php',
        'tentang' => '../public/tentang.php',
        'event' => '../public/infoevent.php',
        'katalog' => '../public/katalog.php',
        'karya' => '../public/karya.php',
    ],
];

require dirname(__DIR__, 2) . '/views/member/reservasi.php';
