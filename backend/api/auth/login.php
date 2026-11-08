
<?php

session_start();

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

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if ($email === '' || !is_string($password) || $password === '') {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Email dan password wajib diisi.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id_user, nama, email, password, role
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Email atau password salah.'
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id_user' => $user['id_user'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil.',
        'data' => $_SESSION['user']
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
