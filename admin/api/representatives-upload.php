<?php
require_once '../../lib/SupabaseStorage.php';
$__cfg = __DIR__ . '/../../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }
require_once '../includes/auth.php';

checkAuth();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        exit;
    }

    if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception('ファイルがアップロードされていません');
    }

    $serviceKey = SupabaseConfig::getServiceRoleKey();
    if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
        throw new Exception('サービスロールキーが未設定のため、アップロードできません。環境変数 SUPABASE_SERVICE_ROLE_KEY を設定してください。');
    }

    $bucket = 'website-assets';
    $ok = SupabaseStorage::ensureBucket($bucket, true);
    if (!$ok) throw new Exception('ストレージバケットの作成/確認に失敗しました');

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif','svg'];
    if (!in_array($ext, $allowed)) throw new Exception('対応していない拡張子です');

    $name = uniqid('rep_', true) . '.' . $ext;
    $objectPath = 'images/representatives/' . $name;
    $contentType = mime_content_type($file['tmp_name']) ?: 'application/octet-stream';
    $contents = file_get_contents($file['tmp_name']);

    $uploaded = SupabaseStorage::upload($bucket, $objectPath, $contents, $contentType, true);
    if (!$uploaded) throw new Exception('アップロードに失敗しました');

    $url = SupabaseStorage::getPublicObjectUrl($bucket, $objectPath);
    echo json_encode(['success' => true, 'url' => $url], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>