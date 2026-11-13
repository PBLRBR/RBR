<?php
/**
 * Nomor seluler Indonesia: terima 08xxxxxxxx, 628xxxxxxxx, atau +628xxxxxxxx.
 * Disimpan seragam sebagai 08... (10-13 digit). Nilai null berarti tidak valid.
 */
function normalizeIndonesianMobile($value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    // Izinkan pemisah yang umum saat menyalin nomor, tetapi tidak huruf.
    $phone = preg_replace('/[\s().-]+/u', '', trim($value));
    if ($phone === null) {
        return null;
    }

    if (str_starts_with($phone, '+62')) {
        $phone = '0' . substr($phone, 3);
    } elseif (str_starts_with($phone, '62')) {
        $phone = '0' . substr($phone, 2);
    }

    return preg_match('/^08[0-9]{8,11}$/D', $phone) === 1 ? $phone : null;
}
