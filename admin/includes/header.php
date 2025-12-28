<?php
require_once __DIR__ . '/../../lib/SupabaseAuth.php';
require_once __DIR__ . '/../../lib/SupabaseClient.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>片山建設工業 管理画面</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --brand-primary: #0A192F; /* Deep Navy */
            --brand-secondary: #172A45; /* Lighter Navy */
            --brand-accent: #C5A059; /* Gold/Bronze */
            --text-dark: #333333;
            --text-muted: #6c757d;
            --sidebar-width: 260px;
            
            /* Glassmorphism Variables */
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-sidebar: rgba(10, 25, 47, 0.95);
            --glass-border: 1px solid rgba(255, 255, 255, 0.3);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        body { 
            background-color: transparent; /* iframeが見えるように透明に */
            font-family: 'Noto Sans JP', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Site Background iframe */
        #site-background-frame {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            z-index: 1; /* z-indexを正の値に */
            pointer-events: none; /* デフォルトでは操作不可 */
        }
        
        /* Overlay to darken the background site slightly when admin is active */
        #admin-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(3px);
            z-index: 2; /* iframeより上に */
            transition: opacity 0.3s ease;
            pointer-events: none; /* 操作を透過させる */
        }

        /* Admin UI Wrapper */
        #admin-wrapper {
            transition: opacity 0.3s ease, transform 0.3s ease;
            position: relative;
            z-index: 100;
        }

        /* Sidebar Styling with Glass */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--glass-sidebar);
            backdrop-filter: blur(10px);
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            background: rgba(255,255,255,0.05);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: white;
            font-size: 1.1rem;
            text-transform: uppercase;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            color: #a8b2d1;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--brand-accent);
            backdrop-filter: blur(5px);
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .nav-link.logout {
            color: #ff8a93;
            margin-top: 2rem;
        }
        
        .nav-link.logout:hover {
            color: #ff5c6a;
            background: rgba(220, 53, 69, 0.1);
            border-left-color: #ff5c6a;
        }

        /* Main Content with Glass */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
            background: rgba(244, 246, 249, 0.5); /* Semi-transparent */
        }
        
        /* Dashboard Cards Glass Effect */
        .dashboard-card {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }
        
        /* Floating Toggle Button Container */
        .view-toggle-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Tooltip Style */
        .view-toggle-tooltip {
            background: rgba(10, 25, 47, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            transform: translateX(10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .view-toggle-container:hover .view-toggle-tooltip {
            opacity: 1;
            transform: translateX(0);
        }

        /* Floating Toggle Button */
        #view-toggle-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--brand-accent);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        #view-toggle-btn:hover {
            transform: scale(1.1);
            background: #d4b06a;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        
        #view-toggle-btn:active {
            transform: scale(0.95);
        }
        
        /* Hidden state for Admin UI */
        body.view-site-mode #admin-wrapper {
            opacity: 0;
            pointer-events: none;
            transform: scale(0.98);
        }
        
        body.view-site-mode #admin-backdrop {
            opacity: 0;
            pointer-events: none;
        }
        
        body.view-site-mode .sidebar {
            transform: translateX(-100%);
        }

        /* 
           重要: 閲覧モード時のiframe操作許可
           admin-wrapperが消えている時だけ、iframeの操作を許可する
        */
        body.view-site-mode #site-background-frame {
            pointer-events: auto;
            z-index: 9998; /* トグルボタン(9999)の下、他全ての上 */
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding-top: 80px; /* Space for mobile menu button */
            }
            
            #mobile-menu-btn {
                display: flex !important;
            }
            
            #mobile-sidebar-overlay {
                display: block !important;
            }
            
            #mobile-sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            /* Prevent body scroll when menu is open */
            body.menu-open {
                overflow: hidden !important;
                touch-action: none; /* Disable default touch actions on body */
            }
            
            /* Allow scroll in sidebar */
            .sidebar.show {
                overflow-y: auto;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling for iOS */
                touch-action: pan-y; /* Allow vertical scroll in sidebar */
            }
        }
        
        /* Mobile Menu Button */
        #mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 8px;
            width: 45px;
            height: 45px;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        #mobile-menu-btn:active {
            transform: scale(0.95);
        }

        /* Mobile Sidebar Overlay */
        #mobile-sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 900;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(3px);
        }
        
        /* Utility */
        .text-accent { color: var(--brand-accent); }
        .bg-white-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
<!-- Background Site Iframe -->
<iframe id="site-background-frame" src="/"></iframe>
<div id="admin-backdrop"></div>

<?php if (SupabaseAuth::isLoggedIn()): ?>
    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" aria-label="メニューを開く">
        <i class="bi bi-list fs-4"></i>
    </button>
<?php endif; ?>

