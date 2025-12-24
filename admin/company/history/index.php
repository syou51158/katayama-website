<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/header.php';

// Fetch history
$history = SupabaseClient::select('company_history', [], [
    'order' => 'year.desc,month.desc'
]);

$error = SupabaseClient::getLastError();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="../index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">会社沿革管理</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>新規追加
        </a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger shadow-sm border-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success shadow-sm border-0">
        <i class="bi bi-check-circle-fill me-2"></i>
        操作が完了しました。
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">年月</th>
                        <th>出来事</th>
                        <th>ステータス</th>
                        <th class="text-end pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history && is_array($history) && count($history) > 0): ?>
                        <?php foreach ($history as $row): ?>
                        <tr>
                            <td class="ps-4 fw-bold">
                                <?php echo htmlspecialchars($row['year']); ?>年
                                <?php echo !empty($row['month']) ? $row['month'] . '月' : ''; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['event_description']); ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">有効</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">無効</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('本当に削除しますか？');">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                沿革データがありません
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
