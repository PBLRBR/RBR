<?php

require_once __DIR__ . '/auth/auth.php';
simart_start_session();
$componentOptions = [
    'active' => 'beranda',
    'logo_href' => simart_url('/index.php'),
    'about_href' => simart_url('/pages/karya/tentang.php'),
    'page_links' => [],
    'show_reservation' => true,
    'linked_logo' => false,
];
require __DIR__ . '/home.view.php';
