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
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'image' => trim($_POST['image'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'completion_date' => $_POST['completion_date'] ?? '',
            'construction_period' => trim($_POST['construction_period'] ?? ''),
            'floor_area' => trim($_POST['floor_area'] ?? ''),
            'status' => $_POST['status'] ?? 'draft'
        ];
        
        // バリデーション
        if (empty($data['title']) || empty($data['description'])) {
            $error = 'タイトルと説明は必須です。';
        } else {
            try {
                if ($action === 'create') {
                    $newId = $db->insert('works', $data);
                    $message = '施工実績を作成しました。';
                    $action = 'list';
                } else if ($action === 'edit' && $id) {
                    $db->update('works', $id, $data);
                    $message = '施工実績を更新しました。';
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
        $db->delete('works', $id);
        $message = '施工実績を削除しました。';
        $action = 'list';
    } catch (Exception $e) {
        $error = '削除に失敗しました: ' . $e->getMessage();
    }
}

// データ取得
$worksList = $db->read('works');
$currentWork = null;

if ($action === 'edit' && $id) {
    $currentWork = $db->findById('works', $id);
    if (!$currentWork) {
        $error = '施工実績が見つかりません。';
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>施工実績管理 - 片山建設工業 CMS</title>
    
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
                    <h1 class="text-xl font-semibold text-primary">施工実績管理</h1>
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
            <!-- 施工実績一覧 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">施工実績一覧</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    <?php foreach ($worksList as $work): ?>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition duration-200">
                            <?php if ($work['image']): ?>
                                <img src="../../<?php echo htmlspecialchars($work['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($work['title']); ?>"
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500">画像なし</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                           <?php 
                                           $categoryColors = [
                                               'Residential' => 'bg-blue-100 text-blue-800',
                                               'Commercial' => 'bg-green-100 text-green-800',
                                               'Public' => 'bg-purple-100 text-purple-800',
                                               'Renovation' => 'bg-yellow-100 text-yellow-800'
                                           ];
                                           echo $categoryColors[$work['category']] ?? 'bg-gray-100 text-gray-800';
                                           ?>">
                                        <?php echo htmlspecialchars($work['category']); ?>
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                           <?php echo $work['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo $work['status'] === 'published' ? '公開中' : '下書き'; ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <?php echo htmlspecialchars($work['title']); ?>
                                </h3>
                                
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars($work['description']); ?>
                                </p>
                                
                                <div class="text-xs text-gray-500 space-y-1">
                                    <?php if ($work['location']): ?>
                                        <div>📍 <?php echo htmlspecialchars($work['location']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($work['completion_date']): ?>
                                        <div>📅 <?php echo date('Y年m月', strtotime($work['completion_date'])); ?>完成</div>
                                    <?php endif; ?>
                                    <?php if ($work['floor_area']): ?>
                                        <div>📐 <?php echo htmlspecialchars($work['floor_area']); ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between mt-4 pt-3 border-t border-gray-100">
                                    <a href="?action=edit&id=<?php echo $work['id']; ?>" 
                                       class="text-primary hover:text-blue-900 text-sm font-medium">編集</a>
                                    <a href="?action=delete&id=<?php echo $work['id']; ?>" 
                                       class="text-red-600 hover:text-red-900 text-sm font-medium"
                                       onclick="return confirm('本当に削除しますか？')">削除</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php elseif ($action === 'create' || $action === 'edit'): ?>
            <!-- 施工実績作成・編集フォーム -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <?php echo $action === 'create' ? '施工実績作成' : '施工実績編集'; ?>
                    </h2>
                </div>
                <form method="POST" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">タイトル *</label>
                            <input type="text" id="title" name="title" required
                                   value="<?php echo htmlspecialchars($currentWork['title'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">カテゴリ</label>
                            <select id="category" name="category"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="Residential" <?php echo ($currentWork['category'] ?? '') === 'Residential' ? 'selected' : ''; ?>>住宅</option>
                                <option value="Commercial" <?php echo ($currentWork['category'] ?? '') === 'Commercial' ? 'selected' : ''; ?>>商業施設</option>
                                <option value="Public" <?php echo ($currentWork['category'] ?? '') === 'Public' ? 'selected' : ''; ?>>公共工事</option>
                                <option value="Renovation" <?php echo ($currentWork['category'] ?? '') === 'Renovation' ? 'selected' : ''; ?>>リノベーション</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">説明 *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="施工実績の説明を入力してください"><?php echo htmlspecialchars($currentWork['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">画像パス</label>
                        <input type="text" id="image" name="image"
                               value="<?php echo htmlspecialchars($currentWork['image'] ?? ''); ?>"
                               placeholder="assets/img/works_01.jpg"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">例: assets/img/works_01.jpg</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">所在地</label>
                            <input type="text" id="location" name="location"
                                   value="<?php echo htmlspecialchars($currentWork['location'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="completion_date" class="block text-sm font-medium text-gray-700 mb-2">完成日</label>
                            <input type="date" id="completion_date" name="completion_date"
                                   value="<?php echo $currentWork['completion_date'] ?? ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="construction_period" class="block text-sm font-medium text-gray-700 mb-2">工期</label>
                            <input type="text" id="construction_period" name="construction_period"
                                   value="<?php echo htmlspecialchars($currentWork['construction_period'] ?? ''); ?>"
                                   placeholder="6ヶ月"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="floor_area" class="block text-sm font-medium text-gray-700 mb-2">延床面積</label>
                            <input type="text" id="floor_area" name="floor_area"
                                   value="<?php echo htmlspecialchars($currentWork['floor_area'] ?? ''); ?>"
                                   placeholder="120㎡"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">ステータス</label>
                            <select id="status" name="status"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="draft" <?php echo ($currentWork['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>下書き</option>
                                <option value="published" <?php echo ($currentWork['status'] ?? '') === 'published' ? 'selected' : ''; ?>>公開</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
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
