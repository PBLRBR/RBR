<?php
require_once __DIR__ . '/../../helpers/phone.php';
require_once __DIR__ . '/../../helpers/rate_limit.php';

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

    $namaInput = $input['nama_pengunjung'] ?? null;
    $keperluanInput = $input['keperluan'] ?? null;

    $nama = is_string($namaInput) ? trim($namaInput) : '';
    $no_hp = normalizeIndonesianMobile($input['no_hp'] ?? null);
    $keperluan = is_string($keperluanInput) ? trim($keperluanInput) : '';

    if ($nama === '' || $keperluan === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama, nomor HP, dan keperluan wajib diisi.'
        ]);
        exit;
    }

    if (preg_match('/.{101}/us', $nama) === 1) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Nama pengunjung maksimal 100 karakter.',
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

    $requestId = $input['idempotency_key'] ?? null;

    if (
        $requestId !== null && (
            !is_string($requestId) ||
            !preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/iD', $requestId)
        )
    ) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Kunci pengiriman tidak valid.',
        ]);
        exit;
    }

    $windowSeconds = 600;

    if (!simartRateLimitConsume($pdo, 'guestbook_ip', simartClientIp(), 60, $windowSeconds)) {
        http_response_code(429);
        header('Retry-After: ' . $windowSeconds);
        echo json_encode([
            'success' => false,
            'message' => 'Terlalu banyak kiriman. Coba lagi nanti.',
        ]);
        exit;
    }

    $pdo->beginTransaction();

    // Cari pengunjung berdasarkan nomor HP.
    $stmt = $pdo->prepare(
        'INSERT INTO pengunjung (id_user, nama_pengunjung, no_hp)
     VALUES (NULL, :nama, :no_hp)
     ON CONFLICT (no_hp) DO NOTHING
     RETURNING id_pengunjung'
    );
    $stmt->execute([
        'nama' => $nama,
        'no_hp' => $no_hp,
    ]);
    $id_pengunjung = $stmt->fetchColumn();

    if ($id_pengunjung === false) {
        $stmt = $pdo->prepare(
            'SELECT id_pengunjung
         FROM pengunjung
         WHERE no_hp = :no_hp
         FOR KEY SHARE'
        );
        $stmt->execute(['no_hp' => $no_hp]);
        $id_pengunjung = $stmt->fetchColumn();

        if ($id_pengunjung === false) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Profil pengunjung berubah. Silakan kirim ulang.',
            ]);
            exit;
        }
    }

    // Catat kunjungan pengunjung.
    $stmt = $pdo->prepare(
        'INSERT INTO buku_tamu (id_pengunjung, keperluan, idempotency_key)
     VALUES (:id_pengunjung, :keperluan, :request_id)
     ON CONFLICT (idempotency_key) DO NOTHING
     RETURNING id_buku_tamu, tanggal_kunjungan, jam_masuk'
    );

    $params = [
        'id_pengunjung' => $id_pengunjung,
        'keperluan' => $keperluan,
        'request_id' => $requestId,
    ];

    $stmt->execute($params);
    $kunjungan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($kunjungan === false) {
        $stmt = $pdo->prepare(
            'SELECT id_buku_tamu, tanggal_kunjungan, jam_masuk
         FROM buku_tamu
         WHERE idempotency_key = :request_id
           AND id_pengunjung = :id_pengunjung
           AND keperluan = :keperluan'
        );

        $stmt->execute($params);
        $kunjungan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kunjungan === false) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Kunci pengiriman sudah dipakai untuk data lain.',
            ]);
            exit;
        }
    }

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
