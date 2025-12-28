<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Fetch inquiries
$inquiries = SupabaseClient::select('inquiries', [], ['order' => 'created_at.desc', 'limit' => $limit, 'offset' => $offset]);

if ($inquiries === false) {
    $error = SupabaseClient::getLastError();
    $inquiries = []; // Ensure array for view
}

// Next page check: if fewer items than limit, no next page
$hasNext = count($inquiries) === $limit;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>お問い合わせ一覧</h2>
    <div>
        <button type="button" class="btn btn-danger" id="btn-bulk-delete" disabled>
            <i class="bi bi-trash"></i> 選択した項目を削除
        </button>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">エラーが発生しました: <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="table-responsive bg-white rounded shadow-sm p-3">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="select-all"></th>
                <th>日時</th>
                <th>お名前</th>
                <th>ステータス</th>
                <th>メッセージ</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($inquiries)): ?>
                <?php foreach ($inquiries as $row): ?>
                <?php 
                    $isNew = ($row['status'] ?? 'new') === 'new';
                    $rowClass = $isNew ? 'fw-bold bg-light' : '';
                ?>
                <tr class="<?php echo $rowClass; ?>" id="row-<?php echo $row['id']; ?>">
                    <td><input type="checkbox" class="form-check-input row-checkbox" value="<?php echo $row['id']; ?>"></td>
                    <td>
                        <?php 
                        // UTCからJSTへ変換
                        $date = new DateTime($row['created_at']);
                        $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
                        echo htmlspecialchars($date->format('Y/m/d H:i')); 
                        ?>
                        <?php if($isNew): ?>
                            <span class="badge bg-danger ms-1">NEW</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <?php
                        $status = $row['status'] ?? 'new';
                        $statusClass = match($status) {
                            'resolved' => 'success',
                            'in_progress' => 'warning',
                            'new' => 'danger',
                            default => 'secondary'
                        };
                        $statusLabel = match($status) {
                            'resolved' => '対応完了',
                            'in_progress' => '対応中/既読',
                            'new' => '未読',
                            default => $status
                        };
                        ?>
                        <span class="badge bg-<?php echo $statusClass; ?> status-badge-<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($statusLabel); ?>
                        </span>
                    </td>
                    <td class="text-truncate" style="max-width: 250px;">
                        <?php echo htmlspecialchars($row['message'] ?? ''); ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-detail" 
                            data-id="<?php echo $row['id']; ?>"
                            data-date="<?php echo htmlspecialchars((new DateTime($row['created_at']))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y/m/d H:i:s')); ?>"
                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                            data-tel="<?php echo htmlspecialchars($row['tel'] ?? '-'); ?>"
                            data-email="<?php echo htmlspecialchars($row['email'] ?? '-'); ?>"
                            data-source="<?php echo htmlspecialchars($row['source'] ?? '-'); ?>"
                            data-status="<?php echo htmlspecialchars($row['status'] ?? 'new'); ?>"
                            data-message="<?php echo htmlspecialchars($row['message'] ?? ''); ?>">
                            詳細
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">データがありません。</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<nav class="mt-4" aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>">前へ</a>
        </li>
        <li class="page-item disabled">
            <span class="page-link border-0">Page <?php echo $page; ?></span>
        </li>
        <li class="page-item <?php if (!$hasNext) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>">次へ</a>
        </li>
    </ul>
</nav>

