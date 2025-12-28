<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../lib/SupabaseClient.php';
require_once __DIR__ . '/../vendor/autoload.php';

// CORS設定
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// OPTIONSリクエストの処理（プリフライトリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// POSTリクエストのみを許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // JSONデータの取得
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // フォームデータの場合もサポート
        $input = $_POST;
    }
    
    // 必須項目の検証
    $requiredFields = ['name', 'email', 'inquiry_type', 'message'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '必須項目が不足しています',
            'missing_fields' => $missingFields
        ]);
        exit();
    }
    
    // メールアドレスの検証
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '有効なメールアドレスを入力してください'
        ]);
        exit();
    }
    
    // 電話番号の検証（提供されている場合）
    if (!empty($input['phone']) && !preg_match('/^[0-9\-\(\)\+\s]+$/', $input['phone'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '有効な電話番号を入力してください'
        ]);
        exit();
    }
    
    // データの準備
    $inquiryData = [
        'name' => htmlspecialchars(strip_tags($input['name']), ENT_QUOTES, 'UTF-8'),
        'company' => !empty($input['company']) ? htmlspecialchars(strip_tags($input['company']), ENT_QUOTES, 'UTF-8') : null,
        'email' => htmlspecialchars($input['email'], ENT_QUOTES, 'UTF-8'),
        'phone' => !empty($input['phone']) ? htmlspecialchars(strip_tags($input['phone']), ENT_QUOTES, 'UTF-8') : null,
        'inquiry_type' => htmlspecialchars(strip_tags($input['inquiry_type']), ENT_QUOTES, 'UTF-8'),
        'message' => htmlspecialchars(strip_tags($input['message']), ENT_QUOTES, 'UTF-8'),
        'status' => 'new',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
    
    // Supabaseにデータを挿入
    $result = SupabaseClient::insert('inquiries', $inquiryData);
    
    if ($result) {
        // メール通知の送信（SMTP）
        try {
            require_once __DIR__ . '/../config/supabase.secrets.php';

            // PHPMailerの読み込み
            // 自社サーバーのSMTP情報
            $smtpHost = SupabaseConfig::getSmtpHost();
            $smtpPort = SupabaseConfig::getSmtpPort();
            $smtpUser = SupabaseConfig::getSmtpUser();
            $smtpPass = SupabaseConfig::getSmtpPass();
            $mailFrom = SupabaseConfig::getMailFromAddress();
            $mailFromName = SupabaseConfig::getMailFromName();
            $mailTo = SupabaseConfig::getMailAdminAddress();
            $lineToken = SupabaseConfig::getLineNotifyToken();

            // 1. 管理者へのメール通知 (PHPMailer)
            $mailAdmin = new PHPMailer(true);
            try {
                // サーバー設定
                $mailAdmin->isSMTP();
                $mailAdmin->Host       = $smtpHost;
                $mailAdmin->SMTPAuth   = true;
                $mailAdmin->Username   = $smtpUser;
                $mailAdmin->Password   = $smtpPass;
                $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // ポート465の場合はSMTPS
                $mailAdmin->Port       = $smtpPort;
                $mailAdmin->CharSet    = 'UTF-8';

                // 受信者設定
                $mailAdmin->setFrom($mailFrom, $mailFromName);
                $mailAdmin->addAddress($mailTo);
                $mailAdmin->addReplyTo($inquiryData['email'], $inquiryData['name']);

                // コンテンツ
                $mailAdmin->isHTML(false);
                $mailAdmin->Subject = '【片山建設工業】新規お問い合わせがありました';
                $mailAdmin->Body    = <<<EOT
新規のお問い合わせがありました。

お名前: {$inquiryData['name']}
会社名: {$inquiryData['company']}
メールアドレス: {$inquiryData['email']}
電話番号: {$inquiryData['phone']}
お問い合わせ種別: {$inquiryData['inquiry_type']}

お問い合わせ内容:
{$inquiryData['message']}

管理画面で確認してください。
EOT;

                $mailAdmin->send();
                error_log('管理者メール送信成功: ' . $mailTo);
            } catch (Exception $e) {
                error_log('管理者メール送信失敗: ' . $mailAdmin->ErrorInfo);
            }

            // 2. LINE通知 (トークンが設定されている場合のみ)
            if (!empty($lineToken)) {
                $lineMessage = "\n新規お問い合わせがありました。\n\n" .
                               "お名前: {$inquiryData['name']}\n" .
                               "種別: {$inquiryData['inquiry_type']}\n\n" .
                               "内容:\n{$inquiryData['message']}";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/notify');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $lineToken
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $lineMessage]));
                
                $lineResult = curl_exec($ch);
                $lineStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($lineStatus !== 200) {
                    error_log('LINE通知送信失敗: Status ' . $lineStatus . ', Response: ' . $lineResult);
                } else {
                    error_log('LINE通知送信成功');
                }
            }

            // 3. お客様への自動返信メール (PHPMailer)
            $mailCustomer = new PHPMailer(true);
            try {
                // サーバー設定
                $mailCustomer->isSMTP();
                $mailCustomer->Host       = $smtpHost;
                $mailCustomer->SMTPAuth   = true;
                $mailCustomer->Username   = $smtpUser;
                $mailCustomer->Password   = $smtpPass;
                $mailCustomer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mailCustomer->Port       = $smtpPort;
                $mailCustomer->CharSet    = 'UTF-8';

                // 受信者設定
                $mailCustomer->setFrom($mailFrom, $mailFromName);
                $mailCustomer->addAddress($inquiryData['email'], $inquiryData['name']);
                $mailCustomer->addReplyTo($mailFrom, $mailFromName);

                // コンテンツ
                $mailCustomer->isHTML(false);
                $mailCustomer->Subject = '【片山建設工業】お問い合わせありがとうございます';
                $mailCustomer->Body    = <<<EOT
{$inquiryData['name']} 様

この度は、片山建設工業へお問い合わせいただき、誠にありがとうございます。
以下の内容でお問い合わせを受け付けいたしました。

--------------------------------------------------
お名前: {$inquiryData['name']}
お問い合わせ種別: {$inquiryData['inquiry_type']}
お問い合わせ内容:
{$inquiryData['message']}
--------------------------------------------------

内容を確認の上、担当者より改めてご連絡させていただきます。
今しばらくお待ちいただけますようお願い申し上げます。

※このメールは自動送信されています。
お心当たりのない場合は、お手数ですが本メールを破棄してください。

--------------------------------------------------
株式会社 片山建設工業
〒000-0000 住所が入ります
TEL: 00-0000-0000
URL: https://katayama-construction.co.jp
--------------------------------------------------
EOT;

                $mailCustomer->send();
                error_log('自動返信メール送信成功: ' . $inquiryData['email']);
            } catch (Exception $e) {
                error_log('自動返信メール送信失敗: ' . $mailCustomer->ErrorInfo);
            }

            // 3. LINE Notify通知
            $lineToken = SupabaseConfig::getLineNotifyToken();
            if (!empty($lineToken)) {
                $lineMessage = "\n【新規お問い合わせ】\n"
                    . "お名前: {$inquiryData['name']}\n"
                    . "種別: {$inquiryData['inquiry_type']}\n"
                    . "内容: " . mb_strimwidth($inquiryData['message'], 0, 100, '...');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://notify-api.line.me/api/notify');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $lineToken
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $lineMessage]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $lineResult = curl_exec($ch);
                curl_close($ch);
                
                error_log('LINE通知結果: ' . $lineResult);
            }
            
        } catch (Exception $e) {
            // メール送信エラーは無視して続行
            error_log('メール送信エラー: ' . $e->getMessage());
        }
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'お問い合わせを受け付けました。担当者よりご連絡いたします。',
            'data' => [
                'id' => $result['id'] ?? null,
                'name' => $inquiryData['name']
            ]
        ]);
        
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'お問い合わせの送信に失敗しました。しばらく経ってからもう一度お試しください。'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Inquiry submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'サーバーエラーが発生しました。しばらく経ってからもう一度お試しください。'
    ]);
}
?>