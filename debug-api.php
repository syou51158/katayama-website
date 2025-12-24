<?php
/**
 * デバッグ用API - ローカル環境でのSupabase接続テスト
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'status' => 'debug',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
    'current_path' => __DIR__,
    'file_exists' => [
        'SupabaseClient' => file_exists(__DIR__ . '/lib/SupabaseClient.php'),
        'config' => file_exists(__DIR__ . '/config/supabase.secrets.php'),
        'api_news' => file_exists(__DIR__ . '/api/supabase-news.php')
    ]
]);

// Supabase接続テスト
try {
    require_once __DIR__ . '/lib/SupabaseClient.php';
    
    echo "\n\n<!-- Supabase Test -->\n";
    echo "<!-- Attempting to connect to Supabase... -->\n";
    
    $testResult = SupabaseClient::select('news', [], ['limit' => 1]);
    
    if ($testResult !== false) {
        echo "<!-- SUCCESS: Connected to Supabase -->\n";
        echo "<!-- Data count: " . count($testResult) . " -->\n";
    } else {
        echo "<!-- ERROR: Failed to connect to Supabase -->\n";
    }
    
} catch (Exception $e) {
    echo "<!-- EXCEPTION: " . $e->getMessage() . " -->\n";
}
?>

