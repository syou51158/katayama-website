<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// プリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../cms/includes/database.php';

try {
    $db = new JsonDatabase();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : 'published';
    
    if ($method === 'GET') {
        $news = $db->read('news');
        
        // ステータスでフィルタリング
        if ($status) {
            $news = array_filter($news, function($item) use ($status) {
                return $item['status'] === $status;
            });
        }
        
        // 日付でソート（新しい順）
        usort($news, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // 件数制限
        if ($limit) {
            $news = array_slice($news, 0, $limit);
        }
        
        // レスポンス用にデータを整形
        $response = array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'content' => $item['content'],
                'category' => $item['category'],
                'date' => $item['date'],
                'formatted_date' => date('Y年m月d日', strtotime($item['date'])),
                'status' => $item['status'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at']
            ];
        }, array_values($news));
        
        echo json_encode([
            'success' => true,
            'data' => $response,
            'count' => count($response)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
