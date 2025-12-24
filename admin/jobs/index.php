<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$jobs = SupabaseClient::select('jobs', [], ['order' => 'created_at.desc', 'limit' => $limit, 'offset' => $offset]);
if ($jobs === false) {
    $error = SupabaseClient::getLastError();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">求人管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus"></i> 新規作成
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">操作が完了しました。</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>タイトル</th>
                <th>クライアント名</th>
                <th>勤務地</th>
                <th>雇用形態</th>
                <th>ステージ</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jobs && is_array($jobs)): ?>
                <?php foreach ($jobs as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['client_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['type'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['stage'] ?? '-'); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary">編集</a>
                        <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('本当に削除しますか？');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">データがありません。</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Simple Pagination -->
<nav>
    <ul class="pagination">
        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">前へ</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">次へ</a>
        </li>
    </ul>
</nav>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
