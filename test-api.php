<?php
/**
 * Supabase API テストファイル
 */

// エラー表示を有効にする
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Supabase API テスト</h1>";

try {
    require_once 'lib/SupabaseClient.php';
    
    echo "<h2>✅ SupabaseClientが正常に読み込まれました</h2>";
    
    // ニュースデータの取得テスト
    echo "<h3>📰 ニュースデータの取得テスト</h3>";
    
    // 管理画面で使用するselectメソッドをテスト
    echo "<h4>全ニュースデータ（管理画面形式）</h4>";
    $allNews = SupabaseClient::select('news', [], [
        'order' => 'created_at.desc',
        'limit' => 10
    ]);
    
    if ($allNews !== false && count($allNews) > 0) {
        echo "<p>✅ 全ニュースデータ取得成功: " . count($allNews) . "件</p>";
        echo "<ul>";
        foreach ($allNews as $item) {
            echo "<li>" . htmlspecialchars($item['title']) . " (" . $item['status'] . "/" . $item['category'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ 全ニュースデータ取得失敗</p>";
        echo "<p>エラー詳細: " . print_r($allNews, true) . "</p>";
    }
    
    // 公開ニュースデータのテスト
    echo "<h4>公開ニュースデータ（フロントエンド形式）</h4>";
    $news = SupabaseClient::getPublishedNews(3);
    
    if ($news && count($news) > 0) {
        echo "<p>✅ 公開ニュースデータ取得成功: " . count($news) . "件</p>";
        foreach ($news as $item) {
            echo "<li>" . htmlspecialchars($item['title']) . " (" . $item['category'] . ")</li>";
        }
    } else {
        echo "<p>❌ 公開ニュースデータ取得失敗</p>";
    }
    
    // 施工実績データの取得テスト
    echo "<h3>🏗️ 施工実績データの取得テスト</h3>";
    $works = SupabaseClient::getPublishedWorks(null, 3);
    
    if ($works && count($works) > 0) {
        echo "<p>✅ 施工実績データ取得成功: " . count($works) . "件</p>";
        foreach ($works as $item) {
            echo "<li>" . htmlspecialchars($item['title']) . " (" . $item['category'] . ")</li>";
        }
    } else {
        echo "<p>❌ 施工実績データ取得失敗</p>";
    }
    
    // お客様の声データの取得テスト
    echo "<h3>💬 お客様の声データの取得テスト</h3>";
    $testimonials = SupabaseClient::getActiveTestimonials(3);
    
    if ($testimonials && count($testimonials) > 0) {
        echo "<p>✅ お客様の声データ取得成功: " . count($testimonials) . "件</p>";
        foreach ($testimonials as $item) {
            echo "<li>" . htmlspecialchars($item['customer_initial']) . "様 (" . $item['project_type'] . ")</li>";
        }
    } else {
        echo "<p>❌ お客様の声データ取得失敗</p>";
    }
    
    // 会社統計データの取得テスト
    echo "<h3>📊 会社統計データの取得テスト</h3>";
    $stats = SupabaseClient::getActiveStats();
    
    if ($stats && count($stats) > 0) {
        echo "<p>✅ 会社統計データ取得成功: " . count($stats) . "件</p>";
        foreach ($stats as $item) {
            echo "<li>" . htmlspecialchars($item['stat_name']) . ": " . $item['stat_value'] . $item['stat_unit'] . "</li>";
        }
    } else {
        echo "<p>❌ 会社統計データ取得失敗</p>";
    }
    
    // サービスデータの取得テスト
    echo "<h3>⚙️ サービスデータの取得テスト</h3>";
    $services = SupabaseClient::getActiveServices();
    
    if ($services && count($services) > 0) {
        echo "<p>✅ サービスデータ取得成功: " . count($services) . "件</p>";
        foreach ($services as $item) {
            echo "<li>" . htmlspecialchars($item['title']) . "</li>";
        }
    } else {
        echo "<p>❌ サービスデータ取得失敗</p>";
    }
    
    echo "<h2>🎉 全テスト完了</h2>";
    echo "<p><a href='index.html'>ホームページに戻る</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ エラーが発生しました</h2>";
    echo "<p>エラー内容: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>ファイル: " . $e->getFile() . " 行: " . $e->getLine() . "</p>";
}
?>
