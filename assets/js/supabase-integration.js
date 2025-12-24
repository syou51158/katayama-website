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
    }
    
    /**
     * 環境に応じて適切なAPIベースパスを検出
     */
    detectApiBase() {
        const path = window.location.pathname;
        const hostname = window.location.hostname;
        
        // ローカル環境の場合
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            if (path.includes('/katayama-website/')) {
                return '/katayama-website/api/';
            }
            return 'api/'; // 相対パス
        }
        
        // プロダクション環境
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
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
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
            return [];
        }
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
        const params = { limit, offset };
        if (category && category !== 'all') {
            params.category = category;
        }
        return await this.fetchData('supabase-works.php', params);
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
        return await this.fetchData('supabase-stats.php');
    }

    /**
     * ニュース一覧をHTMLにレンダリング
     */
    renderNewsList(news, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !news.length) return;

        const newsHtml = news.map(item => {
            const date = new Date(item.published_date).toLocaleDateString('ja-JP', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).replace(/\//g, '.');

            const categoryClass = this.getCategoryClass(item.category);
            
            return `
                <li>
                    <a href="news.html?id=${item.id}" class="block p-6 hover:bg-accent transition-colors">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <div class="flex items-center mb-2 md:mb-0">
                                <span class="text-sm text-gray-500 mr-3">${date}</span>
                                <span class="px-3 py-1 ${categoryClass} text-xs font-medium rounded-sm">${item.category}</span>
                            </div>
                            <h3 class="md:ml-6 font-medium">${this.escapeHtml(item.title)}</h3>
                        </div>
                    </a>
                </li>
            `;
        }).join('');

        container.innerHTML = newsHtml;
    }

    /**
     * 施工実績一覧をHTMLにレンダリング
     */
    renderWorksList(works, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !works.length) return;

        const worksHtml = works.map(item => {
            const completionYear = item.completion_date ? 
                new Date(item.completion_date).getFullYear() + '年竣工' : '';
            
            return `
                <div class="card group work-item" data-category="${item.category.toLowerCase()}">
                    <div class="relative overflow-hidden">
                        <img src="${item.featured_image}" alt="${this.escapeHtml(item.title)}" 
                             class="w-full h-64 object-cover transition-transform duration-700 group-hover:scale-110">
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
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">${completionYear}</span>
                            ${item.location ? `<span class="text-sm bg-accent text-primary px-2 py-1 rounded-sm">${this.escapeHtml(item.location)}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = worksHtml;
    }

    /**
     * お客様の声をHTMLにレンダリング
     */
    renderTestimonials(testimonials, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !testimonials.length) return;

        const testimonialsHtml = testimonials.map((item, index) => `
            <div class="testimonial-card" data-aos="fade-up" data-aos-delay="${index * 100}">
                <p class="mb-6 text-gray-700">${this.escapeHtml(item.content)}</p>
                <div class="flex items-center">
                    <span class="block font-bold">${this.escapeHtml(item.customer_initial || item.customer_name)}</span>
                    <span class="mx-2 text-gray-400">|</span>
                    <span class="block text-sm text-gray-500">${this.escapeHtml(item.project_type)}</span>
                </div>
            </div>
        `).join('');

        container.innerHTML = testimonialsHtml;
    }

    /**
     * 会社統計をHTMLにレンダリング
     */
    renderStats(stats, containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container || !stats.length) return;

        const statsHtml = stats.map((item, index) => `
            <div data-aos="fade-up" data-aos-delay="${index * 100}" 
                 class="p-6 border-b-2 border-secondary elegant-shadow">
                <div class="text-4xl md:text-5xl font-bold mb-3">
                    ${item.stat_value}<span class="text-secondary">${item.stat_unit || ''}</span>
                </div>
                <div class="w-12 h-0.5 bg-secondary mx-auto mb-3"></div>
                <p class="uppercase tracking-wide text-sm">${this.escapeHtml(item.stat_name)}</p>
            </div>
        `).join('');

        container.innerHTML = statsHtml;
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
document.addEventListener('DOMContentLoaded', function() {
    // ホームページの初期化
    if (document.querySelector('.news-list')) {
        initializeHomePage();
    }
    
    // ニュースページの初期化
    if (document.querySelector('#news-container')) {
        initializeNewsPage();
    }
    
    // 施工実績ページの初期化
    if (document.querySelector('#works-grid')) {
        initializeWorksPage();
    }
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

    } catch (error) {
        console.error('Homepage initialization error:', error);
    }
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
                     data-category="${item.category}" data-aos="fade-up" data-aos-delay="${index * 100}">
                <div class="grid grid-cols-1 md:grid-cols-3">
                    <div class="md:col-span-1">
                        <img src="${item.featured_image || 'assets/img/default-news.jpg'}" 
                             alt="${item.title}" class="w-full h-full object-cover">
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
        }
    } catch (error) {
        console.error('Works page initialization error:', error);
        supabaseIntegration.showError(error.message, '#works-grid');
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

// エクスポート（モジュール形式での使用時）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SupabaseIntegration;
}

