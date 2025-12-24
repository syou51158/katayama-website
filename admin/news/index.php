<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Fetch news items
$news = SupabaseClient::select('news', [], [
    'order' => 'published_date.desc,created_at.desc',
    'limit' => $limit,
    'offset' => $offset
]);

$error = SupabaseClient::getLastError();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <i class="bi bi-newspaper fs-4 me-2 text-primary"></i>
        <h1 class="h2 mb-0">お知らせ管理</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> 新規作成
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
                        <th class="ps-4">日付</th>
                        <th>タイトル</th>
                        <th>カテゴリー</th>
                        <th>ステータス</th>
                        <th class="text-end pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($news && is_array($news) && count($news) > 0): ?>
                        <?php foreach ($news as $row): ?>
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <?php echo date('Y-m-d', strtotime($row['published_date'])); ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'published'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> 公開中
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                        <i class="bi bi-pause-circle me-1"></i> 下書き
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i> 編集
                                </a>
                                <form action="delete.php" method="post" style="display:inline" onsubmit="return confirm('本当にこのお知らせを削除しますか？');">
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                データがありません
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<nav class="mt-4" aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
            <a class="page-link border-0 shadow-sm rounded-circle me-2 d-flex align-items-center justify-content-center" 
               href="?page=<?php echo $page - 1; ?>" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        <li class="page-item disabled">
            <span class="page-link border-0 bg-transparent text-muted">Go to page</span>
        </li>
        <li class="page-item">
            <a class="page-link border-0 shadow-sm rounded-circle ms-2 d-flex align-items-center justify-content-center" 
               href="?page=<?php echo $page + 1; ?>" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
