<?php

/** Validasi password baru untuk bcrypt (PASSWORD_DEFAULT pada runtime proyek). */
function simartNewPasswordError($password): ?string
{
    if (!is_string($password) || strlen($password) < 8) {
        return 'Password minimal 8 byte.';
    }
    // Bcrypt hanya memproses 72 byte pertama dan tidak menerima byte NUL.
    if (strlen($password) > 72) {
        return 'Password maksimal 72 byte.';
    }
    if (str_contains($password, "\0")) {
        return 'Password mengandung karakter yang tidak valid.';
    }
    return null;
}
