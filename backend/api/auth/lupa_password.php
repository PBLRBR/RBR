
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
    require_once __DIR__ . '/../../config/mailer.php';

    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        $input = $_POST;
    }

    $email = trim($input['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid.'
        ]);
        exit;
    }

    // Cari akun berdasarkan email
    $stmt = $pdo->prepare(
        'SELECT id_user, nama, email
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Respons tidak membocorkan apakah email terdaftar
    $pesan = 'Jika email terdaftar, tautan reset password akan dikirim.';

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $pdo->beginTransaction();

        // Nonaktifkan token reset sebelumnya yang belum digunakan
        $hapus = $pdo->prepare(
            'DELETE FROM password_resets
             WHERE id_user = :id_user
             AND used_at IS NULL'
        );

        $hapus->execute(['id_user' => $user['id_user']]);

        // Simpan hash token dengan masa berlaku 30 menit
        $simpan = $pdo->prepare(
            'INSERT INTO password_resets
                (id_user, token_hash, expires_at)
             VALUES
                (:id_user, :token_hash, CURRENT_TIMESTAMP + INTERVAL \'30 minutes\')'
        );

        $simpan->execute([
            'id_user' => $user['id_user'],
            'token_hash' => $tokenHash
        ]);

        $pdo->commit();

        $requestPath = (string) ($_SERVER['SCRIPT_NAME'] ?? '/backend/api/auth/lupa_password.php');
        $basePath = dirname(dirname(dirname(dirname(str_replace('\\', '/', $requestPath)))));
        $basePath = $basePath === '/' || $basePath === '.' ? '' : rtrim($basePath, '/');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        if (preg_match('/^[A-Za-z0-9.-]+(?::[0-9]{1,5})?$/', $host) !== 1) {
            $host = 'localhost';
        }
        $linkReset = $scheme . '://' . ($host ?: 'localhost') . $basePath
            . '/frontend/pages/auth/reset_password.php?token=' . urlencode($token);

        $isiHtml = '
            <h2>Reset Password SIMART</h2>
            <p>Halo ' . htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') . ',</p>
            <p>Kami menerima permintaan untuk mengatur ulang password akun SIMART kamu.</p>
            <p>
                <a href="' . htmlspecialchars($linkReset, ENT_QUOTES, 'UTF-8') . '">
                    Reset Password
                </a>
            </p>
            <p>Tautan hanya berlaku selama 30 menit dan hanya bisa digunakan satu kali.</p>
            <p>Jika kamu tidak meminta reset password, abaikan email ini.</p>
        ';

        $berhasil = kirimEmail(
            $user['email'],
            'Reset Password Akun SIMART',
            $isiHtml
        );

        if (!$berhasil) {
            // Hapus token yang gagal dikirim agar tidak ada token yatim
            $hapusToken = $pdo->prepare(
                'DELETE FROM password_resets
                 WHERE token_hash = :token_hash'
            );

            $hapusToken->execute(['token_hash' => $tokenHash]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $pesan
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Lupa password error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
