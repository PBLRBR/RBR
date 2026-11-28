<?php
require_once __DIR__ . '/../../helpers/phone.php';
require_once __DIR__ . '/../../helpers/password.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method harus POST.']);
    exit;
}
try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) throw new InvalidArgumentException('JSON tidak valid.');
    $namaRaw = $data['nama'] ?? null;
    $emailRaw = $data['email'] ?? null;
    if (!is_string($namaRaw) || !is_string($emailRaw)) {
        throw new InvalidArgumentException('Nama dan email harus berupa teks.');
    }
    $nama = trim($namaRaw);
    $email = strtolower(trim($emailRaw));
    $hp = normalizeIndonesianMobile($data['no_hp'] ?? null);
    $password = $data['password'] ?? '';
    if ($nama === '' || strlen($nama) > 100 || str_contains($namaRaw, "\0") || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $hp === null) {
        throw new InvalidArgumentException('Nama (maksimal 100 byte), email (maksimal 150 byte), dan nomor HP seluler valid (10-13 digit awalan 08) wajib diisi.');
    }
    $passwordError = simartNewPasswordError($password);
    if ($passwordError !== null) {
        throw new InvalidArgumentException($passwordError);
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
