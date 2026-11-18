<?php
require_once __DIR__ . '/../config/app.php';

/**
 * Counter atomik PostgreSQL. Tidak menyimpan IP/email dalam bentuk teks.
 * Gunakan REMOTE_ADDR; jangan percaya X-Forwarded-For dari klien.
 */
function simartRateLimitConsume(PDO $pdo, string $bucket, string $identity, int $maxAttempts, int $windowSeconds): bool
{
    if ($maxAttempts < 1 || $windowSeconds < 1) {
        throw new InvalidArgumentException('Konfigurasi pembatasan request tidak valid.');
    }
    $key = hash_hmac('sha256', $bucket . ':' . strtolower(trim($identity)), simartRateLimitSecret());
    $stmt = $pdo->prepare(<<<'SQL'
        INSERT INTO public.auth_rate_limits (key_hash, attempts, window_until)
        VALUES (:key_hash, 1, CURRENT_TIMESTAMP + (CAST(:window_initial AS INTEGER) * INTERVAL '1 second'))
        ON CONFLICT (key_hash) DO UPDATE SET
            attempts = CASE
                WHEN auth_rate_limits.window_until <= CURRENT_TIMESTAMP THEN 1
                ELSE auth_rate_limits.attempts + 1
            END,
            window_until = CASE
                WHEN auth_rate_limits.window_until <= CURRENT_TIMESTAMP
                THEN CURRENT_TIMESTAMP + (CAST(:window_refresh AS INTEGER) * INTERVAL '1 second')
                ELSE auth_rate_limits.window_until
            END
        RETURNING attempts
    SQL);
    $stmt->execute([
        'key_hash' => $key,
        'window_initial' => $windowSeconds,
        'window_refresh' => $windowSeconds,
    ]);
    return (int)$stmt->fetchColumn() <= $maxAttempts;
}

function simartClientIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return is_string($ip) && strlen($ip) <= 45 ? $ip : 'unknown';
}
