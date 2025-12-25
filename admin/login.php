<?php
require_once __DIR__ . '/../lib/SupabaseAuth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (SupabaseAuth::signInWithPassword($email, $password)) {
        header('Location: /admin/');
        exit;
    } else {
        $error = 'ログインに失敗しました。メールアドレスまたはパスワードを確認してください。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理システムログイン | 片山建設工業</title>
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
            --text-light: #E6F1FF;
            --text-muted: #8892B0;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        body {
            font-family: 'Noto Sans JP', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--brand-primary);
            background-image: 
                linear-gradient(rgba(10, 25, 47, 0.85), rgba(10, 25, 47, 0.9)),
                url('/assets/img/hero.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            overflow: hidden;
        }

        /* Ambient floating shapes for background depth */
        .ambient-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.4;
            animation: float 20s infinite ease-in-out;
        }
        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--brand-accent);
            top: -100px;
            left: -100px;
        }
        .shape-2 {
            width: 300px;
            height: 300px;
            background: #1d4ed8; /* Blue accent */
            bottom: -50px;
            right: -50px;
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 50px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: var(--glass-shadow);
            color: var(--text-light);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--brand-accent), transparent);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo {
            width: 180px;
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            background: rgba(255, 255, 255, 0.95); /* Ensure logo visibility */
            padding: 8px 16px;
            border-radius: 8px;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 0.2rem;
            text-transform: uppercase;
            color: var(--brand-accent);
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }

        .form-floating {
            margin-bottom: 1.25rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border-radius: 12px;
            height: 56px;
            padding-left: 1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.15);
            color: white;
        }

        .form-control::placeholder {
            color: transparent; /* For floating labels */
        }
        
        /* Auto-fill fix for dark theme */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #1c2e4a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-floating > label {
            color: var(--text-muted);
            padding-left: 1rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--brand-accent);
            transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
            background-color: transparent; 
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--brand-accent), #a38241);
            border: none;
            color: white;
            font-weight: 600;
            padding: 1rem;
            border-radius: 12px;
            width: 100%;
            margin-top: 1rem;
            font-size: 1rem;
            letter-spacing: 0.05em;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(197, 160, 89, 0.3);
            filter: brightness(1.1);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert-error {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff8a93;
            border-radius: 12px;
            font-size: 0.9rem;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 300;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 5;
            transition: color 0.3s;
        }
        
        .form-control:focus + .input-icon {
            color: var(--brand-accent);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-shape shape-1"></div>
    <div class="ambient-shape shape-2"></div>

    <div class="login-container">
        <div class="glass-card">
            <div class="brand-section">
                <!-- Using a white container for the logo since the original logo might be dark -->
                <img src="/assets/img/logo.svg" alt="片山建設工業" class="brand-logo">
                <span class="page-title">Management System</span>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off">
                <div class="form-floating mb-3 position-relative">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="name@example.com" required autofocus>
                    <label for="emailInput">メールアドレス</label>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" required>
                    <label for="passwordInput">パスワード</label>
                </div>

                <button type="submit" class="btn btn-submit">
                    ログイン <i class="bi bi-arrow-right-short fs-4"></i>
                </button>
            </form>
            
            <div class="footer-text">
                &copy; <?php echo date('Y'); ?> Katayama Construction Industry<br>
                All Rights Reserved.
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ログイン画面では管理者フラグを削除
        if (window.localStorage) {
            localStorage.removeItem('isAdmin');
            localStorage.removeItem('adminLoginTime');
        }
    </script>
</body>
</html>
