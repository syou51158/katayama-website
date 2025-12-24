<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch data
$rep = SupabaseClient::select('representatives', ['id' => $id], ['limit' => 1]);
if (!$rep || count($rep) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $rep[0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $greeting_title = $_POST['greeting_title'] ?? '';
    $greeting_content = $_POST['greeting_content'] ?? '';
    $photo_url = $_POST['photo_url'] ?? '';
    $signature_url = $_POST['signature_url'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    // JSON fields
    $career_raw = $_POST['career'] ?? '';
    $career = array_values(array_filter(array_map('trim', explode("\n", $career_raw)), 'strlen'));
    
    $education_raw = $_POST['education'] ?? '';
    $education = array_values(array_filter(array_map('trim', explode("\n", $education_raw)), 'strlen'));
    
    $biography = [
        'career' => $career,
        'education' => $education
    ];

    $qualifications_raw = $_POST['qualifications'] ?? '';
    $qualifications = array_values(array_filter(array_map('trim', explode("\n", $qualifications_raw)), 'strlen'));

    $errors = [];
    if (empty($name)) $errors[] = '氏名は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::update('representatives', [
            'name' => $name,
            'position' => $position,
            'greeting_title' => $greeting_title,
            'greeting_content' => $greeting_content,
            'photo_url' => $photo_url,
            'signature_url' => $signature_url,
            'sort_order' => $sort_order,
            'status' => $status,
            'biography' => $biography,
            'qualifications' => $qualifications,
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
    $name = $data['name'];
    $position = $data['position'];
    $greeting_title = $data['greeting_title'];
    $greeting_content = $data['greeting_content'];
    $photo_url = $data['photo_url'];
    $signature_url = $data['signature_url'];
    $sort_order = $data['sort_order'];
    $status = $data['status'];

    $biography = $data['biography']; 
    // Usually SupabaseClient::select returns assoc array for JSON if using get_object_vars or similar, 
    // but here it returns array from json_decode($response, true).
    
    $career_arr = $biography['career'] ?? [];
    $education_arr = $biography['education'] ?? [];
    
    $career_raw = implode("\n", is_array($career_arr) ? $career_arr : []);
    $education_raw = implode("\n", is_array($education_arr) ? $education_arr : []);
    
    $qual_arr = $data['qualifications'] ?? [];
    $qualifications_raw = implode("\n", is_array($qual_arr) ? $qual_arr : []);
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">代表者編集</h1>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label fw-bold">氏名 <span class="badge bg-danger ms-1">必須</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">役職</label>
                            <input type="text" class="form-control" id="position" name="position" 
                                   value="<?php echo htmlspecialchars($position ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="greeting_title" class="form-label">挨拶タイトル</label>
                        <input type="text" class="form-control" id="greeting_title" name="greeting_title" 
                               value="<?php echo htmlspecialchars($greeting_title ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="greeting_content" class="form-label">挨拶本文</label>
                        <textarea class="form-control" id="greeting_content" name="greeting_content" rows="6"><?php echo htmlspecialchars($greeting_content ?? ''); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="career" class="form-label fw-bold">経歴</label>
                            <textarea class="form-control" id="career" name="career" rows="5" placeholder="1行に1つの経歴を入力"><?php echo htmlspecialchars($career_raw ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="education" class="form-label fw-bold">学歴</label>
                            <textarea class="form-control" id="education" name="education" rows="5" placeholder="1行に1つの学歴を入力"><?php echo htmlspecialchars($education_raw ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="qualifications" class="form-label fw-bold">保有資格</label>
                        <textarea class="form-control" id="qualifications" name="qualifications" rows="3" placeholder="1行に1つの資格を入力"><?php echo htmlspecialchars($qualifications_raw ?? ''); ?></textarea>
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

                            <div class="mb-3">
                                <label for="photo_url" class="form-label">写真URL</label>
                                <input type="text" class="form-control" id="photo_url" name="photo_url" 
                                       value="<?php echo htmlspecialchars($photo_url ?? ''); ?>">
                                <?php if(!empty($photo_url)): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="signature_url" class="form-label">署名画像URL</label>
                                <input type="text" class="form-control" id="signature_url" name="signature_url" 
                                       value="<?php echo htmlspecialchars($signature_url ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect">
                            <i class="bi bi-save me-2"></i> 更新する
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
