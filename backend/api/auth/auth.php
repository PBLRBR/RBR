
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
