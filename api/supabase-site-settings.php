<?php
require_once __DIR__ . '/../lib/SupabaseClient.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $siteSettings = SupabaseClient::getAllSiteSettings();
    
    if ($siteSettings !== false) {
        echo json_encode([
            'success' => true,
            'data' => $siteSettings
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'サイト設定の取得に失敗しました'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'サーバーエラーが発生しました',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}