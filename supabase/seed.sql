-- Seed Company Info
INSERT INTO public.company_info (company_name, representative_title, representative_name, address_postal, address_detail, phone, email, registration_number, business_details, licenses)
VALUES (
    '株式会社 カタヤマ',
    '代表取締役',
    '片山 太郎',
    '〒123-4567',
    '東京都渋谷区〇〇町1-2-3',
    '03-1234-5678',
    'info@example.com',
    'T1234567890123',
    '["土木工事業", "建築工事業", "とび・土工工事業"]',
    '["東京都知事許可 (般-5) 第12345号"]'
);

-- Seed Site Settings
INSERT INTO public.site_settings (setting_key, setting_value, description) VALUES
('company_name', '株式会社 カタヤマ', '会社名'),
('company_phone', '03-1234-5678', '代表電話番号'),
('company_email', 'info@example.com', '代表メールアドレス'),
('company_address', '東京都渋谷区〇〇町1-2-3', '住所'),
('hero_title', '未来を拓く、確かな技術', 'トップページのヒーロータイトル'),
('hero_subtitle', '私たちは地域社会の発展に貢献します', 'トップページのヒーローサブタイトル');

-- Seed Services
INSERT INTO public.services (title, description, icon, status, sort_order) VALUES
('土木工事', '造成・河川などの土木工事', 'civil', 'active', 1),
('建築工事', '住宅・お店の建設', 'building', 'active', 2),
('リフォーム', '住宅リフォーム', 'reform', 'active', 3),
('外構工事', 'エクステリア工事', 'exterior', 'active', 4),
('公共工事', '自治体向け工事', 'public', 'active', 5),
('設備工事', '電気・給排水など', 'facility', 'active', 6);

-- Seed News
INSERT INTO public.news (title, content, category, status, published_date) VALUES
('ホームページをリニューアルしました', '平素は格別のご高配を賜り、厚く御礼申し上げます。この度、弊社ホームページをリニューアルいたしました。', 'お知らせ', 'published', NOW()),
('夏季休業のお知らせ', '誠に勝手ながら、8月13日〜16日は夏季休業とさせていただきます。', 'お知らせ', 'published', NOW() - INTERVAL '1 month');

-- Seed Works
INSERT INTO public.works (title, description, category, status, completion_date, location, featured_image) VALUES
('S様邸 新築工事', '木造2階建て住宅の新築工事を行いました。', '住宅', 'published', NOW() - INTERVAL '2 months', '東京都世田谷区', 'https://placehold.co/800x600?text=House+Build'),
('Kビル 改修工事', 'オフィスビルの外壁改修工事を行いました。', '商業施設', 'published', NOW() - INTERVAL '3 months', '東京都港区', 'https://placehold.co/800x600?text=Building+Renovation');

-- Seed Company Stats
INSERT INTO public.company_stats (stat_name, stat_value, stat_unit, sort_order) VALUES
('創業', '50', '年', 1),
('施工実績', '1000', '件以上', 2),
('有資格者', '20', '名', 3);
