<?php
require_once __DIR__ . '/../../helpers/phone.php';
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus POST.'
    ]);
    exit;
}

wajibLogin();

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

    $id_user = (int) $_SESSION['user']['id_user'];

    $id_tempat = filter_var(
        $input['id_tempat'] ?? null,
        FILTER_VALIDATE_INT
    );

    $nama_acara = trim($input['nama_acara'] ?? '');
    $penanggung_jawab = trim($input['penanggung_jawab'] ?? '');
    $kontak_wa = normalizeIndonesianMobile($input['kontak_wa'] ?? null);
    $tanggal_acara = $input['tanggal_acara'] ?? '';
    $jam_mulai = $input['jam_mulai'] ?? '';
    $jam_selesai = $input['jam_selesai'] ?? '';

    if (
        !$id_user || !$id_tempat ||
        $nama_acara === '' ||
        $penanggung_jawab === '' ||
        $kontak_wa === null ||
        $tanggal_acara === '' ||
        $jam_mulai === '' ||
        $jam_selesai === ''
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Data wajib belum lengkap atau nomor WhatsApp tidak valid. Gunakan 08 dan 10-13 digit angka.'
        ]);
        exit;
    }

    
    // Validasi format dan tanggal acara.
    $tanggalObj = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $tanggal_acara
    );

    $tanggalValid =
        $tanggalObj !== false &&
        $tanggalObj->format('Y-m-d') === $tanggal_acara;

    if (!$tanggalValid) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Format tanggal harus YYYY-MM-DD.'
        ]);
        exit;
    }

    if ($tanggal_acara < date('Y-m-d')) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Tanggal acara tidak boleh di masa lalu.'
        ]);
        exit;
    }
 
    // Validasi format jam HH:MM atau HH:MM:SS.
    $formatJam = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';

    if (
        !is_string($jam_mulai) ||
        !is_string($jam_selesai) ||
        !preg_match($formatJam, $jam_mulai) ||
        !preg_match($formatJam, $jam_selesai)
    ) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Format jam harus HH:MM atau HH:MM:SS.'
        ]);
        exit;
    }

    // Samakan format HH:MM dan HH:MM:SS sebelum membandingkan.
    if (strlen($jam_mulai) === 5) $jam_mulai .= ':00';
    if (strlen($jam_selesai) === 5) $jam_selesai .= ':00';

    if ($jam_selesai <= $jam_mulai) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Jam selesai harus setelah jam mulai.'
        ]);
        exit;
    }

    
    // Kunci tempat supaya validasi pembuatan dan persetujuan dilakukan berurutan.
    $pdo->beginTransaction();
    $stmtTempat = $pdo->prepare(
        'SELECT id_tempat, status FROM tempat WHERE id_tempat = :id FOR UPDATE'
    );
    $stmtTempat->execute(['id' => $id_tempat]);
    $tempat = $stmtTempat->fetch(PDO::FETCH_ASSOC);
    if (!$tempat) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tempat tidak ditemukan.']);
        exit;
    }
    if ($tempat['status'] !== 'aktif') {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Tempat sedang tidak aktif dan tidak dapat dipesan.']);
        exit;
    }

    $stmtCek = $pdo->prepare(
        "SELECT 1 FROM reservasi
         WHERE id_tempat = :id_tempat
           AND tanggal_acara = :tanggal_acara
           AND status_reservasi = 'disetujui'
           AND jam_mulai < :jam_selesai
           AND jam_selesai > :jam_mulai
         LIMIT 1"
    );
    $stmtCek->execute([
        'id_tempat' => $id_tempat,
        'tanggal_acara' => $tanggal_acara,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai
    ]);
    if ($stmtCek->fetchColumn() !== false) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Tempat sudah memiliki reservasi pada waktu tersebut.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO reservasi (
            id_user, id_tempat, nama_acara, deskripsi_acara,
            penanggung_jawab, kontak_wa, tanggal_acara,
            jam_mulai, jam_selesai, poster_acara,
            tautan_info, status_reservasi
        ) VALUES (
            :id_user, :id_tempat, :nama_acara, :deskripsi_acara,
            :penanggung_jawab, :kontak_wa, :tanggal_acara,
            :jam_mulai, :jam_selesai, :poster_acara,
            :tautan_info, :status_reservasi
        )
        RETURNING id_reservasi, nama_acara, status_reservasi'
    );

    $stmt->execute([
        'id_user' => $id_user,
        'id_tempat' => $id_tempat,
        'nama_acara' => $nama_acara,
        'deskripsi_acara' => $input['deskripsi_acara'] ?? null,
        'penanggung_jawab' => $penanggung_jawab,
        'kontak_wa' => $kontak_wa,
        'tanggal_acara' => $tanggal_acara,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'poster_acara' => $input['poster_acara'] ?? null,
        'tautan_info' => $input['tautan_info'] ?? null,
        'status_reservasi' => 'menunggu'
    ]);

    $reservasiBaru = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil ditambahkan.',
        'data' => $reservasiBaru
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan reservasi.'
    ]);
}
