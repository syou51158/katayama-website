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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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

