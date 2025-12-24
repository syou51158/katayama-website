<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../../lib/SupabaseStorage.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$files = SupabaseClient::select('files', [], ['order' => 'created_at.desc', 'limit' => $limit, 'offset' => $offset]);
if ($files === false) {
    $error = SupabaseClient::getLastError();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">ファイル管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="upload.php" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-upload"></i> アップロード
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
                <th>プレビュー</th>
                <th>パス/ファイル名</th>
                <th>種類</th>
                <th>関連Job ID</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($files && is_array($files)): ?>
                <?php foreach ($files as $row): ?>
                <?php 
                    $publicUrl = SupabaseStorage::getPublicObjectUrl('files', $row['path']);
                    $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $row['path']);
                ?>
                <tr>
                    <td>
                        <?php if ($isImage): ?>
                            <img src="<?php echo htmlspecialchars($publicUrl); ?>" alt="preview" class="rounded shadow-sm" style="height: 50px; width: auto; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-file-earmark h3"></i>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo htmlspecialchars($publicUrl); ?>" target="_blank"><?php echo htmlspecialchars($row['path']); ?></a>
                    </td>
                    <td><?php echo htmlspecialchars($row['kind'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['job_id'] ?? '-'); ?></td>
                    <td>
                        <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('本当に削除しますか？');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="path" value="<?php echo htmlspecialchars($row['path']); ?>">
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
