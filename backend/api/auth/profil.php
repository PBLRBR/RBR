<?php

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

function responsProfil(int $status, string $pesan, ?array $data = null): void
{
    http_response_code($status);
    echo json_encode([
        'success' => $status < 400,
        'message' => $pesan,
        'data' => $data,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    header('Allow: PATCH');
    responsProfil(405, 'Gunakan metode PATCH.');
    exit;
}

wajibLogin();

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    responsProfil(400, 'Format JSON tidak valid.');
    exit;
}

$nama = $input['nama'] ?? null;
$noHp = $input['no_hp'] ?? '';

if (!is_string($nama) || trim($nama) === '' || strlen(trim($nama)) > 100) {
    responsProfil(422, 'Nama wajib diisi dan panjangnya maksimal 100 byte.');
    exit;
}

if (!is_string($noHp) || (trim($noHp) !== '' && !preg_match('/^\+?[0-9]{8,19}$/D', trim($noHp)))) {
    responsProfil(422, 'Nomor WhatsApp harus berisi 8–19 digit, dengan tanda + opsional di awal.');
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';
    $stmt = $pdo->prepare(
        'UPDATE users
         SET nama = :nama, no_hp = :no_hp
         WHERE id_user = :id
         RETURNING nama, email, no_hp'
    );
    $stmt->execute([
        'nama' => trim($nama),
        'no_hp' => trim($noHp) === '' ? null : trim($noHp),
        'id' => $_SESSION['user']['id_user'],
    ]);

    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profil) {
        responsProfil(404, 'Akun tidak ditemukan.');
        exit;
    }

    $_SESSION['user']['nama'] = $profil['nama'];
    responsProfil(200, 'Profil berhasil disimpan.', $profil);
} catch (PDOException $e) {
    error_log($e->getMessage());

    if ((string) $e->getCode() === '23505') {
        responsProfil(409, 'Nomor WhatsApp sudah digunakan.');
        exit;
    }

    responsProfil(500, 'Gagal menyimpan profil.');
}