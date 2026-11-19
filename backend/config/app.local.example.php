<?php
// Salin menjadi app.local.php, JANGAN masukkan app.local.php ke Git.
// Buat secret: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
return [
    'app_url' => 'http://localhost:8000',
    'rate_limit_secret' => 'ISI_DENGAN_RANDOM_64_KARAKTER_HEX_DARI_PERINTAH_DI_ATAS',
];
