<?php
/** Konfigurasi nonpublik SIMART untuk tautan email dan rate limiting. */
function simartAppConfig(): array
{
    static $config = null;
    if ($config !== null) return $config;

    $file = __DIR__ . '/app.local.php';
    $local = is_file($file) ? require $file : [];
    if (!is_array($local)) throw new RuntimeException('Konfigurasi aplikasi tidak valid.');

    $url = getenv('SIMART_APP_URL');
    $secret = getenv('SIMART_RATE_LIMIT_SECRET');
    $config = [
        'app_url' => is_string($url) && $url !== '' ? $url : (string)($local['app_url'] ?? ''),
        'rate_limit_secret' => is_string($secret) && $secret !== '' ? $secret : (string)($local['rate_limit_secret'] ?? ''),
    ];
    return $config;
}

function simartAppUrl(): string
{
    $url = rtrim(trim(simartAppConfig()['app_url']), '/');
    $parts = parse_url($url);
    if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])
        || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['query']) || isset($parts['fragment'])
        || (isset($parts['path']) && $parts['path'] !== '' && $parts['path'] !== '/')) {
        throw new RuntimeException('SIMART_APP_URL belum dikonfigurasi dengan benar.');
    }
    $scheme = strtolower($parts['scheme']);
    $host = strtolower($parts['host']);
    $loopback = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    if (!in_array($scheme, ['http', 'https'], true) || ($scheme !== 'https' && !$loopback)) {
        throw new RuntimeException('Gunakan HTTPS untuk URL produksi.');
    }
    return $url;
}

function simartRateLimitSecret(): string
{
    $secret = simartAppConfig()['rate_limit_secret'];
    if (strlen($secret) < 32) {
        throw new RuntimeException('SIMART_RATE_LIMIT_SECRET belum dikonfigurasi (minimal 32 karakter).');
    }
    return $secret;
}
