<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../lib/SupabaseClient.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No IDs provided']);
    exit;
}

// PostgRESTのinフィルタ用にフォーマット: (id1,id2,id3)
// IDにカンマなどが含まれることはないはずだが、念のためエスケープ等は考慮（UUIDなら安全）
$idList = '(' . implode(',', $ids) . ')';

$result = SupabaseClient::delete('inquiries', [
    'id' => ['operator' => 'in', 'value' => $idList]
]);

if ($result !== false) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => SupabaseClient::getLastError()]);
}
