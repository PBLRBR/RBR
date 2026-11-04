<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_start_session();
$componentOptions = [
    'active' => 'event',
    'logo_href' => 'index.php',
    'about_href' => 'tentang.php',
    'show_reservation' => true,
    'linked_logo' => false,
    'page_links' => [
        'beranda' => 'index.php',
        'tentang' => 'tentang.php',
        'event' => 'infoevent.php',
        'katalog' => 'katalog.php',
    ],
];

require dirname(__DIR__, 2) . '/views/public/infoevent.php';
