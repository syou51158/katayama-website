CREATE TABLE IF NOT EXISTS system_settings (
    key TEXT PRIMARY KEY,
    value TEXT,
    description TEXT,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- RLS設定
ALTER TABLE system_settings ENABLE ROW LEVEL SECURITY;

-- 誰でも参照可能（APIキー必要）
CREATE POLICY "Allow public select" ON system_settings FOR SELECT USING (true);

-- 編集は認証済みユーザーのみ
CREATE POLICY "Allow authenticated update" ON system_settings FOR UPDATE USING (auth.role() = 'authenticated');
CREATE POLICY "Allow authenticated insert" ON system_settings FOR INSERT WITH CHECK (auth.role() = 'authenticated');

-- 初期データ挿入
INSERT INTO system_settings (key, value, description) VALUES
('mail_admin_address', 'kkensetsu1106@outlook.jp', '管理者通知先メールアドレス'),
('mail_from_address', 'info@katayama-kensetsukougyou.jp', '送信元メールアドレス'),
('mail_from_name', '片山建設工業', '送信元名'),
('line_notify_token', '', 'LINE Notifyトークン'),
('mail_autoreply_subject', '【片山建設工業】お問い合わせありがとうございます', '自動返信メールの件名'),
('mail_autoreply_header', 'この度は、片山建設工業へお問い合わせいただき、誠にありがとうございます。\n以下の内容でお問い合わせを受け付けいたしました。', '自動返信メールの本文ヘッダー'),
('mail_autoreply_footer', '内容を確認の上、担当者より改めてご連絡させていただきます。\n今しばらくお待ちいただけますようお願い申し上げます。\n\n※このメールは自動送信されています。\nお心当たりのない場合は、お手数ですが本メールを破棄してください。\n\n--------------------------------------------------\n株式会社 片山建設工業\n〒000-0000 住所が入ります\nTEL: 00-0000-0000\nURL: https://katayama-construction.co.jp\n--------------------------------------------------', '自動返信メールの本文フッター')
ON CONFLICT (key) DO NOTHING;
