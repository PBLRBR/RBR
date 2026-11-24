<?php
// Production hosting uses environment variables. Local development uses mail.local.php.
$names = [
    'host' => 'SIMART_SMTP_HOST',
    'port' => 'SIMART_SMTP_PORT',
    'username' => 'SIMART_SMTP_USER',
    'password' => 'SIMART_SMTP_PASSWORD',
    'from_email' => 'SIMART_SMTP_FROM_EMAIL',
    'from_name' => 'SIMART_SMTP_FROM_NAME',
];

$provided = [];
foreach ($names as $key => $envName) {
    $value = getenv($envName);
    if ($value !== false && $value !== '') {
        $provided[$key] = $value;
    }
}

if (count($provided) === 0) {
    $local = __DIR__ . '/mail.local.php';
    if (!is_file($local)) {
        throw new RuntimeException('Konfigurasi email belum tersedia.');
    }
    $config = require $local;
} else {
    if (count($provided) !== count($names)) {
        throw new RuntimeException('Environment variable email SIMART belum lengkap.');
    }
    $config = $provided;
}

if (!is_array($config)) {
    throw new RuntimeException('Konfigurasi email tidak valid.');
}
foreach (array_keys($names) as $key) {
    if (!isset($config[$key]) || !is_scalar($config[$key]) || (string)$config[$key] === '') {
        throw new RuntimeException('Konfigurasi email tidak lengkap.');
    }
}
return $config;
