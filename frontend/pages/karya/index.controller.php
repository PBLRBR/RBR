<?php
require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
simart_start_session();
$componentOptions = [
    'active' => 'karya',
    'logo_href' => 'index.php',
    'about_href' => 'tentang.php',
    'show_reservation' => true,
    'page_links' => [
        'beranda' => 'index.php',
        'tentang' => 'tentang.php',
        'event' => 'infoevent.php',
        'katalog' => 'katalog.php',
        'karya' => 'karya.php',
    ],
];

require __DIR__ . '/index.view.php';
