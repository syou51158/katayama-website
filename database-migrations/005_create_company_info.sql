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
    '〒520-2264',
    '滋賀県大津市大石東6丁目6-28',
    '090-5650-1106',
    '077-511-9983',
    'kkensetsu1106@outlook.jp',
    'T5810 5497 77522',
    '総合建設業',
    ARRAY[
        '解体工事：住宅、店舗、施設などの全解体・部分解体を、安全かつスピーディーに実施します。',
        'リフォーム工事：住まいや店舗のリノベーション、部分リフォームから全面改修まで幅広く対応します。',
        '不動産コンサルタント・売買サポート：土地、戸建て、マンション、店舗物件などの売買や賃貸をサポートし、最適な資産活用を実現します。',
        '空き家管理・土地活用：空き家問題の解決に向けた管理サービスや、専門的な知見に基づく土地活用コンサルティング（無料診断あり）を行います。',
        '補助金申請支援：解体やリフォーム、省エネ等に関する行政の補助金活用をサポートします。'
    ],
    ARRAY[
        '解体工事業者登録：滋賀県知事（解ー7）第1063号',
        '解体工事業者登録：京都府知事（登ー7）第00ー157号'
    ],
    '信頼と丁寧で創る、地域の未来',
    '「大津地域ナンバーワンの解体×リフォーム×不動産ワンストップ企業」としてのポジションを確立し、空き家問題などの社会的課題に正面から向き合います。単なる業務の遂行ではなく、「信頼」と「丁寧」を柱に、地域の人々とのつながりを深め、地域の価値そのものを高める未来を創造することを目指しています。',
    '[
        {
            "number": "01",
            "title": "ワンストップ解決",
            "description": "解体からリフォーム、売却、活用提案まで社内で一括対応するため、お客様が複数の業者とやり取りする負担を最小限に抑えます。"
        },
        {
            "number": "02",
            "title": "圧倒的なスピードと柔軟性",
            "description": "現地調査から見積り、着工まで「待たせない」対応を徹底し、急ぎの案件にも即応します。"
        },
        {
            "number": "03",
            "title": "リアルな問題解決力",
            "description": "代表自身の豊富な現場経験と営業経験を活かし、お客様の不安に寄り添った最適なプランを最短ルートで提案します。"
        }
    ]'::jsonb,
    '{
        "address": "〒520-2264 滋賀県大津市大石東6丁目6-28",
        "phone": "090-5650-1106",
        "fax": "077-511-9983",
        "business_hours": "平日 8:30〜17:30 (土・日・祝日定休)",
        "access": "○○線△△駅から徒歩5分",
        "parking": "駐車場あり",
        "map_embed": ""
    }'::jsonb