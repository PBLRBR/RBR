<?php
/** SIMART Wasmer router patch. Default: hanya cek, tidak menimpa. */
if (PHP_SAPI !== 'cli') { exit(1); }
$target = $argv[1] ?? '';
$apply = in_array('--apply', $argv, true);
if (!$target || !is_dir($target)) {
    fwrite(STDERR, 'Jalankan: php PASANG_PATCH.php "C:\laragon\www\RBR" [--apply]' . PHP_EOL);
    exit(2);
}
$base = __DIR__;
$files = json_decode(<<<'JSON'
{
  "public/api/index.php": "bb54ca8e2b0393f08594c24a188a8ac3f8c00594db0cd1bcc3fc5d778db67464",
  "public/js/api.js": "a0ee7a421572c717931d88a61627c86394c4cf756f6d4654627f54032087d6ea",
  "public/js/dashboard-analytics.js": "14b86ea7a4042f5bc073aea5014fdb6dce316f96bedf7c4c2578e122eb53bbca",
  "router.php": "72d01ad47860e7b96dfdae9ad962fffe6551720ad6e8a708d16e76eb090463b8"
}
JSON, true);
$compatible = true;
foreach ($files as $file => $expected) {
    $local = rtrim($target, "\\/") . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $patch = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (!is_file($local) || !is_file($patch) || hash_file('sha256', $local) !== $expected) {
        echo "BERBEDA / TIDAK ADA: $file" . PHP_EOL;
        $compatible = false;
    } else {
        echo "COCOK: $file" . PHP_EOL;
    }
}
if (!$compatible) {
    fwrite(STDERR, "BERHENTI: versi file berbeda. Jangan paksa menimpa; bandingkan manual." . PHP_EOL);
    exit(3);
}
if (!$apply) {
    echo "DRY RUN SELESAI. Belum ada perubahan. Jalankan lagi dengan --apply." . PHP_EOL;
    exit(0);
}
$backup = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'SIMART_WASMER_BACKUP_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
foreach ($files as $file => $_) {
    $local = rtrim($target, "\\/") . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $patch = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $saved = $backup . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (!is_dir(dirname($saved))) mkdir(dirname($saved), 0770, true);
    if (!copy($local, $saved)) { fwrite(STDERR, "Gagal backup $file" . PHP_EOL); exit(4); }
    if (!copy($patch, $local)) { fwrite(STDERR, "Gagal memasang $file" . PHP_EOL); exit(5); }
    echo "TERPASANG: $file" . PHP_EOL;
}
echo "SELESAI. Backup asli: $backup" . PHP_EOL;
