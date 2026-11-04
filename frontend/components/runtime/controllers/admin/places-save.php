<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_require_role('admin');
require_once dirname(__DIR__, 3) . '/runtime/models/Reservation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();

$name = trim((string) ($_POST['nama_tempat'] ?? ''));
$description = trim((string) ($_POST['deskripsi'] ?? ''));
$capacity = filter_var($_POST['kapasitas'] ?? null, FILTER_VALIDATE_INT);
$adminUrl = simart_url('/pages/dashboard/index.php');

if ($name === '' || mb_strlen($name) > 100 || mb_strlen($description) > 2000
    || $capacity === false || $capacity < 1 || $capacity > 10000) {
    header('Location: ' . $adminUrl . '?place_error=invalid#tab-reservasi');
    exit;
}

try {
    Reservation::createPlace($name, $description !== '' ? $description : null, $capacity);
    header('Location: ' . $adminUrl . '?place_saved=1#tab-reservasi');
    exit;
} catch (PDOException $exception) {
    if ($exception->getCode() === '23505') {
        header('Location: ' . $adminUrl . '?place_error=duplicate#tab-reservasi');
        exit;
    }
    error_log('SIMART place creation failed: ' . $exception->getMessage());
    header('Location: ' . $adminUrl . '?place_error=failed#tab-reservasi');
    exit;
} catch (Throwable $exception) {
    error_log('SIMART place creation failed: ' . $exception->getMessage());
    header('Location: ' . $adminUrl . '?place_error=failed#tab-reservasi');
    exit;
}
