<?php
require_once __DIR__ . '/../../lib/SupabaseClient.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div class="d-flex align-items-center">
        <i class="bi bi-building fs-4 me-2 text-primary"></i>
        <h1 class="h2 mb-0">会社情報管理</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <a href="info.php" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 card-hover-effect">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-info-circle fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-2">基本情報</h5>
                    <p class="text-muted small">会社名、住所、電話番号などの基本情報を編集・管理します。</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="representatives/index.php" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 card-hover-effect">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-2">代表者情報</h5>
                    <p class="text-muted small">代表者の挨拶、経歴、写真を編集・管理します。</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="history/index.php" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 card-hover-effect">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-2">会社沿革</h5>
                    <p class="text-muted small">会社の歴史、年表を編集・管理します。</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="partners/index.php" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 card-hover-effect">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-2">パートナー企業</h5>
                    <p class="text-muted small">協力会社やパートナー企業の情報を管理します。</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="stats/index.php" class="text-decoration-none">
            <div class="card shadow-sm h-100 border-0 card-hover-effect">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-graph-up-arrow fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold text-dark mb-2">会社統計</h5>
                    <p class="text-muted small">施工件数や創立年数などの統計データを管理します。</p>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.card-hover-effect {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card-hover-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
