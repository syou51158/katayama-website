<?php
/**
 * Supabaseから施工実績データを取得するAPI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../lib/SupabaseClient.php';

try {
    // リクエストパラメータの取得
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    // バリデーション
    $limit = min(max($limit, 1), 50); // 1-50の範囲に制限
    $offset = max($offset, 0);
    
    // カテゴリフィルターがある場合
    if ($category && $category !== 'all') {
        $filters = [
            'status' => 'published',
            'category' => $category
        ];
    } else {
        $filters = ['status' => 'published'];
    }
    
    // Supabaseからデータを取得
    $works = SupabaseClient::select('works', 
        $filters, 
        [
            'order' => 'completion_date.desc,created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ]
    );
    
    if ($works === false) {
        throw new Exception('データベースからの取得に失敗しました');
    }
    
    // レスポンスの整形
    $response = [
        'success' => true,
        'data' => $works,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($works)
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

