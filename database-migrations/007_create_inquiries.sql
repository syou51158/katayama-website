-- お問い合わせテーブルの作成
CREATE TABLE IF NOT EXISTS inquiries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name TEXT NOT NULL,
    company TEXT,
    email TEXT NOT NULL,
    phone TEXT,
    inquiry_type TEXT NOT NULL,
    message TEXT NOT NULL,
    status TEXT DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied', 'closed')),
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- RLS（Row Level Security）の有効化
ALTER TABLE inquiries ENABLE ROW LEVEL SECURITY;

-- 匿名ユーザーの読み取り権限（必要に応じて）
CREATE POLICY "Allow anonymous read" ON inquiries
    FOR SELECT USING (false); -- 基本的に読み取り不可

-- 匿名ユーザーの作成権限
CREATE POLICY "Allow anonymous insert" ON inquiries
    FOR INSERT WITH CHECK (true);

-- 更新・削除権限（管理者のみ）
CREATE POLICY "Allow authenticated full access" ON inquiries
    FOR ALL USING (auth.role() = 'authenticated');

-- updated_atトリガーの作成
CREATE OR REPLACE FUNCTION update_inquiries_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_inquiries_updated_at
    BEFORE UPDATE ON inquiries
    FOR EACH ROW
    EXECUTE FUNCTION update_inquiries_updated_at();

-- インデックスの作成
CREATE INDEX idx_inquiries_created_at ON inquiries(created_at DESC);
CREATE INDEX idx_inquiries_status ON inquiries(status);
CREATE INDEX idx_inquiries_inquiry_type ON inquiries(inquiry