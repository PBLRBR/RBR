<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
require_once dirname(__DIR__, 2) . '/pages/database.php';
simart_require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();
$reservationId = filter_var($_POST['id_reservasi'] ?? null, FILTER_VALIDATE_INT);
$status = (string) ($_POST['status'] ?? '');
if (!$reservationId || $reservationId < 1 || !in_array($status, ['disetujui', 'ditolak'], true)) {
    header('Location: ' . simart_url('/pages/dashboard/index.php') . '?reservation_error=1#tab-reservasi');
    exit;
}

try {
    $update = simart_db()->prepare(
        "UPDATE reservasi SET status_reservasi = :status "
        . "WHERE id_reservasi = :id AND status_reservasi = 'menunggu'"
    );
    $update->execute([':status' => $status, ':id' => $reservationId]);
    header('Location: ' . simart_url('/pages/dashboard/index.php')
        . ($update->rowCount() === 1 ? '?reservation_saved=1' : '?reservation_error=1') . '#tab-reservasi');
    exit;
} catch (Throwable $exception) {
    error_log('SIMART reservation status update failed: ' . $exception->getMessage());
    header('Location: ' . simart_url('/pages/dashboard/index.php') . '?reservation_error=1#tab-reservasi');
    exit;
}
