<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="page-title mb-1">Dashboard</h2>
        <p class="text-muted mb-0">ようこそ、管理者画面へ</p>
    </div>
    <div class="text-muted small">
        <i class="bi bi-calendar-event me-2"></i><?php echo date('Y年m月d日'); ?>
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
