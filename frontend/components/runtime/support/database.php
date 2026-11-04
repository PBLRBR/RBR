<?php

declare(strict_types=1);

/** Reuse the existing PBLReal database connection for the imported frontend. */
function simart_db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $connection = require dirname(__DIR__, 4) . '/backend/config/database.php';

    return $connection;
}

