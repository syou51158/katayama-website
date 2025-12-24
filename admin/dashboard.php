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
    $allRepresentatives = SupabaseClient::select('representatives');
    
    // 統計を計算
    $publishedNews = array_filter($allNews ?: [], fn($item) => $item['status'] === 'published');
    $publishedWorks = array_filter($allWorks ?: [], fn($item) => $item['status'] === 'published');
    $draftNews = array_filter($allNews ?: [], fn($item) => $item['status'] === 'draft');
    $activeRepresentatives = array_filter($allRepresentatives ?: [], fn($item) => ($item['status'] ?? 'active') === 'active');
    
    // 今月の更新を計算
    $thisMonth = date('Y-m');
    $monthlyUpdates = array_filter(array_merge($allNews ?: [], $allWorks ?: []), function($item) use ($thisMonth) {
        return isset($item['updated_at']) && strpos($item['updated_at'], $thisMonth) === 0;
    });
    
    // やることリスト用の集計
    $newsWithoutImage = array_filter($allNews ?: [], function($item) {
        return empty($item['featured_image']);
    });
    $worksWithoutImage = array_filter($allWorks ?: [], function($item) {
        return empty($item['featured_image']);
    });
    $scheduledDrafts = array_filter($allNews ?: [], function($item) {
        return ($item['status'] === 'draft') && !empty($item['published_date']);
    });
    
    // 最近の更新（最新5件）
    $recentNews = array_slice(array_reverse($allNews ?: []), 0, 3);
    $recentWorks = array_slice(array_reverse($allWorks ?: []), 0, 2);
    $connectOk = ($allNews !== false) && ($allWorks !== false);
    $serviceRoleKey = SupabaseConfig::getServiceRoleKey();
    $serviceRoleOk = ($serviceRoleKey && $serviceRoleKey !== 'YOUR_SERVICE_ROLE_KEY_HERE' && $serviceRoleKey !== 'CHANGE_ME');
    $lastError = SupabaseClient::getLastError();
    $offlineMode = method_exists('SupabaseConfig', 'isOfflineMode') ? SupabaseConfig::isOfflineMode() : false;
    
} catch (Exception $e) {
    // エラーハンドリング
    $error = $e->getMessage();
    $publishedNews = $publishedWorks = $draftNews = $monthlyUpdates = [];
    $recentNews = $recentWorks = [];
    $activeRepresentatives = [];
    $connectOk = false;
    $serviceRoleOk = false;
}
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード - 片山建設工業 CMS</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#233A5C',
                        secondary: '#A68B5B',
                        accent: '#F8F9FB',
                        text_dark: '#2C3241',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-text_dark">
    <div class="flex h-screen overflow-hidden">
        <!-- サイドバー -->
        <aside class="w-64 bg-primary text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6">
                <a href="/admin/dashboard.php" class="flex items-center space-x-3 text-xl font-bold">
                    <i class="fas fa-building text-secondary"></i>
                    <span>片山建設工業 CMS</span>
                </a>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="/admin/dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg bg-white/10 text-white">
                            <i class="fas fa-tachometer-alt w-6 text-center"></i>
                            <span>ダッシュボード</span>
                        </a>
                    </li>
                    <li class="pt-4 pb-2 px-3 text-xs uppercase text-gray-400 font-semibold">コンテンツ管理</li>
                    <li>
                        <a href="/admin/pages/news.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white transition-colors">
                            <i class="fas fa-newspaper w-6 text-center"></i>
                            <span>お知らせ</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/pages/works.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white transition-colors">
                            <i class="fas fa-hard-hat w-6 text-center"></i>
                            <span>施工実績</span>
                        </a>
                    </li>
                    <li class="pt-4 pb-2 px-3 text-xs uppercase text-gray-400 font-semibold">システム</li>
                    <li>
                        <a href="/" target="_blank" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-gray-300 hover:text-white transition-colors">
                            <i class="fas fa-external-link-alt w-6 text-center"></i>
                            <span>サイトを確認</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/logout.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-red-500/20 text-red-300 hover:text-red-100 transition-colors">
                            <i class="fas fa-sign-out-alt w-6 text-center"></i>
                            <span>ログアウト</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-4 border-t border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-white font-bold">
                        A
                    </div>
                    <div>
                        <p class="text-sm font-medium">管理者</p>
                        <p class="text-xs text-gray-400">admin</p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- メインコンテンツ -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- モバイルヘッダー -->
            <header class="bg-white shadow-sm md:hidden z-10">
                <div class="flex items-center justify-between p-4">
                    <a href="/admin/dashboard.php" class="text-lg font-bold text-primary">片山建設工業 CMS</a>
                    <button class="text-gray-500 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </header>
            
            <!-- コンテンツエリア -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-2xl md:text-3xl font-bold text-primary mb-8">ダッシュボード</h1>
                    
                    <?php if (isset($connectOk) && !$connectOk): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8" role="alert">
                        <p class="font-bold">データベース接続エラー</p>
                        <p>Supabaseへの接続に失敗しました。設定を確認してください。</p>
                        <?php if (isset($lastError) && $lastError): ?>
                        <p class="text-sm mt-2 font-mono bg-red-50 p-2 rounded"><?php echo htmlspecialchars($lastError); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($serviceRoleOk) && !$serviceRoleOk): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                        <p class="font-bold">設定警告</p>
                        <p>書き込み用APIキー（Service Role Key）が設定されていないか、デフォルトのままです。データの更新・削除ができません。</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- 統計カード -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium uppercase">お知らせ</p>
                                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo count($publishedNews); ?></h3>
                                </div>
                                <div class="p-3 bg-blue-50 rounded-full text-blue-500">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span class="text-green-500 font-medium"><i class="fas fa-arrow-up"></i> 公開中</span>
                                <span class="mx-2">|</span>
                                <span>下書き: <?php echo count($draftNews); ?></span>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium uppercase">施工実績</p>
                                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo count($publishedWorks); ?></h3>
                                </div>
                                <div class="p-3 bg-yellow-50 rounded-full text-yellow-500">
                                    <i class="fas fa-hard-hat"></i>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span class="text-green-500 font-medium"><i class="fas fa-check"></i> 公開済み</span>
                                <span class="mx-2">|</span>
                                <span>全件数: <?php echo count($allWorks ?: []); ?></span>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium uppercase">今月の更新</p>
                                    <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo count($monthlyUpdates); ?></h3>
                                </div>
                                <div class="p-3 bg-green-50 rounded-full text-green-500">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span><?php echo date('Y年m月'); ?>の更新数</span>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium uppercase">アクセス数</p>
                                    <h3 class="text-3xl font-bold text-gray-800 mt-1">-</h3>
                                </div>
                                <div class="p-3 bg-purple-50 rounded-full text-purple-500">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <span>Google Analytics連携未定</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- 最近の更新 -->
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800">最近のお知らせ</h3>
                                <a href="/admin/pages/news.php" class="text-sm text-primary hover:underline">すべて見る</a>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <?php if (empty($recentNews)): ?>
                                    <div class="p-6 text-center text-gray-500">データがありません</div>
                                <?php else: ?>
                                    <?php foreach ($recentNews as $item): ?>
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($item['published_date'], 0, 10)); ?></span>
                                            <span class="px-2 py-0.5 text-xs rounded-full 
                                                <?php echo $item['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                <?php echo $item['status'] === 'published' ? '公開中' : '下書き'; ?>
                                            </span>
                                        </div>
                                        <h4 class="font-medium text-gray-800 mb-1"><?php echo htmlspecialchars($item['title']); ?></h4>
                                        <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($item['excerpt'] ?? ''); ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- クイックアクション -->
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h3 class="font-bold text-gray-800">クイックアクション</h3>
                            </div>
                            <div class="p-6 grid grid-cols-2 gap-4">
                                <a href="/admin/pages/news-create.php" class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors text-blue-700 group">
                                    <i class="fas fa-plus-circle text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
                                    <span class="font-medium">お知らせ作成</span>
                                </a>
                                <a href="/admin/pages/works-create.php" class="flex flex-col items-center justify-center p-6 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors text-yellow-700 group">
                                    <i class="fas fa-camera text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
                                    <span class="font-medium">実績追加</span>
                                </a>
                                <a href="/admin/pages/representatives.php" class="flex flex-col items-center justify-center p-6 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors text-purple-700 group">
                                    <i class="fas fa-user-tie text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
                                    <span class="font-medium">代表者編集</span>
                                </a>
                                <a href="/admin/settings.php" class="flex flex-col items-center justify-center p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors text-gray-700 group">
                                    <i class="fas fa-cog text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
                                    <span class="font-medium">設定</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
