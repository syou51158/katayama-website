<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'] ?? '',
        'client_name' => $_POST['client_name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'type' => $_POST['type'] ?? '',
        'stage' => $_POST['stage'] ?? 'draft'
    ];
    
    if (empty($data['title'])) {
        $error = 'タイトルは必須です。';
    } else {
        $result = SupabaseClient::update('jobs', $data, ['id' => $id]);
        if ($result === false) {
            $error = '更新に失敗しました: ' . SupabaseClient::getLastError();
        } else {
            $success = '求人を更新しました。';
        }
    }
}

// Fetch current data
$jobs = SupabaseClient::select('jobs', ['id' => $id]);
$job = ($jobs && is_array($jobs) && count($jobs) > 0) ? $jobs[0] : null;

if (!$job) {
    echo '<div class="alert alert-danger">求人が見つかりません。</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<h2>求人編集</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index.php">一覧に戻る</a></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">タイトル</label>
        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($job['title']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">クライアント名</label>
        <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($job['client_name'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">勤務地</label>
        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($job['address'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">雇用形態</label>
        <select name="type" class="form-select">
            <option value="正社員" <?php if (($job['type'] ?? '') === '正社員') echo 'selected'; ?>>正社員</option>
            <option value="契約社員" <?php if (($job['type'] ?? '') === '契約社員') echo 'selected'; ?>>契約社員</option>
            <option value="アルバイト" <?php if (($job['type'] ?? '') === 'アルバイト') echo 'selected'; ?>>アルバイト</option>
            <option value="業務委託" <?php if (($job['type'] ?? '') === '業務委託') echo 'selected'; ?>>業務委託</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">ステージ</label>
        <select name="stage" class="form-select">
            <option value="draft" <?php if (($job['stage'] ?? '') === 'draft') echo 'selected'; ?>>下書き</option>
            <option value="published" <?php if (($job['stage'] ?? '') === 'published') echo 'selected'; ?>>公開中</option>
            <option value="closed" <?php if (($job['stage'] ?? '') === 'closed') echo 'selected'; ?>>募集終了</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">更新</button>
    <a href="index.php" class="btn btn-secondary">キャンセル</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
