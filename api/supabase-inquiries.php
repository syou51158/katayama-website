<?php
require_once __DIR__ . '/../lib/SupabaseClient.php';

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
        // メール通知の送信（オプション）
        try {
            $to = 'kkensetsu1106@outlook.jp'; // 管理者のメールアドレス
            $subject = '【片山建設工業】新規お問い合わせがありました';
            $message = <<<EOT
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
            
            $headers = [
                'From' => 'noreply@katayama-construction.co.jp',
                'Reply-To' => $inquiryData['email'],
                'Content-Type' => 'text/plain; charset=UTF-8'
            ];
            
            // メール送信（本番環境でのみ有効）
            // mail($to, $subject, $message, $headers);
            
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