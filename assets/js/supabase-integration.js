/**
 * Supabase統合用JavaScript
 * ウェブサイトのコンテンツを動的に読み込み
 */

class SupabaseIntegration {
    constructor() {
        // ローカル環境とプロダクション環境の両方に対応
        this.apiBase = this.detectApiBase();
        this.cache = new Map();
        this.cacheExpiry = 5 * 60 * 1000; // 5分間キャッシュ
        this.supabaseUrl = window.SUPABASE_URL || 'https://kmdoqdsftiorzmjczzyk.supabase.co';
        this.supabaseAnonKey = window.SUPABASE_ANON_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImttZG9xZHNmdGlvcnptamN6enlrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjI5NTIyODIsImV4cCI6MjA3ODUyODI4Mn0.ZoztxEfNKUX1iMuvV0czfywvyNuxMXY2fhRFeoycBIQ';
    }
    /**
         * 環境に応じて適切なAPIベースパスを検出
         */
    detectApiBase() {
        const path = window.location.pathname;
        const hostname = window.location.hostname;
        const protocol = window.location.protocol;

        // ローカルファイルとして開いている場合、または明示的にローカルの場合
        if (protocol === 'file:' || hostname === 'localhost' || hostname === '127.0.0.1') {
            console.log('📂 ローカル環境を検出しました');

            // fileプロトコルの場合は常にDirectモード
            if (protocol === 'file:') {
                this.useSupabaseDirect = true;
            } else {
                this.useSupabaseDirect = !window.SUPABASE_OFFLINE;
            }

            if (path.includes('/katayama-website/')) {
                return '/katayama-website/api/';
            }
            return 'api/'; // 相対パス
        }

        // プロダクション環境
        this.useSupabaseDirect = false;
        return '/api/';
    }

    /**
     * APIからデータを取得（キャッシュ機能付き）
     */
    async fetchData(endpoint, params = {}) {
        const cacheKey = endpoint + JSON.stringify(params);
        const cached = this.cache.get(cacheKey);

        if (cached && Date.now() - cached.timestamp < this.cacheExpiry) {
            return cached.data;
        }

        try {
            console.log(`🔍 APIリクエスト: ${this.apiBase}${endpoint}`);
            const url = new URL(this.apiBase + endpoint, window.location.origin);
            Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

            console.log(`📡 フルURL: ${url.toString()}`);
            const response = await fetch(url);

            // PHPが実行されていない場合（生のPHPコードが返された場合）はモックAPIを使用
            const responseText = await response.text();
            console.log(`📄 レスポンス内容プレビュー (${endpoint}):`, responseText.substring(0, 100));

            if ((responseText.includes('<?php') || responseText.includes('require_once')) && (typeof window !== 'undefined' && window.mockApiEnabled === true)) {
                console.log(`⚠️ PHPが実行されていないため、モックAPIを使用します: ${endpoint}`);
                if (typeof getMockApiResponse === 'function') {
                    const mockData = await getMockApiResponse(endpoint);
                    // キャッシュに保存
                    this.cache.set(cacheKey, {
                        data: mockData,
                        timestamp: Date.now()
                    });
                    return mockData;
                } else {
                    // モックAPIが利用できない場合は空配列を返す
                    console.warn('モックAPIが利用できません。空データを返します。');
                    return [];
                }
            }

            // JSONとしてパースを試みる
            let data;
            try {
                // PHPエラーが含まれている場合はJSON部分だけを抽出する試み
                if (responseText.includes('<br />') || responseText.includes('<b>')) {
                    const jsonStart = responseText.indexOf('{');
                    const jsonEnd = responseText.lastIndexOf('}') + 1;
                    if (jsonStart !== -1 && jsonEnd > jsonStart) {
                        const jsonStr = responseText.substring(jsonStart, jsonEnd);
                        data = JSON.parse(jsonStr);
                        console.warn('⚠️ レスポンスにPHPエラーが含まれていましたが、JSONの抽出に成功しました');
                    } else {
                        throw new Error('Valid JSON not found in response');
                    }
                } else {
                    data = JSON.parse(responseText);
                }
            } catch (parseError) {
                console.error(`JSONパースエラー (${endpoint}):`, parseError);
                console.log(`レスポンス内容:`, responseText.substring(0, 200));

                // パースエラー時は、PHPがエラーを返している可能性が高いので、
                // 即座にSupabase直接通信フォールバックを試行する
                console.log('🔄 JSONパースエラーのため、Supabase直接通信を試行します...');
                const fallback = await this.fetchSupabaseFallback(endpoint, params);

                const isSettings = endpoint === 'supabase-site-settings.php' && fallback && typeof fallback === 'object' && !Array.isArray(fallback);
                if ((fallback && Array.isArray(fallback) && fallback.length > 0) || isSettings) {
                    this.cache.set(cacheKey, { data: fallback, timestamp: Date.now() });
                    return fallback;
                }

                // JSONパースに失敗し、フォールバックも失敗した場合にモックAPIを試す
                if ((typeof window !== 'undefined' && window.mockApiEnabled === true) && typeof getMockApiResponse === 'function') {
                    console.log(`モックAPIを試行します: ${endpoint}`);
                    const mockData = await getMockApiResponse(endpoint);
                    this.cache.set(cacheKey, {
                        data: mockData,
                        timestamp: Date.now()
                    });
                    return mockData;
                }
                return [];
            }

            console.log(`📦 APIレスポンス:`, data);
            // 様々なレスポンス形式に対応
            let resultData;
            if (Array.isArray(data)) {
                // 直接配列が返された場合
                resultData = data;
            } else if (data.success && data.data) {
                // {success: true, data: [...]} 形式
                resultData = data.data;
            } else if (data.data) {
                // {data: [...]} 形式
                resultData = data.data;
            } else {
                // その他の形式
                resultData = data;
            }

            // キャッシュに保存
            this.cache.set(cacheKey, {
                data: resultData,
                timestamp: Date.now()
            });

            console.log(`✅ API成功: ${endpoint}`, resultData);
            return resultData;
        } catch (error) {
            console.error(`🚨 API fetch error (${endpoint}):`, error);

            // 1. エラー時は常にSupabase直接通信を試行 (本番環境でPHPが失敗した場合のバックアップ)
            let fallback = [];
            console.log('🔄 APIエラーのため、Supabase直接通信を試行します...');
            fallback = await this.fetchSupabaseFallback(endpoint, params);

            const isSettings = endpoint === 'supabase-site-settings.php' && fallback && typeof fallback === 'object' && !Array.isArray(fallback);
            if ((fallback && Array.isArray(fallback) && fallback.length > 0) || isSettings) {
                this.cache.set(cacheKey, { data: fallback, timestamp: Date.now() });
                return fallback;
            }

            // 2. 最終手段：モックAPI
            if (typeof window !== 'undefined' && window.mockApiEnabled === true && typeof getMockApiResponse === 'function') {
                console.log(`⚠️ 最終フォールバック: モックAPIを使用します (${endpoint})`);
                const mockResult = await getMockApiResponse(endpoint);
                const mockData = (mockResult && mockResult.data) ? mockResult.data : mockResult;

                this.cache.set(cacheKey, { data: mockData, timestamp: Date.now() });
                return mockData;
            }

            return [];
        }
    }

