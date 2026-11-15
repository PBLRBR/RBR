
<?php
require_once __DIR__ . '/../../helpers/phone.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method harus POST.'
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format data tidak valid.'
        ]);
        exit;
    }

    $nama = trim($input['nama_pengunjung'] ?? '');
    $no_hp = normalizeIndonesianMobile($input['no_hp'] ?? null);
    $keperluan = trim($input['keperluan'] ?? '');

    if ($nama === '' || $keperluan === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama, nomor HP, dan keperluan wajib diisi.'
        ]);
        exit;
    }

    if ($no_hp === null) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nomor HP harus 10-13 digit angka berawalan 08, 62, atau +62.'
        ]);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';
    $pdo->beginTransaction();

    // Cari pengunjung berdasarkan nomor HP.
    $stmt = $pdo->prepare(
        'SELECT id_pengunjung
         FROM pengunjung
         WHERE no_hp = :no_hp
         LIMIT 1'
    );
    $stmt->execute([':no_hp' => $no_hp]);
    $pengunjung = $stmt->fetch();

    if ($pengunjung) {
        $id_pengunjung = (int) $pengunjung['id_pengunjung'];
    } else {
        // Daftarkan pengunjung baru.
        $stmt = $pdo->prepare(
            'INSERT INTO pengunjung
                (id_user, nama_pengunjung, no_hp)
             VALUES (NULL, :nama, :no_hp)
             RETURNING id_pengunjung'
        );

        $stmt->execute([
            ':nama' => $nama,
            ':no_hp' => $no_hp
        ]);

        $id_pengunjung = (int) $stmt->fetchColumn();
    }

    // Catat kunjungan pengunjung.
    $stmt = $pdo->prepare(
        'INSERT INTO buku_tamu (id_pengunjung, keperluan)
         VALUES (:id_pengunjung, :keperluan)
         RETURNING id_buku_tamu, tanggal_kunjungan, jam_masuk'
    );

    $stmt->execute([
        ':id_pengunjung' => $id_pengunjung,
        ':keperluan' => $keperluan
    ]);

    $kunjungan = $stmt->fetch();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Data kunjungan berhasil disimpan.',
        'data' => [
            'id_pengunjung' => $id_pengunjung,
            'id_buku_tamu' => $kunjungan['id_buku_tamu'],
            'tanggal_kunjungan' => $kunjungan['tanggal_kunjungan'],
            'jam_masuk' => $kunjungan['jam_masuk']
        ]
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan data kunjungan.'
    ]);
}
