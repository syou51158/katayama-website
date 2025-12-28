<?php
// エラー表示設定（本番環境では非表示にすべきだが、デバッグのためログ出力のみにする）
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ヘッダー設定
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ライブラリ読み込み
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/SupabaseClient.php';
require_once __DIR__ . '/../config/supabase.secrets.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// メソッドチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// 入力データの取得
$input = [];
if (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// バリデーション
$errors = [];
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$message = trim($input['message'] ?? '');
$company = trim($input['company'] ?? '');
$phone = trim($input['phone'] ?? '');
$inquiry_type = trim($input['inquiry_type'] ?? '');
$source = trim($input['source'] ?? 'web');

if (empty($name)) {
    $errors[] = 'お名前を入力してください。';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '有効なメールアドレスを入力してください。';
}
if (empty($message)) {
    $errors[] = 'お問い合わせ内容を入力してください。';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '入力内容に不備があります。', 'errors' => $errors]);
    exit;
}

try {
    // 1. データベースへの保存
    $inquiryData = [
        'name' => $name,
        'email' => $email,
        'message' => "【種類】{$inquiry_type}\n【会社名】{$company}\n【電話番号】{$phone}\n\n{$message}",
        'source' => $source,
        'status' => 'new',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // DBに保存（失敗してもメール送信は試みるため、エラーはログに残すだけにするか、あるいは厳密にエラーにするか）
    // ここではDB保存失敗＝システムエラーとして扱う
    $dbResult = SupabaseClient::insert('inquiries', $inquiryData);
    if ($dbResult === false) {
        error_log('Failed to insert inquiry to DB: ' . SupabaseClient::getLastError());
        // DB保存に失敗してもメールだけは飛ばす方針で進める（機会損失を防ぐため）
    }

    // 2. 設定の取得
    $mailAdminAddress = SupabaseClient::getSystemSetting('mail_admin_address', SupabaseConfig::getMailAdminAddress());
    $mailFromAddress = SupabaseClient::getSystemSetting('mail_from_address', SupabaseConfig::getMailFromAddress());
    $mailFromName = SupabaseClient::getSystemSetting('mail_from_name', SupabaseConfig::getMailFromName());
    
    $autoReplySubject = SupabaseClient::getSystemSetting('mail_autoreply_subject', 'お問い合わせありがとうございます');
    $autoReplyHeader = SupabaseClient::getSystemSetting('mail_autoreply_header', "この度は、片山建設工業へお問い合わせいただき、誠にありがとうございます。\n以下の内容でお問い合わせを受け付けいたしました。");
    $autoReplyFooter = SupabaseClient::getSystemSetting('mail_autoreply_footer', "内容を確認の上、担当者より改めてご連絡させていただきます。\n今しばらくお待ちいただけますようお願い申し上げます。\n\n※このメールは自動送信されています。\nお心当たりのない場合は、お手数ですが本メールを破棄してください。\n\n--------------------------------------------------\n片山建設工業\n〒520-2279 滋賀県大津市大石東6丁目6-28\nTEL: 090-5650-1106\nURL: https://katayama-kensetsukougyou.jp\n--------------------------------------------------");

    // エスケープされた改行文字(\\n, \\r)を実際の改行コードに変換
    $autoReplyHeader = str_replace(['\r\n', '\r', '\n'], "\n", $autoReplyHeader);
    $autoReplyFooter = str_replace(['\r\n', '\r', '\n'], "\n", $autoReplyFooter);

    // 3. 管理者への通知メール送信
    $mailSent = false;
    try {
        $mail = new PHPMailer(true);
        
        // サーバー設定
        $mail->isSMTP();
        $mail->Host       = SupabaseConfig::getSmtpHost();
        $mail->SMTPAuth   = true;
        $mail->Username   = SupabaseConfig::getSmtpUser();
        $mail->Password   = SupabaseConfig::getSmtpPass();
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Port 465
        $mail->Port       = SupabaseConfig::getSmtpPort();
        $mail->CharSet    = 'UTF-8';

        // 送信元・宛先
        $mail->setFrom($mailFromAddress, $mailFromName);
        
        // 管理者アドレスはカンマ区切りで複数指定可能
        $adminAddresses = array_map('trim', explode(',', $mailAdminAddress));
        foreach ($adminAddresses as $addr) {
            if (!empty($addr)) {
                $mail->addAddress($addr);
            }
        }

        // コンテンツ
        $mail->isHTML(false);
        $mail->Subject = "【HPよりお問い合わせ】{$name}様より";
        $mailBody = "ホームページより新しいお問い合わせがありました。\n\n";
        $mailBody .= "--------------------------------------------------\n";
        $mailBody .= "お名前: {$name}\n";
        $mailBody .= "会社名: {$company}\n";
        $mailBody .= "メールアドレス: {$email}\n";
        $mailBody .= "電話番号: {$phone}\n";
        $mailBody .= "お問い合わせ種類: {$inquiry_type}\n";
        $mailBody .= "--------------------------------------------------\n\n";
        $mailBody .= "【お問い合わせ内容】\n";
        $mailBody .= $message . "\n\n";
        $mailBody .= "--------------------------------------------------\n";
        $mailBody .= "管理画面で確認: " . SupabaseConfig::getProjectUrl() . "/admin/inquiries/\n";
        
        $mail->Body = $mailBody;

        $mail->send();
        $mailSent = true;

        // 4. ユーザーへの自動返信メール送信
        $mail->clearAddresses(); // 宛先クリア
        $mail->addAddress($email, $name);
        
        $mail->Subject = $autoReplySubject;
        $userMailBody = "{$name} 様\n\n";
        $userMailBody .= $autoReplyHeader . "\n\n";
        $userMailBody .= "--------------------------------------------------\n";
        $userMailBody .= "お問い合わせ種類: {$inquiry_type}\n";
        $userMailBody .= "お問い合わせ内容:\n";
        $userMailBody .= $message . "\n";
        $userMailBody .= "--------------------------------------------------\n\n";
        $userMailBody .= $autoReplyFooter;
        
        $mail->Body = $userMailBody;
        
        $mail->send();
    } catch (Exception $e) {
        // メール送信エラーはログに残すが、処理は続行する
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // 5. LINE Notify通知
    try {
        $lineToken = SupabaseClient::getSystemSetting('line_notify_token', SupabaseConfig::getLineNotifyToken());
        if (!empty($lineToken)) {
            $lineMessage = "\n【HPよりお問い合わせ】\n{$name}様より\n\n{$message}";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/notify');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $lineToken
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $lineMessage]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $lineResult = curl_exec($ch);
            curl_close($ch);
        }
    } catch (\Throwable $e) {
        error_log("LINE Notify Error: " . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'お問い合わせを受け付けました。']);

} catch (\Throwable $e) {
    // その他の致命的エラー
    error_log("An error occurred: " . $e->getMessage());
    // ここに来るのはDB保存前などの致命的エラーのみにする
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'システムエラーが発生しました。']);
}