    async fetchSupabaseFallback(endpoint, params = {}) {
        try {
            const tableMap = {
                'supabase-news.php': 'news',
                'supabase-works.php': 'works',
                'supabase-services.php': 'services',
                'supabase-testimonials.php': 'testimonials',
                'supabase-stats.php': 'company_stats',
                'supabase-representatives.php': 'representatives',
                'supabase-site-settings.php': 'site_settings',
                'supabase-company-history.php': 'company_history',
                'supabase-company-info.php': 'company_info',
                'supabase-partners.php': 'partners'
            };
            const table = tableMap[endpoint];
            if (!table) return [];
            const url = new URL(this.supabaseUrl + '/rest/v1/' + table);
            url.searchParams.set('select', '*');
            if (endpoint === 'supabase-news.php') {
                url.searchParams.set('status', 'eq.published');
                if (params.category && params.category !== 'all') {
                    url.searchParams.set('category', 'eq.' + params.category);
                }
                if (params.limit) url.searchParams.set('limit', String(params.limit));
                if (params.offset) url.searchParams.set('offset', String(params.offset));
                url.searchParams.append('order', 'published_date.desc');
                url.searchParams.append('order', 'created_at.desc');
            } else if (endpoint === 'supabase-works.php') {
                url.searchParams.set('status', 'eq.published');
                if (params.category && params.category !== 'all') {
                    url.searchParams.set('category', 'eq.' + params.category);
                }
                if (params.limit) url.searchParams.set('limit', String(params.limit));
                if (params.offset) url.searchParams.set('offset', String(params.offset));
                url.searchParams.append('order', 'completion_date.desc');
                url.searchParams.append('order', 'created_at.desc');
            } else if (endpoint === 'supabase-services.php') {
                url.searchParams.set('status', 'eq.active');
                url.searchParams.append('order', 'sort_order.asc');
                url.searchParams.append('order', 'created_at.asc');
            } else if (endpoint === 'supabase-testimonials.php') {
                url.searchParams.set('status', 'eq.published');
                if (params.limit) url.searchParams.set('limit', String(params.limit));
                url.searchParams.append('order', 'created_at.desc');
            } else if (endpoint === 'supabase-stats.php') {
                url.searchParams.append('order', 'created_at.asc');
            } else if (endpoint === 'supabase-representatives.php') {
                url.searchParams.set('status', 'eq.active');
                url.searchParams.append('order', 'sort_order.asc');
                url.searchParams.append('order', 'created_at.asc');
            } else if (endpoint === 'supabase-partners.php') {
                url.searchParams.set('status', 'eq.active');
                url.searchParams.append('order', 'created_at.asc');
            } else if (endpoint === 'supabase-company-info.php') {
                // 会社情報は1件のみ取得
                url.searchParams.append('limit', '1');
            } else if (endpoint === 'supabase-company-history.php') {
                url.searchParams.set('status', 'eq.active');
                url.searchParams.append('order', 'year.asc');
                url.searchParams.append('order', 'month.asc');
            }

            const res = await fetch(url.toString(), {
                headers: {
                    apikey: this.supabaseAnonKey,
                    Authorization: 'Bearer ' + this.supabaseAnonKey,
                    'Content-Type': 'application/json'
                }
            });
            if (!res.ok) {
                return [];
            }
            const json = await res.json();
            if (endpoint === 'supabase-site-settings.php') {
                const obj = {};
                if (Array.isArray(json)) {
                    json.forEach(row => {
                        if (row && row.setting_key) obj[row.setting_key] = row.setting_value;
                    });
                }
                return obj;
            }
            return Array.isArray(json) ? json : [];
        } catch (e) {
            return [];
        }
    }

    resolveImageUrl(path) {
        if (!path) return '';
        const p = String(path);
        const partnerSample = p.match(/^\/images\/partners\/sample([1-5])\.png$/);
        if (partnerSample) return `assets/img/partner${partnerSample[1]}.svg`;
        if (p === '/images/service_exterior.png') return 'assets/img/service_exterior.png';
        if (p === '/images/service_equipment.png') return 'assets/img/service_equipment.png';
        if (p.startsWith('http://') || p.startsWith('https://')) return p;
        if (p.startsWith('/storage/')) return this.supabaseUrl + p;
        if (p.startsWith('/images/')) return this.supabaseUrl + '/storage/v1/object/public/website-assets' + p;
        return p;
    }

    getWorksFallbackImage(index) {
        const i = (index % 7) + 1;
        return `assets/img/works_0${i}.jpg`;
    }

    /**
    * ニュースデータを取得
         */
    async getNews(limit = 10, offset = 0, category = null) {
        const params = { limit, offset };
        if (category && category !== 'all') {
            params.category = category;
        }
        return await this.fetchData('supabase-news.php', params);
    }

    /**
     * 施工実績データを取得
     */
    async getWorks(limit = 20, offset = 0, category = null) {
        // データベースのダミーデータ（金沢市など）を無視し、「施工例（イメージ）」として
        // 適切なデータを強制的に返します。
        const works = [
            {
                id: "1",
                title: "自然素材の家",
                description: "木造2階建て、自然素材を活かした温かみのある住宅",
                category: "Residential",
                featured_image: "assets/img/works_01.jpg",
                status: "published"
            },
            {
                id: "2",
                title: "古民家カフェ",
                description: "古民家を改装したカフェの内装・外装工事",
                category: "Commercial",
                featured_image: "assets/img/works_02.jpg",
                status: "published"
            },
            {
                id: "6",
                title: "木造住宅解体工事",
                description: "老朽化した木造住宅の解体工事、整地まで実施",
                category: "Demolition",
                featured_image: "assets/img/service_kenzokaitai.png",
                status: "published"
            },
            {
                id: "7",
                title: "店舗内装解体",
                description: "商業施設テナントの原状回復に伴う内装解体",
                category: "Demolition",
                featured_image: "assets/img/service_naisoukaitai.png",
                status: "published"
            },
            {
                id: "4",
                title: "省エネオフィスビル",
                description: "鉄骨3階建て、省エネ設計のオフィスビル",
                category: "Commercial",
                featured_image: "assets/img/works_04.jpg",
                status: "published"
            },
            {
                id: "5",
                title: "マンション大規模修繕",
                description: "築15年のマンション外壁・共用部分の全面改修",
                category: "Renovation",
                featured_image: "assets/img/works_05.jpg",
                status: "published"
            }
        ];

        // カテゴリフィルタリング
        if (category && category !== 'all') {
            return works.filter(work => work.category.toLowerCase() === category.toLowerCase());
        }

        return works;
        // return await this.fetchData('supabase-works.php', params);
    }

    /**
     * サービスデータを取得
     */
    async getServices() {
        return await this.fetchData('supabase-services.php');
    }

    /**
     * お客様の声データを取得
     */
    async getTestimonials(limit = 10) {
        return await this.fetchData('supabase-testimonials.php', { limit });
    }

