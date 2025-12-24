insert into public.news (title, content, excerpt, category, featured_image, published_date, status)
values
('サンプルニュース 1','本文1','抜粋1','お知らせ',null, current_date - interval '1 day','published'),
('サンプルニュース 2','本文2','抜粋2','イベント',null, current_date - interval '2 day','published'),
('サンプルニュース 3','本文3','抜粋3','施工事例',null, current_date - interval '3 day','published'),
('サンプルニュース 4','本文4','抜粋4','コラム',null, current_date - interval '4 day','published'),
('サンプルニュース 5','本文5','抜粋5','お知らせ',null, current_date - interval '5 day','published'),
('サンプルニュース 6','本文6','抜粋6','イベント',null, current_date - interval '6 day','draft'),
('サンプルニュース 7','本文7','抜粋7','施工事例',null, current_date - interval '7 day','published'),
('サンプルニュース 8','本文8','抜粋8','コラム',null, current_date - interval '8 day','published'),
('サンプルニュース 9','本文9','抜粋9','お知らせ',null, current_date - interval '9 day','published'),
('サンプルニュース 10','本文10','抜粋10','イベント',null, current_date - interval '10 day','published'),
('サンプルニュース 11','本文11','抜粋11','施工事例',null, current_date - interval '11 day','published'),
('サンプルニュース 12','本文12','抜粋12','コラム',null, current_date - interval '12 day','published'),
('サンプルニュース 13','本文13','抜粋13','お知らせ',null, current_date - interval '13 day','published'),
('サンプルニュース 14','本文14','抜粋14','イベント',null, current_date - interval '14 day','published'),
('サンプルニュース 15','本文15','抜粋15','施工事例',null, current_date - interval '15 day','draft'),
('サンプルニュース 16','本文16','抜粋16','コラム',null, current_date - interval '16 day','published'),
('サンプルニュース 17','本文17','抜粋17','お知らせ',null, current_date - interval '17 day','published'),
('サンプルニュース 18','本文18','抜粋18','イベント',null, current_date - interval '18 day','published'),
('サンプルニュース 19','本文19','抜粋19','施工事例',null, current_date - interval '19 day','published'),
('サンプルニュース 20','本文20','抜粋20','コラム',null, current_date - interval '20 day','published');

insert into public.works (title, description, category, featured_image, location, completion_date, construction_period, floor_area, status, gallery_images)
values
('施工実績サンプル 1','説明1','Residential','assets/img/works_01.jpg','香川県高松市', current_date - interval '1 month','2ヶ月','80㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 2','説明2','Commercial','assets/img/works_01.jpg','香川県丸亀市', current_date - interval '2 month','3ヶ月','95㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 3','説明3','Public','assets/img/works_01.jpg','香川県三豊市', current_date - interval '3 month','4ヶ月','120㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 4','説明4','Renovation','assets/img/works_01.jpg','香川県善通寺市', current_date - interval '4 month','2ヶ月','88㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 5','説明5','Residential','assets/img/works_01.jpg','香川県坂出市', current_date - interval '5 month','1ヶ月','76㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 6','説明6','Commercial','assets/img/works_01.jpg','香川県観音寺市', current_date - interval '6 month','2ヶ月','101㎡','draft', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 7','説明7','Public','assets/img/works_01.jpg','香川県東かがわ市', current_date - interval '7 month','3ヶ月','140㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 8','説明8','Renovation','assets/img/works_01.jpg','香川県直島町', current_date - interval '8 month','2ヶ月','92㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 9','説明9','Residential','assets/img/works_01.jpg','香川県小豆島町', current_date - interval '9 month','1ヶ月','78㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 10','説明10','Commercial','assets/img/works_01.jpg','香川県宇多津町', current_date - interval '10 month','4ヶ月','160㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 11','説明11','Public','assets/img/works_01.jpg','香川県三木町', current_date - interval '11 month','3ヶ月','135㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']),
('施工実績サンプル 12','説明12','Renovation','assets/img/works_01.jpg','香川県綾川町', current_date - interval '12 month','2ヶ月','90㎡','published', array['assets/img/works_01.jpg','assets/img/works_02.jpg']);

insert into public.services (name, description, status, sort_order)
values
('新築工事','戸建住宅・マンションの新築', 'active', 1),
('リフォーム','内外装の改修', 'active', 2),
('耐震補強','耐震診断・補強工事', 'active', 3),
('外構工事','エクステリア・外構', 'active', 4),
('設備工事','電気・空調・給排水', 'active', 5),
('解体工事','家屋解体', 'active', 6);

insert into public.testimonials (author, content, status)
values
('A様','とても満足しています。', 'active'),
('B様','丁寧な対応でした。', 'active'),
('C様','仕上がりが綺麗でした。', 'active'),
('D様','提案が的確でした。', 'active'),
('E様','また依頼したいです。', 'active');

insert into public.company_stats (label, value, status, sort_order)
values
('年間施工件数','120件','active',1),
('従業員数','45名','active',2),
('創業','1975年','active',3),
('受賞歴','10件','active',4);

insert into public.partners (name, logo_url, status, sort_order)
values
('パートナーA','assets/img/logo_a.png','active',1),
('パートナーB','assets/img/logo_b.png','active',2),
('パートナーC','assets/img/logo_c.png','active',3),
('パートナーD','assets/img/logo_d.png','active',4),
('パートナーE','assets/img/logo_e.png','active',5);

insert into public.site_settings (setting_key, setting_value)
values
('site_name','片山建設工業'),
('contact_phone','087-000-0000'),
('contact_email','info@example.com'),
('address','香川県高松市'),
('hero_message','地域に根ざした施工品質');

