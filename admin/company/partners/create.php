<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $partner_name = $_POST['partner_name'] ?? '';
    $website_url = $_POST['website_url'] ?? '';
    $logo_url = $_POST['logo_url'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    $errors = [];
    if (empty($partner_name)) $errors[] = '企業名は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::insert('partners', [
            'partner_name' => $partner_name,
            'website_url' => $website_url,
            'logo_url' => $logo_url,
            'sort_order' => $sort_order,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($result !== false) {
            header('Location: index.php?success=1');
            exit;
        } else {
            $error = '保存に失敗しました: ' . SupabaseClient::getLastError();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">パートナー企業追加</h1>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger shadow-sm border-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <form action="" method="post">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="partner_name" class="form-label fw-bold">企業名 <span class="badge bg-danger ms-1">必須</span></label>
                        <input type="text" class="form-control" id="partner_name" name="partner_name" 
                               value="<?php echo htmlspecialchars($partner_name ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="website_url" class="form-label">ウェブサイトURL</label>
                        <input type="url" class="form-control" id="website_url" name="website_url" 
                               value="<?php echo htmlspecialchars($website_url ?? ''); ?>" placeholder="https://example.com">
                    </div>

                    <div class="mb-3">
                        <label for="logo_url" class="form-label">ロゴ画像URL</label>
                        <input type="text" class="form-control" id="logo_url" name="logo_url" 
                               value="<?php echo htmlspecialchars($logo_url ?? ''); ?>">
                        <?php if(!empty($logo_url)): ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Preview" class="img-thumbnail" style="max-height: 80px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">設定</h5>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">ステータス</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($status) && $status === 'active') ? 'selected' : ''; ?>>有効</option>
                                    <option value="inactive" <?php echo (isset($status) && $status === 'inactive') ? 'selected' : ''; ?>>無効</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="sort_order" class="form-label">表示順</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($sort_order ?? 0); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect">
                            <i class="bi bi-save me-2"></i> 保存する
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
