<?php
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
require_once __DIR__ . '/../../lib/SupabaseClient.php';

$serviceKey = SupabaseConfig::getServiceRoleKey();
if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
    echo "SERVICE_ROLE_KEY is not set";
    exit;
}

function trySelect(string $table): bool {
    $res = SupabaseClient::select($table, [], [ 'limit' => 1 ]);
    return $res !== false;
}

function insertBatch(string $table, array $rows): bool {
    $res = SupabaseClient::insert($table, $rows);
    return $res !== false;
}

$tables = ['news','works','services','testimonials','company_stats','partners','site_settings'];
foreach ($tables as $t) {
    if (!trySelect($t)) {
        echo "Missing table: {$t}\n";
    }
}

$news = [
    [
        'title' => '新社屋完成のお知らせ',
        'content' => 'この度、片山建設工業は新社屋が完成いたしましたことをお知らせいたします。新しい環境で、より一層皆様のお役に立てるよう努めてまいります。',
        'category' => 'お知らせ',
        'published_date' => '2023-10-15',
        'status' => 'published'
    ],
    [
        'title' => '秋の住宅見学会開催について',
        'content' => '10月28日（土）、29日（日）に秋の住宅見学会を開催いたします。自然素材を活かした温かみのある住宅をご覧いただけます。事前予約制となっておりますので、お気軽にお問い合わせください。',
        'category' => 'イベント',
        'published_date' => '2023-09-20',
        'status' => 'published'
    ],
    [
        'title' => '夏季休業のお知らせ',
        'content' => '誠に勝手ながら、8月11日（金）から8月16日（水）まで夏季休業とさせていただきます。ご不便をおかけいたしますが、何卒ご了承ください。',
        'category' => 'お知らせ',
        'published_date' => '2023-08-05',
        'status' => 'published'
    ],
    [
        'title' => '新築事例「光と風の家」完成',
        'content' => '○○市での新築住宅「光と風の家」が完成しました。吹き抜けを中心とした明るい空間設計と、自然の風を取り入れる工夫が特徴の住宅です。施工事例に詳細を掲載しましたので、ぜひご覧ください。',
        'excerpt' => '○○市での新築住宅「光と風の家」が完成しました。',
        'category' => '施工事例',
        'published_date' => '2023-07-15',
        'status' => 'published'
    ],
    [
        'title' => 'リノベーションセミナー開催報告',
        'content' => '6月25日に開催いたしましたリノベーションセミナーには多くの方にご参加いただき、誠にありがとうございました。セミナーの様子や質疑応答の内容をまとめました。次回は8月を予定しています。',
        'excerpt' => 'リノベーションセミナーの開催報告をいたします。',
        'category' => 'イベント',
        'published_date' => '2023-06-28',
        'status' => 'published'
    ]
];

