<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
$adminUser = simart_require_role('admin');
require_once dirname(__DIR__, 3) . '/runtime/models/Book.php';
require_once dirname(__DIR__, 3) . '/runtime/models/AdminDashboard.php';
require_once dirname(__DIR__, 3) . '/runtime/models/Reservation.php';
$categories = Book::categoriesWithDescriptions();
$books = Book::adminCatalog();
$saleBooks = Book::saleable();
$reservations = AdminDashboard::reservations();
$places = Reservation::allPlaces();
$summary = AdminDashboard::summary();
$dashboard = $summary['dashboard'];
$unitsThisMonth = $summary['units'];
$pendingReservations = $summary['pending'];
$recentSales = $summary['recent_sales'];
$saleTokenBytes = random_bytes(16);
$saleTokenBytes[6] = chr((ord($saleTokenBytes[6]) & 0x0f) | 0x40);
$saleTokenBytes[8] = chr((ord($saleTokenBytes[8]) & 0x3f) | 0x80);
$saleTokenHex = bin2hex($saleTokenBytes);
$saleToken = sprintf('%s-%s-%s-%s-%s', substr($saleTokenHex, 0, 8), substr($saleTokenHex, 8, 4), substr($saleTokenHex, 12, 4), substr($saleTokenHex, 16, 4), substr($saleTokenHex, 20));
$editBook = null;
$editBookId = filter_input(INPUT_GET, 'edit_book', FILTER_VALIDATE_INT);
if ($editBookId) {
    $editBook = Book::findActive($editBookId);
}

require dirname(__DIR__, 2) . '/views/admin/admin.php';
