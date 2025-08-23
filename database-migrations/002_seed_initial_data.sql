-- 初期データ投入SQL
-- 実行前に001_create_website_tables.sqlを実行してください

-- 1. 既存ニュースデータの移行
INSERT INTO news (title, content, category, published_date, status) VALUES
('新社屋完成のお知らせ', 'この度、片山建設工業は新社屋が完成いたしましたことをお知らせいたします。新しい環境で、より一層皆様のお役に立てるよう努めてまいります。', 'お知らせ', '2023-10-15', 'published'),
('秋の住宅見学会開催について', '10月28日（土）、29日（日）に秋の住宅見学会を開催いたします。自然素材を活かした温かみのある住宅をご覧いただけます。事前予約制となっておりますので、お気軽にお問い合わせください。', 'イベント', '2023-09-20', 'published'),
('夏季休業のお知らせ', '誠に勝手ながら、8月11日（金）から8月16日（水）まで夏季休業とさせていただきます。ご不便をおかけいたしますが、何卒ご了承ください。', 'お知らせ', '2023-08-05', 'published');

-- 2. 既存施工実績データの移行
INSERT INTO works (title, description, category, featured_image, location, completion_date, construction_period, floor_area, status) VALUES
('自然素材の家', '木造2階建て、自然素材を活かした温かみのある住宅', 'Residential', 'assets/img/works_01.jpg', '滋賀県大津市', '2023-08-15', '6ヶ月', '120㎡', 'published'),
('古民家カフェ', '古民家を改装したカフェの内装・外装工事', 'Commercial', 'assets/img/works_02.jpg', '滋賀県草津市', '2023-07-10', '4ヶ月', '85㎡', 'published'),
('市民ホール改修', '市民ホールの耐震補強及び内装リニューアル', 'Public', 'assets/img/works_03.jpg', '滋賀県大津市', '2023-06-30', '8ヶ月', '800㎡', 'published'),
('省エネオフィスビル', '鉄骨3階建て、省エネ設計のオフィスビル', 'Commercial', 'assets/img/works_04.jpg', '滋賀県守山市', '2023-05-25', '10ヶ月', '450㎡', 'published'),
('マンション大規模修繕', '築15年のマンション外壁・共用部分の全面改修', 'Renovation', 'assets/img/works_05.jpg', '滋賀県栗東市', '2023-04-20', '3ヶ月', '1200㎡', 'published');