$works = [
    [
        'title' => '自然素材の家',
        'description' => '木造2階建て、自然素材を活かした温かみのある住宅',
        'category' => 'Residential',
        'featured_image' => 'assets/img/works_01.jpg',
        'location' => '滋賀県大津市',
        'completion_date' => '2023-08-15',
        'construction_period' => '6ヶ月',
        'floor_area' => '120㎡',
        'status' => 'published'
    ],
    [
        'title' => '古民家カフェ',
        'description' => '古民家を改装したカフェの内装・外装工事',
        'category' => 'Commercial',
        'featured_image' => 'assets/img/works_02.jpg',
        'location' => '滋賀県草津市',
        'completion_date' => '2023-07-10',
        'construction_period' => '4ヶ月',
        'floor_area' => '85㎡',
        'status' => 'published'
    ],
    [
        'title' => '市民ホール改修',
        'description' => '市民ホールの耐震補強及び内装リニューアル',
        'category' => 'Public',
        'featured_image' => 'assets/img/works_03.jpg',
        'location' => '滋賀県大津市',
        'completion_date' => '2023-06-30',
        'construction_period' => '8ヶ月',
        'floor_area' => '800㎡',
        'status' => 'published'
    ],
    [
        'title' => '省エネオフィスビル',
        'description' => '鉄骨3階建て、省エネ設計のオフィスビル',
        'category' => 'Commercial',
        'featured_image' => 'assets/img/works_04.jpg',
        'location' => '滋賀県守山市',
        'completion_date' => '2023-05-25',
        'construction_period' => '10ヶ月',
        'floor_area' => '450㎡',
        'status' => 'published'
    ],
    [
        'title' => 'マンション大規模修繕',
        'description' => '築15年のマンション外壁・共用部分の全面改修',
        'category' => 'Renovation',
        'featured_image' => 'assets/img/works_05.jpg',
        'location' => '滋賀県栗東市',
        'completion_date' => '2023-04-20',
        'construction_period' => '3ヶ月',
        'floor_area' => '1200㎡',
        'status' => 'published'
    ],
    [
        'title' => '都市型コンパクトハウス',
        'description' => '狭小地に建つ3階建て都市型住宅',
        'category' => 'Residential',
        'featured_image' => 'assets/img/works_06.jpg',
        'location' => '東京都',
        'completion_date' => '2021-05-15',
        'construction_period' => '5ヶ月',
        'floor_area' => '90㎡',
        'status' => 'published'
    ],
    [
        'title' => '二世帯住宅',
        'description' => '独立型二世帯住宅、親世帯と子世帯の共存',
        'category' => 'Residential',
        'featured_image' => 'assets/img/works_07.jpg',
        'location' => '神奈川県',
        'completion_date' => '2022-03-20',
        'construction_period' => '7ヶ月',
        'floor_area' => '180㎡',
        'status' => 'published'
    ],
    [
        'title' => 'レストラン内装',
        'description' => '高級イタリアンレストランの内装デザイン・施工',
        'category' => 'Commercial',
        'featured_image' => 'assets/img/works_08.jpg',
        'location' => '東京都',
        'completion_date' => '2022-11-10',
        'construction_period' => '3ヶ月',
        'floor_area' => '120㎡',
        'status' => 'published'
    ],
    [
        'title' => '児童館新築',
        'description' => '木造平屋建て、子どもたちが安心して遊べる空間',
        'category' => 'Public',
        'featured_image' => 'assets/img/works_09.jpg',
        'location' => '埼玉県',
        'completion_date' => '2023-03-30',
        'construction_period' => '8ヶ月',
        'floor_area' => '300㎡',
        'status' => 'published'
    ]
];

