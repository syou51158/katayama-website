<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../lib/SupabaseAuth.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = '新しいパスワードを入力してください。';
    } elseif ($new_password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } elseif (strlen($new_password) < 6) {
        $error = 'パスワードは6文字以上で入力してください。';
    } else {
        $accessToken = $_SESSION['sb_access_token'] ?? '';
        if (empty($accessToken)) {
             // セッション切れ等の場合
             header('Location: /admin/login.php');
             exit;
        }

        if (SupabaseAuth::updateUserPassword($accessToken, $new_password)) {
            $success = 'パスワードを変更しました。';
        } else {
            $error = 'パスワードの変更に失敗しました。' . SupabaseAuth::getLastError();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 mb-4 fw-bold text-dark">パスワード変更</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">新しいパスワード</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <div class="form-text">6文字以上で入力してください。</div>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">新しいパスワード（確認）</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-medium">
                                <i class="bi bi-check-lg me-1"></i> パスワードを変更する
                            </button>
                            <a href="/admin/" class="btn btn-outline-secondary py-2 fw-medium">
                                <i class="bi bi-arrow-left me-1"></i> ダッシュボードに戻る
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
