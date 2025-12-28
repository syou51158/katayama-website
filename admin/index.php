<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="page-title mb-1">Dashboard</h2>
        <p class="text-muted mb-0">ようこそ、管理者画面へ</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button id="dashboard-view-site-btn" class="btn btn-primary d-flex align-items-center shadow-sm">
            <i class="bi bi-eye me-2"></i>公開サイトを見る
        </button>
        <div class="text-muted small">
            <i class="bi bi-calendar-event me-2"></i><?php echo date('Y年m月d日'); ?>
        </div>
    </div>
</div>

<style>
    .dashboard-card {
        background: white;
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        height: 100%;
        overflow: hidden;
        position: relative;
        text-decoration: none;
        display: block;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }
    
    .dashboard-card:hover .icon-box {
        transform: scale(1.1);
        background: var(--brand-primary);
        color: white;
    }

    .dashboard-card-body {
        padding: 1.5rem;
    }

    .icon-box {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    /* Card Themes */
    .card-inquiry .icon-box { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .card-inquiry:hover .icon-box { background: #0d6efd; color: white; }

    .card-job .icon-box { background: rgba(25, 135, 84, 0.1); color: #198754; }
    .card-job:hover .icon-box { background: #198754; color: white; }

    .card-property .icon-box { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .card-property:hover .icon-box { background: #ffc107; color: white; }

    .card-file .icon-box { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    .card-file:hover .icon-box { background: #0dcaf0; color: white; }

    /* New Content Management Cards */
    .card-news .icon-box { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .card-news:hover .icon-box { background: #dc3545; color: white; }

    .card-works .icon-box { background: rgba(102, 16, 242, 0.1); color: #6610f2; }
    .card-works:hover .icon-box { background: #6610f2; color: white; }

    .card-services .icon-box { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
    .card-services:hover .icon-box { background: #fd7e14; color: white; }

    .card-company .icon-box { background: rgba(32, 201, 151, 0.1); color: #20c997; }
    .card-company:hover .icon-box { background: #20c997; color: white; }

    .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--brand-primary);
        margin-bottom: 0.5rem;
    }

    .card-text {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin-bottom: 1rem;
    }

    .card-link-text {
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        opacity: 0.8;
    }
</style>

<h5 class="text-muted mb-3 mt-2 small text-uppercase fw-bold ls-1">ウェブサイト管理</h5>
<div class="row g-4 mb-5">
    <!-- News Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/news/" class="dashboard-card card-news">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-newspaper"></i>
                </div>
                <h5 class="card-title">お知らせ</h5>
                <p class="card-text">ニュースやイベント情報の更新を行います。</p>
                <div class="card-link-text text-danger">
                    編集する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Works Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/works/" class="dashboard-card card-works">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-images"></i>
                </div>
                <h5 class="card-title">施工実績</h5>
                <p class="card-text">施工事例の写真や詳細を追加・編集します。</p>
                <div class="card-link-text text-primary">
                    編集する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Services Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/services/" class="dashboard-card card-services">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-tools"></i>
                </div>
                <h5 class="card-title">事業内容</h5>
                <p class="card-text">提供サービスの内容や説明を編集します。</p>
                <div class="card-link-text text-warning">
                    編集する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Company Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/company/" class="dashboard-card card-company">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-building"></i>
                </div>
                <h5 class="card-title">会社情報</h5>
                <p class="card-text">会社概要、沿革、代表者情報の管理を行います。</p>
                <div class="card-link-text text-success">
                    編集する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<h5 class="text-muted mb-3 small text-uppercase fw-bold ls-1">業務管理</h5>
<div class="row g-4">
    <!-- Inquiries Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/inquiries/" class="dashboard-card card-inquiry">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-envelope"></i>
                </div>
                <h5 class="card-title">新着確認</h5>
                <p class="card-text">お問い合わせの一覧を確認・管理します。</p>
                <div class="card-link-text text-primary">
                    確認する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Jobs Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/jobs/" class="dashboard-card card-job">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-briefcase"></i>
                </div>
                <h5 class="card-title">求人管理</h5>
                <p class="card-text">求人情報の追加・編集を行います。</p>
                <div class="card-link-text text-success">
                    管理する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Properties Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/properties/" class="dashboard-card card-property">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-building"></i>
                </div>
                <h5 class="card-title">物件管理</h5>
                <p class="card-text">物件情報の登録・更新を行います。</p>
                <div class="card-link-text text-warning">
                    管理する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Files Card -->
    <div class="col-md-6 col-lg-3">
        <a href="/admin/files/" class="dashboard-card card-file">
            <div class="dashboard-card-body">
                <div class="icon-box">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h5 class="card-title">ファイル管理</h5>
                <p class="card-text">関連ファイルのアップロード管理を行います。</p>
                <div class="card-link-text text-info">
                    管理する <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