<div id="admin-wrapper">
<?php if (SupabaseAuth::isLoggedIn()): ?>
    <!-- Mobile Sidebar Overlay -->
    <div id="mobile-sidebar-overlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-building-fill text-accent fs-4"></i>
            <span class="sidebar-brand-text">Katayama<br><small style="font-size: 0.65rem; color: var(--brand-accent);">Construction Industry</small></span>
        </div>
        
        <div class="d-flex flex-column justify-content-between" style="height: calc(100vh - 100px);">
            <ul class="sidebar-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo $_SERVER['REQUEST_URI'] == '/admin/' || $_SERVER['REQUEST_URI'] == '/admin/index.php' ? 'active' : ''; ?>" href="/admin/">
                        <i class="bi bi-grid-1x2-fill"></i> ダッシュボード
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/news/') !== false ? 'active' : ''; ?>" href="/admin/news/">
                        <i class="bi bi-newspaper"></i> お知らせ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/works/') !== false ? 'active' : ''; ?>" href="/admin/works/">
                        <i class="bi bi-bricks"></i> 施工実績
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/services/') !== false ? 'active' : ''; ?>" href="/admin/services/">
                        <i class="bi bi-gear-wide-connected"></i> 事業案内
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/company/') !== false ? 'active' : ''; ?>" href="/admin/company/">
                        <i class="bi bi-building"></i> 会社情報
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/inquiries/') !== false ? 'active' : ''; ?>" href="/admin/inquiries/">
                        <i class="bi bi-envelope-fill"></i> お問い合わせ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/jobs/') !== false ? 'active' : ''; ?>" href="/admin/jobs/">
                        <i class="bi bi-briefcase-fill"></i> 求人管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/properties/') !== false ? 'active' : ''; ?>" href="/admin/properties/">
                        <i class="bi bi-houses-fill"></i> 物件管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/files/') !== false ? 'active' : ''; ?>" href="/admin/files/">
                        <i class="bi bi-folder-fill"></i> ファイル管理
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/settings/mail.php') !== false ? 'active' : ''; ?>" href="/admin/settings/mail.php">
                        <i class="bi bi-gear-fill"></i> メール設定
                    </a>
                </li>
            </ul>

            <ul class="sidebar-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin/change-password.php') !== false ? 'active' : ''; ?>" href="/admin/change-password.php">
                        <i class="bi bi-key-fill"></i> パスワード変更
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link logout" href="/admin/logout.php">
                        <i class="bi bi-box-arrow-right"></i> ログアウト
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="main-content">
        <style>
            /* Global Premium Overrides */
            
            /* Typography & General */
            h1, h2, h3, h4, h5, h6 {
                color: var(--brand-primary);
                font-family: 'Outfit', sans-serif;
                font-weight: 600;
                letter-spacing: -0.02em;
            }

            /* Tables */
            .table-responsive {
                background: white;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.03);
                padding: 1rem;
                border: 1px solid rgba(0,0,0,0.02);
            }
            
            .table {
                margin-bottom: 0;
                border-color: #f0f0f0;
            }
            
            .table thead th {
                background-color: transparent;
                border-bottom: 2px solid #f0f0f0;
                color: var(--text-muted);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-weight: 600;
                padding: 1rem 1rem;
            }
            
            .table tbody td {
                padding: 1rem 1rem;
                vertical-align: middle;
                color: var(--text-dark);
                border-bottom: 1px solid #f8f9fa;
                font-size: 0.95rem;
            }
            
            .table-hover tbody tr:hover {
                background-color: #f8faff;
            }

            /* Buttons */
            .btn {
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                letter-spacing: 0.02em;
            }
            
            .btn-primary {
                background-color: var(--brand-primary);
                border-color: var(--brand-primary);
            }
            
            .btn-primary:hover {
                background-color: var(--brand-secondary);
                border-color: var(--brand-secondary);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(10, 25, 47, 0.2);
            }
            
            .btn-outline-primary {
                color: var(--brand-primary);
                border-color: var(--brand-primary);
            }
            
            .btn-outline-primary:hover {
                background-color: var(--brand-primary);
                color: white;
            }

            /* Badges */
            .badge {
                padding: 0.5em 0.8em;
                font-weight: 500;
                letter-spacing: 0.05em;
                border-radius: 6px;
            }
            
            /* Forms */
            .form-control, .form-select {
                padding: 0.6rem 1rem;
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                background-color: #fcfcfc;
            }
            
            .form-control:focus, .form-select:focus {
                border-color: var(--brand-accent);
                box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.15);
                background-color: white;
            }
            
            /* Pagination */
            .pagination {
                margin-top: 1.5rem;
                justify-content: center;
            }
            
            .page-link {
                color: var(--brand-primary);
                border: none;
                margin: 0 2px;
                border-radius: 8px !important;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .page-link:hover {
                background-color: #f0f0f0;
                color: var(--brand-primary);
            }
            
            .page-item.active .page-link {
                background-color: var(--brand-primary);
                border-color: var(--brand-primary);
            }
            
            .page-item.disabled .page-link {
                background-color: transparent;
                color: #ccc;
            }

            /* Modals */
            .modal-content {
                border: none;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            }
            
            .modal-header {
                border-bottom: 1px solid #f0f0f0;
                padding: 1.5rem;
            }
            
            .modal-footer {
                border-top: 1px solid #f0f0f0;
                padding: 1.5rem;
            }
            
            .modal-title {
                font-family: 'Outfit', sans-serif;
                font-weight: 600;
                color: var(--brand-primary);
            }
        </style>
        <!-- Optional Mobile Toggle (Logic can be added later if needed) -->
        
<?php else: ?>
    <div class="container py-5">
<?php endif; ?>
