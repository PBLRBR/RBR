<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method harus GET.']); exit;
}
echo json_encode(['success'=>true, 'data'=>$_SESSION['user'] ?? null]);
