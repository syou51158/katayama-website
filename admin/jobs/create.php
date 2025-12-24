<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'] ?? '',
        'client_name' => $_POST['client_name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'type' => $_POST['type'] ?? '',
        'stage' => $_POST['stage'] ?? 'draft'
    ];
    
    // Simple validation
    if (empty($data['title'])) {
        $error = 'タイトルは必須です。';
    } else {
        $result = SupabaseClient::insert('jobs', $data);
        if ($result === false) {
            $error = '作成に失敗しました: ' . SupabaseClient::getLastError();
        } else {
            $success = '求人を作成しました。';
        }
    }
}
?>

<h2>新規求人作成</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index.php">一覧に戻る</a></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">タイトル</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">クライアント名</label>
        <input type="text" name="client_name" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">勤務地</label>
        <input type="text" name="address" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">雇用形態</label>
        <select name="type" class="form-select">
            <option value="正社員">正社員</option>
            <option value="契約社員">契約社員</option>
            <option value="アルバイト">アルバイト</option>
            <option value="業務委託">業務委託</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">ステージ</label>
        <select name="stage" class="form-select">
            <option value="draft">下書き</option>
            <option value="published">公開中</option>
            <option value="closed">募集終了</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">作成</button>
    <a href="index.php" class="btn btn-secondary">キャンセル</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
