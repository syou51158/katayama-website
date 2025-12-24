<?php
/**
 * Supabaseからサービスデータを取得するAPI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../lib/SupabaseClient.php';

try {
    // Supabaseからアクティブなサービスを取得
    $services = SupabaseClient::getActiveServices();
    
    if ($services === false) {
        throw new Exception('データベースからの取得に失敗しました');
    }
    
    // レスポンス
    $response = [
        'success' => true,
        'data' => $services
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
