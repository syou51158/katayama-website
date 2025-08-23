-- ウェブサイト用テーブル作成マイグレーション
-- 実行環境: Supabase Database (iygiuutslpnvrheqbqgv)

-- 1. ニュース・お知らせテーブル
CREATE TABLE IF NOT EXISTS news (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title TEXT NOT NULL,
    content TEXT,
    excerpt TEXT,
    category TEXT DEFAULT 'お知らせ',
    featured_image TEXT,
    published_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status TEXT DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 2. 施工実績テーブル
CREATE TABLE IF NOT EXISTS works (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title TEXT NOT NULL,
    description TEXT,
    category TEXT NOT NULL,
    featured_image TEXT,
    location TEXT,
    completion_date DATE,
    construction_period TEXT,
    floor_area TEXT,
    client_name TEXT,
    tags TEXT[],
    status TEXT DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 3. サービス内容テーブル
CREATE TABLE IF NOT EXISTS services (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title TEXT NOT NULL,
    description TEXT,
    detailed_description TEXT,
    service_image TEXT,
    icon TEXT,
    features TEXT[],
    sort_order INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 4. お客様の声テーブル
CREATE TABLE IF NOT EXISTS testimonials (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_name TEXT NOT NULL,
    customer_initial TEXT,
    project_type TEXT,
    content TEXT NOT NULL,
    rating INTEGER DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    featured_image TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 5. 会社統計数値テーブル
CREATE TABLE IF NOT EXISTS company_stats (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    stat_name TEXT NOT NULL,
    stat_value TEXT NOT NULL,
    stat_unit TEXT,
    description TEXT,
    icon TEXT,
    sort_order INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 6. パートナー企業テーブル
CREATE TABLE IF NOT EXISTS partners (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_name TEXT NOT NULL,
    logo_image TEXT,
    website_url TEXT,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 7. サイト設定テーブル
CREATE TABLE IF NOT EXISTS site_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type TEXT DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- 更新時刻を自動更新するトリガー関数
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- 各テーブルにトリガーを設定
CREATE TRIGGER update_news_updated_at 
    BEFORE UPDATE ON news 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_works_updated_at 
    BEFORE UPDATE ON works 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_services_updated_at 
    BEFORE UPDATE ON services 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_testimonials_updated_at 
    BEFORE UPDATE ON testimonials 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_company_stats_updated_at 
    BEFORE UPDATE ON company_stats 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_partners_updated_at 
    BEFORE UPDATE ON partners 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

CREATE TRIGGER update_site_settings_updated_at 
    BEFORE UPDATE ON site_settings 
    FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

-- RLS（Row Level Security）ポリシー設定
ALTER TABLE news ENABLE ROW LEVEL SECURITY;
ALTER TABLE works ENABLE ROW LEVEL SECURITY;
ALTER TABLE services ENABLE ROW LEVEL SECURITY;
ALTER TABLE testimonials ENABLE ROW LEVEL SECURITY;
ALTER TABLE company_stats ENABLE ROW LEVEL SECURITY;
ALTER TABLE partners ENABLE ROW LEVEL SECURITY;
ALTER TABLE site_settings ENABLE ROW LEVEL SECURITY;

-- パブリック読み取り許可ポリシー
CREATE POLICY "Enable read access for all users" ON news FOR SELECT USING (status = 'published');
CREATE POLICY "Enable read access for all users" ON works FOR SELECT USING (status = 'published');
CREATE POLICY "Enable read access for all users" ON services FOR SELECT USING (status = 'active');
CREATE POLICY "Enable read access for all users" ON testimonials FOR SELECT USING (status = 'active');
CREATE POLICY "Enable read access for all users" ON company_stats FOR SELECT USING (status = 'active');
CREATE POLICY "Enable read access for all users" ON partners FOR SELECT USING (status = 'active');
CREATE POLICY "Enable read access for all users" ON site_settings FOR SELECT USING (true);

-- 管理者権限でのCUD操作ポリシー（認証済みユーザー）
CREATE POLICY "Enable all access for authenticated users" ON news FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON works FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON services FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON testimonials FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON company_stats FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON partners FOR ALL USING (auth.role() = 'authenticated');
CREATE POLICY "Enable all access for authenticated users" ON site_settings FOR ALL USING (auth.role() = 'authenticated');


