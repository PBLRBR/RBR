<?php
// Script lokal CLI untuk memberikan hash password kepada akun admin yang SUDAH ADA.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Hanya bisa melalui terminal.'); }
$email = trim(readline('Email admin yang sudah terdaftar: '));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Email tidak valid.\n");
$password = readline('Password baru minimal 8 karakter (terlihat di terminal): ');
if (strlen($password) < 8) exit("Password terlalu pendek.\n");
try {
    $pdo = require __DIR__ . '/../backend/config/database.php';
    $stmt = $pdo->prepare("UPDATE users SET password=:password WHERE email=:email AND role='admin' RETURNING id_user");
    $stmt->execute(['password'=>password_hash($password,PASSWORD_DEFAULT),'email'=>$email]);
    echo $stmt->fetchColumn() ? "Password admin berhasil diubah.\n" : "Akun admin dengan email tersebut tidak ditemukan.\n";
} catch (Throwable $e) {
    error_log($e->getMessage()); exit("Gagal memperbarui password admin.\n");
}
