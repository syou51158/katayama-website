<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$inquiries = SupabaseClient::select('inquiries', [], ['order' => 'created_at.desc', 'limit' => $limit, 'offset' => $offset]);
if ($inquiries === false) {
    $error = SupabaseClient::getLastError();
}
?>

<h2>お問い合わせ一覧</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">エラーが発生しました: <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>日時</th>
                <th>お名前</th>
                <th>ステータス</th>
                <th>メッセージ</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($inquiries && is_array($inquiries)): ?>
                <?php foreach ($inquiries as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('Y/m/d H:i', strtotime($row['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo ($row['status'] ?? '') === 'completed' ? 'success' : (($row['status'] ?? '') === 'pending' ? 'warning' : 'secondary'); ?>">
                            <?php echo htmlspecialchars($row['status'] ?? 'new'); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($row['message'] ?? '', 0, 50, '...')); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $row['id']; ?>">
                            詳細
                        </button>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="detailModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">お問い合わせ詳細</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>ID:</strong> <?php echo $row['id']; ?></p>
                                        <p><strong>日時:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>
                                        <p><strong>名前:</strong> <?php echo htmlspecialchars($row['name']); ?></p>
                                        <p><strong>電話:</strong> <?php echo htmlspecialchars($row['tel'] ?? '-'); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email'] ?? '-'); ?></p>
                                        <p><strong>媒体:</strong> <?php echo htmlspecialchars($row['source'] ?? '-'); ?></p>
                                        <p><strong>ステータス:</strong> <?php echo htmlspecialchars($row['status'] ?? '-'); ?></p>
                                        <hr>
                                        <p><strong>メッセージ:</strong><br><?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">データがありません。</td></tr>
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
