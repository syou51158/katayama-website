<?php
// 出力バッファリングを開始（余計な出力が混ざらないようにする）
ob_start();

// 設定項目の定義
$settingsKeys = [
    'mail_admin_address' => ['label' => '管理者通知先メールアドレス', 'type' => 'text', 'desc' => 'お問い合わせ通知を受け取るメールアドレスです。複数のアドレスに送信する場合は、カンマ（,）で区切って入力してください。例: admin1@example.com, admin2@example.com'],
    'mail_from_address' => ['label' => '送信元メールアドレス', 'type' => 'email', 'desc' => 'お客様への自動返信メールの送信元として表示されるアドレスです。'],
    'mail_from_name' => ['label' => '送信元名', 'type' => 'text', 'desc' => 'お客様への自動返信メールの送信者名です。'],
    'line_notify_token' => ['label' => 'LINE Notifyトークン', 'type' => 'password', 'desc' => 'LINE通知を行うためのトークンです。空欄の場合はLINE通知が無効になります。'],
    'mail_autoreply_subject' => ['label' => '自動返信メール：件名', 'type' => 'text', 'desc' => 'お客様に送られる自動返信メールの件名です。'],
    'mail_autoreply_header' => ['label' => '自動返信メール：本文ヘッダー', 'type' => 'textarea', 'desc' => 'お客様のお名前などの前に挿入される挨拶文です。'],
    'mail_autoreply_footer' => ['label' => '自動返信メール：本文フッター', 'type' => 'textarea', 'desc' => '署名などの定型文です。'],
];

require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';

// 更新処理（Ajax用）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    // 既存のバッファをクリア
    ob_clean();
    
    header('Content-Type: application/json');
    $successCount = 0;
    $errors = [];
    
    foreach ($settingsKeys as $key => $info) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            // 空文字の場合は空文字として保存（NULLにしない）
            $result = SupabaseClient::upsert('system_settings', [
                'key' => $key,
                'value' => $value,
                'description' => $info['label']
            ], 'key');
            
            if ($result) {
                $successCount++;
            } else {
                $errors[] = SupabaseClient::getLastError();
            }
        }
    }
    
    if ($successCount > 0) {
        echo json_encode(['success' => true, 'message' => '設定を保存しました。']);
    } else {
        echo json_encode(['success' => false, 'message' => '設定の保存に失敗しました。', 'details' => implode(', ', $errors)]);
    }
    exit;
}

require_once __DIR__ . '/../includes/header.php';

// バッファを出力して終了
ob_end_flush();

// 設定項目の定義
$settingsKeys = [
    'mail_admin_address' => ['label' => '管理者通知先メールアドレス', 'type' => 'text', 'desc' => 'お問い合わせ通知を受け取るメールアドレスです。複数のアドレスに送信する場合は、カンマ（,）で区切って入力してください。例: admin1@example.com, admin2@example.com'],
    'mail_from_address' => ['label' => '送信元メールアドレス', 'type' => 'email', 'desc' => 'お客様への自動返信メールの送信元として表示されるアドレスです。'],
    'mail_from_name' => ['label' => '送信元名', 'type' => 'text', 'desc' => 'お客様への自動返信メールの送信者名です。'],
    'line_notify_token' => ['label' => 'LINE Notifyトークン', 'type' => 'password', 'desc' => 'LINE通知を行うためのトークンです。空欄の場合はLINE通知が無効になります。'],
    'mail_autoreply_subject' => ['label' => '自動返信メール：件名', 'type' => 'text', 'desc' => 'お客様に送られる自動返信メールの件名です。'],
    'mail_autoreply_header' => ['label' => '自動返信メール：本文ヘッダー', 'type' => 'textarea', 'desc' => 'お客様のお名前などの前に挿入される挨拶文です。'],
    'mail_autoreply_footer' => ['label' => '自動返信メール：本文フッター', 'type' => 'textarea', 'desc' => '署名などの定型文です。'],
];

/* 以前の処理コードがあった場所（削除済み） */

// Configファイルの値をデフォルトとして表示するための参考値取得（読み取り専用）
require_once __DIR__ . '/../../config/supabase.secrets.php';
$configDefaults = [
    'mail_admin_address' => SupabaseConfig::getMailAdminAddress(),
    'mail_from_address' => SupabaseConfig::getMailFromAddress(),
    'mail_from_name' => SupabaseConfig::getMailFromName(),
    'line_notify_token' => SupabaseConfig::getLineNotifyToken(),
];

