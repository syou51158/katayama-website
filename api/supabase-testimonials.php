<?php
/**
 * Supabaseからお客様の声データを取得するAPI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../lib/SupabaseClient.php';

try {
    // リクエストパラメータの取得
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $limit = min(max($limit, 1), 20); // 1-20の範囲に制限
    
    // Supabaseからアクティブなお客様の声を取得
    $testimonials = SupabaseClient::getActiveTestimonials($limit);
    
    if ($testimonials === false) {
        throw new Exception('データベースからの取得に失敗しました');
    }
    
    // レスポンス
    $response = [
        'success' => true,
        'data' => $testimonials
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>


<<<<<<< HEAD


=======
>>>>>>> 82c831298bb2405620692e687e44f5d7d5eb8485