-- 3. サービス内容データの追加
INSERT INTO services (title, description, detailed_description, service_image, features, sort_order, status) VALUES
('解体工事一式', '木造、鉄骨造、RC造など、あらゆる構造物の解体工事に対応いたします。', 'アスベスト除去や内装解体など、特殊な解体作業もお任せください。解体後の廃棄物処理まで一貫して対応可能です。', 'assets/img/service_kaitai.jpg', ARRAY['木造家屋解体', 'ビル・マンション解体', '店舗・工場解体', '内装解体', 'アスベスト除去'], 1, 'active'),
('土工・土木工事一式', '造成工事、基礎工事、外構工事など、土木工事全般を請け負います。', '小規模な工事から大規模なプロジェクトまで、お客様のニーズに合わせた柔軟な対応が可能です。公共工事の実績も多数ございます。', 'assets/img/service_doboku.jpg', ARRAY['造成工事', '基礎工事', '外構工事・エクステリア', '擁壁工事', '道路工事'], 2, 'active'),
('不動産コンサルタント・仲介', '土地や建物の売買、賃貸借に関するご相談を承ります。', '豊富な情報網と専門知識を活かし、最適な不動産取引をサポート。相続対策や有効活用のご提案もお任せください。', 'assets/img/service_fudosan.jpg', ARRAY['土地・建物売買仲介', '賃貸物件仲介', '不動産有効活用コンサルティング', '相続対策', '資産運用相談'], 3, 'active'),
('リフォーム工事一式', '住宅、マンション、店舗、オフィスなど、あらゆる建物のリフォーム工事に対応します。', '内装リフォーム、水回りリフォーム、外壁塗装、屋根工事、増改築など、規模の大小を問わずご相談ください。', 'assets/img/service_reform.jpg', ARRAY['内装リフォーム（クロス張替え、床工事など）', '水回りリフォーム（キッチン、浴室、トイレ）', '外壁塗装・屋根工事', '増改築・間取り変更', '耐震補強・バリアフリー化'], 4, 'active'),
('建造物解体工事請負業', '建造物の解体工事を専門に請け負っております。', '近隣への配慮を忘れず、騒音や振動、粉塵対策にも万全を期します。解体後の産業廃棄物の適正処理まで責任を持って対応いたします。', 'assets/img/service_kenzokaitai.jpg', ARRAY['家屋解体', 'アパート・マンション解体', '工場・倉庫解体', '店舗解体', '部分解体'], 5, 'active'),
('管理業・草刈など', '空き家管理、駐車場管理、敷地内の草刈りなど、不動産の維持管理に関する様々な業務を承ります。', '遠方にお住まいのオーナー様もご安心ください。きめ細やかな管理サービスで、お客様の大切な資産をお守りします。', 'assets/img/service_kanri.jpg', ARRAY['空き家管理サービス', '駐車場管理', '敷地内草刈り・除草作業', '樹木伐採・剪定', '定期清掃'], 6, 'active'),
('内装解体・土工・ハツリ', '店舗の改装やオフィスの移転に伴う内装解体、小規模な土工事、コンクリートのハツリ作業など、専門的な技術を要する作業もお任せください。', '経験豊富な職人が、安全かつ丁寧に作業を進めます。お客様のご要望に応じて、柔軟に対応いたします。', 'assets/img/service_naisoukaitai.jpg', ARRAY['店舗・オフィスの原状回復工事', '間仕切り撤去', '床材・天井材撤去', '小規模な掘削・埋め戻し', 'コンクリート斫り作業'], 7, 'active'),
('ゴミ処理・運搬', '建設現場で発生する産業廃棄物や、一般家庭の粗大ごみなど、様々な種類のゴミの処理・運搬を承ります。', '分別から収集、運搬、最終処分まで一貫して対応可能です。環境への負荷を低減するため、リサイクル可能なものは積極的に再資源化に努めています。', 'assets/img/service_gomishori.jpg', ARRAY['産業廃棄物収集運搬', '一般廃棄物収集運搬（粗大ごみなど）', '解体工事に伴う廃棄物処理', '不用品回収', '遺品整理'], 8, 'active');

-- 4. お客様の声データ
INSERT INTO testimonials (customer_name, customer_initial, project_type, content, rating, status) VALUES
('佐藤様', 'S.K', '新築住宅', '理想の住まいを実現するためにどんな提案をしていただけるか楽しみにしていましたが、期待以上の素晴らしい住宅に仕上げていただきました。細かい要望にも親身に対応いただき、感謝しています。', 5, 'active'),
('田中様', 'T.M', '店舗リノベーション', '店舗リノベーションを依頼しましたが、予算内で最大限の効果を出す提案をしていただき、オープン後は多くのお客様から好評をいただいています。プロならではの視点に感謝です。', 5, 'active'),
('山田様', 'Y.N', 'オフィス移転', '工期も予算も守りながら、素晴らしい仕上がりのオフィスを作っていただきました。社員からも働きやすくなったと好評です。次のプロジェクトもぜひお願いしたいと思います。', 5, 'active');

-- 5. 会社統計数値データ
INSERT INTO company_stats (stat_name, stat_value, stat_unit, description, sort_order, status) VALUES
('事業実績年数', '15', '+', '片山建設工業として事業を開始してからの年数', 1, 'active'),
('施工実績件数', '320', '+', 'これまでに手がけた施工プロジェクトの総数', 2, 'active'),
('専門スタッフ', '25', '+', '経験豊富な専門技術者の人数', 3, 'active'),
('顧客満足度', '98', '%', 'お客様アンケートによる満足度評価', 4, 'active');

