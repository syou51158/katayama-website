<?php
/**
 * ニュース画像アップロードAPI（完全実装版）
 */

// エラーレポートリングを設定
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 出力バッファリングは使用しない（常にJSONのみを返却）

    require_once '../../lib/SupabaseStorage.php';
    $__cfg = __DIR__ . '/../../config/supabase.secrets.php';
    if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }
    require_once '../includes/auth.php';

    // 認証チェック（API向けにJSONを返す）
    if (!SupabaseAuth::isLoggedIn()) {
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

    // サイズ制限チェック（post_max_size 事前判定）
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
    $toBytes = function($v) {
        $v = trim(strtolower($v));
        if ($v === '') return 0;
        $num = (int)$v;
        if (str_ends_with($v, 'g')) return $num * 1024 * 1024 * 1024;
        if (str_ends_with($v, 'm')) return $num * 1024 * 1024;
        if (str_ends_with($v, 'k')) return $num * 1024;
        return $num;
    };
    $postMax = $toBytes(ini_get('post_max_size')) ?: (16 * 1024 * 1024); // デフォルトフォールバック
    if ($contentLength && $contentLength > $postMax) {
        throw new Exception('リクエストサイズが post_max_size を超えています（現在 ' . ini_get('post_max_size') . '）。');
    }

    // ファイル存在チェックと詳細なエラー分類
    if (!isset($_FILES['file'])) {
        throw new Exception('ファイルフィールドが送信されていません');
    }

    // サービスロールキーチェック
    $serviceKey = SupabaseConfig::getServiceRoleKey();
    if (!$serviceKey) {
        throw new Exception('サービスロールキーが未設定です。config/supabase.secrets.php を確認してください。');
    }

    // ファイル情報取得
    $file = $_FILES['file'];
    $originalName = $file['name'] ?? '';
    $tmpPath = $file['tmp_name'] ?? '';
    $fileSize = $file['size'] ?? 0;
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    // エラーチェック（詳細メッセージ）
    if ($error !== UPLOAD_ERR_OK) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('アップロードサイズが制限を超えています（upload_max_filesize/post_max_size を確認）。');
            case UPLOAD_ERR_PARTIAL:
                throw new Exception('ファイルが一部しかアップロードされていません');
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('ファイルが選択されていません');
            default:
                throw new Exception('ファイルアップロードエラー: ' . $error);
        }
    }

    if (!is_uploaded_file($tmpPath)) {
        throw new Exception('一時ファイルが見つかりません');
    }

    // ファイルサイズチェック（10MB制限 + upload_max_filesize判定）
    $uploadMax = $toBytes(ini_get('upload_max_filesize')) ?: (10 * 1024 * 1024);
    if ($fileSize > $uploadMax) {
        throw new Exception('ファイルサイズが upload_max_filesize（現在 ' . ini_get('upload_max_filesize') . '）を超えています');
    }
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
    $bucket = 'news';

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

    // 成功レスポンス（JSONのみ）
    echo json_encode([
        'success' => true,
        'url' => $publicUrl,
        'path' => $objectPath,
        'bucket' => $bucket,
        'original_name' => $originalName,
        'size' => $fileSize,
        'mime_type' => $mimeType
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;

} catch (Exception $e) {
    // エラーレスポンス
    http_response_code(500);
    error_log('Upload error: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
} finally {
    restore_error_handler();
}



