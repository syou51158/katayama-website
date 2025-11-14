-- 会社沿革テーブルの作成
CREATE TABLE IF NOT EXISTS company_history (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    year INTEGER NOT NULL,
    month INTEGER,
    title TEXT NOT NULL,
    description TEXT,
    details TEXT[],
    sort_order INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- RLS (Row Level Security) を有効化
ALTER TABLE company_history ENABLE ROW LEVEL SECURITY;

-- 匿名ユーザー（サイト訪問者）への読み取り権限
CREATE POLICY "Allow public read access" ON company_history
    FOR SELECT USING (status = 'active');

-- 更新権限（認証済みユーザー）
CREATE POLICY "Allow authenticated users to update" ON company_history
    FOR UPDATE USING (auth.role() = 'authenticated');

-- 更新日時を自動更新する関数
CREATE OR REPLACE FUNCTION update_company_history_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- トリガーの作成
CREATE TRIGGER update_company_history_updated_at_trigger
    BEFORE UPDATE ON company_history
    FOR EACH ROW
    EXECUTE FUNCTION update_company_history_updated_at();

-- 初期データの投入
INSERT INTO company_history (year, month, title, description, details, sort_order) VALUES
(2008, 4, '片山建設工業株式会社設立', '資本金1,000万円で創業', ARRAY['片山建設工業株式会社設立（資本金1,000万円）', '滋賀県大津市に本社を設立'], 1),
(2010, 6, '資本金増資と建設業許可取得', '事業拡大に向けて体質強化', ARRAY['資本金を2,000万円に増資', '一般建設業許可取得'], 2),
(2012, 5, '宅地建物取引業免許取得', '不動産事業への参入', ARRAY['宅地建物取引業免許取得'], 3),
(2015, 10, '特定建設業許可取得と協会加入', '業界での地位確立', ARRAY['特定建設業許可取得', '資本金を3,000万円に増資', '○○建設協会加入'], 4),
(2017, 3, '施工実績100件突破', '着実な成長と実績拡大', ARRAY['創業以来の施工実績が100件を突破', '△△地域優良建設企業として表彰'], 5),
(2020, 9, '環境配慮型建築で評価受賞', '持続可能な建築への取り組み', ARRAY['環境配慮型建築への取り組みが評価され、○○賞受賞', 'ISO9001（品質マネジメントシステム）認証取得'], 6),
(2022, 4, '施工実績300件突破と財団設立', '大きな節目と社会貢献', ARRAY['創業以来の施工実績が300件を突破', '地域貢献活動の一環として△△財団を設立'], 7),
(2023, 10, '新社屋完成と15周年', '新たなスタート', ARRAY['新社屋完成・移転', '創業15周年記念式典開催'], 8);