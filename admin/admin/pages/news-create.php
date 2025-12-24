<?php
require_once '../includes/auth.php';
require_once '../../lib/SupabaseClient.php';

// 認証チェック
checkAuth();

$pageTitle = '新規お知らせ作成';
$currentPage = 'news-create';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - 片山建設工業 管理画面</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#233A5C',
                        secondary: '#A68B5B'
                    }
                }
            }
        }
    </script>
    <style>
        .step-active { @apply border-primary text-primary; }
        .step-completed { @apply border-green-500 text-green-500; }
        .step-inactive { @apply border-gray-300 text-gray-400; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="supabase-news.php" class="text-gray-500 hover:text-primary mr-4 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-800">お知らせを作る</h1>
                </div>
                <div class="text-sm text-gray-500" id="saveStatus">
                    <!-- 自動保存ステータス -->
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- ステップインジケータ -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div class="flex flex-col items-center w-1/4 step-indicator" data-step="1">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold mb-2 step-circle bg-white">1</div>
                    <span class="text-xs font-medium">基本情報</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                <div class="flex flex-col items-center w-1/4 step-indicator" data-step="2">
                    <div class="w-8 h-8 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center font-bold mb-2 step-circle bg-white">2</div>
                    <span class="text-xs font-medium text-gray-400">画像</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                <div class="flex flex-col items-center w-1/4 step-indicator" data-step="3">
                    <div class="w-8 h-8 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center font-bold mb-2 step-circle bg-white">3</div>
                    <span class="text-xs font-medium text-gray-400">設定</span>
                </div>
                <div class="flex-1 h-0.5 bg-gray-200 mx-2"></div>
                <div class="flex flex-col items-center w-1/4 step-indicator" data-step="4">
                    <div class="w-8 h-8 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center font-bold mb-2 step-circle bg-white">4</div>
                    <span class="text-xs font-medium text-gray-400">確認</span>
                </div>
            </div>
        </div>

        <form id="wizardForm" class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Step 1: 基本情報 -->
            <div class="step-content p-8" data-step="1">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">どんなお知らせですか？</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-2">タイトル <span class="text-red-500">*</span></label>
                        <p class="text-sm text-gray-500 mb-2">一目で内容がわかる短いタイトルをつけましょう。</p>
                        <input type="text" name="title" id="title" required
                            class="w-full text-lg border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-primary focus:ring-0 transition"
                            placeholder="例：夏季休業のお知らせ">
                    </div>

                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-2">本文 <span class="text-red-500">*</span></label>
                        <p class="text-sm text-gray-500 mb-2">詳細な内容を入力してください。</p>
                        <textarea name="content" id="content" rows="10" required
                            class="w-full text-base border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-primary focus:ring-0 transition"
                            placeholder="ここにお知らせの内容を入力してください..."></textarea>
                    </div>

                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-2">短い説明（概要）</label>
                        <p class="text-sm text-gray-500 mb-2">一覧ページでタイトルの下に表示されます。未入力の場合は本文の最初が使われます。</p>
                        <textarea name="excerpt" id="excerpt" rows="2"
                            class="w-full text-base border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-primary focus:ring-0 transition"
                            placeholder="例：8月13日から16日まで休業いたします。"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: 画像 -->
            <div class="step-content p-8 hidden" data-step="2">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">画像を追加しますか？</h2>
                <p class="text-gray-600 mb-8">写真があると、お知らせがより注目されやすくなります。</p>

                <div class="space-y-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition cursor-pointer" id="dropZone">
                        <div id="imagePreviewContainer" class="hidden mb-4 relative group">
                            <img id="imagePreview" src="" alt="プレビュー" class="max-h-64 mx-auto rounded shadow-sm">
                            <button type="button" id="removeImageBtn" class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 shadow transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div id="uploadPrompt">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-1">画像をドラッグ＆ドロップ</p>
                            <p class="text-sm text-gray-500 mb-4">または</p>
                            <button type="button" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition" onclick="document.getElementById('fileInput').click()">
                                ファイルを選択
                            </button>
                        </div>
                        <input type="file" id="fileInput" accept="image/*" class="hidden">
                        <input type="hidden" name="featured_image" id="featured_image">
                    </div>
                    
                    <div id="uploadProgress" class="hidden">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-primary">アップロード中...</span>
                            <span class="text-sm font-medium text-primary" id="progressText">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-primary h-2.5 rounded-full" style="width: 0%" id="progressBar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: 設定 -->
            <div class="step-content p-8 hidden" data-step="3">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">公開設定</h2>

                <div class="space-y-8">
                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-4">カテゴリを選んでください <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="お知らせ" class="peer hidden" checked>
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-primary peer-checked:bg-blue-50 transition hover:bg-gray-50">
                                    <span class="block font-bold text-gray-800">お知らせ</span>
                                    <span class="text-xs text-gray-500">一般的な情報発信</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="施工事例" class="peer hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-primary peer-checked:bg-blue-50 transition hover:bg-gray-50">
                                    <span class="block font-bold text-gray-800">施工事例</span>
                                    <span class="text-xs text-gray-500">工事完了の報告など</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="イベント" class="peer hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-primary peer-checked:bg-blue-50 transition hover:bg-gray-50">
                                    <span class="block font-bold text-gray-800">イベント</span>
                                    <span class="text-xs text-gray-500">見学会や相談会</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="コラム" class="peer hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-primary peer-checked:bg-blue-50 transition hover:bg-gray-50">
                                    <span class="block font-bold text-gray-800">コラム</span>
                                    <span class="text-xs text-gray-500">読み物・技術紹介</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-2">公開日 <span class="text-red-500">*</span></label>
                        <input type="date" name="published_date" id="published_date" required
                            class="w-full text-lg border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-primary focus:ring-0 transition"
                            value="<?php echo date('Y-m-d'); ?>">
                        <p class="text-sm text-gray-500 mt-2">未来の日付にすると予約投稿になります。</p>
                    </div>

                    <div>
                        <label class="block text-lg font-medium text-gray-900 mb-4">公開状態</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="published" class="peer hidden" checked>
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition hover:bg-gray-50">
                                    <div class="font-bold text-green-700 mb-1">すぐ公開する</div>
                                    <div class="text-xs text-gray-500">サイトに表示されます</div>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="draft" class="peer hidden">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition hover:bg-gray-50">
                                    <div class="font-bold text-yellow-700 mb-1">下書きとして保存</div>
                                    <div class="text-xs text-gray-500">まだ公開されません</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: 確認 -->
            <div class="step-content p-8 hidden" data-step="4">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">内容の確認</h2>
                
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 space-y-4 mb-6">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">タイトル</span>
                        <p class="text-xl font-bold text-gray-900 mt-1" id="confirmTitle">タイトルなし</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">カテゴリ</span>
                            <p class="font-medium text-gray-800 mt-1" id="confirmCategory">-</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">公開日</span>
                            <p class="font-medium text-gray-800 mt-1" id="confirmDate">-</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">画像</span>
                        <div id="confirmImageContainer" class="mt-2">
                            <span class="text-gray-500 text-sm">画像なし</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">本文</span>
                        <div class="mt-2 p-4 bg-white rounded border border-gray-200 text-gray-700 text-sm max-h-40 overflow-y-auto whitespace-pre-wrap" id="confirmContent">
                            
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                内容に間違いがなければ、下の「完了」ボタンを押してください。<br>
                                <span id="publishMessage">サイトに即時公開されます。</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- フッター（ナビゲーション） -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200 flex justify-between items-center">
                <button type="button" id="prevBtn" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-white transition hidden">
                    戻る
                </button>
                <div class="flex gap-4 ml-auto">
                    <button type="button" id="saveDraftBtn" class="px-4 py-2 text-primary hover:text-primary-dark transition text-sm font-medium">
                        一時保存
                    </button>
                    <button type="button" id="nextBtn" class="px-8 py-2 bg-primary text-white rounded-lg hover:bg-primary-light transition shadow-sm font-bold">
                        次へ進む
                    </button>
                    <button type="submit" id="submitBtn" class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm font-bold hidden">
                        完了する
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        // 状態管理
        let currentStep = 1;
        const totalSteps = 4;
        const formData = {
            title: '',
            content: '',
            excerpt: '',
            featured_image: '',
            category: 'お知らせ',
            published_date: new Date().toISOString().split('T')[0],
            status: 'published'
        };

        // DOM要素
        const form = document.getElementById('wizardForm');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        // 初期化
        document.addEventListener('DOMContentLoaded', () => {
            updateUI();
            loadDraft();
        });

        // ナビゲーション制御
        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                currentStep++;
                updateUI();
                saveDraft();
            }
        });

        prevBtn.addEventListener('click', () => {
            currentStep--;
            updateUI();
        });

        // UI更新
        function updateUI() {
            // ステップ表示切り替え
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.querySelector(`.step-content[data-step="${currentStep}"]`).classList.remove('hidden');

            // インジケータ更新
            document.querySelectorAll('.step-indicator').forEach(el => {
                const step = parseInt(el.dataset.step);
                const circle = el.querySelector('.step-circle');
                const label = el.querySelector('span');
                
                // リセット
                circle.className = 'w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold mb-2 step-circle bg-white transition-all duration-300';
                label.className = 'text-xs font-medium transition-colors duration-300';

                if (step < currentStep) {
                    // 完了済み
                    circle.classList.add('border-green-500', 'text-green-500', 'bg-green-50');
                    circle.innerHTML = '✓';
                    label.classList.add('text-green-600');
                } else if (step === currentStep) {
                    // 現在
                    circle.classList.add('border-primary', 'text-primary');
                    label.classList.add('text-primary');
                } else {
                    // 未到達
                    circle.classList.add('border-gray-300', 'text-gray-400');
                    label.classList.add('text-gray-400');
                }
            });

            // ボタン表示制御
            prevBtn.classList.toggle('hidden', currentStep === 1);
            nextBtn.classList.toggle('hidden', currentStep === totalSteps);
            submitBtn.classList.toggle('hidden', currentStep !== totalSteps);
            
            // 確認画面の更新
            if (currentStep === 4) {
                updateConfirmation();
            }
        }

        // 入力チェック
        function validateStep(step) {
            const currentPanel = document.querySelector(`.step-content[data-step="${step}"]`);
            const inputs = currentPanel.querySelectorAll('input[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    // 入力時にエラー解除
                    input.addEventListener('input', () => {
                        input.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    }, { once: true });
                }
            });

            if (!isValid) {
                alert('必須項目を入力してください。');
            }
            return isValid;
        }

        // 確認画面の反映
        function updateConfirmation() {
            const formData = new FormData(form);
            document.getElementById('confirmTitle').textContent = formData.get('title') || 'タイトルなし';
            document.getElementById('confirmCategory').textContent = formData.get('category');
            document.getElementById('confirmDate').textContent = formData.get('published_date');
            document.getElementById('confirmContent').textContent = formData.get('content');
            
            const imgUrl = document.getElementById('featured_image').value;
            const imgContainer = document.getElementById('confirmImageContainer');
            if (imgUrl) {
                imgContainer.innerHTML = `<img src="${imgUrl}" class="h-32 rounded object-cover border border-gray-200">`;
            } else {
                imgContainer.innerHTML = '<span class="text-gray-500 text-sm">画像なし</span>';
            }

            const status = formData.get('status');
            const msg = document.getElementById('publishMessage');
            if (status === 'draft') {
                msg.textContent = '下書きとして保存されます。サイトにはまだ表示されません。';
            } else {
                msg.textContent = 'サイトに即時公開されます。';
            }
        }

        // ドラッグ＆ドロップ処理
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length) handleFileUpload(files[0]);
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) handleFileUpload(e.target.files[0]);
        });

        async function handleFileUpload(file) {
            if (!file.type.startsWith('image/')) {
                alert('画像ファイルを選択してください。');
                return;
            }

            // プレビュー表示
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
                document.getElementById('uploadPrompt').classList.add('hidden');
            };
            reader.readAsDataURL(file);

            // アップロード処理
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            progressDiv.classList.remove('hidden');
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('bucket', 'news');

            try {
                // 仮の進捗表示（実際のXHR進捗はfetchでは取れないため）
                progressBar.style.width = '50%';
                progressText.textContent = '50%';

                const response = await fetch('../api/news-upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    progressBar.style.width = '100%';
                    progressText.textContent = '完了';
                    document.getElementById('featured_image').value = result.url;
                    saveDraft(); // 画像URLも含めて保存
                } else {
                    throw new Error(result.error || 'アップロード失敗');
                }
            } catch (error) {
                alert('画像のアップロードに失敗しました: ' + error.message);
                progressBar.style.width = '0%';
                progressDiv.classList.add('hidden');
            }
        }

        document.getElementById('removeImageBtn').addEventListener('click', (e) => {
            e.stopPropagation(); // 親への伝播を止める
            document.getElementById('featured_image').value = '';
            document.getElementById('fileInput').value = '';
            document.getElementById('imagePreviewContainer').classList.add('hidden');
            document.getElementById('uploadPrompt').classList.remove('hidden');
            document.getElementById('uploadProgress').classList.add('hidden');
        });

        // 下書き保存機能（localStorage）
        function saveDraft() {
            const data = new FormData(form);
            const draft = {};
            data.forEach((value, key) => draft[key] = value);
            localStorage.setItem('news_draft', JSON.stringify(draft));
            
            const statusEl = document.getElementById('saveStatus');
            statusEl.textContent = '下書き保存済み ' + new Date().toLocaleTimeString();
            setTimeout(() => statusEl.textContent = '', 3000);
        }

        function loadDraft() {
            const draft = localStorage.getItem('news_draft');
            if (draft) {
                const data = JSON.parse(draft);
                // 各フィールドに値をセット
                Object.keys(data).forEach(key => {
                    const el = document.getElementsByName(key)[0];
                    if (el) {
                        if (el.type === 'radio') {
                            const radio = document.querySelector(`input[name="${key}"][value="${data[key]}"]`);
                            if (radio) radio.checked = true;
                        } else {
                            el.value = data[key];
                        }
                    }
                });
                
                // 画像プレビュー復元
                if (data.featured_image) {
                    document.getElementById('imagePreview').src = data.featured_image;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                    document.getElementById('uploadPrompt').classList.add('hidden');
                }
            }
        }

        saveDraftBtn.addEventListener('click', saveDraft);

        // 送信処理
        // 重複登録防止のため、イベントリスナーを一度削除してから追加するか、
        // あるいはフラグ管理を行うのが安全ですが、ここではシンプルに再登録を防ぐ構造にします。
        // ※ページ読み込み時に一度だけ実行される想定です。
        
        const handleSubmit = async (e) => {
            e.preventDefault(); // デフォルトの送信を防ぐ
            e.stopPropagation(); // バブリングを防ぐ
            
            console.log('保存ボタンがクリックされました');

            if (!confirm('この内容で保存してよろしいですか？')) return;

            submitBtn.disabled = true;
            submitBtn.textContent = '保存中...';

            // フォームデータを取得
            // 隠しフィールドの値も確実に取得するため、個別に取得して確認
            const featuredImageVal = document.getElementById('featured_image').value;
            console.log('画像URL:', featuredImageVal);

            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            // 画像URLを明示的に上書き（念のため）
            data['featured_image'] = featuredImageVal;

            try {
                const response = await fetch('../api/news-crud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'create',
                        ...data
                    })
                });

                // レスポンスのステータスコードを確認
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('保存結果:', result);

                if (result.success) {
                    localStorage.removeItem('news_draft'); // 下書き削除
                    alert('保存しました！');
                    window.location.href = 'supabase-news.php';
                } else {
                    throw new Error(result.error || '保存に失敗しました');
                }
            } catch (error) {
                console.error('保存エラー:', error);
                alert('エラーが発生しました: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.textContent = '完了する';
            }
        };

        // イベントリスナーの追加（既存のリスナーがあれば削除してから追加することを推奨する場合もあるが、
        // 今回はスクリプトが末尾で一度だけ実行されるため直接追加）
        submitBtn.onclick = handleSubmit; // addEventListenerではなくonclickプロパティを使用してみる（重複防止）
    </script>
</body>
</html>