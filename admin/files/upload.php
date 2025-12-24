<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../lib/SupabaseStorage.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = $_POST['job_id'] ?? null;
    $kind = $_POST['kind'] ?? 'other';
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file']['tmp_name'];
        $name = basename($_FILES['file']['name']);
        $type = $_FILES['file']['type'];
        
        // Generate unique path
        $path = date('YmdHis') . '_' . $name;
        $content = file_get_contents($tmpName);
        
        // Ensure bucket exists
        SupabaseStorage::ensureBucket('files');
        
        // Upload
        if (SupabaseStorage::upload('files', $path, $content, $type)) {
            // Insert into DB
            $data = [
                'path' => $path,
                'kind' => $kind,
                'job_id' => $jobId ?: null
            ];
            
            $result = SupabaseClient::insert('files', $data);
            if ($result === false) {
                $error = 'DB登録に失敗しました: ' . SupabaseClient::getLastError();
            } else {
                $success = 'アップロード完了しました。';
            }
        } else {
            $error = 'アップロードに失敗しました。Storage設定を確認してください。';
        }
    } else {
        $error = 'ファイルが選択されていないか、エラーが発生しました。';
    }
}

// Fetch jobs for dropdown
$jobs = SupabaseClient::select('jobs', [], ['select' => 'id,title']);
?>

<h2>ファイルアップロード</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index.php">一覧に戻る</a></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">ファイル選択</label>
        <input type="file" name="file" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">種類</label>
        <select name="kind" class="form-select">
            <option value="image">画像</option>
            <option value="document">ドキュメント</option>
            <option value="other">その他</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">関連求人 (任意)</label>
        <select name="job_id" class="form-select">
            <option value="">選択なし</option>
            <?php if ($jobs && is_array($jobs)): ?>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">アップロード</button>
    <a href="index.php" class="btn btn-secondary">キャンセル</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
