<?php
// Prefer environment variables on hosting; preserve local PHP config on development.
$names = [
    'host' => 'SIMART_DB_HOST',
    'port' => 'SIMART_DB_PORT',
    'dbname' => 'SIMART_DB_NAME',
    'user' => 'SIMART_DB_USER',
    'password' => 'SIMART_DB_PASSWORD',
];

$present = [];
foreach ($names as $key => $envName) {
    $value = getenv($envName);
    if ($value !== false && $value !== '') {
        $present[$key] = $value;
    }
}

if (count($present) === 0) {
    $local = __DIR__ . '/database.local.php';
    if (!is_file($local)) {
        throw new RuntimeException('Konfigurasi database belum tersedia.');
    }
    $config = require $local;
} else {
    // Never mix local and hosting credentials: either all env vars are set, or none.
    if (count($present) !== count($names)) {
        throw new RuntimeException('Environment variable database SIMART belum lengkap.');
    }
    $config = $present;
}

if (!is_array($config)) {
    throw new RuntimeException('Konfigurasi koneksi database tidak valid.');
}
foreach (array_keys($names) as $key) {
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
    $pdo = new PDO($dsn, (string)$config['user'], (string)$config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log('SIMART database connection: ' . $e->getMessage());
    throw new RuntimeException('Koneksi database gagal.', 0, $e);
}
return $pdo;
