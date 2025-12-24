-- 会社情報テーブルの作成
CREATE TABLE IF NOT EXISTS company_info (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_name TEXT NOT NULL DEFAULT '片山建設工業',
    representative_name TEXT NOT NULL DEFAULT '片山 秀樹',
    representative_title TEXT NOT NULL DEFAULT '代表',
    address_postal TEXT NOT NULL DEFAULT '〒520-2279',
    address_detail TEXT NOT NULL DEFAULT '滋賀県大津市大石東6丁目6-28',
    phone TEXT NOT NULL DEFAULT '090-5650-1106',
    fax TEXT DEFAULT '077-511-9983',
    email TEXT NOT NULL DEFAULT 'kkensetsu1106@outlook.jp',
    registration_number TEXT DEFAULT 'T5810549777522',
    business_description TEXT DEFAULT '総合建設業',
    business_details TEXT[] DEFAULT ARRAY['総合建設業', '解体工事一式', '土工、土木工事一式', '不動産コンサルタント、仲介', 'リフォーム工事一式', '建造物解体工事請負業', '管理業、草刈など', '内装解体、土工、ハツリ', 'ゴミ処理、運搬'],
    licenses TEXT[] DEFAULT ARRAY['建設業許可（詳細確認後、追記予定）'],
    philosophy_title TEXT NOT NULL DEFAULT '伝統と革新で創る、上質な建築の世界',
    philosophy_content TEXT NOT NULL DEFAULT '私たちは、確かな技術と洗練されたデザインで、お客様一人ひとりの想いを形にします。長く愛される建物づくりを通じて、地域社会の発展と豊かな暮らしに貢献することを目指しています。',
    philosophy_items JSONB DEFAULT '[
        {
            "number": "01",
            "title": "確かな技術",
            "description": "創業以来培ってきた確かな技術力を大切にし、安全で快適な建物を提供します。常に最新の技術を学び、品質の向上に努めます。"
        },
        {
            "number": "02", 
            "title": "誠実な対応",
            "description": "お客様との信頼関係を何よりも大切にし、誠実な対応を心がけます。ニーズに真摯に向き合い、最適な提案を行います。"
        },
        {
            "number": "03",
            "title": "地域貢献", 
            "description": "地域社会の一員として、環境に配慮した建築活動を行い、地域の発展に貢献します。次世代に誇れる街づくりを目指します。"
        }
    ]',
    access_info JSONB DEFAULT '{
        "address": "〒123-4567 東京都○○区△△町1-2-3",
        "phone": "03-1234-5678",
        "fax": "03-1234-5679",
        "business_hours": "平日 8:30〜17:30 (土・日・祝日定休)",
        "access": "○○線△△駅から徒歩5分",
        "parking": "駐車場あり",
        "map_embed": ""
    }',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- RLS (Row Level Security) を有効化
ALTER TABLE company_info ENABLE ROW LEVEL SECURITY;

-- 匿名ユーザー（サイト訪問者）への読み取り権限
CREATE POLICY "Allow public read access" ON company_info
    FOR SELECT USING (true);

-- 更新権限（認証済みユーザー）
CREATE POLICY "Allow authenticated users to update" ON company_info
    FOR UPDATE USING (auth.role() = 'authenticated');

-- 更新日時を自動更新する関数
CREATE OR REPLACE FUNCTION update_company_info_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- トリガーの作成
CREATE TRIGGER update_company_info_updated_at_trigger
    BEFORE UPDATE ON company_info
    FOR EACH ROW
    EXECUTE FUNCTION update_company_info_updated_at();

-- 初期データの投入
INSERT INTO company_info (
    company_name,
    representative_name,
    representative_title,
    address_postal,
    address_detail,
    phone,
    fax,
    email,
    registration_number,
    business_description,
    business_details,
    licenses,
    philosophy_title,
    philosophy_content,
    philosophy_items,
    access_info
) VALUES (
    '片山建設工業',
    '片山 秀樹',
    '代表',
    '〒520-2279',
    '滋賀県大津市大石東6丁目6-28',
    '090-5650-1106',
    '077-511-9983',
    'kkensetsu1106@outlook.jp',
    'T5810549777522',
    '総合建設業',
    ARRAY['総合建設業', '解体工事一式', '土工、土木工事一式', '不動産コンサルタント、仲介', 'リフォーム工事一式', '建造物解体工事請負業', '管理業、草刈など', '内装解体、土工、ハツリ', 'ゴミ処理、運搬'],
    ARRAY['建設業許可（詳細確認後、追記予定）'],
    '伝統と革新で創る、上質な建築の世界',
    '私たちは、確かな技術と洗練されたデザインで、お客様一人ひとりの想いを形にします。長く愛される建物づくりを通じて、地域社会の発展と豊かな暮らしに貢献することを目指しています。',
    '[
        {
            "number": "01",
            "title": "確かな技術",
            "description": "創業以来培ってきた確かな技術力を大切にし、安全で快適な建物を提供します。常に最新の技術を学び、品質の向上に努めます。"
        },
        {
            "number": "02", 
            "title": "誠実な対応",
            "description": "お客様との信頼関係を何よりも大切にし、誠実な対応を心がけます。ニーズに真摯に向き合い、最適な提案を行います。"
        },
        {
            "number": "03",
            "title": "地域貢献", 
            "description": "地域社会の一員として、環境に配慮した建築活動を行い、地域の発展に貢献します。次世代に誇れる街づくりを目指します。"
        }
    ]'::jsonb,
    '{
        "address": "〒520-2279 滋賀県大津市大石東6丁目6-28",
        "phone": "090-5650-1106",
        "fax": "077-511-9983",
        "business_hours": "平日 8:30〜17:30 (土・日・祝日定休)",
        "access": "大津駅から車で10分",
        "parking": "駐車場あり",
        "map_embed": ""
    }'::jsonb
);