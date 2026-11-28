<?php

require_once __DIR__ . '/../../config/csrf.php';
csrfStartSession();

// Header harus tersedia sebelum wajibLogin/wajibAdmin dapat menghentikan request.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function wajibLogin(): void
{
    if (!isset($_SESSION['user'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => !empty($_SESSION['session_expired'])
                ? 'Sesi telah berakhir. Silakan login kembali.'
                : 'Silakan login terlebih dahulu.',
            'code' => !empty($_SESSION['session_expired']) ? 'SESSION_EXPIRED' : 'AUTH_REQUIRED'
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
