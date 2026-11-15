<?php

require_once __DIR__ . '/../../config/csrf.php';
csrfStartSession();

function wajibLogin(): void
{
    if (!isset($_SESSION['user'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu.'
        ]);
        exit;
    }
}

function wajibAdmin(): void
{
    wajibLogin();

    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Akses hanya untuk admin.'
        ]);
        exit;
    }
}
