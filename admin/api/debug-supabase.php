<?php
require_once '../../lib/SupabaseClient.php';
$__cfg = __DIR__ . '/../../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../../config/supabase.secrets.dist.php'; }

header('Content-Type: application/json; charset=utf-8');

// 詳細なデバッグを有効にする
error_log('=== Starting Supabase Debug Test ===');

try {
    // 1. サービスロールキーをチェック
    $serviceKey = SupabaseConfig::getServiceRoleKey();
    error_log('Service Role Key: ' . substr($serviceKey, 0, 20) . '...');
    
    // 2. プロジェクトURLをチェック
    $projectUrl = SupabaseConfig::getProjectUrl();
    error_log('Project URL: ' . $projectUrl);
    
    // 3. API URLをチェック
    $apiUrl = SupabaseConfig::getApiUrl();
    error_log('API URL: ' . $apiUrl);
    
    // 4. ニューステーブルの構造を確認
    error_log('Checking news table structure...');
    $result = SupabaseClient::select('news', [], ['limit' => 1, 'select' => '*']);
    error_log('News table select result: ' . json_encode($result));
    
    if ($result === false) {
        throw new Exception('Failed to select from news table');
    }
    
    if (is_array($result) && count($result) > 0) {
        $firstRecord = $result[0];
        error_log('First record keys: ' . json_encode(array_keys($firstRecord)));
        error_log('First record ID: ' . ($firstRecord['id'] ?? 'no id'));
        error_log('First record ID type: ' . gettype($firstRecord['id'] ?? 'null'));
    }
    
    // 5. INSERT テスト
    error_log('Testing INSERT operation...');
    $testData = [
        'title' => 'テストタイトル',
        'content' => 'これはテストコンテンツです',
        'category' => 'お知らせ',
        'published_date' => '2025-11-20',
        'status' => 'draft'
    ];
    
    error_log('Insert test data: ' . json_encode($testData));
    $insertResult = SupabaseClient::insert('news', $testData);
    error_log('Insert result: ' . json_encode($insertResult));
    
    if ($insertResult === false) {
        throw new Exception('Failed to insert test record');
    }
    
    // 6. UPDATE テスト
    if ($insertResult && isset($insertResult[0]['id'])) {
        $newId = $insertResult[0]['id'];
        error_log('Testing UPDATE operation with ID: ' . $newId);
        
        $updateData = ['title' => '更新テストタイトル'];
        $updateResult = SupabaseClient::update('news', $updateData, ['id' => $newId]);
        error_log('Update result: ' . json_encode($updateResult));
        
        if ($updateResult === false) {
            throw new Exception('Failed to update test record');
        }
        
        // 7. DELETE テスト（クリーンアップ）
        error_log('Testing DELETE operation with ID: ' . $newId);
        $deleteResult = SupabaseClient::delete('news', ['id' => $newId]);
        error_log('Delete result: ' . json_encode($deleteResult));
    }
    
    echo json_encode(['success' => true, 'message' => 'All tests passed']);
    
} catch (Exception $e) {
    error_log('Test error: ' . $e->getMessage());
    error_log('Test error file: ' . $e->getFile() . ' line: ' . $e->getLine());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

error_log('=== Supabase Debug Test Completed ===');
?>