// 現在の設定値を取得（HTML表示用）
$currentSettings = [];
foreach ($settingsKeys as $key => $info) {
    $currentSettings[$key] = SupabaseClient::getSystemSetting($key);
    
    // DBに値がない場合のデフォルト値
    if (empty($currentSettings[$key])) {
        if ($key === 'mail_autoreply_header') {
            $currentSettings[$key] = "この度は、片山建設工業へお問い合わせいただき、誠にありがとうございます。\n以下の内容でお問い合わせを受け付けいたしました。";
        } elseif ($key === 'mail_autoreply_footer') {
            $currentSettings[$key] = "内容を確認の上、担当者より改めてご連絡させていただきます。\n今しばらくお待ちいただけますようお願い申し上げます。\n\n※このメールは自動送信されています。\nお心当たりのない場合は、お手数ですが本メールを破棄してください。\n\n--------------------------------------------------\n片山建設工業\n〒520-2279 滋賀県大津市大石東6丁目6-28\nTEL: 090-5650-1106\nURL: https://katayama-kensetsukougyou.jp\n--------------------------------------------------";
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <i class="bi bi-gear-fill fs-4 me-2 text-primary"></i>
        <h1 class="h2 mb-0">メール設定</h1>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(0,0,0,0.4); z-index: 1050;">
    <div class="text-center bg-white p-4 rounded shadow">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3 fw-bold text-dark">保存中...</div>
    </div>
</div>

<!-- Alert Container -->
<div id="alert-container"></div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4">
                <form method="post" id="settings-form">
                    <h4 class="mb-3 text-primary border-bottom pb-2">基本設定</h4>
                    
                    <?php foreach ($settingsKeys as $key => $info): ?>
                        <?php if ($key === 'mail_autoreply_subject') echo '<h4 class="mb-3 mt-5 text-primary border-bottom pb-2">自動返信メール設定</h4>'; ?>
                        
                        <div class="mb-4">
                            <label for="<?php echo $key; ?>" class="form-label fw-bold"><?php echo $info['label']; ?></label>
                            <?php if ($info['type'] === 'textarea'): ?>
                                <textarea class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" rows="6"><?php echo htmlspecialchars($currentSettings[$key]); ?></textarea>
                            <?php else: ?>
                                <input type="<?php echo $info['type']; ?>" class="form-control" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($currentSettings[$key]); ?>">
                            <?php endif; ?>
                            <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i><?php echo $info['desc']; ?></div>
                            <?php if (isset($configDefaults[$key]) && empty($currentSettings[$key])): ?>
                                <div class="alert alert-warning py-2 mt-2 mb-0 small">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    現在の設定ファイル値: <strong><?php echo htmlspecialchars($configDefaults[$key]); ?></strong><br>
                                    （この項目が空欄の場合、上記の設定ファイルの値が使用されます）
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-5 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i>設定を保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4 border-0 bg-light">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb-fill text-warning me-2"></i>設定のヒント</h5>
                <p class="small text-muted">
                    ここで設定した内容は即座に反映されます。
                    送信元のメールアドレスを変更する場合は、そのアドレスが実際にメールを送受信できることを確認してください。
                </p>
                <p class="small text-muted">
                    <strong>LINE Notifyトークン</strong>を設定すると、お問い合わせがあった際にご自身のLINEに通知が届くようになります。
                    トークンは <a href="https://notify-bot.line.me/ja/" target="_blank">LINE Notify公式サイト</a> から取得できます。
                </p>
                
                <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-shield-lock-fill text-success me-2"></i>SMTP設定</h5>
                <p class="small text-muted">
                    メールサーバー（SMTP）の接続情報（ホスト名、ポート、パスワードなど）は、セキュリティのためこの画面からは変更できません。
                    変更が必要な場合は、サーバー管理者に連絡するか、サーバー上の設定ファイル（<code>config/supabase.secrets.php</code>）を直接編集してください。
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settings-form');
    const overlay = document.getElementById('loading-overlay');
    const alertContainer = document.getElementById('alert-container');
    
    // フォーム送信時のローディング表示
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // 通常の送信をキャンセル
            
            // 既存のローディングオーバーレイがあれば削除（重複防止）
            const existingOverlay = document.getElementById('loading-overlay');
            if (existingOverlay) {
                // クラス操作で表示切り替え
                existingOverlay.classList.remove('d-none');
                existingOverlay.classList.add('d-flex');
            } else {
                // なければコンソールにエラー出力（通常ありえない）
                console.error('Loading overlay not found');
                return;
            }
            
            // フォームデータの取得
            const formData = new FormData(form);
            formData.append('ajax_action', 'save');
            
            // Ajax送信
            fetch(window.location.href, { // 現在のURLに対してPOSTする
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                // レスポンスがJSONかどうかを確認
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    // JSONでない場合はテキストとして取得してエラー表示
                    return response.text().then(text => {
                        console.error('Invalid JSON response:', text);
                        // ローディング解除（ここでも必要）
                        const currentOverlay = document.getElementById('loading-overlay');
                        if (currentOverlay) {
                            currentOverlay.classList.remove('d-flex');
                            currentOverlay.classList.add('d-none');
                        }
                        // エラー表示
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>サーバーエラーが発生しました。<br><small>応答形式が不正です。</small>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`;
                        throw new Error('サーバーからの応答が不正です。');
                    });
                }
            })
            .then(data => {
                // ローディング非表示（要素を再取得して確実に操作）
                const currentOverlay = document.getElementById('loading-overlay');
                if (currentOverlay) {
                    currentOverlay.classList.remove('d-flex');
                    currentOverlay.classList.add('d-none');
                }
                
                // メッセージ表示
                let alertHtml = '';
                if (data.success) {
                    alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                } else {
                    alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message}
                            ${data.details ? `<br><small class="text-muted">${data.details}</small>` : ''}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                }
                alertContainer.innerHTML = alertHtml;
                
                // 成功時は画面トップへスクロール
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Fetch error:', error); // エラー内容をコンソールに出力
                // エラー時（要素を再取得して確実に操作）
                const currentOverlay = document.getElementById('loading-overlay');
                if (currentOverlay) {
                    currentOverlay.classList.remove('d-flex');
                    currentOverlay.classList.add('d-none');
                }
                
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>通信エラーが発生しました。<br><small>${error.message}</small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
