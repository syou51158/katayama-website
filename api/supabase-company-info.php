<?php
require_once __DIR__ . '/../lib/SupabaseClient.php';

header('Content-Type: application/json; charset=utf-8');

// エラーハンドリング
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $companyInfo = SupabaseClient::getCompanyInfo();
    
    if ($companyInfo) {
        echo json_encode([
            'success' => true,
            'data' => $companyInfo
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => '会社情報が見つかりませんでした'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'データベースエラー: ' . $e->getMessage()
    ]);
}