$services = [
    [
        'title' => '解体工事一式',
        'description' => '木造、鉄骨造、RC造など、あらゆる構造物の解体工事に対応いたします。',
        'detailed_description' => 'アスベスト除去や内装解体など、特殊な解体作業もお任せください。解体後の廃棄物処理まで一貫して対応可能です。',
        'service_image' => 'assets/img/service_kaitai.jpg',
        'features' => ['木造家屋解体','ビル・マンション解体','店舗・工場解体','内装解体','アスベスト除去'],
        'sort_order' => 1,
        'status' => 'active'
    ],
    [
        'title' => '土工・土木工事一式',
        'description' => '造成工事、基礎工事、外構工事など、土木工事全般を請け負います。',
        'detailed_description' => '小規模な工事から大規模なプロジェクトまで、お客様のニーズに合わせた柔軟な対応が可能です。公共工事の実績も多数ございます。',
        'service_image' => 'assets/img/service_doboku.jpg',
        'features' => ['造成工事','基礎工事','外構工事・エクステリア','擁壁工事','道路工事'],
        'sort_order' => 2,
        'status' => 'active'
    ],
    [
        'title' => '不動産コンサルタント・仲介',
        'description' => '土地や建物の売買、賃貸借に関するご相談を承ります。',
        'detailed_description' => '豊富な情報網と専門知識を活かし、最適な不動産取引をサポート。相続対策や有効活用のご提案もお任せください。',
        'service_image' => 'assets/img/service_fudosan.jpg',
        'features' => ['土地・建物売買仲介','賃貸物件仲介','不動産有効活用コンサルティング','相続対策','資産運用相談'],
        'sort_order' => 3,
        'status' => 'active'
    ],
    [
        'title' => 'リフォーム工事一式',
        'description' => '住宅、マンション、店舗、オフィスなど、あらゆる建物のリフォーム工事に対応します。',
        'detailed_description' => '内装リフォーム、水回りリフォーム、外壁塗装、屋根工事、増改築など、規模の大小を問わずご相談ください。',
        'service_image' => 'assets/img/service_reform.jpg',
        'features' => ['内装リフォーム（クロス張替え、床工事など）','水回りリフォーム（キッチン、浴室、トイレ）','外壁塗装・屋根工事','増改築・間取り変更','耐震補強・バリアフリー化'],
        'sort_order' => 4,
        'status' => 'active'
    ],
    [
        'title' => '建造物解体工事請負業',
        'description' => '建造物の解体工事を専門に請け負っております。',
        'detailed_description' => '近隣への配慮を忘れず、騒音や振動、粉塵対策にも万全を期します。解体後の産業廃棄物の適正処理まで責任を持って対応いたします。',
        'service_image' => 'assets/img/service_kenzokaitai.jpg',
        'features' => ['家屋解体','アパート・マンション解体','工場・倉庫解体','店舗解体','部分解体'],
        'sort_order' => 5,
        'status' => 'active'
    ],
    [
        'title' => '管理業・草刈など',
        'description' => '空き家管理、駐車場管理、敷地内の草刈りなど、不動産の維持管理に関する様々な業務を承ります。',
        'detailed_description' => '遠方にお住まいのオーナー様もご安心ください。きめ細やかな管理サービスで、お客様の大切な資産をお守りします。',
        'service_image' => 'assets/img/service_kanri.jpg',
        'features' => ['空き家管理サービス','駐車場管理','敷地内草刈り・除草作業','樹木伐採・剪定','定期清掃'],
        'sort_order' => 6,
        'status' => 'active'
    ],
    [
        'title' => '内装解体・土工・ハツリ',
        'description' => '店舗の改装やオフィスの移転に伴う内装解体、小規模な土工事、コンクリートのハツリ作業など、専門的な技術を要する作業もお任せください。',
        'detailed_description' => '経験豊富な職人が、安全かつ丁寧に作業を進めます。お客様のご要望に応じて、柔軟に対応いたします。',
        'service_image' => 'assets/img/service_naisoukaitai.jpg',
        'features' => ['店舗・オフィスの原状回復工事','間仕切り撤去','床材・天井材撤去','小規模な掘削・埋め戻し','コンクリート斫り作業'],
        'sort_order' => 7,
        'status' => 'active'
    ],
    [
        'title' => 'ゴミ処理・運搬',
        'description' => '建設現場で発生する産業廃棄物や、一般家庭の粗大ごみなど、様々な種類のゴミの処理・運搬を承ります。',
        'detailed_description' => '分別から収集、運搬、最終処分まで一貫して対応可能です。環境への負荷を低減するため、リサイクル可能なものは積極的に再資源化に努めています。',
        'service_image' => 'assets/img/service_gomishori.jpg',
        'features' => ['産業廃棄物収集運搬','一般廃棄物収集運搬（粗大ごみなど）','解体工事に伴う廃棄物処理','不用品回収','遺品整理'],
        'sort_order' => 8,
        'status' => 'active'
    ]
];

$testimonials = [
    [
        'customer_name' => '佐藤様',
        'customer_initial' => 'S.K',
        'project_type' => '新築住宅',
        'content' => '理想の住まいを実現するためにどんな提案をしていただけるか楽しみにしていましたが、期待以上の素晴らしい住宅に仕上げていただきました。細かい要望にも親身に対応いただき、感謝しています。',
        'rating' => 5,
        'status' => 'active'
    ],
    [
        'customer_name' => '田中様',
        'customer_initial' => 'T.M',
        'project_type' => '店舗リノベーション',
        'content' => '店舗リノベーションを依頼しましたが、予算内で最大限の効果を出す提案をしていただき、オープン後は多くのお客様から好評をいただいています。プロならではの視点に感謝です。',
        'rating' => 5,
        'status' => 'active'
    ],
    [
        'customer_name' => '山田様',
        'customer_initial' => 'Y.N',
        'project_type' => 'オフィス移転',
        'content' => '工期も予算も守りながら、素晴らしい仕上がりのオフィスを作っていただきました。社員からも働きやすくなったと好評です。次のプロジェクトもぜひお願いしたいと思います。',
        'rating' => 5,
        'status' => 'active'
    ]
];

