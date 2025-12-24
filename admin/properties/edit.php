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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'address' => $_POST['address'] ?? '',
        'structure' => $_POST['structure'] ?? '',
        'area' => $_POST['area'] ?? '',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    if (empty($data['address'])) {
        $error = '住所は必須です。';
    } else {
        $result = SupabaseClient::update('properties', $data, ['id' => $id]);
        if ($result === false) {
            $error = '更新に失敗しました: ' . SupabaseClient::getLastError();
        } else {
            $success = '物件を更新しました。';
        }
    }
}

$properties = SupabaseClient::select('properties', ['id' => $id]);
$property = ($properties && is_array($properties) && count($properties) > 0) ? $properties[0] : null;

if (!$property) {
    echo '<div class="alert alert-danger">物件が見つかりません。</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<h2>物件編集</h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="index.php">一覧に戻る</a></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">住所</label>
        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($property['address']); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">構造</label>
        <input type="text" name="structure" class="form-control" value="<?php echo htmlspecialchars($property['structure'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">面積</label>
        <input type="text" name="area" class="form-control" value="<?php echo htmlspecialchars($property['area'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">備考</label>
        <textarea name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($property['notes'] ?? ''); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">更新</button>
    <a href="index.php" class="btn btn-secondary">キャンセル</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
