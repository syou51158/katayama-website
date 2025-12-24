<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch services
$services = SupabaseClient::select('services', [], [
    'order' => 'sort_order.asc,created_at.asc'
]);

$error = SupabaseClient::getLastError();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <i class="bi bi-gear-wide-connected fs-4 me-2 text-primary"></i>
        <h1 class="h2 mb-0">事業案内管理</h1>
    </div>
    <!-- Add button could go here, but usually services are static -->
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
                        <th class="ps-4">順序</th>
                        <th>アイコン</th>
                        <th>サービス名</th>
                        <th>概要</th>
                        <th>ステータス</th>
                        <th class="text-end pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services && is_array($services) && count($services) > 0): ?>
                        <?php foreach ($services as $row): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted">
                                <?php echo htmlspecialchars($row['sort_order']); ?>
                            </td>
                            <td>
                                <?php if (!empty($row['icon'])): ?>
                                    <div class="bg-light rounded p-2 d-inline-block text-center" style="width: 40px;">
                                        <!-- Assuming icon name maps to some image or class, for now just text -->
                                        <i class="bi bi-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 50, '...')); ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> 有効
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                        <i class="bi bi-pause-circle me-1"></i> 無効
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> 編集
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                サービスが登録されていません
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
