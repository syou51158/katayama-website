<?php
require_once '../includes/auth.php';
require_once '../../lib/SupabaseClient.php';

// 認証チェック
checkAuth();

$pageTitle = '施工実績管理（Supabase）';

// ページネーション
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// カテゴリフィルター
$category = isset($_GET['category']) ? $_GET['category'] : null;

try {
    $filters = [];
    if ($category && $category !== 'all') {
        $filters['category'] = $category;
    }
    
    $works = SupabaseClient::select('works', $filters, [
        'order' => 'created_at.desc',
        'limit' => $limit,
        'offset' => $offset
    ]);

    if ($works === false) {
        throw new Exception('データの取得に失敗しました');
    }

    // 総数取得
    $totalResult = SupabaseClient::select('works', $filters, [ 'select' => 'id' ]);
    $total = $totalResult ? count($totalResult) : 0;
} catch (Exception $e) {
    $error = $e->getMessage();
    $works = [];
    $total = 0;
}

// 画像パス解決（管理画面用）
function resolveImagePathForAdmin(?string $path): string {
    $baseAssets = '../../assets/img/';
    if (!$path || trim($path) === '') {
        return $baseAssets . 'works_01.jpg';
    }
    if (preg_match('/^(https?:|data:)/', $path)) {
        return $path;
    }
    if (strpos($path, '../') === 0 || strpos($path, '/') === 0) {
        return $path;
    }
    if (strpos($path, 'assets/') === 0) {
        return '../../' . $path;
    }
    return $baseAssets . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - 片山建設工業 管理画面</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#233A5C', secondary: '#A68B5B' } } } }
    </script>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="../dashboard.php" class="text-gray-600 hover:text-primary mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <h1 class="text-xl font-semibold text-primary">施工実績管理（Supabase）</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openCreateModal()" class="bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-light transition">新規作成</button>
                    <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">ログアウト</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="text-gray-600 mt-2">Supabaseデータベースの施工実績を管理します</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>エラー:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- サマリー / ビュー切り替え -->
        <div class="bg-gray-50 border border-gray-200 text-gray-700 px-4 py-3 rounded mb-6">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div>総件数: <span class="font-semibold"><?php echo $total; ?></span></div>
                <div>現在のページ: <span class="font-semibold"><?php echo $page; ?></span></div>
                <div>総ページ数: <span class="font-semibold"><?php echo max(1, (int)ceil(($total ?: 0) / $limit)); ?></span></div>
                <div>表示件数/ページ: <span class="font-semibold"><?php echo $limit; ?></span></div>
                <div class="ml-auto flex items-center gap-2">
                    <span class="text-gray-500">表示形式:</span>
                    <button id="btnTable" class="px-3 py-1 border rounded text-sm bg-white hover:bg-gray-100">表</button>
                    <button id="btnCards" class="px-3 py-1 border rounded text-sm bg-white hover:bg-gray-100">カード</button>
                </div>
            </div>
        </div>

        <!-- 一覧（表ビュー） -->
        <div id="tableView" class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">カテゴリ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">完成日</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($works)): ?>
                    <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500"><?php echo isset($error) ? 'データの取得に失敗しました' : 'データがありません'; ?></td></tr>
                    <?php else: ?>
                    <?php foreach ($works as $item): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['title']); ?></div>
                            <?php if (!empty($item['description'])): ?><div class="text-sm text-gray-500"><?php echo htmlspecialchars(mb_substr($item['description'],0,50)); ?>...</div><?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"><?php echo htmlspecialchars($item['category']); ?></span></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo !empty($item['completion_date']) ? date('Y/m', strtotime($item['completion_date'])) : '-'; ?></td>
                        <td class="px-6 py-4">
                            <?php $sClass = $item['status']==='published'?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'; $sText = $item['status']==='published'?'公開':'下書き'; ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $sClass; ?>"><?php echo $sText; ?></span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button onclick="editWork('<?php echo $item['id']; ?>')" class="text-indigo-600 hover:text-indigo-900 mr-3">編集</button>
                            <button onclick="deleteWork('<?php echo $item['id']; ?>')" class="text-red-600 hover:text-red-900">削除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- 一覧（カードビュー） -->
        <div id="cardView" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($works)): ?>
                    <div class="col-span-full text-center text-gray-500">データがありません</div>
                <?php else: ?>
                <?php foreach ($works as $item): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="relative overflow-hidden">
                        <img src="<?php echo htmlspecialchars(resolveImagePathForAdmin($item['featured_image'] ?: 'works_01.jpg')); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='../../assets/img/works_01.jpg'">
                        <div class="absolute top-2 left-2 px-2 py-1 bg-secondary text-white text-xs rounded"><?php echo htmlspecialchars($item['category']); ?></div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <?php $sClass = $item['status']==='published'?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'; $sText = $item['status']==='published'?'公開':'下書き'; ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $sClass; ?>"><?php echo $sText; ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars(mb_substr($item['description'] ?? '', 0, 80)); ?></p>
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span><?php echo !empty($item['completion_date']) ? date('Y/m', strtotime($item['completion_date'])) : '-'; ?></span>
                            <?php if (!empty($item['location'])): ?><span class="bg-gray-100 px-2 py-0.5 rounded"><?php echo htmlspecialchars($item['location']); ?></span><?php endif; ?>
                        </div>
                        <div class="flex justify-end gap-3 mt-4">
                            <button onclick="editWork('<?php echo $item['id']; ?>')" class="text-indigo-600 hover:text-indigo-900 text-sm">編集</button>
                            <button onclick="deleteWork('<?php echo $item['id']; ?>')" class="text-red-600 hover:text-red-900 text-sm">削除</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

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
    <div id="workModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="modalTitle" class="text-lg font-medium text-gray-900">施工実績の作成</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form id="workForm">
                        <input type="hidden" id="workId" name="id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="title">タイトル</label>
                                <input id="title" name="title" type="text" required class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="category">カテゴリ</label>
                                <select id="category" name="category" class="w-full border border-gray-300 rounded px-3 py-2">
                                    <option value="Residential">住宅</option>
                                    <option value="Commercial">商業施設</option>
                                    <option value="Public">公共工事</option>
                                    <option value="Renovation">リノベーション</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="completion_date">完成日</label>
                                <input id="completion_date" name="completion_date" type="date" class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="location">所在地</label>
                                <input id="location" name="location" type="text" class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="featured_image">画像URL（サムネイル）</label>
                                <input id="featured_image" name="featured_image" type="text" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="assets/img/works_01.jpg" oninput="updatePreview()">
                                <div class="mt-3">
                                    <img id="imagePreview" src="" alt="プレビュー" class="w-full max-h-60 object-contain bg-gray-50 border rounded hidden">
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" onclick="clearFeatured()" class="px-3 py-2 border rounded text-sm hover:bg-gray-50">サムネイルを削除</button>
                                    <label class="px-3 py-2 border rounded text-sm hover:bg-gray-50 cursor-pointer">
                                        サムネイルをアップロード
                                        <input id="featuredUpload" type="file" accept="image/*" class="hidden" onchange="uploadFeatured(this)">
                                    </label>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">ギャラリー画像</label>
                                <div id="galleryList" class="grid grid-cols-2 md:grid-cols-3 gap-3"></div>
                                <div class="mt-3 flex flex-wrap gap-2 items-center">
                                    <input id="galleryInput" type="text" class="flex-1 min-w-[240px] border border-gray-300 rounded px-3 py-2" placeholder="画像URL または assets/img/works_02.jpg または works_02.jpg">
                                    <button type="button" onclick="addGalleryImage()" class="px-3 py-2 bg-primary text-white rounded hover:bg-primary-light">URL追加</button>
                                    <label class="px-3 py-2 border rounded text-sm hover:bg-gray-50 cursor-pointer">
                                        画像をアップロード
                                        <input id="galleryUpload" type="file" accept="image/*" multiple class="hidden" onchange="uploadGallery(this)">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">サムネ以外の補助画像を追加できます。URLは外部/相対/ファイル名のみ対応。カードでプレビュー、クリックで削除できます。</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="description">説明</label>
                                <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="status">ステータス</label>
                                <select id="status" name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                                    <option value="draft">下書き</option>
                                    <option value="published">公開</option>
                                    <option value="archived">アーカイブ</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">キャンセル</button>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-light">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 表/カード切替（ローカルに記憶）
        function applyView(view) {
            const table = document.getElementById('tableView');
            const cards = document.getElementById('cardView');
            const btnTable = document.getElementById('btnTable');
            const btnCards = document.getElementById('btnCards');
            if (!table || !cards) return;
            if (view === 'cards') {
                cards.classList.remove('hidden');
                table.classList.add('hidden');
                btnCards?.classList.add('bg-primary','text-white');
                btnTable?.classList.remove('bg-primary','text-white');
            } else {
                table.classList.remove('hidden');
                cards.classList.add('hidden');
                btnTable?.classList.add('bg-primary','text-white');
                btnCards?.classList.remove('bg-primary','text-white');
            }
        }
        function loadInitialView() {
            const saved = localStorage.getItem('adminWorksView') || 'table';
            applyView(saved);
        }
        document.getElementById('btnTable')?.addEventListener('click', () => {
            localStorage.setItem('adminWorksView','table');
            applyView('table');
        });
        document.getElementById('btnCards')?.addEventListener('click', () => {
            localStorage.setItem('adminWorksView','cards');
            applyView('cards');
        });
        // 初期表示で復元
        loadInitialView();

        // ギャラリー状態
        let gallery = [];

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = '施工実績の作成';
            document.getElementById('workForm').reset();
            document.getElementById('workId').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('imagePreview').src = '';
            gallery = [];
            renderGallery();
            document.getElementById('workModal').classList.remove('hidden');
        }
        function closeModal() { document.getElementById('workModal').classList.add('hidden'); }

        async function editWork(id) {
            try {
                const res = await fetch(`../api/works-crud.php?id=${id}`);
                const result = await res.json();
                if (result.success) {
                    const w = result.data;
                    document.getElementById('modalTitle').textContent = (w.title ? w.title : '施工実績') + ' の編集';
                    document.getElementById('workId').value = w.id;
                    document.getElementById('title').value = w.title || '';
                    document.getElementById('category').value = w.category || 'Residential';
                    document.getElementById('completion_date').value = w.completion_date || '';
                    document.getElementById('location').value = w.location || '';
                    document.getElementById('featured_image').value = w.featured_image || '';
                    // プレビューに解決済みURLを反映
                    const raw = (w.featured_image || '').trim();
                    const img = document.getElementById('imagePreview');
                    if (raw) {
                        let url = raw;
                        if (!/^https?:|^data:|^\//.test(raw)) {
                            if (raw.startsWith('assets/')) {
                                url = '../../' + raw;
                            } else {
                                url = '../../assets/img/' + raw;
                            }
                        }
                        img.src = url;
                        img.classList.remove('hidden');
                    } else {
                        img.classList.add('hidden');
                        img.src = '';
                    }
                    document.getElementById('description').value = w.description || '';
                    // ギャラリー
                    gallery = Array.isArray(w.gallery_images) ? w.gallery_images : [];
                    renderGallery();
                    document.getElementById('status').value = w.status || 'draft';
                    document.getElementById('workModal').classList.remove('hidden');
                } else {
                    alert('取得に失敗しました: ' + result.error);
                }
            } catch (e) {
                alert('エラーが発生しました: ' + e.message);
            }
        }

        async function deleteWork(id) {
            if (!confirm('この施工実績を削除してもよろしいですか？')) return;
            try {
                const res = await fetch(`../api/works-crud.php?id=${id}`, { method: 'DELETE' });
                const result = await res.json();
                if (result.success) {
                    alert('削除しました');
                    location.reload();
                } else {
                    alert('削除に失敗しました: ' + result.error);
                }
            } catch (e) { alert('エラーが発生しました: ' + e.message); }
        }

        document.getElementById('workForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            for (let [k, v] of formData.entries()) { if (v) data[k] = v; }
            data.gallery_images = gallery;
            const isEdit = !!data.id;
            const method = isEdit ? 'PUT' : 'POST';
            try {
                const res = await fetch('../api/works-crud.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
                const result = await res.json();
                if (result.success) {
                    alert(isEdit ? '更新しました' : '作成しました');
                    closeModal();
                    location.reload();
                } else {
                    alert('保存に失敗しました: ' + result.error);
                }
            } catch (e) { alert('エラーが発生しました: ' + e.message); }
        });

        // 画像プレビュー
        function updatePreview() {
            const raw = document.getElementById('featured_image').value.trim();
            const img = document.getElementById('imagePreview');
            if (raw) {
                let url = raw;
                if (!/^https?:|^data:|^\//.test(raw)) {
                    if (raw.startsWith('assets/')) {
                        url = '../../' + raw;
                    } else {
                        url = '../../assets/img/' + raw;
                    }
                }
                img.src = url;
                img.classList.remove('hidden');
            } else {
                img.classList.add('hidden');
                img.src = '';
            }
        }

        function clearFeatured() {
            document.getElementById('featured_image').value = '';
            updatePreview();
        }

        async function uploadFeatured(input) {
            try {
                if (!input.files || !input.files[0]) return;
                const form = new FormData();
                form.append('file', input.files[0]);
                const res = await fetch('../api/works-upload.php', { method: 'POST', body: form });
                const result = await res.json();
                if (result.success) {
                    document.getElementById('featured_image').value = result.url;
                    updatePreview();
                    input.value = '';
                } else {
                    alert('アップロードに失敗しました: ' + result.error);
                }
            } catch (e) {
                alert('アップロード中にエラーが発生しました: ' + e.message);
            }
        }

        function normalizeUrl(raw) {
            if (!raw) return '';
            if (/^https?:|^data:|^\//.test(raw)) return raw;
            if (raw.startsWith('assets/')) return '../../' + raw;
            return '../../assets/img/' + raw;
        }

        function addGalleryImage() {
            const input = document.getElementById('galleryInput');
            const raw = input.value.trim();
            if (!raw) return;
            gallery.push(raw);
            input.value = '';
            renderGallery();
        }

        function removeGalleryImage(idx) {
            gallery.splice(idx, 1);
            renderGallery();
        }

        function renderGallery() {
            const list = document.getElementById('galleryList');
            list.innerHTML = '';
            gallery.forEach((raw, idx) => {
                const url = normalizeUrl(raw);
                const el = document.createElement('div');
                el.className = 'relative group border rounded overflow-hidden bg-white';
                el.innerHTML = `
                    <img src="${url}" class="w-full h-28 object-cover" onerror="this.onerror=null;this.src='../../assets/img/works_01.jpg'" />
                    <button type="button" class="absolute top-1 right-1 bg-white/90 border rounded px-2 py-1 text-xs opacity-0 group-hover:opacity-100 transition" onclick="removeGalleryImage(${idx})">削除</button>
                    <div class="px-2 py-1 text-[11px] text-gray-500 truncate">${raw}</div>
                `;
                list.appendChild(el);
            });
        }

        async function uploadGallery(input) {
            try {
                if (!input.files || input.files.length === 0) return;
                for (const file of input.files) {
                    const form = new FormData();
                    form.append('file', file);
                    const res = await fetch('../api/works-upload.php', { method: 'POST', body: form });
                    const result = await res.json();
                    if (result.success) {
                        gallery.push(result.url);
                    } else {
                        alert('アップロードに失敗しました: ' + result.error);
                    }
                }
                input.value = '';
                renderGallery();
            } catch (e) {
                alert('アップロード中にエラーが発生しました: ' + e.message);
            }
        }
    </script>
</body>
</html>


