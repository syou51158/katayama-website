<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch existing data
$newsItem = SupabaseClient::select('news', ['id' => $id], ['limit' => 1]);
if (!$newsItem || count($newsItem) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $newsItem[0];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'お知らせ';
    $status = $_POST['status'] ?? 'draft';
    $published_date = $_POST['published_date'] ?? date('Y-m-d');

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = 'タイトルは必須です。';
    if (empty($published_date)) $errors[] = '日付は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::update('news', [
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'status' => $status,
            'published_date' => $published_date,
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
    $content = $data['content'];
    $category = $data['category'];
    $status = $data['status'];
    // Format timestamp to Y-m-d for date input
    $published_date = date('Y-m-d', strtotime($data['published_date']));
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">お知らせ編集</h1>
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
                               value="<?php echo htmlspecialchars($title ?? ''); ?>" required placeholder="記事のタイトルを入力">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label fw-bold">本文</label>
                        <textarea class="form-control" id="content" name="content" rows="10" 
                                  placeholder="記事の内容を入力"><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                        <div class="form-text">HTMLタグが使用可能です。</div>
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
                                <label for="published_date" class="form-label">公開日</label>
                                <input type="date" class="form-control" id="published_date" name="published_date" 
                                       value="<?php echo htmlspecialchars($published_date ?? date('Y-m-d')); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">カテゴリー</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="お知らせ" <?php echo (isset($category) && $category === 'お知らせ') ? 'selected' : ''; ?>>お知らせ</option>
                                    <option value="プレスリリース" <?php echo (isset($category) && $category === 'プレスリリース') ? 'selected' : ''; ?>>プレスリリース</option>
                                    <option value="イベント" <?php echo (isset($category) && $category === 'イベント') ? 'selected' : ''; ?>>イベント</option>
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
