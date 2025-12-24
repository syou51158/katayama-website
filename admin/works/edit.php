<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch existing data
$worksItem = SupabaseClient::select('works', ['id' => $id], ['limit' => 1]);
if (!$worksItem || count($worksItem) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $worksItem[0];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '住宅';
    $status = $_POST['status'] ?? 'draft';
    $completion_date = $_POST['completion_date'] ?? null;
    $location = $_POST['location'] ?? '';
    $featured_image = $_POST['featured_image'] ?? '';
    $client_name = $_POST['client_name'] ?? '';

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = 'タイトルは必須です。';

    if (empty($errors)) {
        if (empty($completion_date)) $completion_date = null;

        $result = SupabaseClient::update('works', [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'status' => $status,
            'completion_date' => $completion_date,
            'location' => $location,
            'featured_image' => $featured_image,
            'client_name' => $client_name,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        if ($result !== false) {
            header('Location: index.php?success=1');
            exit;
        } else {
            $error = '更新に失敗しました: ' . SupabaseClient::getLastError();
        }
    } else {
        $error = implode('<br>', $errors);
    }
} else {
    // Initial values from DB
    $title = $data['title'];
    $description = $data['description'];
    $category = $data['category'];
    $status = $data['status'];
    $location = $data['location'];
    $featured_image = $data['featured_image'];
    $client_name = $data['client_name'];
    $completion_date = !empty($data['completion_date']) ? date('Y-m-d', strtotime($data['completion_date'])) : '';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">施工実績編集</h1>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger shadow-sm border-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <form action="" method="post">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">タイトル <span class="badge bg-danger ms-1">必須</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" 
                               value="<?php echo htmlspecialchars($title ?? ''); ?>" required placeholder="例：S様邸 新築工事">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">概要</label>
                        <textarea class="form-control" id="description" name="description" rows="6" 
                                  placeholder="工事の概要を入力"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">施工場所</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($location ?? ''); ?>" placeholder="例：東京都世田谷区">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="client_name" class="form-label">発注者名</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" 
                                   value="<?php echo htmlspecialchars($client_name ?? ''); ?>" placeholder="任意">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="featured_image" class="form-label fw-bold">アイキャッチ画像URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-image"></i></span>
                            <input type="text" class="form-control" id="featured_image" name="featured_image" 
                                   value="<?php echo htmlspecialchars($featured_image ?? ''); ?>" 
                                   placeholder="/storage/v1/object/public/..."
                                   oninput="updatePreview(this)">
                        </div>
                        <div class="form-text">
                            <a href="/admin/files/" target="_blank" class="text-decoration-none">
                                <i class="bi bi-box-arrow-up-right"></i> ファイル管理を開く
                            </a>
                            から画像のURLをコピーしてください。
                        </div>
                        <div id="preview-container" class="mt-2" style="<?php echo empty($featured_image) ? 'display:none;' : ''; ?>">
                            <img id="preview-image" src="<?php echo htmlspecialchars($featured_image ?? ''); ?>" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                        <script>
                            function updatePreview(input) {
                                const container = document.getElementById('preview-container');
                                const img = document.getElementById('preview-image');
                                const url = input.value.trim();
                                if (url) {
                                    img.src = url;
                                    container.style.display = 'block';
                                } else {
                                    container.style.display = 'none';
                                }
                            }
                        </script>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">公開設定</h5>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">ステータス</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="published" <?php echo (isset($status) && $status === 'published') ? 'selected' : ''; ?>>公開</option>
                                    <option value="draft" <?php echo (isset($status) && $status === 'draft') ? 'selected' : ''; ?>>下書き</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="completion_date" class="form-label">竣工日</label>
                                <input type="date" class="form-control" id="completion_date" name="completion_date" 
                                       value="<?php echo htmlspecialchars($completion_date ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">カテゴリー</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="住宅" <?php echo (isset($category) && $category === '住宅') ? 'selected' : ''; ?>>住宅</option>
                                    <option value="商業施設" <?php echo (isset($category) && $category === '商業施設') ? 'selected' : ''; ?>>商業施設</option>
                                    <option value="公共施設" <?php echo (isset($category) && $category === '公共施設') ? 'selected' : ''; ?>>公共施設</option>
                                    <option value="土木" <?php echo (isset($category) && $category === '土木') ? 'selected' : ''; ?>>土木</option>
                                    <option value="リフォーム" <?php echo (isset($category) && $category === 'リフォーム') ? 'selected' : ''; ?>>リフォーム</option>
                                    <option value="その他" <?php echo (isset($category) && $category === 'その他') ? 'selected' : ''; ?>>その他</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect">
                            <i class="bi bi-save me-2"></i> 更新する
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">キャンセル</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
