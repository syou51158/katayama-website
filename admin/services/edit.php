<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch existing data
$svc = SupabaseClient::select('services', ['id' => $id], ['limit' => 1]);
if (!$svc || count($svc) === 0) {
    header('Location: index.php?error=not_found');
    exit;
}
$data = $svc[0];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $detailed_description = $_POST['detailed_description'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $service_image = $_POST['service_image'] ?? '';
    $features_raw = $_POST['features'] ?? '';
    $icon = $_POST['icon'] ?? '';

    // Process features (convert multiline text to array)
    $features = array_filter(array_map('trim', explode("\n", $features_raw)), 'strlen');
    $features_json = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = 'サービス名は必須です。';

    if (empty($errors)) {
        $result = SupabaseClient::update('services', [
            'title' => $title,
            'description' => $description,
            'detailed_description' => $detailed_description,
            'status' => $status,
            'sort_order' => $sort_order,
            'service_image' => $service_image,
            'features' => $features_json, // Sending as JSON string, SupabaseClient handles encoding if it expects array but we pass string? No, SupabaseClient expects array and does encoding.
            // Wait, looking at SupabaseClient::makeRequest, it JSON encodes the entire data array.
            // So if I pass a JSON string here, it will be double encoded?
            // "features": "[\"a\",\"b\"]"
            // Actually I should pass the PHP array if I want it to be a JSON array in DB?
            // Let's check SupabaseClient again. Yes, curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            // So if I pass 'features' => $features (array), it becomes "features": ["a", "b"] in the payload. Correct.
            // However, $features_json above is a string. So I should pass $features array.
            'features' => array_values($features), 
            'icon' => $icon,
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
    // Initial values
    $title = $data['title'];
    $description = $data['description'];
    $detailed_description = $data['detailed_description'];
    $status = $data['status'];
    $sort_order = $data['sort_order'];
    $service_image = $data['service_image'];
    $icon = $data['icon'];
    
    // Decode features JSON to string for textarea
    $features_arr = isset($data['features']) ? $data['features'] : [];
    // The DB might return an array if json_decode is done in SupabaseClient::select? 
    // SupabaseClient::select returns `json_decode($response, true)`. So JSON columns become PHP arrays.
    $features_str = '';
    if (is_array($features_arr)) {
        $features_str = implode("\n", $features_arr);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">事業案内編集</h1>
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
                        <label for="title" class="form-label fw-bold">サービス名 <span class="badge bg-danger ms-1">必須</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" 
                               value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">概要 (一覧表示用)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="detailed_description" class="form-label fw-bold">詳細説明</label>
                        <textarea class="form-control" id="detailed_description" name="detailed_description" rows="6"><?php echo htmlspecialchars($detailed_description ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="features" class="form-label fw-bold">特徴リスト</label>
                        <textarea class="form-control" id="features" name="features" rows="5" placeholder="1行に1つの特徴を入力してください"><?php echo htmlspecialchars($features_str ?? ''); ?></textarea>
                        <div class="form-text">改行区切りで入力すると、箇条書きとして表示されます。</div>
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
                                <label for="icon" class="form-label">アイコンキー</label>
                                <input type="text" class="form-control" id="icon" name="icon" 
                                       value="<?php echo htmlspecialchars($icon ?? ''); ?>" placeholder="例: civil, building">
                            </div>

                            <div class="mb-3">
                                <label for="service_image" class="form-label">画像URL</label>
                                <input type="text" class="form-control" id="service_image" name="service_image" 
                                       value="<?php echo htmlspecialchars($service_image ?? ''); ?>">
                                <?php if(!empty($service_image)): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($service_image); ?>" alt="Preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect">
                            <i class="bi bi-save me-2"></i> 更新する
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">キャンセル</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
