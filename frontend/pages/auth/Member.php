<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/database.php';

/** Read operations for member account pages. */
final class Member
{
    public static function findProfile(int $memberId): ?array
    {
        $query = simart_db()->prepare(
            'SELECT nama, email, no_hp FROM users WHERE id_user = :id_user'
        );
        $query->execute([':id_user' => $memberId]);
        $profile = $query->fetch();

        return is_array($profile) ? $profile : null;
    }
}
