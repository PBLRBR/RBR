<?php

// PostgreSQL VARCHAR counts characters, not UTF-8 bytes.
function simartValidateText($value, string $field, ?int $max = null): void
{
    if ($value === null) {
        return;
    }
    if (!is_string($value) || str_contains($value, "\0")) {
        throw new InvalidArgumentException("Kolom {$field} harus berupa teks yang valid.");
    }
    $length = preg_match_all('/./us', $value);
    if ($length === false) {
        throw new InvalidArgumentException("Kolom {$field} bukan teks UTF-8 yang valid.");
    }
    if ($max !== null && $length > $max) {
        throw new InvalidArgumentException("Kolom {$field} maksimal {$max} karakter.");
    }
}
