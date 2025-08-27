<?php
require_once 'includes/auth.php';
require_once '../lib/SupabaseClient.php';

// 認証チェック
checkAuth();

try {
    // Supabaseから統計データを取得
    $allNews = SupabaseClient::select('news');
    $allWorks = SupabaseClient::select('works');
    $allServices = SupabaseClient::select('services');
    $allTestimonials = SupabaseClient::select('testimonials');
    
    // 統計を計算
    $publishedNews = array_filter($allNews ?: [], fn($item) => $item['status'] === 'published');
    $publishedWorks = array_filter($allWorks ?: [], fn($item) => $item['status'] === 'published');
    $draftNews = array_filter($allNews ?: [], fn($item) => $item['status'] === 'draft');
    
    // 今月の更新を計算
    $thisMonth = date('Y-m');
    $monthlyUpdates = array_filter(array_merge($allNews ?: [], $allWorks ?: []), function($item) use ($thisMonth) {
        return isset($item['updated_at']) && strpos($item['updated_at'], $thisMonth) === 0;
    });
    
    // 最近の更新（最新5件）
    $recentNews = array_slice(array_reverse($allNews ?: []), 0, 3);
    $recentWorks = array_slice(array_reverse($allWorks ?: []), 0, 2);
    
} catch (Exception $e) {
    // エラーハンドリング
    $error = $e->getMessage();
    $publishedNews = $publishedWorks = $draftNews = $monthlyUpdates = [];
    $recentNews = $recentWorks = [];
}
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード - 片山建設工業 CMS</title>
    
    <!-- Tailwind (built) -->
    <link rel="stylesheet" href="../assets/css/build.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap');
        body { font-family: 'Noto Sans JP', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <img src="../assets/img/logo.svg" alt="片山建設工業" class="h-8 mr-4">
                    <h1 class="text-xl font-semibold text-primary">CMS管理画面</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        ようこそ、<?php echo htmlspecialchars($currentUser['username'] ?? '管理者'); ?>さん
                    </span>
                    <a href="../index.html" target="_blank" 
                       class="text-sm text-gray-600 hover:text-primary transition duration-200">
                        サイトを見る
                    </a>
                    <a href="logout.php" 
                       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition duration-200">
                        ログアウト
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (isset($error)): ?>
        <!-- エラー表示 -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>データベース接続エラー:</strong> <?php echo htmlspecialchars($error); ?>
            <br><small>Supabaseからのデータ取得に失敗しました。設定を確認してください。</small>
        </div>
        <?php endif; ?>
        
        <!-- 統計カード -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">公開中のお知らせ</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo count($publishedNews); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">公開中の施工実績</h3>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo count($publishedWorks); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">下書きの記事</h3>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php echo count($draftNews); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">今月の更新</h3>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php echo count($monthlyUpdates); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 管理メニュー -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">管理メニュー</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <a href="pages/supabase-news.php" 
                           class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-medium text-gray-900">お知らせ管理（Supabase）</h3>
                                <p class="text-sm text-gray-500">お知らせの作成・編集・削除</p>
                            </div>
                        </a>
                        
                        
                        
                        <a href="pages/supabase-works.php" 
                           class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-medium text-gray-900">施工実績管理（Supabase）</h3>
                                <p class="text-sm text-gray-500">施工実績の作成・編集・削除</p>
                            </div>
                        </a>
                        
                        <a href="pages/settings.php" 
                           class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                            <div class="p-2 bg-gray-100 text-gray-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-medium text-gray-900">システム設定</h3>
                                <p class="text-sm text-gray-500">サイト設定とバックアップ</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 最近の更新 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">最近の更新</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach (array_slice(array_merge($recentNews, $recentWorks), 0, 5) as $item): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        <?php echo date('Y年m月d日', strtotime($item['updated_at'])); ?>
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                           <?php echo $item['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $item['status'] === 'published' ? '公開中' : '下書き'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
