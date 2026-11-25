<?php

require_once __DIR__ . '/../auth/auth.php';
require_once __DIR__ . '/../../helpers/reservasi.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus PATCH.'
    ]);
    exit;
}

wajibLogin();

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Hanya admin yang dapat mengubah status reservasi.'
    ]);
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format data tidak valid.'
        ]);
        exit;
    }

    // Validasi ID reservasi
    $id = filter_var(
        $input['id_reservasi'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id || $id < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID reservasi tidak valid.'
        ]);
        exit;
    }

    // Validasi status
    $status = $input['status_reservasi'] ?? '';

    if (!in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Status harus menunggu, disetujui, atau ditolak.'
        ]);
        exit;
    }

    // Alasan wajib diisi jika reservasi ditolak
    $alasanInput = $input['alasan_penolakan'] ?? null;
    $alasan = is_string($alasanInput) ? trim($alasanInput) : '';

    if ($status === 'ditolak' && $alasan === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Alasan penolakan wajib diisi.'
        ]);
        exit;
    }

    // Jika disetujui, alasan penolakan dikosongkan
    if ($status !== 'ditolak') {
        $alasan = null;
    }

    $pdo->beginTransaction();

    $stmtTempat = $pdo->prepare(
        'SELECT t.id_tempat, t.status
     FROM tempat AS t
     JOIN reservasi AS r ON r.id_tempat = t.id_tempat
     WHERE r.id_reservasi = :id
     FOR UPDATE OF t'
    );
    $stmtTempat->execute(['id' => $id]);
    $tempat = $stmtTempat->fetch(PDO::FETCH_ASSOC);

    // Ambil dan kunci data reservasi yang akan diubah
    $stmt = $pdo->prepare(
        'SELECT
            id_reservasi,
            id_tempat,
            tanggal_acara,
            jam_mulai,
            jam_selesai,
            status_reservasi
         FROM reservasi
         WHERE id_reservasi = :id
         FOR UPDATE'
    );

    $stmt->execute(['id' => $id]);
    $reservasiLama = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservasiLama) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Reservasi tidak ditemukan.'
        ]);
        exit;
    }

    if (!$tempat || (int) $tempat['id_tempat'] !== (int) $reservasiLama['id_tempat']) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Tempat reservasi berubah. Muat ulang dan coba lagi.'
        ]);
        exit;
    }

    // Jika akan disetujui, periksa tempat dan jadwalnya.
    if ($status === 'disetujui') {
        if ($tempat['status'] !== 'aktif') {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Reservasi tidak dapat disetujui karena tempat sedang tidak aktif.'
            ]);
            exit;
        }

        // Cari reservasi lain yang sudah disetujui
        // dan waktunya beririsan
        $stmtBentrok = $pdo->prepare(
            'SELECT id_reservasi, nama_acara
             FROM reservasi
             WHERE id_tempat = :id_tempat
               AND tanggal_acara = :tanggal_acara
               AND status_reservasi = :status
               AND id_reservasi <> :id
               AND jam_mulai < :jam_selesai
               AND jam_selesai > :jam_mulai
             LIMIT 1'
        );

        $stmtBentrok->execute([
            'id_tempat' => $reservasiLama['id_tempat'],
            'tanggal_acara' => $reservasiLama['tanggal_acara'],
            'status' => 'disetujui',
            'id' => $id,
            'jam_selesai' => $reservasiLama['jam_selesai'],
            'jam_mulai' => $reservasiLama['jam_mulai']
        ]);

        $bentrok = $stmtBentrok->fetch(PDO::FETCH_ASSOC);

        if ($bentrok) {
            $pdo->rollBack();

            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Reservasi tidak dapat disetujui karena jadwal tempat bentrok dengan reservasi lain.',
                'data' => [
                    'id_reservasi_bentrok' => $bentrok['id_reservasi'],
                    'nama_acara_bentrok' => $bentrok['nama_acara']
                ]
            ]);
            exit;
        }
    }

    // Simpan status dan alasan penolakan
    $stmtUpdate = $pdo->prepare(
        'UPDATE reservasi
         SET status_reservasi = :status,
             alasan_penolakan = :alasan
         WHERE id_reservasi = :id
         RETURNING
             id_reservasi,
             nama_acara,
             status_reservasi,
             alasan_penolakan'
    );

    $stmtUpdate->execute([
        'status' => $status,
        'alasan' => $alasan,
        'id' => $id
    ]);

    $reservasi = $stmtUpdate->fetch(PDO::FETCH_ASSOC);

    $reservasi['jumlah_ditolak_otomatis'] = 0;

    if ($status === 'disetujui') {
        $reservasi['jumlah_ditolak_otomatis'] = tolakReservasiMenungguYangBentrok(
            $pdo,
            $id,
            (int) $reservasiLama['id_tempat'],
            $reservasiLama['tanggal_acara'],
            $reservasiLama['jam_mulai'],
            $reservasiLama['jam_selesai']
        );
        $reservasi['jumlah_ditolak_otomatis'] = 0;

        if ($status === 'disetujui') {
            $reservasi['jumlah_ditolak_otomatis'] = tolakReservasiMenungguYangBentrok(
                $pdo,
                $id,
                (int) $reservasiLama['id_tempat'],
                $reservasiLama['tanggal_acara'],
                $reservasiLama['jam_mulai'],
                $reservasiLama['jam_selesai']
            );
        }

        $pdo->commit();
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Status reservasi berhasil diubah.',
        'data' => $reservasi
    ]);

} catch (Throwable $e) {

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengubah status reservasi.'
    ]);
}
