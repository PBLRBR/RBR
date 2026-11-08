<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method harus POST.']);
    exit;
}
try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) throw new InvalidArgumentException('JSON tidak valid.');
    $nama = trim((string)($data['nama'] ?? ''));
    $email = strtolower(trim((string)($data['email'] ?? '')));
    $hp = trim((string)($data['no_hp'] ?? ''));
    $password = $data['password'] ?? '';
    if ($nama === '' || strlen($nama) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($hp) < 8 || strlen($hp) > 20 || !is_string($password) || strlen($password) < 8) {
        throw new InvalidArgumentException('Nama, email valid, nomor HP, dan password minimal 8 karakter wajib diisi.');
    }
    $pdo = require __DIR__ . '/../../config/database.php';
    $stmt = $pdo->prepare("INSERT INTO users (nama, email, no_hp, password, role) VALUES (:nama,:email,:no_hp,:password,'member') RETURNING id_user");
    $stmt->execute(['nama'=>$nama,'email'=>$email,'no_hp'=>$hp,'password'=>password_hash($password, PASSWORD_DEFAULT)]);
    http_response_code(201);
    echo json_encode(['success'=>true,'message'=>'Pendaftaran berhasil. Silakan login.']);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code($e->getCode() === '23505' ? 409 : 500);
    echo json_encode(['success'=>false,'message'=>$e->getCode() === '23505' ? 'Email atau nomor HP sudah terdaftar.' : 'Registrasi gagal.']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Registrasi gagal.']);
}
