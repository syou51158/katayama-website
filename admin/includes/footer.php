<?php if (SupabaseAuth::isLoggedIn()): ?>
    </div> <!-- End .main-content -->
<?php else: ?>
    </div> <!-- End .container -->
<?php endif; ?>
</div> <!-- End #admin-wrapper -->

<?php if (SupabaseAuth::isLoggedIn()): ?>
<!-- View Toggle Button -->
<div class="view-toggle-container">
    <div class="view-toggle-tooltip" id="view-toggle-tooltip">公開サイトを見る</div>
    <button id="view-toggle-btn" aria-label="表示切り替え">
        <i class="bi bi-eye"></i>
    </button>
</div>
<?php endif; ?>

<!-- Page Transition Loading Overlay -->
<div id="page-transition-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none justify-content-center align-items-center" style="background: rgba(0,0,0,0.4); z-index: 1050;">
    <div class="text-center bg-white p-4 rounded shadow">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3 fw-bold text-dark">読み込み中...</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ページ遷移ローディング制御 (Ajax SPA風遷移)
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('page-transition-overlay');
        const contentWrapper = document.querySelector('.main-content'); // コンテンツの差し替え対象
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link'); // サイドバーのリンク
        
        // Mobile Menu Toggle
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.querySelector('.sidebar');
        const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
        
        if (mobileBtn && sidebar && mobileOverlay) {
            function toggleMenu() {
                sidebar.classList.toggle('show');
                mobileOverlay.classList.toggle('show');
                
                // Toggle body class for scroll prevention
                if (sidebar.classList.contains('show')) {
                    document.body.classList.add('menu-open');
                } else {
                    document.body.classList.remove('menu-open');
                }
                
                // Icon toggle
                const icon = mobileBtn.querySelector('i');
                if (icon) {
                    if (sidebar.classList.contains('show')) {
                        icon.classList.remove('bi-list');
                        icon.classList.add('bi-x-lg');
                    } else {
                        icon.classList.remove('bi-x-lg');
                        icon.classList.add('bi-list');
                    }
                }
            }
            
            mobileBtn.addEventListener('click', toggleMenu);
            mobileOverlay.addEventListener('click', toggleMenu);
            
            // Close menu when clicking a link
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        mobileOverlay.classList.remove('show');
                        document.body.classList.remove('menu-open'); // Remove scroll prevention
                        const icon = mobileBtn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-x-lg');
                            icon.classList.add('bi-list');
                        }
                    }
                });
            });
        }
        
        // 履歴管理（ブラウザの戻る・進む対応）
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.path) {
                loadPage(e.state.path, false);
            } else {
                location.reload(); // 履歴がない場合は通常リロード
            }
        });

        // ページ読み込み関数
        function loadPage(url, pushState = true) {
            // ローディング表示
            if (overlay) {
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    // HTMLからメインコンテンツ部分を抽出
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('.main-content');
                    
                    if (newContent && contentWrapper) {
                        // コンテンツを差し替え
                        contentWrapper.innerHTML = newContent.innerHTML;
                        
                        // URLを更新
                        if (pushState) {
                            history.pushState({ path: url }, '', url);
                        }
                        
                        // アクティブなメニューを更新
                        sidebarLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('href') === url) {
                                link.classList.add('active');
                            }
                        });
                        
                        // ページ内のスクリプトを再実行（必要に応じて）
                        // ※単純なinnerHTML置換ではscriptタグは実行されないため、
                        // admin/settings/mail.php のような固有のスクリプトがある場合は
                        // 手動で再設定するか、イベントリスナーをdocument全体に委譲する必要があります。
                        // 今回は特にメール設定ページのフォーム制御を再バインドします。
                        if (url.includes('/admin/settings/mail.php')) {
                            const scriptContent = doc.querySelector('script:not([src])');
                            if (scriptContent) {
                                const script = document.createElement('script');
                                script.textContent = scriptContent.textContent;
                                document.body.appendChild(script);
                            }
                        }
                        
                        // 画面トップへ
                        window.scrollTo(0, 0);
                    } else {
                        // 構造が違う場合は通常遷移にフォールバック
                        window.location.href = url;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    window.location.href = url; // エラー時は通常遷移
                })
                .finally(() => {
                    // ローディング非表示
                    if (overlay) {
                        overlay.classList.remove('d-flex');
                        overlay.classList.add('d-none');
                    }
                });
        }

        // リンククリックイベントの乗っ取り
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // 内部リンクかつ通常遷移すべきでないもの
                if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !this.hasAttribute('target') && !this.classList.contains('logout')) {
                    e.preventDefault(); // 通常遷移をキャンセル
                    loadPage(href);
                }
            });
        });
    });

    // 管理者としてログイン中であることを記録
    if (window.localStorage) {
        localStorage.setItem('isAdmin', 'true');
        localStorage.setItem('adminLoginTime', new Date().getTime());
    }

    // View Toggle & Iframe State Logic
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('view-toggle-btn');
        const tooltip = document.getElementById('view-toggle-tooltip');
        const body = document.body;
        const iframe = document.getElementById('site-background-frame');
        
        // Update Tooltip Text Helper
        const updateTooltip = (isHidden) => {
            if (tooltip) {
                tooltip.textContent = isHidden ? '管理画面へ' : '公開サイトを見る';
            }
        };

        // Restore view mode state
        const isViewSiteMode = localStorage.getItem('adminViewSiteMode') === 'true';
        if (isViewSiteMode) {
            body.classList.add('view-site-mode');
            if(toggleBtn) toggleBtn.innerHTML = '<i class="bi bi-speedometer2"></i>';
            updateTooltip(true);
        } else {
            updateTooltip(false);
        }

        // Restore iframe URL state if available
        const savedUrl = localStorage.getItem('adminBgUrl');
        if (savedUrl && iframe) {
            try {
                // Only set if it looks like a valid URL for this site
                if (savedUrl.includes(window.location.host)) {
                    iframe.src = savedUrl;
                }
            } catch(e) { console.error(e); }
        }

        // Toggle Button Click
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                body.classList.toggle('view-site-mode');
                const isHidden = body.classList.contains('view-site-mode');
                
                // Update Icon
                this.innerHTML = isHidden ? '<i class="bi bi-speedometer2"></i>' : '<i class="bi bi-eye"></i>';
                
                // Update Tooltip
                updateTooltip(isHidden);
                
                // Iframe内にも状態を通知（フッター非表示制御用）
                if (iframe && iframe.contentWindow) {
                    iframe.contentWindow.postMessage({
                        type: 'adminModeChange',
                        isViewSiteMode: isHidden
                    }, '*');
                }
                
                // Save state
                localStorage.setItem('adminViewSiteMode', isHidden);
            });
        }
        
        // iframe読み込み完了時にも初期状態を送信
        if (iframe) {
            iframe.onload = function() {
                const isHidden = body.classList.contains('view-site-mode');
                if (iframe.contentWindow) {
                    iframe.contentWindow.postMessage({
                        type: 'adminModeChange',
                        isViewSiteMode: isHidden
                    }, '*');
                }
            };
        }
        
        // Save iframe URL before leaving the page
        window.addEventListener('beforeunload', function() {
            if (iframe && iframe.contentWindow) {
                try {
                    // Check if accessible (same-origin)
                    const currentUrl = iframe.contentWindow.location.href;
                    if (currentUrl && currentUrl !== 'about:blank') {
                        localStorage.setItem('adminBgUrl', currentUrl);
                    }
                } catch (e) {
                    // Ignore errors (e.g. if iframe navigated to cross-origin)
                }
            }
        });
    });
</script>
</body>
</html>

