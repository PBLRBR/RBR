<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
$admin = simart_require_role('admin');
require_once dirname(__DIR__, 2) . '/pages/dashboard/AdminDashboard.php';

$transactionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$transactionId || $transactionId < 1) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}
$receipt = AdminDashboard::receipt($transactionId);
if ($receipt === null) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}
$sale = $receipt['sale'];
$lines = $receipt['lines'];

require __DIR__ . '/receipt.view.php';
