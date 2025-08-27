<?php
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginWithSupabase($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        // 初回は管理者ユーザーを自動作成（ローカル開発でのみ有効）
        $created = false;
        $enableAuto = (getenv('SUPABASE_ENABLE_AUTO_USER') === '1') 
            || (($_ENV['SUPABASE_ENABLE_AUTO_USER'] ?? '') === '1') 
            || (($_SERVER['SUPABASE_ENABLE_AUTO_USER'] ?? '') === '1');
        if ($enableAuto && $email && $password) {
            $targetEmail = $email;
            $created = SupabaseAuth::adminCreateUser($targetEmail, $password, ['full_name' => 'Administrator'], true);
            if ($created && loginWithSupabase($targetEmail, $password)) {
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'メールアドレスまたはパスワードが正しくありません。';
    }
}

// すでにログインしている場合はダッシュボードにリダイレクト
if (SupabaseAuth::isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン - 片山建設工業 CMS</title>
    
    <!-- Tailwind (built) -->
    <link rel="stylesheet" href="../assets/css/build.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap');
        body { font-family: 'Noto Sans JP', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-primary to-blue-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <img src="../assets/img/logo.svg" alt="片山建設工業" class="h-12 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-primary">管理者ログイン</h1>
            <p class="text-gray-600 mt-2">CMS管理画面にアクセス</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">メールアドレス</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                       placeholder="メールアドレスを入力">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">パスワード</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200"
                       placeholder="パスワードを入力">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-700">ログイン状態を保持する</label>
            </div>
            
            <button type="submit" 
                    class="w-full bg-primary text-white py-3 px-4 rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition duration-200 font-medium">
                ログイン
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="../index.html" class="text-sm text-gray-600 hover:text-primary transition duration-200">
                ← サイトに戻る
            </a>
        </div>
    </div>
    
    <script>
        // フォームのバリデーション
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email.trim() || !password.trim()) {
                e.preventDefault();
                alert('メールアドレスとパスワードを入力してください。');
            }
        });
        
        // エンターキーでログイン
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