    /**
     * 会社統計データを取得
     */
    async getStats() {
        // 会社概要(company.html)に基づく実際のデータ（役所提出用）
        // 創業: 2023年11月 -> 1年
        // 許可: 滋賀・京都の解体工事業者登録 -> 2件
        // 事業内容: 解体、リフォーム、不動産、管理、補助金 -> 5事業
        // エリア: 滋賀県・京都府 -> 2府県
        return [
            {
                id: "1",
                stat_name: "創業年数",
                stat_value: "1",
                stat_unit: "年",
                description: "2023年11月創業",
                sort_order: 1,
                status: "active"
            },
            {
                id: "2",
                stat_name: "保有許可数",
                stat_value: "2",
                stat_unit: "件",
                description: "解体工事業者登録（滋賀・京都）",
                sort_order: 2,
                status: "active"
            },
            {
                id: "3",
                stat_name: "提供サービス",
                stat_value: "5",
                stat_unit: "事業",
                description: "解体・リフォーム・不動産ほか",
                sort_order: 3,
                status: "active"
            },
            {
                id: "4",
                stat_name: "対応エリア",
                stat_value: "2",
                stat_unit: "府県",
                description: "滋賀県・京都府",
                sort_order: 4,
                status: "active"
            }
        ];
    }

    /**
* 代表者データを取得
*/
    async getRepresentatives() {
        return await this.fetchData('supabase-representatives.php');
    }

    /**
     * パートナー企業データを取得
     */
    async getPartners() {
        return await this.fetchData('supabase-partners.php');
    }

    /**
     * サイト設定を取得
     */
    async getSiteSettings() {
        return await this.fetchData('supabase-site-settings.php');
    }

    /**
     * 会社情報を取得
     */
    async getCompanyInfo() {
        return await this.fetchData('supabase-company-info.php');
    }

    /**
     * 会社沿革を取得
     */
    async getCompanyHistory() {
        return await this.fetchData('supabase-company-history.php');
    }

    /**
     * ニュース一覧をHTMLにレンダリング
     */
    renderNewsList(news, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) {
            console.error(`❌ エラー: ${containerSelector} が見つかりません`);
            return;
        }

        if (!Array.isArray(news) || news.length === 0) {
            container.innerHTML = '<div class="text-center py-16 text-gray-500">現在、お知らせはありません。</div>';
            return;
        }

        const newsHtml = news.map(item => {
            let date = '';
            if (item.published_date) {
                date = new Date(item.published_date).toLocaleDateString('ja-JP', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }).replace(/\//g, '.');
            } else if (item.created_at) {
                date = new Date(item.created_at).toLocaleDateString('ja-JP', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }).replace(/\//g, '.');
            }

            const category = item.category || 'お知らせ';
            const categoryClass = this.getCategoryClass(category);
            const title = this.escapeHtml(item.title || '無題');
            const id = item.id || '#';

            // AOSへの依存を削除し、確実に表示させる
            return `
                <div class="news-item border-b border-gray-100 last:border-0" data-category="${category}">
                    <a href="news.html?id=${id}" class="block p-6 hover:bg-accent transition-colors">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <div class="flex items-center mb-2 md:mb-0 shrink-0">
                                <span class="text-sm text-gray-500 mr-3 font-mono">${date}</span>
                                <span class="px-3 py-1 ${categoryClass} text-xs font-medium rounded-sm whitespace-nowrap">${category}</span>
                            </div>
                            <h3 class="md:ml-6 font-medium text-lg leading-relaxed text-text_dark group-hover:text-primary transition-colors">${title}</h3>
                        </div>
                    </a>
                </div>
            `;
        }).join('');

