<?php
require_once __DIR__ . '/../lib/SupabaseClient.php';

// CORS設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// OPTIONSリクエストの処理（プリフライトリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // パートナー企業を取得
    $partners = SupabaseClient::getActivePartners();
    
    if ($partners !== false) {
        echo json_encode([
            'success' => true,
            'data' => $partners,
            'count' => count($partners)
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'パートナー企業データの取得に失敗しました'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Partners API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'サーバーエラーが発生しました'
    ]);
}
