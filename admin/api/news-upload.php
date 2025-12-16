<?php
/**
 * ニュース画像アップロードAPI（完全実装版）
 */

// エラーレポートリングを設定
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 出力バッファリングは使用しない（常にJSONのみを返却）

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        if ($needle == '') return true;
        $len = strlen($needle);
        return substr($haystack, -$len) === $needle;
    }
}

    require_once '../../lib/SupabaseStorage.php';
    $__cfg = __DIR__ . '/../../config/supabase.secrets.php';
    if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }
    require_once '../includes/auth.php';
    header_remove('Content-Type');
    header('Content-Type: application/json; charset=utf-8');

    // 認証チェック（API向けにJSONを返す）
    if (!SupabaseAuth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => '認証が必要です']);
        exit;
    }

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
    $mimeType = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
        }
    }
    if (!$mimeType && function_exists('mime_content_type')) {
        $mimeType = @mime_content_type($tmpPath);
    }
    if (!$mimeType && function_exists('getimagesize')) {
        $imgInfo = @getimagesize($tmpPath);
        if (is_array($imgInfo) && isset($imgInfo['mime']) && $imgInfo['mime']) {
            $mimeType = $imgInfo['mime'];
        }
    }
    if (!$mimeType) {
        throw new Exception('MIMEタイプの判定に失敗しました');
    }

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

    $useLocal = SupabaseConfig::isOfflineMode();
    if (!$useLocal) {
        $bucketOk = SupabaseStorage::ensureBucket($bucket, true);
        if (!$bucketOk) {
            $useLocal = true;
        } else {
            $contents = file_get_contents($tmpPath);
            if ($contents === false) {
                throw new Exception('ファイルの読み込みに失敗しました');
            }
            $uploaded = SupabaseStorage::upload($bucket, $objectPath, $contents, $mimeType, true);
            if ($uploaded) {
                $publicUrl = SupabaseStorage::getPublicObjectUrl($bucket, $objectPath);
            } else {
                $useLocal = true;
            }
        }
    }

    if ($useLocal) {
        $root = realpath(__DIR__ . '/../../');
        if ($root === false) {
            throw new Exception('ドキュメントルートの解決に失敗しました');
        }
        $localBase = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $bucket;
        $localDir = $localBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subdir);
        if (!is_dir($localDir)) {
            if (!mkdir($localDir, 0777, true)) {
                throw new Exception('ローカル保存用ディレクトリの作成に失敗しました');
            }
        }
        $destPath = $localDir . DIRECTORY_SEPARATOR . $safeName;
        if (!move_uploaded_file($tmpPath, $destPath)) {
            // moveが失敗した場合はコピーにフォールバック
            $data = file_get_contents($tmpPath);
            if ($data === false || file_put_contents($destPath, $data) === false) {
                throw new Exception('ローカル保存に失敗しました');
            }
        }
        $publicUrl = '/assets/uploads/' . $bucket . '/' . $objectPath;
    }

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

} catch (Throwable $e) {
    // エラーレスポンス
    http_response_code(500);
    $errorMsg = $e->getMessage();
    $errorLine = $e->getLine();
    $errorFile = basename($e->getFile());
    
    // 詳細なログ出力
    error_log(sprintf(
        "Upload Error: %s in %s:%d\nRequest: %s\nFiles: %s",
        $errorMsg,
        $errorFile,
        $errorLine,
        print_r($_POST, true),
        print_r($_FILES, true)
    ));
    
    echo json_encode([
        'success' => false,
        'error' => $errorMsg,
        'debug' => [
            'file' => $errorFile,
            'line' => $errorLine
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
} finally {
    restore_error_handler();
}
