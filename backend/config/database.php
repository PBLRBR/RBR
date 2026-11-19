<?php

$localConfigPath = __DIR__ . '/database.local.php';
if (!is_file($localConfigPath)) {
    throw new RuntimeException('Konfigurasi koneksi database belum tersedia.');
}
$config = require $localConfigPath;
if (!is_array($config)) {
    throw new RuntimeException('Konfigurasi koneksi database tidak valid.');
}
foreach (['host', 'port', 'dbname', 'user', 'password'] as $key) {
    if (!isset($config[$key]) || !is_scalar($config[$key]) || (string)$config[$key] === '') {
        throw new RuntimeException('Konfigurasi database tidak lengkap.');
    }
}

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
    $config['host'],
    $config['port'],
    $config['dbname']
);

try {
    $pdo = new PDO(
        $dsn,
        $config['user'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log('SIMART database connection: ' . $e->getMessage());
    // Jangan keluarkan HTML/teks HTTP 200 dari lapisan koneksi.
    // Endpoint API menangani exception ini sebagai HTTP 500 JSON.
    throw new RuntimeException('Koneksi database gagal.', 0, $e);
}

return $pdo;