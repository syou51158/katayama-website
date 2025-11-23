<?php
require_once '../../lib/SupabaseClient.php';
$__cfg = __DIR__ . '/../../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }

header('Content-Type: application/json; charset=utf-8');

try {
    // テスト用のデータ
    $testData = [
        'title' => 'テストタイトル',
        'content' => 'テストコンテンツ',
        'category' => 'お知らせ',
        'published_date' => '2025-11-20',
        'status' => 'draft'
    ];
    
    // 既存のレコードを取得してみる
    $existing = SupabaseClient::select('news', [], ['limit' => 5]);
    error_log('Existing records: ' . json_encode($existing));
    
    if ($existing && count($existing) > 0) {
        $id = $existing[0]['id'];
        error_log('Testing update with ID: ' . $id . ' (type: ' . gettype($id) . ')');
        
        // アップデートをテスト
        $updateData = ['title' => '更新テスト'];
        $result = SupabaseClient::update('news', $updateData, ['id' => $id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Update test successful', 'result' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Update test failed']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No existing records found']);
    }
    
} catch (Exception $e) {
    error_log('Test error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>