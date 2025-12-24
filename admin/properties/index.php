<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$properties = SupabaseClient::select('properties', [], ['order' => 'created_at.desc', 'limit' => $limit, 'offset' => $offset]);
if ($properties === false) {
    $error = SupabaseClient::getLastError();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">物件管理</h1>
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
                <th>住所</th>
                <th>構造</th>
                <th>面積</th>
                <th>備考</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($properties && is_array($properties)): ?>
                <?php foreach ($properties as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['structure'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['area'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($row['notes'] ?? '', 0, 30, '...')); ?></td>
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
                <tr><td colspan="5">データがありません。</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
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
