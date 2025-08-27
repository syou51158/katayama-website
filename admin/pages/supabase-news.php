<?php
require_once '../includes/auth.php';
require_once '../../lib/SupabaseClient.php';

// 認証チェック
checkAuth();

$pageTitle = 'お知らせ管理';
$currentPage = 'supabase-news';

// ページネーション設定
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// カテゴリフィルター
$category = isset($_GET['category']) ? $_GET['category'] : null;

try {
    // ニュースデータを取得
    $filters = [];
    if ($category && $category !== 'all') {
        $filters['category'] = $category;
    }
    
    $news = SupabaseClient::select('news', $filters, [
        'order' => 'created_at.desc',
        'limit' => $limit,
        'offset' => $offset
    ]);
    
    if ($news === false) {
        throw new Exception('データの取得に失敗しました');
    }
    
    // 総数を取得（ページネーション用）
    $totalResult = SupabaseClient::select('news', $filters, [
        'select' => 'id'
    ]);
    $total = $totalResult ? count($totalResult) : 0;
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $news = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - 片山建設工業 管理画面</title>
    <link rel="stylesheet" href="../../assets/css/build.css">
    </head>
<body class="bg-gray-50">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="../dashboard.php" class="text-gray-600 hover:text-primary mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-semibold text-primary"><?php echo htmlspecialchars($pageTitle); ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light transition duration-200">
                        新規作成
                    </button>
                    <a href="../logout.php" 
                       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition duration-200">
                        ログアウト
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h2>
                <p class="text-gray-600 mt-2">Supabaseデータベースのニュースを管理します</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>エラー:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- サマリー -->
            <div class="bg-gray-50 border border-gray-200 text-gray-700 px-4 py-3 rounded mb-4">
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div>総件数: <span class="font-semibold"><?php echo $total; ?></span></div>
                    <div>現在のページ: <span class="font-semibold"><?php echo $page; ?></span></div>
                    <div>総ページ数: <span class="font-semibold"><?php echo max(1, (int)ceil(($total ?: 0) / $limit)); ?></span></div>
                    <div>表示件数/ページ: <span class="font-semibold"><?php echo $limit; ?></span></div>
                    <div>フィルター: <span class="font-semibold"><?php echo $category ? htmlspecialchars($category) : 'なし'; ?></span></div>
                </div>
            </div>

            <!-- フィルターとアクション -->
            <div class="mb-6 flex justify-between items-center">
                <div class="flex gap-4">
                    <!-- カテゴリフィルター -->
                    <select id="categoryFilter" class="border border-gray-300 rounded px-3 py-2" onchange="filterNews()">
                        <option value="all" <?php echo $category === null || $category === 'all' ? 'selected' : ''; ?>>全カテゴリ</option>
                        <option value="お知らせ" <?php echo $category === 'お知らせ' ? 'selected' : ''; ?>>お知らせ</option>
                        <option value="イベント" <?php echo $category === 'イベント' ? 'selected' : ''; ?>>イベント</option>
                        <option value="施工事例" <?php echo $category === '施工事例' ? 'selected' : ''; ?>>施工事例</option>
                        <option value="コラム" <?php echo $category === 'コラム' ? 'selected' : ''; ?>>コラム</option>
                    </select>
                </div>
                
                <button onclick="openCreateModal()" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-light">
                    新規作成
                </button>
            </div>

            <!-- ニュース一覧テーブル -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">カテゴリ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">公開日</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($news)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <?php echo isset($error) ? 'データの取得に失敗しました' : 'データがありません'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($news as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </div>
                                    <?php if ($item['excerpt']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars(substr($item['excerpt'], 0, 50)); ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('Y/m/d', strtotime($item['published_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $statusClass = $item['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                    $statusText = $item['status'] === 'published' ? '公開' : '下書き';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="editNews('<?php echo $item['id']; ?>')" 
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">編集</button>
                                    <button onclick="deleteNews('<?php echo $item['id']; ?>')" 
                                            class="text-red-600 hover:text-red-900">削除</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ページネーション -->
            <?php 
                $totalPages = max(1, (int)ceil(($total ?: 0) / $limit));
                $prevPage = max(1, $page - 1);
                $nextPage = min($totalPages, $page + 1);
                $qsCategory = $category ? '&category=' . urlencode($category) : '';
            ?>
            <div class="mt-6 flex justify-center">
                <div class="flex items-center gap-2 text-sm">
                    <a href="?page=1<?php echo $qsCategory; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>">最初</a>
                    <a href="?page=<?php echo $prevPage; ?><?php echo $qsCategory; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>">前へ</a>
                    <span class="px-3 py-2 bg-primary text-white rounded"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
                    <a href="?page=<?php echo $nextPage; ?><?php echo $qsCategory; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page >= $totalPages ? 'pointer-events-none opacity-50' : ''; ?>">次へ</a>
                    <a href="?page=<?php echo $totalPages; ?><?php echo $qsCategory; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page >= $totalPages ? 'pointer-events-none opacity-50' : ''; ?>">最後</a>
                </div>
            </div>
    </div>

    <!-- 作成/編集モーダル -->
    <div id="newsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="modalTitle" class="text-lg font-medium text-gray-900">新規ニュース作成</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="newsForm">
                        <input type="hidden" id="newsId" name="id">
                        
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">タイトル</label>
                            <input type="text" id="title" name="title" required 
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        
                        <div class="mb-4">
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">概要</label>
                            <textarea id="excerpt" name="excerpt" rows="2" 
                                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">本文</label>
                            <textarea id="content" name="content" rows="6" required 
                                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                        </div>

                        <!-- サムネイル画像 -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">サムネイル画像</label>
                            <div class="flex flex-col md:flex-row gap-4 items-start">
                                <div class="w-full md:w-64 h-40 bg-gray-100 border border-gray-200 rounded flex items-center justify-center overflow-hidden">
                                    <img id="imagePreview" src="" alt="プレビュー" class="max-w-full max-h-full object-contain hidden">
                                    <span id="imageFallback" class="text-gray-400 text-sm">プレビューなし</span>
                                </div>
                                <div class="flex-1 w-full">
                                    <label for="featured_image" class="block text-xs text-gray-500 mb-1">画像URL</label>
                                    <input type="text" id="featured_image" name="featured_image" placeholder="https://... または /path/to.jpg" 
                                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <div class="flex gap-2 mt-2">
                                        <button type="button" id="btnPreview" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50">プレビュー更新</button>
                                        <button type="button" id="btnUpload" class="px-3 py-2 bg-primary text-white rounded hover:bg-primary-light">画像をアップロード</button>
                                        <button type="button" id="btnClearImage" class="px-3 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">クリア</button>
                                    </div>
                                    <input type="file" id="uploadNewsImageInput" accept="image/*" class="hidden">
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">カテゴリ</label>
                                <select id="category" name="category" required 
                                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="お知らせ">お知らせ</option>
                                    <option value="イベント">イベント</option>
                                    <option value="施工事例">施工事例</option>
                                    <option value="コラム">コラム</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="published_date" class="block text-sm font-medium text-gray-700 mb-2">公開日</label>
                                <input type="date" id="published_date" name="published_date" required 
                                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                            <select id="status" name="status" 
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="draft">下書き</option>
                                <option value="published">公開</option>
                                <option value="archived">アーカイブ</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeModal()" 
                                    class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                キャンセル
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-light">
                                保存
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // カテゴリフィルター
        function filterNews() {
            const category = document.getElementById('categoryFilter').value;
            const url = new URL(window.location);
            if (category === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', category);
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // モーダル関連
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = '新規ニュース作成';
            document.getElementById('newsForm').reset();
            document.getElementById('newsId').value = '';
            document.getElementById('published_date').value = new Date().toISOString().split('T')[0];
            updateImagePreview('');
            document.getElementById('newsModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('newsModal').classList.add('hidden');
        }

        // ニュース編集
        async function editNews(id) {
            try {
                const response = await fetch(`../api/news-crud.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const news = result.data;
                    document.getElementById('modalTitle').textContent = (news.title ? news.title + ' の編集' : 'ニュース編集');
                    document.getElementById('newsId').value = news.id;
                    document.getElementById('title').value = news.title;
                    document.getElementById('excerpt').value = news.excerpt || '';
                    document.getElementById('content').value = news.content;
                    document.getElementById('category').value = news.category;
                    document.getElementById('published_date').value = news.published_date;
                    document.getElementById('status').value = news.status;
                    document.getElementById('featured_image').value = news.featured_image || '';
                    updateImagePreview(news.featured_image || '');
                    document.getElementById('newsModal').classList.remove('hidden');
                } else {
                    alert('データの取得に失敗しました: ' + result.error);
                }
            } catch (error) {
                alert('エラーが発生しました: ' + error.message);
            }
        }

        // ニュース削除
        async function deleteNews(id) {
            if (confirm('このニュースを削除してもよろしいですか？')) {
                try {
                    const response = await fetch(`../api/news-crud.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('ニュースを削除しました');
                        location.reload();
                    } else {
                        alert('削除に失敗しました: ' + result.error);
                    }
                } catch (error) {
                    alert('エラーが発生しました: ' + error.message);
                }
            }
        }

        // フォーム送信
        document.getElementById('newsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (value) data[key] = value;
            }
            
            const isEdit = !!data.id;
            const method = isEdit ? 'PUT' : 'POST';
            
            try {
                const response = await fetch('../api/news-crud.php', {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(isEdit ? 'ニュースを更新しました' : 'ニュースを作成しました');
                    closeModal();
                    location.reload();
                } else {
                    alert('保存に失敗しました: ' + result.error);
                }
            } catch (error) {
                alert('エラーが発生しました: ' + error.message);
            }
        });

        // モーダル外クリックで閉じる
        document.getElementById('newsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        // 画像プレビュー更新（相対/絶対URLを許容）
        function resolveAdminImageUrl(url) {
            if (!url) return '';
            // 絶対URL
            if (/^https?:\/\//i.test(url)) return url;
            // ルート相対
            if (url.startsWith('/')) return url;
            // 管理画面から見た相対補完（一般的資産パス）
            if (url.startsWith('assets/')) return '../../' + url;
            // 画像ファイル名のみが来た場合はニュースバケットの想定はできないためそのまま
            return url;
        }

        function updateImagePreview(url) {
            const img = document.getElementById('imagePreview');
            const fb = document.getElementById('imageFallback');
            if (url) {
                img.src = resolveAdminImageUrl(url);
                img.classList.remove('hidden');
                fb.classList.add('hidden');
            } else {
                img.src = '';
                img.classList.add('hidden');
                fb.classList.remove('hidden');
            }
        }

        // 入力URLでプレビュー
        document.getElementById('btnPreview').addEventListener('click', function() {
            const url = document.getElementById('featured_image').value.trim();
            updateImagePreview(url);
        });

        // クリア
        document.getElementById('btnClearImage').addEventListener('click', function() {
            document.getElementById('featured_image').value = '';
            updateImagePreview('');
        });

        // アップロード
        document.getElementById('btnUpload').addEventListener('click', function() {
            document.getElementById('uploadNewsImageInput').click();
        });

        document.getElementById('uploadNewsImageInput').addEventListener('change', async function() {
            const file = this.files && this.files[0];
            if (!file) return;
            try {
                const fd = new FormData();
                fd.append('file', file);
                const res = await fetch('../api/news-upload.php', { method: 'POST', body: fd });
                const json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.error || 'アップロードに失敗しました');
                document.getElementById('featured_image').value = json.url;
                updateImagePreview(json.url);
            } catch (err) {
                alert('アップロードに失敗しました: ' + err.message);
            } finally {
                this.value = '';
            }
        });
    </script>
</body>
</html>

