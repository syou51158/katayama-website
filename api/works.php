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
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    if ($method === 'GET') {
        $works = $db->read('works');
        
        // ステータスでフィルタリング
        if ($status) {
            $works = array_filter($works, function($item) use ($status) {
                return $item['status'] === $status;
            });
        }
        
        // カテゴリでフィルタリング
        if ($category) {
            $works = array_filter($works, function($item) use ($category) {
                return $item['category'] === $category;
            });
        }
        
        // 完成日でソート（新しい順）
        usort($works, function($a, $b) {
            $dateA = $a['completion_date'] ?? '1970-01-01';
            $dateB = $b['completion_date'] ?? '1970-01-01';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        // 件数制限
        if ($limit) {
            $works = array_slice($works, 0, $limit);
        }
        
        // レスポンス用にデータを整形
        $response = array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'description' => $item['description'],
                'category' => $item['category'],
                'image' => $item['image'],
                'location' => $item['location'],
                'completion_date' => $item['completion_date'],
                'formatted_completion_date' => $item['completion_date'] ? date('Y年m月', strtotime($item['completion_date'])) : '',
                'construction_period' => $item['construction_period'],
                'floor_area' => $item['floor_area'],
                'status' => $item['status'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at']
            ];
        }, array_values($works));
        
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
