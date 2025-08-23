<?php
/**
 * Supabaseから会社統計データを取得するAPI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../lib/SupabaseClient.php';

try {
    // Supabaseからアクティブな統計データを取得
    $stats = SupabaseClient::getActiveStats();
    
    if ($stats === false) {
        throw new Exception('データベースからの取得に失敗しました');
    }
    
    // レスポンス
    $response = [
        'success' => true,
        'data' => $stats
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


