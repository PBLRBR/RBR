<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
require_once dirname(__DIR__, 3) . '/runtime/support/database.php';

$member = simart_require_role('member');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode tidak diizinkan.');
}
simart_verify_csrf();

$idTempat = filter_var($_POST['id_tempat'] ?? null, FILTER_VALIDATE_INT);
$namaAcara = trim((string) ($_POST['nama_acara'] ?? ''));
$penanggungJawab = trim((string) ($_POST['penanggung_jawab'] ?? ''));
$kontakWa = trim((string) ($_POST['kontak_wa'] ?? ''));
$tanggalAcara = (string) ($_POST['tanggal_acara'] ?? '');
$jamMulai = (string) ($_POST['jam_mulai'] ?? '');
$jamSelesai = (string) ($_POST['jam_selesai'] ?? '');
$deskripsi = trim((string) ($_POST['deskripsi_acara'] ?? ''));
$parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggalAcara);
$dateErrors = DateTimeImmutable::getLastErrors();
$validDate = $parsedDate !== false
    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
    && $parsedDate->format('Y-m-d') === $tanggalAcara
    && $tanggalAcara >= (new DateTimeImmutable('today', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$validTime = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jamMulai) === 1
    && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jamSelesai) === 1
    && $jamSelesai > $jamMulai;
$validPhone = preg_match('/^[0-9+(). -]{8,20}$/', $kontakWa) === 1;

if ($idTempat === false || $idTempat < 1 || $namaAcara === '' || mb_strlen($namaAcara) > 150
    || $penanggungJawab === '' || mb_strlen($penanggungJawab) > 100 || !$validPhone
    || !$validDate || !$validTime || mb_strlen($deskripsi) > 5000) {
    $_SESSION['reservation_notice'] = 'error';
    header('Location: ' . simart_url('/pages/reservasi/index.php'));
    exit;
}

try {
    $db = simart_db();
    $memberCheck = $db->prepare("SELECT role FROM users WHERE id_user = :id_user");
    $memberCheck->execute([':id_user' => $member['id_user']]);
    if ($memberCheck->fetchColumn() !== 'member') {
        http_response_code(403);
        exit('Hanya akun Member yang dapat mengajukan reservasi.');
    }

    $insert = $db->prepare(
        "INSERT INTO reservasi "
        . "(id_user, id_tempat, nama_acara, deskripsi_acara, penanggung_jawab, kontak_wa, "
        . "tanggal_acara, jam_mulai, jam_selesai) "
        . "SELECT :id_user, id_tempat, :nama_acara, :deskripsi_acara, :penanggung_jawab, :kontak_wa, "
        . ":tanggal_acara, :jam_mulai, :jam_selesai "
        . "FROM tempat WHERE id_tempat = :id_tempat AND status = 'aktif' "
        . "RETURNING id_reservasi"
    );
    $insert->execute([
        ':id_user' => $member['id_user'],
        ':nama_acara' => $namaAcara,
        ':deskripsi_acara' => $deskripsi !== '' ? $deskripsi : null,
        ':penanggung_jawab' => $penanggungJawab,
        ':kontak_wa' => $kontakWa,
        ':tanggal_acara' => $tanggalAcara,
        ':jam_mulai' => $jamMulai,
        ':jam_selesai' => $jamSelesai,
        ':id_tempat' => $idTempat,
    ]);
    $reservationId = $insert->fetchColumn();
    if ($reservationId === false) {
        $_SESSION['reservation_notice'] = 'error';
        header('Location: ' . simart_url('/pages/reservasi/index.php'));
        exit;
    }

    $_SESSION['reservation_notice'] = 'saved';
    $adminPhone = preg_replace('/\D+/', '', (string) (getenv('SIMART_ADMIN_WA') ?: ''));
    if (preg_match('/^[0-9]{8,15}$/', $adminPhone) === 1) {
        $message = "Halo Admin Rumah Budaya Ratna, saya mengajukan permintaan reservasi #{$reservationId}:\n"
            . "Acara: {$namaAcara}\nTanggal: {$tanggalAcara}\nWaktu: {$jamMulai}–{$jamSelesai}\n"
            . "Penanggung jawab: {$penanggungJawab}\nKontak: {$kontakWa}\n"
            . "Permintaan ini masih menunggu persetujuan.";
        $_SESSION['reservation_whatsapp'] = 'https://wa.me/' . $adminPhone . '?text=' . rawurlencode($message);
    }
    header('Location: ' . simart_url('/pages/reservasi/index.php'));
    exit;
} catch (PDOException $exception) {
    error_log('SIMART reservation persistence error: ' . $exception->getMessage());
    $_SESSION['reservation_notice'] = 'error';
    header('Location: ' . simart_url('/pages/reservasi/index.php'));
    exit;
}
