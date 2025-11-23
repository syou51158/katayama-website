<?php
/**
 * ニュース管理用CRUD API
 */

require_once '../../lib/SupabaseClient.php';
require_once '../includes/auth.php';

// 認証チェック（APIはJSONで返却）
if (!SupabaseAuth::isLoggedIn()) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '認証が必要です']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            throw new Exception('未対応のHTTPメソッドです');
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('News CRUD Error: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
    
    // エラーの詳細情報を含める
    $errorDetails = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    echo json_encode($errorDetails, JSON_UNESCAPED_UNICODE);
}

function handleGet() {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // 特定のニュース取得
        $news = SupabaseClient::select('news', ['id' => $id]);
        if ($news && count($news) > 0) {
            echo json_encode([
                'success' => true,
                'data' => $news[0]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'ニュースが見つかりません'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // ニュース一覧取得（管理画面用：全ステータス）
        $limit = (int)($_GET['limit'] ?? 10);
        $offset = (int)($_GET['offset'] ?? 0);
        $category = $_GET['category'] ?? null;
        
        $filters = [];
        if ($category && $category !== 'all') {
            $filters['category'] = $category;
        }
        
        $news = SupabaseClient::select('news', $filters, [
            'order' => 'created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        echo json_encode([
            'success' => true,
            'data' => $news ?: [],
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'count' => count($news ?: [])
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}

function handlePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('無効なJSONデータです');
    }
    
    // 必須フィールドの検証
    $required = ['title', 'content', 'category', 'published_date'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("必須フィールド '{$field}' が入力されていません");
        }
    }
    
    $publishedDate = str_replace('/', '-', $input['published_date']);
    $data = [
        'title' => $input['title'],
        'content' => $input['content'],
        'excerpt' => $input['excerpt'] ?? null,
        'category' => $input['category'],
        'featured_image' => $input['featured_image'] ?? null,
        'published_date' => $publishedDate,
        'status' => $input['status'] ?? 'draft'
    ];
    
    $result = SupabaseClient::insert('news', $data);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'ニュースが正常に作成されました'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $detail = SupabaseClient::getLastError();
        throw new Exception('ニュースの作成に失敗しました' . ($detail ? '（詳細: ' . $detail . '）' : ''));
    }
}

function handlePut() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // デバッグ用ログ
    error_log('News update received data: ' . json_encode($input));
    
    if (!$input || empty($input['id'])) {
        throw new Exception('IDが指定されていません。受信データ: ' . json_encode($input));
    }
    
    $id = $input['id'];
    unset($input['id']);
    
    // 更新データの準備
    $data = [];
    $allowedFields = ['title', 'content', 'excerpt', 'category', 'featured_image', 'published_date', 'status'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $data[$field] = $input[$field];
        }
    }
    
    if (empty($data)) {
        throw new Exception('更新するデータがありません');
    }
    
    error_log('News update attempting - Data: ' . json_encode($data) . ' - ID: ' . $id);
    $result = SupabaseClient::update('news', $data, ['id' => $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'ニュースが正常に更新されました'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        error_log('News update failed - Data: ' . json_encode($data) . ' - ID: ' . $id);
        $detail = SupabaseClient::getLastError();
        throw new Exception('ニュースの更新に失敗しました' . ($detail ? '（詳細: ' . $detail . '）' : ''));
    }
}

function handleDelete() {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('IDが指定されていません');
    }
    
    $result = SupabaseClient::delete('news', ['id' => $id]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'ニュースが正常に削除されました'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('ニュースの削除に失敗しました');
    }
}

