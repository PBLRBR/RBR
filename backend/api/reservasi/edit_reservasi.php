
<?php
require_once __DIR__ . '/../../helpers/phone.php';

require_once __DIR__ . '/../auth/auth.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus PUT.'
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

    $id_reservasi = filter_var(
        $input['id_reservasi'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$id_reservasi || $id_reservasi < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID reservasi tidak valid.'
        ]);
        exit;
    }

    $stmtCek = $pdo->prepare(
        'SELECT id_user FROM reservasi WHERE id_reservasi = :id'
    );
    $stmtCek->execute(['id' => $id_reservasi]);
    $pemilik = $stmtCek->fetch(PDO::FETCH_ASSOC);

    if (!$pemilik) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Reservasi tidak ditemukan.'
        ]);
        exit;
    }

    $user = $_SESSION['user'];
    $isAdmin = ($user['role'] ?? '') === 'admin';

    if (!$isAdmin && (int) $pemilik['id_user'] !== (int) $user['id_user']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Tidak berhak mengubah reservasi ini.'
        ]);
        exit;
    }

    if (!$isAdmin) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Hanya admin yang dapat mengubah reservasi.'
        ]);
        exit;
    }

    $id_tempat = filter_var($input['id_tempat'] ?? null, FILTER_VALIDATE_INT);
    $nama_acara = trim($input['nama_acara'] ?? '');
    $penanggung_jawab = trim($input['penanggung_jawab'] ?? '');
    $kontak_wa = normalizeIndonesianMobile($input['kontak_wa'] ?? null);
    $tanggal_acara = $input['tanggal_acara'] ?? '';
    $jam_mulai = $input['jam_mulai'] ?? '';
    $jam_selesai = $input['jam_selesai'] ?? '';

    if (
        !$id_tempat || $nama_acara === '' ||
        $penanggung_jawab === '' || $kontak_wa === null ||
        $tanggal_acara === '' || $jam_mulai === '' ||
        $jam_selesai === ''
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Data wajib belum lengkap atau nomor WhatsApp tidak valid. Gunakan 08 dan 10-13 digit angka.'
        ]);
        exit;
    }

    if ($jam_selesai <= $jam_mulai) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Jam selesai harus setelah jam mulai.'
        ]);
        exit;
    }

    $cekBentrok = $pdo->prepare(
        "SELECT COUNT(*) FROM reservasi
         WHERE id_tempat = :id_tempat
           AND tanggal_acara = :tanggal
           AND id_reservasi <> :id_reservasi
           AND status_reservasi = 'disetujui'
           AND jam_mulai < :jam_selesai
           AND jam_selesai > :jam_mulai"
    );

    $cekBentrok->execute([
        'id_tempat' => $id_tempat,
        'tanggal' => $tanggal_acara,
        'id_reservasi' => $id_reservasi,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai
    ]);

    if ((int) $cekBentrok->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Jadwal tempat bertabrakan dengan reservasi lain.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE reservasi SET
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
         RETURNING id_reservasi, nama_acara, status_reservasi"
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

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi berhasil diperbarui.',
        'data' => $stmt->fetch(PDO::FETCH_ASSOC)
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memperbarui reservasi.'
    ]);
}
