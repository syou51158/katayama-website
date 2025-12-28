<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../lib/SupabaseClient.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id || !$status) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Update status
$result = SupabaseClient::update('inquiries', ['status' => $status], ['id' => $id]);

if ($result !== false) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => SupabaseClient::getLastError()]);
}
?>
