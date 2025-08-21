// CMS連携用JavaScript
class CMSIntegration {
    constructor() {
        this.apiBase = '/api/';
        this.init();
    }
    
    init() {
        // ページロード時にCMSデータを取得・表示
        document.addEventListener('DOMContentLoaded', () => {
            this.loadNewsData();
            this.loadWorksData();
        });
    }
    
    // お知らせデータを取得・表示
    async loadNewsData() {
        try {
            const response = await fetch(`${this.apiBase}news.php?status=published&limit=3`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                this.updateNewsSection(result.data);
            }
        } catch (error) {
            console.warn('お知らせデータの取得に失敗しました:', error);
        }
    }
    
    // 施工実績データを取得・表示
    async loadWorksData() {
        try {
            const response = await fetch(`${this.apiBase}works.php?status=published&limit=5`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                this.updateWorksSection(result.data);
            }
        } catch (error) {
            console.warn('施工実績データの取得に失敗しました:', error);
        }
    }
    
    // お知らせセクションを更新
    updateNewsSection(newsData) {
        const newsContainer = document.querySelector('.news-list');
        if (!newsContainer) return;
        
        const newsHtml = newsData.map(news => `
            <li>
                <a href="news.html?id=${news.id}" class="block p-6 hover:bg-accent transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="flex items-center mb-2 md:mb-0">
                            <span class="text-sm text-gray-500 mr-3">${news.formatted_date}</span>
                            <span class="px-3 py-1 ${this.getCategoryStyle(news.category)} text-xs font-medium rounded-sm">
                                ${news.category}
                            </span>
                        </div>
                        <h3 class="md:ml-6 font-medium">${this.escapeHtml(news.title)}</h3>
                    </div>
                </a>
            </li>
        `).join('');
        
        newsContainer.innerHTML = newsHtml;
    }
    
    // 施工実績セクションを更新
    updateWorksSection(worksData) {
        const worksContainer = document.querySelector('.works-slider');
        if (!worksContainer) return;
        
        const worksHtml = worksData.map(work => `
            <div class="w-80 md:w-96 flex-shrink-0 px-4">
                <div class="card h-full group">
                    <div class="relative overflow-hidden">
                        <img src="${work.image}" alt="${this.escapeHtml(work.title)}" 
                             class="w-full h-64 object-cover lightbox-image transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-primary bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <button class="btn-secondary px-4 py-2 text-sm" onclick="openLightbox('${work.image}')">詳細を見る</button>
                        </div>
                    </div>
                    <div class="p-6">
                        <span class="text-xs uppercase tracking-wider text-secondary mb-2 block">${work.category}</span>
                        <h3 class="text-xl font-bold mb-2">${this.escapeHtml(work.title)}</h3>
                        <p class="text-gray-600">${this.escapeHtml(work.description)}</p>
                    </div>
                </div>
            </div>
        `).join('');
        
        worksContainer.innerHTML = worksHtml;
    }
    
    // カテゴリスタイルを取得
    getCategoryStyle(category) {
        const styles = {
            'お知らせ': 'bg-blue-50 text-primary',
            'イベント': 'bg-green-50 text-green-700',
            '重要': 'bg-red-50 text-red-700'
        };
        return styles[category] || 'bg-gray-50 text-gray-700';
    }
    
    // HTMLエスケープ
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // 特定のお知らせを取得（詳細ページ用）
    async getNewsById(id) {
        try {
            const response = await fetch(`${this.apiBase}news.php`);
            const result = await response.json();
            
            if (result.success) {
                return result.data.find(item => item.id == id);
            }
        } catch (error) {
            console.error('お知らせの取得に失敗しました:', error);
        }
        return null;
    }
    
    // 特定の施工実績を取得（詳細ページ用）
    async getWorkById(id) {
        try {
            const response = await fetch(`${this.apiBase}works.php`);
            const result = await response.json();
            
            if (result.success) {
                return result.data.find(item => item.id == id);
            }
        } catch (error) {
            console.error('施工実績の取得に失敗しました:', error);
        }
        return null;
    }
}

// グローバルインスタンスを作成
window.cmsIntegration = new CMSIntegration();
