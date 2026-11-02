
<?php

require_once __DIR__ . '/../auth/auth.php';
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

    if (!in_array($status, ['disetujui', 'ditolak'], true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Status hanya boleh disetujui atau ditolak.'
        ]);
        exit;
    }

    // Alasan wajib diisi jika reservasi ditolak
    $alasan = trim($input['alasan_penolakan'] ?? '');

    if ($status === 'ditolak' && $alasan === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Alasan penolakan wajib diisi.'
        ]);
        exit;
    }

    // Jika disetujui, alasan penolakan dikosongkan
    if ($status === 'disetujui') {
        $alasan = null;
    }

    $pdo->beginTransaction();

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

    // Jika akan disetujui, periksa bentrok jadwal
    if ($status === 'disetujui') {

        // Kunci data tempat agar persetujuan untuk tempat
        // yang sama dapat diperiksa secara berurutan
        $stmtTempat = $pdo->prepare(
            'SELECT id_tempat
             FROM tempat
             WHERE id_tempat = :id_tempat
             FOR UPDATE'
        );

        $stmtTempat->execute([
            'id_tempat' => $reservasiLama['id_tempat']
        ]);

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