$stats = [
    [ 'stat_name' => '事業実績年数', 'stat_value' => '15', 'stat_unit' => '+', 'description' => '片山建設工業として事業を開始してからの年数', 'sort_order' => 1, 'status' => 'active' ],
    [ 'stat_name' => '施工実績件数', 'stat_value' => '320', 'stat_unit' => '+', 'description' => 'これまでに手がけた施工プロジェクトの総数', 'sort_order' => 2, 'status' => 'active' ],
    [ 'stat_name' => '専門スタッフ', 'stat_value' => '25', 'stat_unit' => '+', 'description' => '経験豊富な専門技術者の人数', 'sort_order' => 3, 'status' => 'active' ],
    [ 'stat_name' => '顧客満足度', 'stat_value' => '98', 'stat_unit' => '%', 'description' => 'お客様アンケートによる満足度評価', 'sort_order' => 4, 'status' => 'active' ]
];

$partners = [
    [ 'company_name' => 'パートナー企業1', 'logo_image' => 'assets/img/partner1.svg', 'website_url' => 'https://example1.com', 'description' => '信頼できるパートナー企業様', 'sort_order' => 1, 'status' => 'active' ],
    [ 'company_name' => 'パートナー企業2', 'logo_image' => 'assets/img/partner2.svg', 'website_url' => 'https://example2.com', 'description' => '技術力の高いパートナー企業様', 'sort_order' => 2, 'status' => 'active' ],
    [ 'company_name' => 'パートナー企業3', 'logo_image' => 'assets/img/partner3.svg', 'website_url' => 'https://example3.com', 'description' => '長年の協力関係にあるパートナー企業様', 'sort_order' => 3, 'status' => 'active' ],
    [ 'company_name' => 'パートナー企業4', 'logo_image' => 'assets/img/partner4.svg', 'website_url' => 'https://example4.com', 'description' => '専門分野でのパートナー企業様', 'sort_order' => 4, 'status' => 'active' ],
    [ 'company_name' => 'パートナー企業5', 'logo_image' => 'assets/img/partner5.svg', 'website_url' => 'https://example5.com', 'description' => '地域密着型のパートナー企業様', 'sort_order' => 5, 'status' => 'active' ]
];

$settings = [
    [ 'setting_key' => 'company_name', 'setting_value' => '片山建設工業', 'setting_type' => 'text', 'description' => '会社名' ],
    [ 'setting_key' => 'company_tagline', 'setting_value' => '伝統と革新で創る、上質な建築の世界', 'setting_type' => 'text', 'description' => '会社のキャッチフレーズ' ],
    [ 'setting_key' => 'company_phone', 'setting_value' => '090-5650-1106', 'setting_type' => 'text', 'description' => '会社電話番号' ],
    [ 'setting_key' => 'company_fax', 'setting_value' => '077-511-9983', 'setting_type' => 'text', 'description' => '会社FAX番号' ],
    [ 'setting_key' => 'company_email', 'setting_value' => 'kkensetsu1106@outlook.jp', 'setting_type' => 'email', 'description' => '会社メールアドレス' ],
    [ 'setting_key' => 'company_address', 'setting_value' => '〒520-2279 滋賀県大津市大石東6丁目6-28', 'setting_type' => 'text', 'description' => '会社住所' ],
    [ 'setting_key' => 'hero_title', 'setting_value' => '伝統と革新で創る、上質な建築の世界', 'setting_type' => 'text', 'description' => 'ヒーローセクションのタイトル' ],
    [ 'setting_key' => 'hero_subtitle', 'setting_value' => '確かな技術と信頼で、皆様の理想を形にします', 'setting_type' => 'text', 'description' => 'ヒーローセクションのサブタイトル' ],
    [ 'setting_key' => 'hero_background_image', 'setting_value' => 'assets/img/hero.jpg', 'setting_type' => 'text', 'description' => 'ヒーローセクションの背景画像' ],
    [ 'setting_key' => 'company_logo', 'setting_value' => 'assets/img/logo.svg', 'setting_type' => 'text', 'description' => '会社ロゴ' ],
    [ 'setting_key' => 'established_year', 'setting_value' => '2008', 'setting_type' => 'text', 'description' => '設立年' ]
];

$ok = true;
$ok = $ok && insertBatch('news', $news);
$ok = $ok && insertBatch('works', $works);
$ok = $ok && insertBatch('services', $services);
$ok = $ok && insertBatch('testimonials', $testimonials);
$ok = $ok && insertBatch('company_stats', $stats);
$ok = $ok && insertBatch('partners', $partners);
$ok = $ok && insertBatch('site_settings', $settings);

echo $ok ? "SEED OK" : "SEED FAILED";