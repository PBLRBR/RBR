
<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ]);
    exit;
}

try {
    $pdo = require __DIR__ . '/../../config/database.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        $input = $_POST;
    }

    $token = trim($input['token'] ?? '');
    $password = $input['password'] ?? '';
    $konfirmasi = $input['konfirmasi_password'] ?? '';

    // Validasi input
    if ($token === '' || $password === '' || $konfirmasi === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Semua kolom wajib diisi.'
        ]);
        exit;
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token reset tidak valid.'
        ]);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password minimal 8 karakter.'
        ]);
        exit;
    }

    if ($password !== $konfirmasi) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Konfirmasi password tidak cocok.'
        ]);
        exit;
    }

    $tokenHash = hash('sha256', $token);

    $pdo->beginTransaction();

    // Cari dan kunci token selama proses reset
    $stmt = $pdo->prepare(
        'SELECT id_reset, id_user
         FROM password_resets
         WHERE token_hash = :token_hash
           AND used_at IS NULL
           AND expires_at > CURRENT_TIMESTAMP
         FOR UPDATE'
    );

    $stmt->execute([
        'token_hash' => $tokenHash
    ]);

    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $pdo->rollBack();

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token tidak valid, sudah digunakan, atau kedaluwarsa.'
        ]);
        exit;
    }

    // Buat hash password baru
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Perbarui password pengguna
    $update = $pdo->prepare(
        'UPDATE users
         SET password = :password
         WHERE id_user = :id_user'
    );

    $update->execute([
        'password' => $passwordHash,
        'id_user' => $reset['id_user']
    ]);

    // Tandai token sudah digunakan
    $tandai = $pdo->prepare(
        'UPDATE password_resets
         SET used_at = CURRENT_TIMESTAMP
         WHERE id_reset = :id_reset'
    );

    $tandai->execute([
        'id_reset' => $reset['id_reset']
    ]);

    // Hapus token reset lain yang belum digunakan milik akun ini
    $hapus = $pdo->prepare(
        'DELETE FROM password_resets
         WHERE id_user = :id_user
           AND used_at IS NULL'
    );

    $hapus->execute([
        'id_user' => $reset['id_user']
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Password berhasil diperbarui. Silakan login kembali.'
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Reset password error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
