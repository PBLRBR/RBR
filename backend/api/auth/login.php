<?php
require_once __DIR__ . '/../../config/csrf.php';
require_once __DIR__ . '/../../helpers/rate_limit.php';
csrfStartSession();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus POST.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format data tidak valid.']);
        exit;
    }
    $emailRaw = $input['email'] ?? null;
    $password = $input['password'] ?? null;
    if (!is_string($emailRaw) || !is_string($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email dan password wajib diisi.']);
        exit;
    }
    $email = strtolower(trim($emailRaw));
    if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email atau password tidak valid.']);
        exit;
    }

    $pdo = require __DIR__ . '/../../config/database.php';

    // Kedua counter diperbarui secara atomik di PostgreSQL, termasuk request paralel.
    // Batas: maksimal 30 request/IP dan 10 request/email per 15 menit.
    $allowedIp = simartRateLimitConsume($pdo, 'login_ip', simartClientIp(), 30, 900);
    $allowedAccount = simartRateLimitConsume($pdo, 'login_account', $email, 10, 900);
    if (!$allowedIp || !$allowedAccount) {
        http_response_code(429);
        header('Retry-After: 900');
        echo json_encode(['success' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi nanti.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id_user, nama, email, password, role FROM users WHERE lower(email) = :email LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, (string)$user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email atau password salah.']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id_user' => $user['id_user'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
    echo json_encode(['success' => true, 'message' => 'Login berhasil.', 'data' => $_SESSION['user']]);
} catch (Throwable $e) {
    error_log('SIMART login error [' . get_class($e) . ']: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
