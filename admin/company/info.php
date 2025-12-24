<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Fetch existing data (Assume single record)
$info = SupabaseClient::select('company_info', [], ['limit' => 1]);
$data = ($info && count($info) > 0) ? $info[0] : [];
$id = $data['id'] ?? null;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $representative_title = $_POST['representative_title'] ?? '';
    $representative_name = $_POST['representative_name'] ?? '';
    $address_postal = $_POST['address_postal'] ?? '';
    $address_detail = $_POST['address_detail'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $fax = $_POST['fax'] ?? '';
    $email = $_POST['email'] ?? '';
    $registration_number = $_POST['registration_number'] ?? '';
    
    // JSON fields
    $business_raw = $_POST['business_details'] ?? '';
    $business_details = array_values(array_filter(array_map('trim', explode("\n", $business_raw)), 'strlen'));
    
    $licenses_raw = $_POST['licenses'] ?? '';
    $licenses = array_values(array_filter(array_map('trim', explode("\n", $licenses_raw)), 'strlen'));

    // Validation
    $errors = [];
    if (empty($company_name)) $errors[] = '会社名は必須です。';

    if (empty($errors)) {
        $payload = [
            'company_name' => $company_name,
            'representative_title' => $representative_title,
            'representative_name' => $representative_name,
            'address_postal' => $address_postal,
            'address_detail' => $address_detail,
            'phone' => $phone,
            'fax' => $fax,
            'email' => $email,
            'registration_number' => $registration_number,
            'business_details' => $business_details,
            'licenses' => $licenses,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id) {
            $result = SupabaseClient::update('company_info', $payload, ['id' => $id]);
        } else {
            // Create new if not exists
            $payload['created_at'] = date('Y-m-d H:i:s');
            $result = SupabaseClient::insert('company_info', $payload);
        }

        if ($result !== false) {
            $success_msg = '会社情報を更新しました。';
            // Refetch to show updated data
            $info = SupabaseClient::select('company_info', [], ['limit' => 1]);
            $data = ($info && count($info) > 0) ? $info[0] : [];
            $id = $data['id'] ?? null;
        } else {
            $error = '更新に失敗しました: ' . SupabaseClient::getLastError();
        }
    } else {
        $error = implode('<br>', $errors);
    }
} else {
    // Initial JSON to string
    $business_details_str = isset($data['business_details']) && is_array($data['business_details']) ? implode("\n", $data['business_details']) : '';
    $licenses_str = isset($data['licenses']) && is_array($data['licenses']) ? implode("\n", $data['licenses']) : '';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <a href="index.php" class="text-decoration-none text-muted me-2">
            <i class="bi bi-arrow-left-circle fs-4"></i>
        </a>
        <h1 class="h2 mb-0">基本情報編集</h1>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger shadow-sm border-0">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (isset($success_msg)): ?>
    <div class="alert alert-success shadow-sm border-0">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo $success_msg; ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body p-4">
        <form action="" method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">基本情報</h5>
                    <div class="mb-3">
                        <label for="company_name" class="form-label fw-bold">会社名 <span class="badge bg-danger ms-1">必須</span></label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?php echo htmlspecialchars($data['company_name'] ?? ''); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="representative_title" class="form-label">役職</label>
                            <input type="text" class="form-control" id="representative_title" name="representative_title" 
                                   value="<?php echo htmlspecialchars($data['representative_title'] ?? ''); ?>">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="representative_name" class="form-label">代表者名</label>
                            <input type="text" class="form-control" id="representative_name" name="representative_name" 
                                   value="<?php echo htmlspecialchars($data['representative_name'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="registration_number" class="form-label">登録番号 (インボイス等)</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" 
                               value="<?php echo htmlspecialchars($data['registration_number'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="business_details" class="form-label fw-bold">事業内容</label>
                        <textarea class="form-control" id="business_details" name="business_details" rows="5" placeholder="1行に1つの事業を入力"><?php echo htmlspecialchars($business_details_str ?? ''); ?></textarea>
                        <div class="form-text">改行区切りで入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="licenses" class="form-label fw-bold">許可・免許</label>
                        <textarea class="form-control" id="licenses" name="licenses" rows="5" placeholder="1行に1つの許可・免許を入力"><?php echo htmlspecialchars($licenses_str ?? ''); ?></textarea>
                        <div class="form-text">改行区切りで入力してください。</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">連絡先・所在地</h5>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="address_postal" class="form-label">郵便番号</label>
                            <input type="text" class="form-control" id="address_postal" name="address_postal" 
                                   value="<?php echo htmlspecialchars($data['address_postal'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address_detail" class="form-label">住所</label>
                        <input type="text" class="form-control" id="address_detail" name="address_detail" 
                               value="<?php echo htmlspecialchars($data['address_detail'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">電話番号</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fax" class="form-label">FAX番号</label>
                        <input type="text" class="form-control" id="fax" name="fax" 
                               value="<?php echo htmlspecialchars($data['fax'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg text-white card-hover-effect px-5">
                    <i class="bi bi-save me-2"></i> 保存する
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
