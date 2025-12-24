<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch existing data
$history = SupabaseClient::select('company_history', ['id' => $id], ['limit' => 1]);
if (!$history || count($history) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $history[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = (int)($_POST['year'] ?? 0);
    $month = !empty($_POST['month']) ? (int)$_POST['month'] : null;
    $event_description = $_POST['event_description'] ?? '';
    $status = $_POST['status'] ?? 'active';

    $errors = [];
    if ($year < 1900 || $year > 2100) $errors[] = '年は正しく入力してください。';
    if (empty($event_description)) $errors[] = '出来事は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::update('company_history', [
            'year' => $year,
            'month' => $month,
            'event_description' => $event_description,
            'status' => $status,
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
    $year = $data['year'];
    $month = $data['month'];
    $event_description = $data['event_description'];
    $status = $data['status'];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">沿革編集</h1>
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
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="year" class="form-label fw-bold">年 <span class="badge bg-danger ms-1">必須</span></label>
                    <input type="number" class="form-control" id="year" name="year" 
                           value="<?php echo htmlspecialchars($year ?? date('Y')); ?>" required min="1900" max="2100">
                </div>
                <div class="col-md-6">
                    <label for="month" class="form-label">月</label>
                    <select class="form-select" id="month" name="month">
                        <option value="">--</option>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo (isset($month) && $month == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>月
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="event_description" class="form-label fw-bold">出来事 <span class="badge bg-danger ms-1">必須</span></label>
                <textarea class="form-control" id="event_description" name="event_description" rows="3" required><?php echo htmlspecialchars($event_description ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">ステータス</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?php echo (isset($status) && $status === 'active') ? 'selected' : ''; ?>>有効</option>
                    <option value="inactive" <?php echo (isset($status) && $status === 'inactive') ? 'selected' : ''; ?>>無効</option>
                </select>
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
