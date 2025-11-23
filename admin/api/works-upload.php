<?php
/**
 * 施工実績画像アップロードAPI（完全実装版）
 */
require_once '../../lib/SupabaseStorage.php';
$__cfg = __DIR__ . '/../../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }
require_once '../includes/auth.php';

// 認証チェック
if (!checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '認証が必要です']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// エラーハンドリング
set_error_handler(function ($errno, $errstr) {
    throw new RuntimeException($errstr, $errno);
});

try {
    // メソッドチェック
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POSTメソッドのみ許可されています']);
        exit;
    }

    // ファイル存在チェック
    if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception('ファイルがアップロードされていません');
    }

    // サービスロールキーチェック
    $serviceKey = SupabaseConfig::getServiceRoleKey();
    if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
        throw new Exception('サービスロールキーが未設定です。config/supabase.secrets.php を確認してください。');
    }

    // ファイル情報取得
    $file = $_FILES['file'];
    $originalName = $file['name'];
    $tmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $error = $file['error'];

    // エラーチェック
    if ($error !== UPLOAD_ERR_OK) {
        throw new Exception('ファイルアップロードエラー: ' . $error);
    }

    // ファイルサイズチェック（10MB制限）
    if ($fileSize > 10 * 1024 * 1024) {
        throw new Exception('ファイルサイズは10MB以下にしてください');
    }

    // MIMEタイプ判定
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        throw new Exception('MIMEタイプの判定に失敗しました');
    }
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    // 画像ファイルのみ許可
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('画像ファイル（JPEG, PNG, GIF, WebP）のみアップロード可能です');
    }

    // 拡張子取得
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    if (!$ext) {
        // MIMEタイプから拡張子を推定
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $ext = $extMap[$mimeType] ?? 'jpg';
    }

    // 安全なファイル名生成
    $safeName = bin2hex(random_bytes(16)) . '.' . strtolower($ext);

    // ディレクトリ構造（年/月）
    $subdir = date('Y/m');
    $objectPath = $subdir . '/' . $safeName;

    // バケット名
    $bucket = 'works';

    // バケット存在確認・作成
    $bucketOk = SupabaseStorage::ensureBucket($bucket, true);
    if (!$bucketOk) {
        throw new Exception('ストレージバケットの作成に失敗しました');
    }

    // ファイル内容読み込み
    $contents = file_get_contents($tmpPath);
    if ($contents === false) {
        throw new Exception('ファイルの読み込みに失敗しました');
    }

    // アップロード実行
    $uploaded = SupabaseStorage::upload($bucket, $objectPath, $contents, $mimeType, true);
    if (!$uploaded) {
        throw new Exception('ファイルのアップロードに失敗しました');
    }

    // 公開URL取得
    $publicUrl = SupabaseStorage::getPublicObjectUrl($bucket, $objectPath);

    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'url' => $publicUrl,
        'path' => $objectPath,
        'bucket' => $bucket,
        'original_name' => $originalName,
        'size' => $fileSize,
        'mime_type' => $mimeType
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // エラーレスポンス
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} finally {
    restore_error_handler();
}
?>


