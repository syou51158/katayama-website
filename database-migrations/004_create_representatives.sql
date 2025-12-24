-- 代表情報テーブル作成
CREATE TABLE IF NOT EXISTS representatives (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name TEXT NOT NULL,
    position TEXT NOT NULL DEFAULT '代表取締役',
    photo_url TEXT,
    greeting_title TEXT NOT NULL DEFAULT 'ごあいさつ',
    greeting_content TEXT NOT NULL,
    biography JSONB, -- 経歴をJSON配列で保存 [{"year": "1975", "event": "滋賀県生まれ"}, ...]
    qualifications TEXT[], -- 資格一覧
    signature_url TEXT,
    sort_order INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 更新時刻トリガー
CREATE TRIGGER update_representatives_updated_at 
    BEFORE UPDATE ON representatives 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

-- RLS 設定
ALTER TABLE representatives ENABLE ROW LEVEL SECURITY;
CREATE POLICY "Enable read access for all users" ON representatives FOR SELECT USING (status = 'active');
CREATE POLICY "Enable all access for authenticated users" ON representatives FOR ALL USING (auth.role() = 'authenticated');

-- サンプルデータ投入（片山秀樹代表）
INSERT INTO representatives (
    name, position, photo_url, greeting_title, greeting_content, biography, qualifications, signature_url, status, sort_order
) VALUES (
    '片山 秀樹',
    '代表取締役',
    'assets/img/president.jpg',
    'ごあいさつ',
    '片山建設工業をご覧いただき、誠にありがとうございます。代表の片山秀樹と申します。

私は地元の建設会社に就職後、10年間現場監督として経験を積み、その後独立して片山建設工業を創業いたしました。創業から15年、地域の皆様に支えられながら、住宅建築から商業施設、公共工事まで幅広い建設サービスを提供してまいりました。

当社の理念は「地域と共に歩む」こと。安全で快適な暮らしの場を創ることはもちろん、地域の発展に貢献することを常に心がけています。建物は長く使われるものですから、お客様の想いをしっかりと受け止め、未来を見据えた提案を行うことが私たちの使命だと考えています。

これからも確かな技術と誠実な対応で、皆様の暮らしや事業の基盤となる建物づくりをサポートしてまいります。どうぞよろしくお願いいたします。',
    '[
        {"year": "1975", "event": "滋賀県生まれ"},
        {"year": "1998", "event": "○○大学工学部建築学科卒業"},
        {"year": "1998", "event": "株式会社△△建設入社"},
        {"year": "2003", "event": "一級建築士資格取得"},
        {"year": "2008", "event": "片山建設工業として独立"},
        {"year": "2015", "event": "□□建設協会理事就任"},
        {"year": "2020", "event": "地域貢献賞受賞"}
    ]'::jsonb,
    ARRAY['一級建築士', '一級建築施工管理技士', '宅地建物取引士'],
    'assets/img/signature.png',
    'active',
    1
);

-- 他の代表（副代表など）がいれば追加
-- INSERT INTO representatives (name, position, photo_url, greeting_title, greeting_content, biography, qualifications, signature_url, status, sort_order)
-- VALUES (...);
