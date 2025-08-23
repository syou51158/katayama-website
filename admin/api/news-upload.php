<?php
require_once '../../lib/SupabaseStorage.php';
require_once '../../config/supabase.php';
require_once '../includes/auth.php';

// 認証
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

    // 前提チェック（SERVICE_ROLE_KEY 必須）
    $serviceKey = SupabaseConfig::getServiceRoleKey();
    if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
        throw new Exception('サービスロールキーが未設定のため、アップロードできません。環境変数 SUPABASE_SERVICE_ROLE_KEY を設定してください。');
    }

    $bucket = 'news'; // 公開バケット
    $ok = SupabaseStorage::ensureBucket($bucket, true);
    if (!$ok) throw new Exception('ストレージバケットの作成/確認に失敗しました');

    $file = $_FILES['file'];
    $originalName = $file['name'];
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $safeName = bin2hex(random_bytes(8)) . ($ext ? ('.' . strtolower($ext)) : '');

    // サブディレクトリ（年月）
    $subdir = date('Y/m');
    $objectPath = $subdir . '/' . $safeName;

    // MIMEタイプ
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // 転送
    $contents = file_get_contents($file['tmp_name']);
    $uploaded = SupabaseStorage::upload($bucket, $objectPath, $contents, $mime, true);
    if (!$uploaded) throw new Exception('アップロードに失敗しました');

    $publicUrl = SupabaseStorage::getPublicObjectUrl($bucket, $objectPath);

    echo json_encode([
        'success' => true,
        'url' => $publicUrl,
        'path' => $objectPath,
        'bucket' => $bucket
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>



