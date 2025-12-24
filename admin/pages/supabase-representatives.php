<?php
require_once '../includes/auth.php';
require_once '../../lib/SupabaseClient.php';

checkAuth();

$pageTitle = '代表管理（Supabase）';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $representatives = SupabaseClient::select('representatives', [], [
        'order' => 'sort_order.asc,created_at.asc',
        'limit' => $limit,
        'offset' => $offset
    ]);
    if ($representatives === false) throw new Exception('データの取得に失敗しました');
    $totalResult = SupabaseClient::select('representatives', [], [ 'select' => 'id' ]);
    $total = $totalResult ? count($totalResult) : 0;
} catch (Exception $e) {
    $error = $e->getMessage();
    $representatives = [];
    $total = 0;
}

function resolveImagePathForAdmin(?string $path): string {
    if (!$path || trim($path) === '') return '../../assets/img/ogp.jpg';
    if (preg_match('/^(https?:|data:|\/storage\/)/', $path)) return $path;
    if (strpos($path, 'assets/') === 0) return '../../' . $path;
    return '../../assets/img/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - 片山建設工業 管理画面</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#233A5C',secondary:'#A68B5B'}}}}</script>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="../dashboard.php" class="text-gray-600 hover:text-primary mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <h1 class="text-xl font-semibold text-primary">代表管理（Supabase）</h1>
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
            <p class="text-gray-600 mt-2">Supabaseデータベースの代表者情報を管理します</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>エラー:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="bg-gray-50 border border-gray-200 text-gray-700 px-4 py-3 rounded mb-6">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div>総件数: <span class="font-semibold"><?php echo $total; ?></span></div>
                <div>現在のページ: <span class="font-semibold"><?php echo $page; ?></span></div>
                <div>総ページ数: <span class="font-semibold"><?php echo max(1, (int)ceil(($total ?: 0) / $limit)); ?></span></div>
                <div>表示件数/ページ: <span class="font-semibold"><?php echo $limit; ?></span></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">写真</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">氏名</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">役職</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">並び順</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($representatives)): ?>
                        <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500"><?php echo isset($error) ? 'データの取得に失敗しました' : 'データがありません'; ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($representatives as $item): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <img src="<?php echo htmlspecialchars(resolveImagePathForAdmin($item['photo_url'] ?? null)); ?>" class="w-16 h-16 object-cover rounded" onerror="this.style.display='none'">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($item['position'] ?? ''); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo (int)($item['sort_order'] ?? 0); ?></td>
                                <td class="px-6 py-4">
                                    <?php $active = ($item['status'] ?? 'inactive') === 'active'; $cls = $active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; $txt = $active ? '公開' : '非公開'; ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $cls; ?>"><?php echo $txt; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <button onclick='openEditModal(<?php echo json_encode($item, JSON_UNESCAPED_UNICODE); ?>)' class="text-primary hover:text-primary_light">編集</button>
                                    <button onclick="deleteRepresentative('<?php echo htmlspecialchars($item['id']); ?>')" class="text-red-600 hover:text-red-800">削除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php 
            $totalPages = max(1, (int)ceil(($total ?: 0) / $limit));
            $prevPage = max(1, $page - 1);
            $nextPage = min($totalPages, $page + 1);
        ?>
        <div class="mt-6 flex justify-center">
            <div class="flex items-center gap-2 text-sm">
                <a href="?page=1" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>">最初</a>
                <a href="?page=<?php echo $prevPage; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == 1 ? 'pointer-events-none opacity-50' : ''; ?>">前へ</a>
                <span class="px-3 py-2 bg-primary text-white rounded"><?php echo $page; ?> / <?php echo $totalPages; ?></span>
                <a href="?page=<?php echo $nextPage; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == $totalPages ? 'pointer-events-none opacity-50' : ''; ?>">次へ</a>
                <a href="?page=<?php echo $totalPages; ?>" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50 <?php echo $page == $totalPages ? 'pointer-events-none opacity-50' : ''; ?>">最後</a>
            </div>
        </div>
    </div>

    <div id="repModal" class="fixed inset-0 bg-black/40 hidden">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl">
                <div class="flex justify-between items-center px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">代表情報</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">×</button>
                </div>
                <form id="repForm" class="p-6 space-y-4">
                    <input type="hidden" id="id" name="id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="name">氏名</label>
                            <input id="name" name="name" type="text" required class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="position">役職</label>
                            <input id="position" name="position" type="text" class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="greeting_title">ごあいさつタイトル</label>
                            <input id="greeting_title" name="greeting_title" type="text" class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="sort_order">並び順</label>
                            <input id="sort_order" name="sort_order" type="number" value="0" class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="greeting_content">ごあいさつ本文</label>
                        <textarea id="greeting_content" name="greeting_content" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="photo_url">写真URL</label>
                            <div class="flex items-center gap-2">
                                <input id="photo_url" name="photo_url" type="text" class="flex-1 border border-gray-300 rounded px-3 py-2" placeholder="assets/img/representative_official.jpg">
                                <label class="px-3 py-2 border rounded text-sm hover:bg-gray-50 cursor-pointer">
                                    画像アップロード
                                    <input id="uploadPhotoInput" type="file" accept="image/*" class="hidden">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">URLは外部/相対/ファイル名のみ対応。プレビュー表示可能。</p>
                            <img id="photoPreview" src="" class="mt-2 w-full h-40 object-cover rounded hidden">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="signature_url">署名画像URL</label>
                            <input id="signature_url" name="signature_url" type="text" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="assets/img/signature.png">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="career">経歴（行区切り）</label>
                            <textarea id="career" name="career" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="education">学歴（行区切り）</label>
                            <textarea id="education" name="education" rows="4" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="qualifications">保有資格（行区切り）</label>
                        <textarea id="qualifications" name="qualifications" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="status">ステータス</label>
                            <select id="status" name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                                <option value="active">公開</option>
                                <option value="inactive">非公開</option>
                            </select>
                        </div>
                        <div class="flex items-end justify-end gap-2">
                            <button type="button" id="btnPreviewPhoto" class="px-3 py-2 border border-gray-300 rounded hover:bg-gray-50">プレビュー</button>
                            <button type="button" id="btnClearPhoto" class="px-3 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">クリア</button>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">キャンセル</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-light">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openCreateModal(){
        document.getElementById('id').value='';
        document.getElementById('name').value='';
        document.getElementById('position').value='';
        document.getElementById('greeting_title').value='';
        document.getElementById('greeting_content').value='';
        document.getElementById('photo_url').value='';
        document.getElementById('signature_url').value='';
        document.getElementById('sort_order').value='0';
        document.getElementById('career').value='';
        document.getElementById('education').value='';
        document.getElementById('qualifications').value='';
        document.getElementById('status').value='active';
        document.getElementById('photoPreview').classList.add('hidden');
        document.getElementById('repModal').classList.remove('hidden');
    }
    function openEditModal(item){
        document.getElementById('id').value=item.id||'';
        document.getElementById('name').value=item.name||'';
        document.getElementById('position').value=item.position||'';
        document.getElementById('greeting_title').value=item.greeting_title||'';
        document.getElementById('greeting_content').value=item.greeting_content||'';
        document.getElementById('photo_url').value=item.photo_url||'';
        document.getElementById('signature_url').value=item.signature_url||'';
        document.getElementById('sort_order').value=item.sort_order||0;
        var bio=item.biography||{};
        document.getElementById('career').value=(bio.career||[]).join('\n');
        document.getElementById('education').value=(bio.education||[]).join('\n');
        document.getElementById('qualifications').value=(item.qualifications||[]).join('\n');
        document.getElementById('status').value=item.status||'active';
        updatePhotoPreview();
        document.getElementById('repModal').classList.remove('hidden');
    }
    function closeModal(){ document.getElementById('repModal').classList.add('hidden'); }
    function updatePhotoPreview(){
        var raw=document.getElementById('photo_url').value.trim();
        var img=document.getElementById('photoPreview');
        if(!raw){ img.classList.add('hidden'); img.src=''; return; }
        var url=raw;
        if(!/^https?:|^data:|^\/storage\//.test(raw)){
            if(raw.startsWith('assets/')) url='../../'+raw; else url='../../assets/img/'+raw;
        }
        img.src=url; img.classList.remove('hidden');
    }
    document.getElementById('btnPreviewPhoto').addEventListener('click',updatePhotoPreview);
    document.getElementById('btnClearPhoto').addEventListener('click',function(){ document.getElementById('photo_url').value=''; updatePhotoPreview(); });
    document.getElementById('uploadPhotoInput').addEventListener('change',async function(){
        var file=this.files&&this.files[0]; if(!file) return;
        try{
            var fd=new FormData(); fd.append('file',file);
            var res=await fetch('../api/representatives-upload.php',{method:'POST',body:fd});
            var json=await res.json();
            if(!res.ok||!json.success) throw new Error(json.error||'アップロードに失敗しました');
            document.getElementById('photo_url').value=json.url;
            updatePhotoPreview(); this.value='';
        }catch(e){ alert('アップロードに失敗しました: '+e.message); }
    });
    document.getElementById('repForm').addEventListener('submit',async function(e){
        e.preventDefault();
        var form=new FormData(this); var data={};
        for(var [k,v] of form.entries()){ if(v!==null&&String(v).length>0) data[k]=v; }
        data.career = document.getElementById('career').value.split('\n').filter(Boolean);
        data.education = document.getElementById('education').value.split('\n').filter(Boolean);
        data.qualifications = document.getElementById('qualifications').value.split('\n').filter(Boolean);
        var isEdit=!!data.id; var method=isEdit?'PUT':'POST';
        try{
            var res=await fetch('../api/representatives-crud.php',{method:method,headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
            var result=await res.json();
            if(result.success){ alert(isEdit?'更新しました':'作成しました'); closeModal(); location.reload(); }
            else{ alert('保存に失敗しました: '+result.error); }
        }catch(e){ alert('エラーが発生しました: '+e.message); }
    });
    async function deleteRepresentative(id){
        if(!confirm('この代表者情報を削除してもよろしいですか？')) return;
        try{
            var res=await fetch(`../api/representatives-crud.php?id=${id}`,{method:'DELETE'});
            var result=await res.json();
            if(result.success){ alert('削除しました'); location.reload(); }
            else{ alert('削除に失敗しました: '+result.error); }
        }catch(e){ alert('エラーが発生しました: '+e.message); }
    }
    </script>
</body>
</html>