<!-- Common Modal -->
<div class="modal fade" id="inquiryDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">お問い合わせ詳細</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">日時</p>
                        <p class="fw-bold" id="modal-date"></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">ステータス</p>
                        <p><span class="badge" id="modal-status-badge"></span></p>
                    </div>
                </div>
                
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>お名前:</strong> <span id="modal-name"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>媒体:</strong> <span id="modal-source"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>電話番号:</strong> <span id="modal-tel"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong> <span id="modal-email"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">メッセージ内容</label>
                    <div class="p-3 border rounded bg-white" style="white-space: pre-wrap; min-height: 100px;" id="modal-message"></div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="#" id="btn-reply-mail" class="btn btn-outline-primary">
                        <i class="bi bi-envelope"></i> メールソフトで返信する
                    </a>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="small me-2">ステータス変更:</span>
                    <select class="form-select form-select-sm" id="modal-status-select" style="width: auto;">
                        <option value="new">未読</option>
                        <option value="in_progress">対応中/既読</option>
                        <option value="resolved">対応完了</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-update-status">更新</button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrapの読み込み確認
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS is not loaded.');
        return;
    }

    // 1. Cleanup old modals
    const oldModals = document.querySelectorAll('body > #inquiryDetailModal');
    oldModals.forEach(el => el.remove());
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(bd => bd.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';

    const modalElement = document.getElementById('inquiryDetailModal');
    
    if (!modalElement) {
        console.error('Modal element not found');
        return;
    }

    // 2. Move *new* modal to body
    if (modalElement.parentNode !== document.body) {
        document.body.appendChild(modalElement);
    }
    
    const modal = new bootstrap.Modal(modalElement);
    
    // 3. Elements inside modal
    const mDate = modalElement.querySelector('#modal-date');
    const mStatusBadge = modalElement.querySelector('#modal-status-badge');
    const mName = modalElement.querySelector('#modal-name');
    const mSource = modalElement.querySelector('#modal-source');
    const mTel = modalElement.querySelector('#modal-tel');
    const mEmail = modalElement.querySelector('#modal-email');
    const mMessage = modalElement.querySelector('#modal-message');
    const mReplyBtn = modalElement.querySelector('#btn-reply-mail');
    
    // Status update controls
    const mStatusSelect = modalElement.querySelector('#modal-status-select');
    const mUpdateBtn = modalElement.querySelector('#btn-update-status');
    
    let currentId = null; // Store current inquiry ID

    // Bulk Delete Controls
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('btn-bulk-delete');

    function updateBulkDeleteButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (bulkDeleteBtn) {
            // 0件のときはボタン自体を非表示にする（あるいはdisabledにする）
            // ここではわかりやすさのため、0件なら非表示、1件以上で表示＆文言切り替えとする
            if (checkedCount === 0) {
                bulkDeleteBtn.style.display = 'none';
            } else {
                bulkDeleteBtn.style.display = 'inline-block';
                bulkDeleteBtn.disabled = false;
                
                if (checkedCount === 1) {
                    bulkDeleteBtn.innerHTML = `<i class="bi bi-trash"></i> 削除`;
                } else {
                    bulkDeleteBtn.innerHTML = `<i class="bi bi-trash"></i> 選択した ${checkedCount} 件を削除`;
                }
            }
        }
    }

    // 初期状態は非表示
    if (bulkDeleteBtn) {
        bulkDeleteBtn.style.display = 'none';
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            rowCheckboxes.forEach(cb => cb.checked = isChecked);
            updateBulkDeleteButton();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteButton);
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            let confirmMessage = '';
            if (checkedBoxes.length === 1) {
                confirmMessage = 'このお問い合わせを削除しますか？\nこの操作は取り消せません。';
            } else {
                confirmMessage = `選択した ${checkedBoxes.length} 件のお問い合わせを削除しますか？\nこの操作は取り消せません。`;
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            
            // Disable button
            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 削除中...';

            fetch('delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    alert('削除しました。');
                    window.location.reload();
                } else {
                    alert('削除に失敗しました: ' + (data.error || '不明なエラー'));
                    bulkDeleteBtn.disabled = false;
                    updateBulkDeleteButton();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('通信エラーが発生しました。');
                bulkDeleteBtn.disabled = false;
                updateBulkDeleteButton();
            });
        });
    }

    // イベント委譲を使ってボタンクリックを捕捉（より堅牢にする）
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-detail');
        if (!btn) return;

        const id = btn.dataset.id;
        const status = btn.dataset.status;
        currentId = id; // Set current ID
        
        // Populate modal
        if(mDate) mDate.textContent = btn.dataset.date;
        if(mName) mName.textContent = btn.dataset.name;
        if(mSource) mSource.textContent = btn.dataset.source;
        if(mTel) mTel.textContent = btn.dataset.tel;
        if(mEmail) mEmail.textContent = btn.dataset.email;
        if(mMessage) mMessage.textContent = btn.dataset.message;
        
        // Setup Reply Button
        if (mReplyBtn && btn.dataset.email) {
            const subject = encodeURIComponent("Re: お問い合わせについて");
            const body = encodeURIComponent(
                `${btn.dataset.name} 様\n\n` +
                `いつもお世話になっております。\n` +
                `片山建設工業です。\n\n` +
                `お問い合わせいただきました件につきまして、\n\n\n` +
                `--------------------------------------------------\n` +
                `お問い合わせ内容:\n` +
                `${btn.dataset.message}\n` +
                `--------------------------------------------------`
            );
            mReplyBtn.href = `mailto:${btn.dataset.email}?subject=${subject}&body=${body}`;
            mReplyBtn.classList.remove('disabled');
        } else if (mReplyBtn) {
            mReplyBtn.href = '#';
            mReplyBtn.classList.add('disabled');
        }

        // Set status badge look
        updateModalBadge(status);
        
        // Set select box value
        if(mStatusSelect) mStatusSelect.value = status;

        // Open modal
        modal.show();
        
        // If status is new, mark as pending automatically
        if (status === 'new') {
            updateStatus(id, 'in_progress', btn);
        }
    });
    
    // Manual update button event
    if (mUpdateBtn) {
        mUpdateBtn.addEventListener('click', function() {
            if (!currentId || !mStatusSelect) return;
            
            const newStatus = mStatusSelect.value;
            // モーダルが開いている間はDOMツリー上のボタンを探す必要がある
            // (ページネーション等で消えていない前提)
            const btn = document.querySelector(`.btn-detail[data-id="${currentId}"]`);
            
            // Disable button during update
            mUpdateBtn.disabled = true;
            mUpdateBtn.textContent = '更新中...';
            
            updateStatus(currentId, newStatus, btn)
                .finally(() => {
                    mUpdateBtn.disabled = false;
                    mUpdateBtn.textContent = '更新';
                });
        });
    }
    
    function updateModalBadge(status) {
        if(!mStatusBadge) return;
        mStatusBadge.className = 'badge '; // reset
        let label = status;
        if (status === 'new') {
            mStatusBadge.classList.add('bg-danger');
            label = '未読';
        } else if (status === 'in_progress') {
            mStatusBadge.classList.add('bg-warning');
            label = '対応中/既読';
        } else if (status === 'resolved') {
            mStatusBadge.classList.add('bg-success');
            label = '対応完了';
        } else {
            mStatusBadge.classList.add('bg-secondary');
        }
        mStatusBadge.textContent = label;
    }

    function updateStatus(id, newStatus, btnElement) {
        // 相対パスでAPIを叩く
        return fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, status: newStatus })
        })
        .then(res => {
            if (!res.ok) {
                // 認証エラーなどでリダイレクトされた場合などはHTMLが返る可能性があるためチェック
                const contentType = res.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") === -1) {
                    throw new Error("Invalid response format (not JSON)");
                }
                return res.json().then(data => { throw new Error(data.error || 'Server Error') });
            }
            return res.json();
        })
        .then(data => {
            if (data.ok) {
                // Update UI to reflect change
                const row = document.getElementById('row-' + id);
                if (row) {
                    // Remove "new" styling if exists
                    row.classList.remove('fw-bold', 'bg-light');
                    const newBadge = row.querySelector('.badge.bg-danger');
                    if (newBadge && newBadge.textContent === 'NEW') newBadge.remove();
                    
                    // Update status badge in the list
                    const statusBadge = row.querySelector('.status-badge-' + id);
                    if (statusBadge) {
                        statusBadge.className = 'badge status-badge-' + id;
                        let label = newStatus;
                        if (newStatus === 'new') {
                            statusBadge.classList.add('bg-danger');
                            label = '未読';
                        } else if (newStatus === 'in_progress') {
                            statusBadge.classList.add('bg-warning');
                            label = '対応中/既読';
                        } else if (newStatus === 'resolved') {
                            statusBadge.classList.add('bg-success');
                            label = '対応完了';
                        } else {
                            statusBadge.classList.add('bg-secondary');
                        }
                        statusBadge.textContent = label;
                    }
                    
                    // Update button data attribute
                    if (btnElement) {
                        btnElement.dataset.status = newStatus;
                    }
                    
                    // Update modal badge and select box
                    updateModalBadge(newStatus);
                    if(mStatusSelect) mStatusSelect.value = newStatus;
                }
            } else {
                console.error('Failed to update status:', data.error);
                alert('ステータスの更新に失敗しました: ' + (data.error || '不明なエラー'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('更新エラー: ' + err.message);
        });
    }
});
</script>
