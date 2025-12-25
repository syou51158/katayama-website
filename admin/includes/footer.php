<?php if (SupabaseAuth::isLoggedIn()): ?>
    </div> <!-- End .main-content -->
<?php else: ?>
    </div> <!-- End .container -->
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 管理者としてログイン中であることを記録
    if (window.localStorage) {
        localStorage.setItem('isAdmin', 'true');
        localStorage.setItem('adminLoginTime', new Date().getTime());
    }
</script>
</body>
</html>
