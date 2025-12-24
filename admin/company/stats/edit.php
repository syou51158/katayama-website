<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$history = SupabaseClient::select('company_stats', ['id' => $id], ['limit' => 1]);
if (!$history || count($history) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $history[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stat_name = $_POST['stat_name'] ?? '';
    $stat_value = $_POST['stat_value'] ?? '';
    $stat_unit = $_POST['stat_unit'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $errors = [];
    if (empty($stat_name)) $errors[] = '項目名は必須です。';
    if (empty($stat_value)) $errors[] = '数値は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::update('company_stats', [
            'stat_name' => $stat_name,
            'stat_value' => $stat_value,
            'stat_unit' => $stat_unit,
            'sort_order' => $sort_order,
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
    $stat_name = $data['stat_name'];
    $stat_value = $data['stat_value'];
    $stat_unit = $data['stat_unit'];
    $sort_order = $data['sort_order'];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">統計項目編集</h1>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger shadow-sm border-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-3" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body p-4">
        <form action="" method="post">
            <div class="mb-3">
                <label for="stat_name" class="form-label fw-bold">項目名 <span class="badge bg-danger ms-1">必須</span></label>
                <input type="text" class="form-control" id="stat_name" name="stat_name" 
                       value="<?php echo htmlspecialchars($stat_name ?? ''); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="stat_value" class="form-label fw-bold">数値 <span class="badge bg-danger ms-1">必須</span></label>
                    <input type="text" class="form-control" id="stat_value" name="stat_value" 
                           value="<?php echo htmlspecialchars($stat_value ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stat_unit" class="form-label">単位</label>
                    <input type="text" class="form-control" id="stat_unit" name="stat_unit" 
                           value="<?php echo htmlspecialchars($stat_unit ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="sort_order" class="form-label">表示順</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                       value="<?php echo htmlspecialchars($sort_order ?? 0); ?>">
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect">
                    <i class="bi bi-save me-2"></i> 更新する
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
