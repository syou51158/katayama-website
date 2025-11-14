/**
 * モックAPIレスポンス - デモ用
 * PHPサーバーが利用できない場合の代替として使用
 */

// ニュースのモックデータ
const mockNewsData = {
    success: true,
    data: [
        {
            id: "1",
            title: "新社屋完成のお知らせ",
            content: "この度、片山建設工業は新社屋が完成いたしましたことをお知らせいたします。新しい環境で、より一層皆様のお役に立てるよう努めてまいります。",
            category: "お知らせ",
            published_date: "2023-10-15",
            status: "published",
            created_at: "2023-10-15T00:00:00Z"
        },
        {
            id: "2",
            title: "秋の住宅見学会開催について",
            content: "10月28日（土）、29日（日）に秋の住宅見学会を開催いたします。自然素材を活かした温かみのある住宅をご覧いただけます。",
            category: "イベント",
            published_date: "2023-09-20",
            status: "published",
            created_at: "2023-09-20T00:00:00Z"
        },
        {
            id: "3",
            title: "夏季休業のお知らせ",
            content: "誠に勝手ながら、8月11日（金）から8月16日（水）まで夏季休業とさせていただきます。",
            category: "お知らせ",
            published_date: "2023-08-05",
            status: "published",
            created_at: "2023-08-05T00:00:00Z"
        }
    ]
};

// パートナー企業のモックデータ
const mockPartnersData = {
    success: true,
    data: [
        {
            id: "1",
            company_name: "パートナー企業1",
            logo_image: "assets/img/partner1.svg",
            website_url: "https://example1.com",
            description: "信頼できるパートナー企業様",
            sort_order: 1,
            status: "active"
        },
        {
            id: "2", 
            company_name: "パートナー企業2",
            logo_image: "assets/img/partner2.svg",
            website_url: "https://example2.com",
            description: "技術力の高いパートナー企業様",
            sort_order: 2,
            status: "active"
        },
        {
            id: "3",
            company_name: "パートナー企業3", 
            logo_image: "assets/img/partner3.svg",
            website_url: "https://example3.com",
            description: "長年の協力関係にあるパートナー企業様",
            sort_order: 3,
            status: "active"
        },
        {
            id: "4",
            company_name: "パートナー企業4",
            logo_image: "assets/img/partner4.svg", 
            website_url: "https://example4.com",
            description: "専門分野でのパートナー企業様",
            sort_order: 4,
            status: "active"
        },
        {
            id: "5",
            company_name: "パートナー企業5",
            logo_image: "assets/img/partner5.svg",
            website_url: "https://example5.com", 
            description: "地域密着型のパートナー企業様",
            sort_order: 5,
            status: "active"
        }
    ],
    count: 5
};

// お客様の声のモックデータ
const mockTestimonialsData = {
    success: true,
    data: [
        {
            id: "1",
            customer_name: "佐藤様",
            customer_initial: "S.K",
            project_type: "新築住宅",
            content: "理想の住まいを実現するためにどんな提案をしていただけるか楽しみにしていましたが、期待以上の素晴らしい住宅に仕上げていただきました。細かい要望にも親身に対応いただき、感謝しています。",
            rating: 5,
            status: "active"
        },
        {
            id: "2",
            customer_name: "田中様",
            customer_initial: "T.M",
            project_type: "店舗リノベーション",
            content: "店舗リノベーションを依頼しましたが、予算内で最大限の効果を出す提案をしていただき、オープン後は多くのお客様から好評をいただいています。プロならではの視点に感謝です。",
            rating: 5,
            status: "active"
        },
        {
            id: "3",
            customer_name: "山田様",
            customer_initial: "Y.N",
            project_type: "オフィス移転",
            content: "工期も予算も守りながら、素晴らしい仕上がりのオフィスを作っていただきました。社員からも働きやすくなったと好評です。次のプロジェクトもぜひお願いしたいと思います。",
            rating: 5,
            status: "active"
        }
    ]
};

