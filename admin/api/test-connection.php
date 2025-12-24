<?php
require_once '../../lib/SupabaseClient.php';
$__cfg = __DIR__ . '/../../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }

header('Content-Type: application/json; charset=utf-8');

try {
    // サービスロールキーをテスト
    $serviceKey = SupabaseConfig::getServiceRoleKey();
    error_log('Service Key test: ' . substr($serviceKey, 0, 20) . '...');
    
    // ニューステーブルの存在確認
    $result = SupabaseClient::select('news', [], ['limit' => 1]);
    
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => '接続成功',
            'data' => $result
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => '接続失敗'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    error_log('Test error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
