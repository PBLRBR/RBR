<?php
require_once __DIR__ . '/../../helpers/phone.php';
require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus PUT.']);
    exit;
}

// Sesuai kode lama: hanya admin yang diizinkan mengedit reservasi.
wajibAdmin();

try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format data tidak valid.']);
        exit;
    }

    $id_reservasi = filter_var($input['id_reservasi'] ?? null, FILTER_VALIDATE_INT);
    $id_tempat = filter_var($input['id_tempat'] ?? null, FILTER_VALIDATE_INT);
    $nama_acara = is_string($input['nama_acara'] ?? null) ? trim($input['nama_acara']) : '';
    $penanggung_jawab = is_string($input['penanggung_jawab'] ?? null) ? trim($input['penanggung_jawab']) : '';
    $kontak_wa = normalizeIndonesianMobile($input['kontak_wa'] ?? null);
    $tanggal_acara = $input['tanggal_acara'] ?? null;
    $jam_mulai = $input['jam_mulai'] ?? null;
    $jam_selesai = $input['jam_selesai'] ?? null;

    if (!$id_reservasi || $id_reservasi < 1 || !$id_tempat || $id_tempat < 1 ||
        $nama_acara === '' || $penanggung_jawab === '' || $kontak_wa === null ||
        !is_string($tanggal_acara) || !is_string($jam_mulai) || !is_string($jam_selesai)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data wajib tidak valid, termasuk nomor WhatsApp (08, 10-13 digit).']);
        exit;
    }

    $tanggalObj = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal_acara);
    if (!$tanggalObj || $tanggalObj->format('Y-m-d') !== $tanggal_acara) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format tanggal harus YYYY-MM-DD.']);
        exit;
    }
    $formatJam = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
    if (!preg_match($formatJam, $jam_mulai) || !preg_match($formatJam, $jam_selesai)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format jam harus HH:MM atau HH:MM:SS.']);
        exit;
    }
    if (strlen($jam_mulai) === 5) $jam_mulai .= ':00';
    if (strlen($jam_selesai) === 5) $jam_selesai .= ':00';
    if ($jam_selesai <= $jam_mulai) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Jam selesai harus setelah jam mulai.']);
        exit;
    }

    $pdo->beginTransaction();

    // Urutan penguncian sama seperti ubah_status_reservasi.php:
    // (1) reservasi yang akan diubah, kemudian (2) tempat yang dituju.
    $stmtLama = $pdo->prepare(
        'SELECT id_reservasi, id_user, status_reservasi FROM reservasi
         WHERE id_reservasi = :id FOR UPDATE'
    );
    $stmtLama->execute(['id' => $id_reservasi]);
    $reservasiLama = $stmtLama->fetch(PDO::FETCH_ASSOC);
    if (!$reservasiLama) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reservasi tidak ditemukan.']);
        exit;
    }

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
        echo json_encode(['success' => false, 'message' => 'Tempat tidak aktif dan tidak dapat digunakan untuk reservasi.']);
        exit;
    }

    // Kecualikan dirinya sendiri; menunggu tidak mengunci slot.
    $stmtBentrok = $pdo->prepare(
        "SELECT 1 FROM reservasi
         WHERE id_tempat = :id_tempat
           AND tanggal_acara = :tanggal
           AND id_reservasi <> :id_reservasi
           AND status_reservasi = 'disetujui'
           AND jam_mulai < :jam_selesai
           AND jam_selesai > :jam_mulai
         LIMIT 1"
    );
    $stmtBentrok->execute([
        'id_tempat' => $id_tempat,
        'tanggal' => $tanggal_acara,
        'id_reservasi' => $id_reservasi,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai
    ]);
    if ($stmtBentrok->fetchColumn() !== false) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Jadwal tempat bertabrakan dengan reservasi lain.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE reservasi SET
            id_tempat = :id_tempat,
            nama_acara = :nama_acara,
            deskripsi_acara = :deskripsi,
            penanggung_jawab = :penanggung_jawab,
            kontak_wa = :kontak_wa,
            tanggal_acara = :tanggal,
            jam_mulai = :jam_mulai,
            jam_selesai = :jam_selesai,
            poster_acara = :poster,
            tautan_info = :tautan
         WHERE id_reservasi = :id
         RETURNING id_reservasi, nama_acara, status_reservasi'
    );
    $stmt->execute([
        'id_tempat' => $id_tempat,
        'nama_acara' => $nama_acara,
        'deskripsi' => $input['deskripsi_acara'] ?? null,
        'penanggung_jawab' => $penanggung_jawab,
        'kontak_wa' => $kontak_wa,
        'tanggal' => $tanggal_acara,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'poster' => $input['poster_acara'] ?? null,
        'tautan' => $input['tautan_info'] ?? null,
        'id' => $id_reservasi
    ]);
    $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil diperbarui.',
        'data' => $reservasi
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui reservasi.']);
}
