<?php

$configFile = __DIR__ . '/mail.local.php';

if (!file_exists($configFile)) {
    throw new RuntimeException('Konfigurasi email belum tersedia.');
}

return require $configFile;