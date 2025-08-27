<?php
require_once '../../cms/includes/auth.php';
require_once '../../cms/includes/database.php';

$auth = new Auth();
$auth->requireAuth();

$db = new JsonDatabase();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// フォーム処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'edit') {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'draft'
        ];
        
        // バリデーション
        if (empty($data['title']) || empty($data['content'])) {
            $error = 'タイトルと内容は必須です。';
        } else {
            try {
                if ($action === 'create') {
                    $newId = $db->insert('news', $data);
                    $message = 'お知らせを作成しました。';
                    $action = 'list';
                } else if ($action === 'edit' && $id) {
                    $db->update('news', $id, $data);
                    $message = 'お知らせを更新しました。';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'エラーが発生しました: ' . $e->getMessage();
            }
        }
    }
}

// 削除処理
if ($action === 'delete' && $id) {
    try {
        $db->delete('news', $id);
        $message = 'お知らせを削除しました。';
        $action = 'list';
    } catch (Exception $e) {
        $error = '削除に失敗しました: ' . $e->getMessage();
    }
}

// データ取得
$newsList = $db->read('news');
$currentNews = null;

if ($action === 'edit' && $id) {
    $currentNews = $db->findById('news', $id);
    if (!$currentNews) {
        $error = 'お知らせが見つかりません。';
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ管理 - 片山建設工業 CMS</title>
    
    <!-- Tailwind (built) -->
    <link rel="stylesheet" href="../../assets/css/build.css">
    
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
                    <a href="../dashboard.php" class="text-gray-600 hover:text-primary mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-semibold text-primary">お知らせ管理</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if ($action === 'list'): ?>
                        <a href="?action=create" 
                           class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-900 transition duration-200">
                            新規作成
                        </a>
                    <?php endif; ?>
                    <a href="../logout.php" 
                       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition duration-200">
                        ログアウト
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- メッセージ表示 -->
        <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <!-- お知らせ一覧 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">お知らせ一覧</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">カテゴリ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日付</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($newsList as $news): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($news['title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                               <?php echo $news['category'] === 'お知らせ' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo htmlspecialchars($news['category']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('Y年m月d日', strtotime($news['date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                               <?php echo $news['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $news['status'] === 'published' ? '公開中' : '下書き'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?action=edit&id=<?php echo $news['id']; ?>" 
                                           class="text-primary hover:text-blue-900 mr-3">編集</a>
                                        <a href="?action=delete&id=<?php echo $news['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('本当に削除しますか？')">削除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($action === 'create' || $action === 'edit'): ?>
            <!-- お知らせ作成・編集フォーム -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <?php echo $action === 'create' ? 'お知らせ作成' : 'お知らせ編集'; ?>
                    </h2>
                </div>
                <form method="POST" class="p-6 space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">タイトル *</label>
                        <input type="text" id="title" name="title" required
                               value="<?php echo htmlspecialchars($currentNews['title'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">カテゴリ</label>
                            <select id="category" name="category"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="お知らせ" <?php echo ($currentNews['category'] ?? '') === 'お知らせ' ? 'selected' : ''; ?>>お知らせ</option>
                                <option value="イベント" <?php echo ($currentNews['category'] ?? '') === 'イベント' ? 'selected' : ''; ?>>イベント</option>
                                <option value="重要" <?php echo ($currentNews['category'] ?? '') === '重要' ? 'selected' : ''; ?>>重要</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">公開日</label>
                            <input type="date" id="date" name="date"
                                   value="<?php echo $currentNews['date'] ?? date('Y-m-d'); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">内容 *</label>
                        <textarea id="content" name="content" rows="8" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="お知らせの内容を入力してください"><?php echo htmlspecialchars($currentNews['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="status" class="block text_sm font-medium text-gray-700 mb-2">ステータス</label>
                        <select id="status" name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="draft" <?php echo ($currentNews['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>下書き</option>
                            <option value="published" <?php echo ($currentNews['status'] ?? '') === 'published' ? 'selected' : ''; ?>>公開</option>
                        </select>
                    </div>
                    
                    <div class="flex justify_between">
                        <a href="?action=list" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition duration-200">
                            キャンセル
                        </a>
                        <button type="submit" 
                                class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-900 transition duration-200">
                            <?php echo $action === 'create' ? '作成' : '更新'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
