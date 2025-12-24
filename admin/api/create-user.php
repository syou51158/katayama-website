<?php
require_once '../../lib/SupabaseAuth.php';
require_once '../../config/supabase.secrets.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POSTのみ']);
    exit;
}
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'メールとパスワードが必要']);
    exit;
}
$ok = SupabaseAuth::adminCreateUser($email, $password, ['full_name' => '管理者', 'role' => 'admin'], true);
if ($ok) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => SupabaseAuth::getLastError()]);
}