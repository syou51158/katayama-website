<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'address' => $_POST['address'] ?? '',
        'structure' => $_POST['structure'] ?? '',
        'area' => $_POST['area'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    // Simple validation
    if (empty($data['address'])) {
        $error = '住所は必須です。';
    } else {
        $result = SupabaseClient::insert('properties', $data);
        if ($result === false) {
            $error = '作成に失敗しました: ' . SupabaseClient::getLastError();
        } else {
            $success = '物件を作成しました。';
        }
    }
}
?>

<h2>新規物件作成</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index.php">一覧に戻る</a></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">住所</label>
        <input type="text" name="address" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">構造</label>
        <input type="text" name="structure" class="form-control" placeholder="例：木造2階建">
    </div>
    <div class="mb-3">
        <label class="form-label">面積</label>
        <input type="text" name="area" class="form-control" placeholder="例：100m2">
    </div>
    <div class="mb-3">
        <label class="form-label">備考</label>
        <textarea name="notes" class="form-control" rows="4"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">作成</button>
    <a href="index.php" class="btn btn-secondary">キャンセル</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
