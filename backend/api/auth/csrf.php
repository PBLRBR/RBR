<?php
require_once __DIR__ . '/../../config/csrf.php';
csrfStartSession();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method harus GET.']);
    exit;
}
echo json_encode(['success' => true, 'data' => ['csrf_token' => csrfToken()]]);