// 会社統計のモックデータ
const mockStatsData = {
    success: true,
    data: [
        {
            id: "1",
            stat_name: "事業実績年数",
            stat_value: "15",
            stat_unit: "+",
            description: "片山建設工業として事業を開始してからの年数",
            sort_order: 1,
            status: "active"
        },
        {
            id: "2",
            stat_name: "施工実績件数",
            stat_value: "320",
            stat_unit: "+",
            description: "これまでに手がけた施工プロジェクトの総数",
            sort_order: 2,
            status: "active"
        },
        {
            id: "3",
            stat_name: "専門スタッフ",
            stat_value: "25",
            stat_unit: "+",
            description: "経験豊富な専門技術者の人数",
            sort_order: 3,
            status: "active"
        },
        {
            id: "4",
            stat_name: "顧客満足度",
            stat_value: "98",
            stat_unit: "%",
            description: "お客様アンケートによる満足度評価",
            sort_order: 4,
            status: "active"
        }
    ]
};

// 施工実績のモックデータ
const mockWorksData = {
    success: true,
    data: [
        {
            id: "1",
            title: "自然素材の家",
            description: "木造2階建て、自然素材を活かした温かみのある住宅",
            category: "Residential",
            featured_image: "assets/img/works_01.jpg",
            location: "滋賀県大津市",
            completion_date: "2023-08-15",
            construction_period: "6ヶ月",
            floor_area: "120㎡",
            status: "published"
        },
        {
            id: "2",
            title: "古民家カフェ",
            description: "古民家を改装したカフェの内装・外装工事",
            category: "Commercial",
            featured_image: "assets/img/works_02.jpg",
            location: "滋賀県草津市",
            completion_date: "2023-07-10",
            construction_period: "4ヶ月",
            floor_area: "85㎡",
            status: "published"
        },
        {
            id: "3",
            title: "市民ホール改修",
            description: "市民ホールの耐震補強及び内装リニューアル",
            category: "Public",
            featured_image: "assets/img/works_03.jpg",
            location: "滋賀県大津市",
            completion_date: "2023-06-30",
            construction_period: "8ヶ月",
            floor_area: "800㎡",
            status: "published"
        },
        {
            id: "4",
            title: "省エネオフィスビル",
            description: "鉄骨3階建て、省エネ設計のオフィスビル",
            category: "Commercial",
            featured_image: "assets/img/works_04.jpg",
            location: "滋賀県守山市",
            completion_date: "2023-05-25",
            construction_period: "10ヶ月",
            floor_area: "450㎡",
            status: "published"
        },
        {
            id: "5",
            title: "マンション大規模修繕",
            description: "築15年のマンション外壁・共用部分の全面改修",
            category: "Renovation",
            featured_image: "assets/img/works_05.jpg",
            location: "滋賀県栗東市",
            completion_date: "2023-04-20",
            construction_period: "3ヶ月",
            floor_area: "1200㎡",
            status: "published"
        }
    ]
};

// サイト設定のモックデータ
const mockSiteSettingsData = {
    success: true,
    data: {
        company_name: "片山建設工業",
        company_tagline: "伝統と革新で創る、上質な建築の世界",
        company_phone: "090-5650-1106",
        company_fax: "077-511-9983",
        company_email: "kkensetsu1106@outlook.jp",
        company_address: "〒520-2279 滋賀県大津市大石東6丁目6-28",
        hero_title: "伝統と革新で創る、上質な建築の世界",
        hero_subtitle: "確かな技術と信頼で、皆様の理想を形にします",
        hero_background_image: "assets/img/hero.jpg",
        company_logo: "assets/img/logo.svg",
        established_year: "2008"
    }
};

// モックAPIレスポンスを返す関数
function getMockApiResponse(endpoint) {
    return new Promise((resolve) => {
        setTimeout(() => {
            switch(endpoint) {
                case 'supabase-news.php':
                    resolve(mockNewsData);
                    break;
                case 'supabase-partners.php':
                    resolve(mockPartnersData);
                    break;
                case 'supabase-testimonials.php':
                    resolve(mockTestimonialsData);
                    break;
                case 'supabase-stats.php':
                    resolve(mockStatsData);
                    break;
                case 'supabase-works.php':
                    resolve(mockWorksData);
                    break;
                case 'supabase-site-settings.php':
                    resolve(mockSiteSettingsData);
                    break;
                default:
                    resolve({ success: false, error: 'モックデータが見つかりません' });
            }
        }, 500); // ネットワーク遅延をシミュレート
    });
}

// 実際のAPIコールをモックに置き換える
window.mockApiEnabled = true;

console.log('モックAPIが有効になりました。PHPサーバーが利用できない場合の代替として使用されます。');