<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_start_session();
$componentOptions = [
    'active' => 'tentang',
    'logo_href' => 'index.php',
    'about_href' => 'tentang.php',
    'page_links' => [
        'beranda' => 'index.php',
        'tentang' => 'tentang.php',
        'event' => 'infoevent.php',
        'katalog' => 'katalog.php',
    ],
    'show_reservation' => true,
];

require dirname(__DIR__, 2) . '/views/public/tentang.php';