-- 6. パートナー企業データ（サンプル）
INSERT INTO partners (company_name, logo_image, website_url, description, sort_order, status) VALUES
('パートナー企業1', 'assets/img/partner1.svg', 'https://example1.com', '信頼できるパートナー企業様', 1, 'active'),
('パートナー企業2', 'assets/img/partner2.svg', 'https://example2.com', '技術力の高いパートナー企業様', 2, 'active'),
('パートナー企業3', 'assets/img/partner3.svg', 'https://example3.com', '長年の協力関係にあるパートナー企業様', 3, 'active'),
('パートナー企業4', 'assets/img/partner4.svg', 'https://example4.com', '専門分野でのパートナー企業様', 4, 'active'),
('パートナー企業5', 'assets/img/partner5.svg', 'https://example5.com', '地域密着型のパートナー企業様', 5, 'active');

-- 7. サイト設定データ
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', '片山建設工業', 'text', '会社名'),
('company_tagline', '伝統と革新で創る、上質な建築の世界', 'text', '会社のキャッチフレーズ'),
('company_phone', '090-5650-1106', 'text', '会社電話番号'),
('company_fax', '077-511-9983', 'text', '会社FAX番号'),
('company_email', 'kkensetsu1106@outlook.jp', 'email', '会社メールアドレス'),
('company_address', '〒520-2279 滋賀県大津市大石東6丁目6-28', 'text', '会社住所'),
('hero_title', '伝統と革新で創る、上質な建築の世界', 'text', 'ヒーローセクションのタイトル'),
('hero_subtitle', '確かな技術と信頼で、皆様の理想を形にします', 'text', 'ヒーローセクションのサブタイトル'),
('hero_background_image', 'assets/img/hero.jpg', 'text', 'ヒーローセクションの背景画像'),
('company_logo', 'assets/img/logo.svg', 'text', '会社ロゴ'),
('established_year', '2008', 'text', '設立年');

-- 8. さらにニュースとワークスを追加してコンテンツを充実
INSERT INTO news (title, content, excerpt, category, published_date, status) VALUES
('新築事例「光と風の家」完成', '○○市での新築住宅「光と風の家」が完成しました。吹き抜けを中心とした明るい空間設計と、自然の風を取り入れる工夫が特徴の住宅です。施工事例に詳細を掲載しましたので、ぜひご覧ください。', '○○市での新築住宅「光と風の家」が完成しました。', '施工事例', '2023-07-15', 'published'),
('リノベーションセミナー開催報告', '6月25日に開催いたしましたリノベーションセミナーには多くの方にご参加いただき、誠にありがとうございました。セミナーの様子や質疑応答の内容をまとめました。次回は8月を予定しています。', 'リノベーションセミナーの開催報告をいたします。', 'イベント', '2023-06-28', 'published');

INSERT INTO works (title, description, category, featured_image, location, completion_date, construction_period, floor_area, status) VALUES
('都市型コンパクトハウス', '狭小地に建つ3階建て都市型住宅', 'Residential', 'assets/img/works_06.jpg', '東京都', '2021-05-15', '5ヶ月', '90㎡', 'published'),
('二世帯住宅', '独立型二世帯住宅、親世帯と子世帯の共存', 'Residential', 'assets/img/works_07.jpg', '神奈川県', '2022-03-20', '7ヶ月', '180㎡', 'published'),
('レストラン内装', '高級イタリアンレストランの内装デザイン・施工', 'Commercial', 'assets/img/works_08.jpg', '東京都', '2022-11-10', '3ヶ月', '120㎡', 'published'),
('児童館新築', '木造平屋建て、子どもたちが安心して遊べる空間', 'Public', 'assets/img/works_09.jpg', '埼玉県', '2023-03-30', '8ヶ月', '300㎡', 'published');

