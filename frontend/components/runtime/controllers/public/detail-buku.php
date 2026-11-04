<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_start_session();
require_once dirname(__DIR__, 3) . '/runtime/models/Book.php';
$bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bookId || $bookId < 1) {
    http_response_code(404);
    exit('Buku tidak ditemukan.');
}
$book = Book::findPublished($bookId);
if (!$book) {
    http_response_code(404);
    exit('Buku tidak ditemukan.');
}
$componentOptions = [
    'active' => 'katalog',
    'logo_href' => 'index.php',
    'about_href' => 'tentang.php',
    'show_reservation' => false,
    'page_links' => [
        'beranda' => 'index.php',
        'tentang' => 'tentang.php',
        'event' => 'infoevent.php',
        'katalog' => 'katalog.php',
    ],
];

require dirname(__DIR__, 2) . '/views/public/detail-buku.php';
