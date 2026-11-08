
<?php

require_once __DIR__ . '/../config/mailer.php';

$emailTujuan = 'Ahmdrzlap327@gmail.com';

$hasil = kirimEmail(
    $emailTujuan,
    'Tes Email SIMART',
    '<h2>Halo!</h2><p>Pengiriman email SIMART berhasil.</p>',
    'Pengiriman email SIMART berhasil.'
);

if ($hasil) {
    echo 'Email berhasil dikirim.';
} else {
    echo 'Email gagal dikirim. Periksa log PHP.';
}