        container.innerHTML = `<div class="bg-white rounded-sm shadow-sm overflow-hidden border border-gray-100">${newsHtml}</div>`;
        console.log(`✅ ニュースリストをレンダリングしました: ${news.length}件`);
    }

    getCategoryClass(category) {
        if (!category) return 'bg-gray-100 text-gray-800';
        const c = String(category).toLowerCase();
        if (c.includes('お知らせ') || c.includes('news')) return 'bg-blue-100 text-blue-800';
        if (c.includes('イベント') || c.includes('event')) return 'bg-green-100 text-green-800';
        if (c.includes('施工') || c.includes('work')) return 'bg-orange-100 text-orange-800';
        if (c.includes('メディア') || c.includes('media')) return 'bg-purple-100 text-purple-800';
        return 'bg-gray-100 text-gray-800';
    }

    escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    /**
     * 施工実績一覧をHTMLにレンダリング
     */
    renderWorksList(works, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !works.length) return;

        const worksHtml = works.map((item, index) => {
            const resolved = this.resolveImageUrl(item.featured_image);
            const imgSrc = resolved || this.getWorksFallbackImage(index);
            return `
                <div class="card group work-item" data-category="${item.category.toLowerCase()}">
                    <div class="relative overflow-hidden">
                        <img src="${imgSrc}" alt="${this.escapeHtml(item.title)}" 
                             class="w-full h-64 object-cover transition-transform duration-700 group-hover:scale-110" onerror="this.onerror=null;this.src='${this.getWorksFallbackImage(index)}'">
                        <div class="absolute top-0 right-0 bg-secondary text-white px-4 py-2 text-sm uppercase tracking-wider">
                            ${item.category}
                        </div>
                        <div class="absolute inset-0 bg-primary bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <button class="btn-secondary px-4 py-2 text-sm" 
                                    onclick="openWorkDetail('${item.id}')">詳細を見る</button>
                        </div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase tracking-wider text-secondary mb-2 block">${item.category}</span>
                        <h3 class="text-xl font-bold mb-2">${this.escapeHtml(item.title)}</h3>
                        <p class="text-gray-600 mb-4">${this.escapeHtml(item.description)}</p>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = worksHtml;
    }

    renderServices(services, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;
        if (!Array.isArray(services) || services.length === 0) {
            services = this.getDefaultServices();
        }

        const cards = services.map((svc, index) => {
            const title = this.escapeHtml(svc.title || '');
            const desc = this.escapeHtml(svc.description || svc.detailed_description || '');
            const features = Array.isArray(svc.features) ? svc.features : [];
            const featureHtml = features.length ? `<ul class="service-features">${features.map(f => `<li>${this.escapeHtml(f)}</li>`).join('')}</ul>` : '';
            const remoteImg = this.resolveImageUrl(svc.service_image);
            const fallbackImg = this.getServiceFallbackImage(svc);
            const secondaryFallbackImg = this.getServiceSecondaryFallbackImage(svc);
            const img = remoteImg || fallbackImg;
            const icon = this.escapeHtml(svc.icon || '');
            const derivedIcon = icon || this.getServiceIconByTitle(svc.title || '');
            const badge = derivedIcon ? `<span class="service-tag">${derivedIcon}</span>` : '';

            // Alternate layout direction for better visual flow
            const isEven = index % 2 === 0;
            const imageOrder = isEven ? 'order-1' : 'order-1 md:order-2';
            const textOrder = isEven ? 'order-2' : 'order-2 md:order-1';
            const bgColor = index % 2 === 1 ? 'bg-gray-50' : 'bg-white';

            return `
                <section class="service-section ${bgColor}">
                    <div class="container mx-auto px-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                            <div class="relative ${imageOrder}">
                                ${badge}
                                <div class="service-parallax-wrapper rounded-lg shadow-lg">
                                    <img src="${img}" alt="${title}" class="service-parallax-img" onerror="this.onerror=null;this.src='${fallbackImg}'">
                                </div>
                            </div>
                            <div class="${textOrder}">
                                <h3 class="service-title">${title}</h3>
                                ${desc ? `<p class=\"service-description\">${desc}</p>` : ''}
                                ${featureHtml}
                            </div>
                        </div>
                    </div>
                </section>
            `;
        }).join('');

        container.innerHTML = `${cards}`;
        if (typeof AOS !== 'undefined' && typeof AOS.refresh === 'function') {
            try {
                AOS.refresh();
            } catch (_) { }
        }
        if (typeof setupParallax === 'function') {
            setupParallax();
        }
    }

    getServiceFallbackImage(svc) {
        const t = String(svc.title || '').toLowerCase();
        if (t.includes('土木')) return 'assets/img/service_doboku.jpg';
        if (t.includes('建築')) return 'assets/img/service_house.jpg';
        if (t.includes('リフォーム')) return 'assets/img/service_reform.jpg';
        if (t.includes('外構')) return 'assets/img/service_exterior.png';
        if (t.includes('公共')) return 'assets/img/service_public.jpg';
        if (t.includes('設備')) return 'assets/img/service_equipment.png';
        return 'assets/img/service_house.jpg';
    }

    getServiceSecondaryFallbackImage(svc) {
        const t = String(svc.title || '').toLowerCase();
        if (t.includes('外構')) return 'assets/img/service_commercial.jpg';
        if (t.includes('設備')) return 'assets/img/works_07.jpg';
        if (t.includes('土木')) return 'assets/img/works_01.jpg';
        if (t.includes('建築')) return 'assets/img/service_house.jpg';
        if (t.includes('リフォーム')) return 'assets/img/service_reform.jpg';
        if (t.includes('公共')) return 'assets/img/service_public.jpg';
        return this.getWorksFallbackImage(0);
    }

    getServiceIconByTitle(title) {
        const t = String(title || '').toLowerCase();
        if (t.includes('土木')) return 'civil';
        if (t.includes('建築')) return 'building';
        if (t.includes('リフォーム')) return 'reform';
        if (t.includes('外構')) return 'exterior';
        if (t.includes('公共')) return 'public';
        if (t.includes('設備')) return 'facility';
        return '';
    }

    getDefaultServices() {
        return [
            {
                title: '土木工事',
                description: '造成・河川などの土木工事',
                features: ['造成', '河川改修', '舗装', '擁壁'],
                service_image: 'assets/img/service_doboku.jpg',
                icon: 'residence'
            },
            {
                title: '建築工事',
                description: '住宅・お店の建設',
                features: ['新築', '増改築', '改修'],
                service_image: 'assets/img/service_house.jpg',
                icon: 'building'
            },
            {
                title: 'リフォーム',
                description: '住宅リフォーム',
                features: ['キッチン', 'バス', '洗面'],
                service_image: 'assets/img/service_reform.jpg',
                icon: 'rock'
            },
            {
                title: '外構工事',
                description: 'エクステリア工事',
                features: ['カーポート', '塀', '舗装'],
                service_image: 'assets/img/service_exterior.png',
                icon: 'fence'
            },
            {
                title: '設備工事',
                description: '電気・給排水など',
                features: ['電気設備', '空調', '給排水'],
                service_image: 'assets/img/service_equipment.png',
                icon: 'electric'
            }
        ];
    }

    /**
    * お客様の声をHTMLにレンダリング
         */
    renderTestimonials(testimonials, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !testimonials.length) return;
        const testimonialsHtml = testimonials.map((item, index) => {
            const name = this.escapeHtml(item.customer_name || '');
            const project = this.escapeHtml(item.project_type || '');
            const content = this.escapeHtml(item.content || '');
            const rating = Math.max(0, Math.min(5, Number(item.rating || 0)));
            const stars = Array.from({ length: 5 }, (_, i) => {
                return i < rating
                    ? `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-secondary" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.176 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`
                    : `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.176 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>`;
            }).join('');

            return `
            <div class="card elegant-shadow p-6">
                <div class="flex items-start mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary mr-3 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M7.17 6A5.17 5.17 0 002 11.17V20h7v-8H6.83A3.83 3.83 0 0110.66 8V6H7.17zm9 0A5.17 5.17 0 0011 11.17V20h7v-8h-2.17A3.83 3.83 0 0119.66 8V6h-3.49z"/></svg>
                    <p class="text-gray-700">${content}</p>
                </div>
                <div class="flex items-center justify-between mt-6">
                    <div class="flex items-center">
                        <span class="font-bold">${name}</span>
                        ${project ? `<span class="text-sm text-gray-500 ml-2">${project}</span>` : ''}
                    </div>
                    <div class="flex items-center">${stars}</div>
                </div>
            </div>`;
        }).join('');
        container.innerHTML = testimonialsHtml;
    }

    /**
     * 会社統計をHTMLにレンダリング
     */
    renderStats(stats, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !stats.length) return;

        const statsHtml = stats.map((item, index) => {
            const value = item.stat_value ?? item.value ?? '';
            const unit = item.stat_unit ?? item.unit ?? '';
            const name = this.escapeHtml(item.stat_name ?? item.label ?? '');
            return `
            <div class="p-6 border-b-2 border-secondary elegant-shadow">
                <div class="text-4xl md:text-5xl font-bold mb-3">
                    ${value}<span class="text-secondary">${unit}</span>
                </div>
                <div class="w-12 h-0.5 bg-secondary mx-auto mb-3"></div>
                <p class="uppercase tracking-wide text-sm">${name}</p>
            </div>
        `}).join('');

        container.innerHTML = statsHtml;
    }

    /**
* 代表者をHTMLにレンダリング
*/
    renderRepresentatives(representatives, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !representatives.length) return;

        const html = representatives.map((item, index) => {
            const photoUrl = item.photo_url || 'assets/img/ogp.jpg';
            const signatureUrl = item.signature_url || '';
            const biography = item.biography || {};

            // 経歴データをHTMLに変換
            let biographyHtml = '';
            if (biography.career && Array.isArray(biography.career)) {
                biographyHtml += '<h4 class="font-bold mb-2">経歴</h4><ul class="list-disc list-inside mb-4 text-gray-700">';
                biographyHtml += biography.career.map(career => `<li>${this.escapeHtml(career)}</li>`).join('');
                biographyHtml += '</ul>';
            }

            if (biography.education && Array.isArray(biography.education)) {
                biographyHtml += '<h4 class="font-bold mb-2">学歴</h4><ul class="list-disc list-inside mb-4 text-gray-700">';
                biographyHtml += biography.education.map(edu => `<li>${this.escapeHtml(edu)}</li>`).join('');
                biographyHtml += '</ul>';
            }

            // 資格データをHTMLに変換
            let qualificationsHtml = '';
            if (item.qualifications && Array.isArray(item.qualifications)) {
                qualificationsHtml = '<h4 class="font-bold mb-2">保有資格</h4><ul class="list-disc list-inside text-gray-700">';
                qualificationsHtml += item.qualifications.map(qual => `<li>${this.escapeHtml(qual)}</li>`).join('');
                qualificationsHtml += '</ul>';
            }

            return `
                <div class="bg-white shadow-md rounded-sm overflow-hidden mb-12">
                    <div class="grid grid-cols-1 md:grid-cols-3">
                        <div class="md:col-span-1">
                            <img src="${photoUrl}" alt="${this.escapeHtml(item.name)}" 
                                 class="w-full h-full object-cover" onerror="this.onerror=null;this.src='assets/img/ogp.jpg'">
                        </div>
                        <div class="p-8 md:col-span-2">
                            <div class="mb-6">
                                <h3 class="text-2xl font-bold mb-2">${this.escapeHtml(item.name)}</h3>
                                <p class="text-secondary font-medium mb-4">${this.escapeHtml(item.position)}</p>
                                
                                <div class="prose max-w-none mb-6">
                                    <h4 class="font-bold mb-2">${this.escapeHtml(item.greeting_title)}</h4>
                                    <p class="text-gray-700 leading-relaxed">${this.escapeHtml(item.greeting_content)}</p>
                                </div>

                                ${biographyHtml}
                                ${qualificationsHtml}

                                ${signatureUrl ? `
                                    <div class="mt-6">
                                        <img src="${signatureUrl}" alt="${this.escapeHtml(item.name)} 署名" 
                                             class="h-12 object-contain" onerror="this.style.display='none'">
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
        if (typeof AOS !== 'undefined') {
            setTimeout(() => {
                AOS.refresh();
            }, 100);
        }
    }

    /**
     * サイト設定を適用
     */
    applySiteSettings(siteSettings) {
        if (!siteSettings) return;

        const companyName = siteSettings.company_name || siteSettings.site_name;
        const companyPhone = siteSettings.company_phone || siteSettings.contact_tel;
        const companyFax = siteSettings.company_fax;
        const companyEmail = siteSettings.company_email || siteSettings.contact_email;
        const companyAddress = siteSettings.company_address || siteSettings.address;
        const representativeName = siteSettings.representative_name;
        const registrationNumber = siteSettings.registration_number;
        const heroTitle = siteSettings.hero_title;
        const heroSubtitle = siteSettings.hero_subtitle;

        if (companyName) {
            const els = document.querySelectorAll('[data-site-setting="company_name"]');
            els.forEach(el => { el.textContent = companyName; });
        }

        if (companyPhone) {
            const els = document.querySelectorAll('[data-site-setting="company_phone"]');
            els.forEach(el => {
                el.textContent = companyPhone;
                if (el.tagName === 'A') { el.href = `tel:${companyPhone}`; }
            });
        }

        if (companyFax) {
            const els = document.querySelectorAll('[data-site-setting="company_fax"]');
            els.forEach(el => {
                // 親要素がPタグかつ、中身が会社情報として置換される場合、
                // 親が<p>FAX: <span>...</span></p>の形式ならtextContentのみ更新されるのでOK
                // しかし、もし親が<p data-site-setting="company_fax">...</p>の形式だった場合、
                // "FAX: "が消えてしまうのを防ぐため、以下のロジックを追加
                if (el.tagName === 'P' && el.innerHTML.includes('FAX:')) {
                    // 既にFAX: がある場合は、中身を書き換える際にFAX: を残す（簡易対応）
                    // ただし、基本はHTML側でspanタグにdata属性をつける修正を行っているため
                    // ここでは単純にtextContent書き換えで、HTML修正漏れがないことを前提とする
                    // あるいは、念のため "FAX: " が含まれていない場合で、かつPタグなら付与する？
                    // 今回はHTML側修正で対応済みのため、そのままtextContent更新でOK
                    // el.textContent = 'FAX: ' + companyFax; 
                }
                el.textContent = companyFax;
            });
        }

        if (companyEmail) {
            const els = document.querySelectorAll('[data-site-setting="company_email"]');
            els.forEach(el => {
                el.textContent = companyEmail;
                if (el.tagName === 'A') { el.href = `mailto:${companyEmail}`; }
            });
        }

        if (companyAddress) {
            const els = document.querySelectorAll('[data-site-setting="company_address"]');
            els.forEach(el => { el.textContent = companyAddress; });
        }

        if (heroTitle) {
            const els = document.querySelectorAll('[data-site-setting="hero_title"]');
            els.forEach(el => { el.textContent = heroTitle; });
        }

        if (heroSubtitle) {
            const els = document.querySelectorAll('[data-site-setting="hero_subtitle"]');
            els.forEach(el => { el.textContent = heroSubtitle; });
        }

        // 住所の更新
        if (siteSettings.company_address) {
            const addressElements = document.querySelectorAll('[data-site-setting="company_address"]');
            addressElements.forEach(el => {
                el.textContent = siteSettings.company_address;
            });
        }

        // 郵便番号の更新
        if (siteSettings.company_address_postal) {
            const postalElements = document.querySelectorAll('[data-site-setting="company_address_postal"]');
            postalElements.forEach(el => {
                el.textContent = siteSettings.company_address_postal;
            });
        }

        // 住所詳細の更新
        if (siteSettings.company_address_detail) {
            const detailElements = document.querySelectorAll('[data-site-setting="company_address_detail"]');
            detailElements.forEach(el => {
                el.textContent = siteSettings.company_address_detail;
            });
        }

        // タグラインの更新
        if (siteSettings.company_tagline) {
            const taglineElements = document.querySelectorAll('[data-site-setting="company_tagline"]');
            taglineElements.forEach(el => {
                el.textContent = siteSettings.company_tagline;
            });
        }

        if (representativeName) {
            const repEls = document.querySelectorAll('[data-site-setting="representative_name"]');
            repEls.forEach(el => { el.textContent = representativeName; });
        }

        if (registrationNumber) {
            const regEls = document.querySelectorAll('[data-site-setting="registration_number"]');
            regEls.forEach(el => { el.textContent = registrationNumber; });
        }

        // ヒーローセクションの更新
        if (siteSettings.hero_title) {
            const heroTitleElements = document.querySelectorAll('[data-site-setting="hero_title"]');
            heroTitleElements.forEach(el => {
                el.textContent = siteSettings.hero_title;
            });
        }

        if (siteSettings.hero_subtitle) {
            const heroSubtitleElements = document.querySelectorAll('[data-site-setting="hero_subtitle"]');
            heroSubtitleElements.forEach(el => {
                el.textContent = siteSettings.hero_subtitle;
            });
        }
    }

    /**
     * 会社情報をHTMLにレンダリング
     */
    renderCompanyInfo(companyInfo, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !companyInfo) return;

        const businessItems = companyInfo.business_details || [];
        const licenses = companyInfo.licenses || [];

        const businessHtml = businessItems.map(item => `<li>${this.escapeHtml(item)}</li>`).join('');
        const licensesHtml = licenses.map(license => `<li>${this.escapeHtml(license)}</li>`).join('');

        const html = `
            <table class="w-full">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left w-1/3">会社名</th>
                        <td class="py-4 px-6">${this.escapeHtml(companyInfo.company_name)}</td>
                    </tr>
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left">代表者</th>
                        <td class="py-4 px-6">${this.escapeHtml(companyInfo.representative_title)} ${this.escapeHtml(companyInfo.representative_name)}</td>
                    </tr>
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left">所在地</th>
                        <td class="py-4 px-6">
                            ${this.escapeHtml(companyInfo.address_postal)}<br>
                            ${this.escapeHtml(companyInfo.address_detail)}<br>
                            TEL: ${this.escapeHtml(companyInfo.phone)}<br>
                            ${companyInfo.fax ? `FAX: ${this.escapeHtml(companyInfo.fax)}<br>` : ''}
                            E-mail: ${this.escapeHtml(companyInfo.email)}
                        </td>
                    </tr>
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left">登録番号</th>
                        <td class="py-4 px-6">${this.escapeHtml(companyInfo.registration_number)}</td>
                    </tr>
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left">事業内容</th>
                        <td class="py-4 px-6">
                            <ul class="list-disc list-inside">
                                ${businessHtml}
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th class="py-4 px-6 bg-gray-50 text-left">許可・登録</th>
                        <td class="py-4 px-6">
                            <ul class="list-disc list-inside">
                                ${licensesHtml}
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        `;

        container.innerHTML = html;
    }

    /**
     * 企業理念をHTMLにレンダリング
     */
    renderPhilosophy(companyInfo, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !companyInfo) return;

        const philosophyItems = companyInfo.philosophy_items || [];

        const itemsHtml = philosophyItems.map((item, index) => `
            <div class="bg-accent p-8 rounded-sm" data-aos="fade-up" data-aos-delay="${(index + 1) * 100}">
                <div class="text-secondary text-4xl font-bold mb-4">${this.escapeHtml(item.number)}</div>
                <h3 class="text-xl font-bold mb-3">${this.escapeHtml(item.title)}</h3>
                <p>${this.escapeHtml(item.description)}</p>
            </div>
        `).join('');

        const html = `
            <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title">企業理念</h2>
                <div class="mt-16 space-y-10">
                    <div class="relative">
                        <div class="text-2xl md:text-3xl font-bold mb-6 text-primary">
                            「${this.escapeHtml(companyInfo.philosophy_title)}」
                        </div>
                        <p class="text-lg leading-relaxed">
                            ${this.escapeHtml(companyInfo.philosophy_content)}
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                        ${itemsHtml}
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = html;
        if (typeof AOS !== 'undefined') {
            setTimeout(() => {
                AOS.refresh();
            }, 100);
        }
    }

    /**
     * パートナー企業をHTMLにレンダリング
     */
    renderPartners(partners, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !partners.length) return;

        const partnersHtml = partners.map((partner, index) => {
            const resolvedLogo = this.resolveImageUrl(partner.logo_image);
            const fallbackLogo = `assets/img/partner${Math.min(index + 1, 5)}.svg`;
            const src = resolvedLogo || fallbackLogo;
            const img = `<img src="${src}" alt="${this.escapeHtml(partner.company_name || '')}" 
                           class="h-10 md:h-12 opacity-70 grayscale hover:grayscale-0 hover:opacity-100 transition-all"
                           onerror="this.onerror=null;this.src='${fallbackLogo}'">`;
            if (partner.website_url) {
                const href = this.escapeHtml(partner.website_url);
                return `<a href="${href}" target="_blank" rel="noopener noreferrer" class="flex justify-center items-center">${img}</a>`;
            }
            return `<div class="flex justify-center items-center">${img}</div>`;
        }).join('');

        container.innerHTML = partnersHtml;
    }

    /**
     * 会社沿革をHTMLにレンダリング
     */
    renderCompanyHistory(history, containerSelector, companyInfo = null) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        // 沿革データがない場合のフォールバック
        const historyData = Array.isArray(history) ? history : [];

        const historyHtml = historyData.map((item, index) => {
            const yearShort = String(item.year).slice(-2);
            const monthText = item.month ? `${item.month}月` : '';

            // detailsの配列化処理を強化
            let detailsArray = [];
            if (Array.isArray(item.details)) {
                detailsArray = item.details;
            } else if (typeof item.details === 'string') {
                try {
                    // JSON形式の場合
                    const parsed = JSON.parse(item.details);
                    if (Array.isArray(parsed)) detailsArray = parsed;
                    else detailsArray = [item.details];
                } catch (e) {
                    // PostgreSQLの配列形式 "{item1,item2}" の場合や通常の文字列の場合
                    if (item.details.startsWith('{') && item.details.endsWith('}')) {
                        // 簡易的なパース: 中身を取り出してカンマ区切り（引用符などは考慮しない簡易版）
                        detailsArray = item.details.slice(1, -1).split(',').map(s => s.trim().replace(/^"|"$/g, ''));
                    } else {
                        detailsArray = [item.details];
                    }
                }
            }

            const detailsHtml = detailsArray.map(detail => `<p>${this.escapeHtml(detail)}</p>`).join('');

            return `
              <div class="relative z-10 flex" data-aos="fade-up" data-aos-delay="${(index % 5) * 100}">
                <div class="h-12 w-12 rounded-full bg-primary text-white flex items-center justify-center text-lg font-bold shrink-0 z-10 border-4 border-white shadow-sm">${yearShort}</div>
                <div class="ml-6 pb-10">
                  <div class="text-xl font-bold text-primary">${item.year}年${monthText}</div>
                  <div class="mt-2 text-gray-700 leading-relaxed">
                    ${detailsHtml}
                  </div>
                </div>
              </div>
            `;
        }).join('');

        // 今後の展望（ビジョン）のレンダリング
        let visionHtml = '';
        if (companyInfo && companyInfo.future_vision) {
            let visions = [];
            try {
                visions = typeof companyInfo.future_vision === 'string' ? JSON.parse(companyInfo.future_vision) : companyInfo.future_vision;
            } catch (e) {
                console.error('Failed to parse future_vision:', e);
            }

            if (Array.isArray(visions) && visions.length > 0) {
                const visionItemsHtml = visions.map((item, index) => `
                    <div class="bg-accent p-6 rounded-sm border-l-4 border-primary" data-aos="fade-up" data-aos-delay="${(index + 1) * 100}">
                        <h4 class="text-lg font-bold text-primary mb-2">${this.escapeHtml(item.title)}</h4>
                        <p class="text-gray-700">${this.escapeHtml(item.description)}</p>
                    </div>
                `).join('');

                visionHtml = `
                    <div class="mt-16 pt-10 border-t border-gray-200">
                        <div class="text-center mb-10" data-aos="fade-up">
                            <h3 class="text-2xl font-bold text-primary">今後の展望（ビジョン）</h3>
                            <p class="mt-4 text-gray-600">片山建設工業は、単なる工事請負業に留まらず、以下の目標を掲げて成長を続けています。</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            ${visionItemsHtml}
                        </div>
                    </div>
                `;
            }
        }

        const html = `
          <h2 class="section-title">沿革</h2>
          
          <div class="max-w-4xl mx-auto mt-12">
            <div class="space-y-8 relative">
              <!-- 縦線 -->
              <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-gray-200 z-0 ml-6 h-full"></div>
              
              ${historyHtml}
            </div>
            
            ${visionHtml}
          </div>
        `;

        container.innerHTML = html;
        if (typeof AOS !== 'undefined') {
            setTimeout(() => {
                AOS.refresh();
            }, 100);
        }
    }

    /**
* カテゴリに応じたCSSクラスを取得
*/
    getCategoryClass(category) {
        const categoryClasses = {
            'お知らせ': 'bg-blue-50 text-primary',
            'イベント': 'bg-green-50 text-green-700',
            '施工事例': 'bg-yellow-50 text-yellow-700',
            'コラム': 'bg-purple-50 text-purple-700'
        };

        return categoryClasses[category] || 'bg-gray-50 text-gray-700';
    }

    /**
     * HTMLエスケープ
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * エラーメッセージを表示
     */
    showError(message, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (container) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-gray-500">データの読み込みに失敗しました。</p>
                    <p class="text-sm text-gray-400">${this.escapeHtml(message)}</p>
                </div>
            `;
        }
    }

    /**
     * ローディング表示
     */
    showLoading(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (container) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <p class="mt-2 text-gray-500">読み込み中...</p>
                </div>
            `;
        }
    }
}

// グローバルインスタンス
const supabaseIntegration = new SupabaseIntegration();

// ページ読み込み時の初期化
document.addEventListener('DOMContentLoaded', function () {
    // ページ固有の初期化ロジックがある場合は、共通初期化をスキップするフラグ
    const skipInit = window.suppressCommonInit === true;

    // ホームページの初期化
    if (document.querySelector('.news-list') && !skipInit) {
        initializeHomePage();
    }

    // ニュースページの初期化
    // news.htmlなどの個別ページでinitializeNewsPageが再定義されている場合、
    // そちら側で呼び出し制御を行いたい場合は window.suppressCommonInit = true を設定してください。
    if (document.querySelector('#news-container') && !skipInit) {
        // グローバル関数として定義されている場合のみ実行
        if (typeof initializeNewsPage === 'function') {
            initializeNewsPage();
        }
    }

    // 施工実績ページの初期化
    if (document.querySelector('#works-grid') && !skipInit) {
        initializeWorksPage();
    }
    // サービスページの初期化
    if (document.querySelector('#services-container')) {
        initializeServicesPage();
    }

    // Aboutページの初期化
    if (document.querySelector('#representatives-container')) {
        initializeAboutPage();
    }

    // 会社概要ページの初期化
    if (document.querySelector('#philosophy-container') || document.querySelector('#company-info-container') || document.querySelector('#company-history-container')) {
        initializeCompanyPage();
    }

    // パートナー企業の初期化
    if (document.querySelector('#partners-container')) {
        initializePartners();
    }

    // お問い合わせフォームの初期化
    if (document.querySelector('#contact-form')) {
        initializeContactForm();
    }

    // サイト設定の初期化（全ページ共通）
    initializeSiteSettings();
});

/**
 * ホームページの初期化
 */
async function initializeHomePage() {
    try {
        // ニュース一覧の読み込み
        const newsContainer = document.querySelector('.news-list');
        if (newsContainer) {
            supabaseIntegration.showLoading('.news-list');
            const news = await supabaseIntegration.getNews(3);
            supabaseIntegration.renderNewsList(news, '.news-list');
        }

        // お客様の声の読み込み
        const testimonialsContainer = document.querySelector('.testimonials-container');
        if (testimonialsContainer) {
            const testimonials = await supabaseIntegration.getTestimonials(3);
            supabaseIntegration.renderTestimonials(testimonials, '.testimonials-container');
        }

        // 会社統計の読み込み
        const statsContainer = document.querySelector('.stats-container');
        if (statsContainer) {
            const stats = await supabaseIntegration.getStats();
            supabaseIntegration.renderStats(stats, '.stats-container');
        }

        // ホーム: 施工実績スライダーに最新を反映
        const homeWorksSlider = document.querySelector('.works-slider');
        if (homeWorksSlider) {
            const works = await supabaseIntegration.getWorks(6);
            renderHomeWorksSlider(works);
        }

    } catch (error) {
        console.error('Homepage initialization error:', error);
    }
}

/**
* ホームの施工実績スライダーをレンダリング
*/
function renderHomeWorksSlider(works) {
    const slider = document.querySelector('.works-slider');
    if (!slider) return;
    if (!Array.isArray(works) || works.length === 0) {
        slider.innerHTML = '<div class="w-full text-center text-gray-500 py-8">施工実績がありません。</div>';
        return;
    }

    const cards = works.map((item, index) => {
        const img = supabaseIntegration.resolveImageUrl(item.featured_image) || supabaseIntegration.getWorksFallbackImage(index);
        const category = item.category || '';
        const title = supabaseIntegration.escapeHtml(item.title || '');
        const desc = supabaseIntegration.escapeHtml((item.description || '').substring(0, 40));
        return `
            <div class="w-80 md:w-96 flex-shrink-0 px-4">
              <div class="card h-full group">
                <div class="relative overflow-hidden">
                  <img src="${img}" alt="${title}" class="w-full h-64 object-cover lightbox-image transition-transform duration-700 group-hover:scale-110" onerror="this.onerror=null;this.src='${supabaseIntegration.getWorksFallbackImage(index)}'">
                  <div class="absolute inset-0 bg-primary bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <button class="btn-secondary px-4 py-2 text-sm" onclick="openLightbox('${img}','${title}','${desc}')">詳細を見る</button>
                  </div>
                </div>
                <div class="p-6">
                  <span class="text-xs uppercase tracking-wider text-secondary mb-2 block">${category}</span>
                  <h3 class="text-xl font-bold mb-2">${title}</h3>
                  <p class="text-gray-600">${desc}</p>
                </div>
              </div>
            </div>
        `;
    }).join('');

    slider.innerHTML = cards;
}

/**
* ニュースページ用のレンダリング関数
*/
function renderNewsPage(news) {
    const container = document.querySelector('#news-container');
    if (!container || !Array.isArray(news)) {
        console.error('renderNewsPage: Invalid container or news data');
        return;
    }

    if (news.length === 0) {
        container.innerHTML = '<div class="text-center py-16 text-gray-500">ニュースがありません。</div>';
        return;
    }

    let html = '';
    news.forEach((item, index) => {
        const categoryClass = getCategoryClass(item.category);
        const formattedDate = new Date(item.published_date).toLocaleDateString('ja-JP');

        html += `
            <article class="bg-white shadow-md rounded-sm overflow-hidden mb-8 news-item" 
                     data-category="${item.category}">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    <div class="md:col-span-1">
<img src="${supabaseIntegration.resolveImageUrl(item.featured_image) || 'assets/img/ogp.jpg'}" 
                             alt="${item.title}" class="w-full h-full object-cover" 
                             onerror="this.onerror=null;this.src='assets/img/ogp.jpg'">
</div>
<div class="p-6 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <span class="text-sm text-gray-500 mr-3">${formattedDate}</span>
                            <span class="${categoryClass}">${item.category}</span>
                        </div>
                        <h2 class="text-xl font-bold mb-3 hover:text-secondary transition-colors">
                            <a href="#">${item.title}</a>
                        </h2>
                        <p class="text-gray-600 mb-4">${item.excerpt || item.content.substring(0, 150) + '...'}</p>
                        <a href="#" class="inline-flex items-center text-primary font-medium hover:text-secondary transition-colors">
                            詳しく見る
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        `;
    });

    container.innerHTML = html;
}

/**
 * カテゴリのスタイルクラスを取得
 */
function getCategoryClass(category) {
    const classes = {
        'お知らせ': 'px-3 py-1 bg-blue-50 text-primary text-xs font-medium rounded-sm',
        'イベント': 'px-3 py-1 bg-green-50 text-green-700 text-xs font-medium rounded-sm',
        '施工事例': 'px-3 py-1 bg-yellow-50 text-yellow-700 text-xs font-medium rounded-sm',
        'コラム': 'px-3 py-1 bg-purple-50 text-purple-700 text-xs font-medium rounded-sm'
    };
    return classes[category] || 'px-3 py-1 bg-gray-50 text-gray-700 text-xs font-medium rounded-sm';
}

/**
 * ニュースページの初期化
 */
async function initializeNewsPage() {
    try {
        const container = document.querySelector('#news-container');
        if (container) {
            supabaseIntegration.showLoading('#news-container');
            const news = await supabaseIntegration.getNews(50); // より多くのニュースを取得
            console.log('📰 取得したニュース:', news);
            renderNewsPage(news);
        }
    } catch (error) {
        console.error('News page initialization error:', error);
        supabaseIntegration.showError(error.message, '#news-container');
    }
}

/**
 * 施工実績ページの初期化
 */
async function initializeWorksPage() {
    try {
        const container = document.querySelector('#works-grid');
        if (container) {
            supabaseIntegration.showLoading('#works-grid');
            const works = await supabaseIntegration.getWorks(20);
            supabaseIntegration.renderWorksList(works, '#works-grid');
            if (typeof setupCategoryFilter === 'function') {
                setupCategoryFilter();
            }
        }
    } catch (error) {
        console.error('Works page initialization error:', error);
        supabaseIntegration.showError(error.message, '#works-grid');
    }
}

async function initializeServicesPage() {
    try {
        const container = document.querySelector('#services-container');
        if (container) {
            supabaseIntegration.showLoading('#services-container');
            const services = await supabaseIntegration.getServices();
            supabaseIntegration.renderServices(services, '#services-container');
            if (typeof setupParallax === 'function') { setupParallax(); }
        }
    } catch (error) {
        console.error('Services page initialization error:', error);
        supabaseIntegration.showError(error.message, '#services-container');
    }
}

function setupParallax() {
    const els = Array.from(document.querySelectorAll('.service-parallax-img'));
    if (!els.length) return;
    const onScroll = () => {
        const vh = window.innerHeight || 800;
        for (const el of els) {
            const r = el.getBoundingClientRect();
            if (r.bottom < 0 || r.top > vh) continue;
            const s = 0.15;
            const max = 30;
            const o = Math.max(-max, Math.min(max, (r.top - vh / 2) * s));
            el.style.transform = `translateY(${o}px)`;
        }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

/**
 * Aboutページの初期化
 */
async function initializeAboutPage() {
    try {
        const container = document.querySelector('#representatives-container');
        if (container) {
            supabaseIntegration.showLoading('#representatives-container');
            const representatives = await supabaseIntegration.getRepresentatives();
            supabaseIntegration.renderRepresentatives(representatives, '#representatives-container');
        }
    } catch (error) {
        console.error('About page initialization error:', error);
        supabaseIntegration.showError(error.message, '#representatives-container');
    }
}

/**
 * パートナー企業ページの初期化
 */
async function initializePartners() {
    try {
        const container = document.querySelector('#partners-container');
        if (container) {
            supabaseIntegration.showLoading('#partners-container');
            const partners = await supabaseIntegration.getPartners();
            if (Array.isArray(partners) && partners.length > 0) {
                supabaseIntegration.renderPartners(partners, '#partners-container');
            } else {
                const fallback = [1, 2, 3, 4, 5].map(i => ({
                    company_name: '',
                    logo_image: `assets/img/partner${i}.svg`,
                    website_url: ''
                }));
                supabaseIntegration.renderPartners(fallback, '#partners-container');
            }
        }
    } catch (error) {
        console.error('Partners initialization error:', error);
        supabaseIntegration.showError(error.message, '#partners-container');
    }
}

/**
 * 会社概要ページの初期化
 */
async function initializeCompanyPage() {
    try {
        // 会社情報の取得（全セクションで使うため最初に取得）
        const companyInfo = await supabaseIntegration.getCompanyInfo();

        // 企業理念の読み込み
        const philosophyContainer = document.querySelector('#philosophy-container');
        if (philosophyContainer) {
            supabaseIntegration.showLoading('#philosophy-container');
            supabaseIntegration.renderPhilosophy(companyInfo, '#philosophy-container');
        }

        // 会社情報の読み込み
        const infoContainer = document.querySelector('#company-info-container');
        if (infoContainer) {
            supabaseIntegration.showLoading('#company-info-container');
            supabaseIntegration.renderCompanyInfo(companyInfo, '#company-info-container');
        }

        // 会社沿革の読み込み
        const historyContainer = document.querySelector('#company-history-container');
        if (historyContainer) {
            supabaseIntegration.showLoading('#company-history-container');
            const companyHistory = await supabaseIntegration.getCompanyHistory();
            supabaseIntegration.renderCompanyHistory(companyHistory, '#company-history-container', companyInfo);
        }
    } catch (error) {
        console.error('Company page initialization error:', error);
        supabaseIntegration.showError(error.message, '#philosophy-container');
    }
}

/**
 * 施工実績の詳細を開く
 */
function openWorkDetail(workId) {
    // 将来的に詳細モーダルまたは詳細ページの実装
    console.log('Opening work detail for ID:', workId);
    // 現在はライトボックスのプレースホルダー
    alert('詳細機能は準備中です。ID: ' + workId);
}

/**
 * お問い合わせフォームの初期化
 * 注: 現在は contact.html と main.js 側で処理しているため、ここは無効化しています。
 * 競合を防ぐため、イベントリスナーを登録しません。
 */
async function initializeContactForm() {
    // 競合防止のため無効化
    /*
    const form = document.getElementById('contact-form');
    if (!form) return;

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // フォームデータの収集
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            company: formData.get('company'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            inquiry_type: formData.get('inquiry_type'),
            message: formData.get('message')
        };

        // 送信前の検証
        if (!data.name || !data.email || !data.inquiry_type || !data.message) {
            alert('必須項目をすべて入力してください。');
            return;
        }

        // メールアドレスの検証
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            alert('有効なメールアドレスを入力してください。');
            return;
        }

        // プライバシーポリシーの同意確認
        const privacyCheckbox = form.querySelector('input[type="checkbox"]');
        if (!privacyCheckbox.checked) {
            alert('プライバシーポリシーに同意してください。');
            return;
        }

        let submitButton = null;

        try {
            // 送信ボタンを無効化
            submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = '送信中...';

            // Supabaseに送信
            const response = await fetch('api/supabase-inquiries.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // 成功時の処理
                alert('お問い合わせありがとうございます。内容を確認の上、担当者より連絡いたします。');
                form.reset();
            } else {
                // エラー時の処理
                alert(result.error || '送信に失敗しました。しばらく経ってからもう一度お試しください。');
            }

        } catch (error) {
            console.error('送信エラー:', error);
            alert('送信に失敗しました。しばらく経ってからもう一度お試しください。');
        } finally {
            // ボタンを再有効化
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = '送信する'; // originalText変数がスコープ外の可能性があるため固定文言
            }
        }
    });
    */
}

/**
 * サイト設定の初期化（全ページ共通）
 */
async function initializeSiteSettings() {
    try {
        const siteSettings = await supabaseIntegration.getSiteSettings();
        if (siteSettings) {
            supabaseIntegration.applySiteSettings(siteSettings);
        }
    } catch (error) {
        console.error('Site settings initialization error:', error);
    }
}

// エクスポート（モジュール形式での使用時）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SupabaseIntegration;
}
