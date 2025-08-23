<?php
require_once __DIR__ . '/../config/supabase.php';

class SupabaseAuth {
    public static function signInWithPassword(string $email, string $password): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $projectUrl = rtrim(SupabaseConfig::getProjectUrl(), '/');
        $anonKey = SupabaseConfig::getAnonKey();
        $url = $projectUrl . '/auth/v1/token?grant_type=password';

        $payload = json_encode([
            'email' => $email,
            'password' => $password,
        ]);

        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $anonKey,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log('SupabaseAuth signIn error: ' . $error);
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('SupabaseAuth signIn http ' . $httpCode . ' ' . $response);
            return false;
        }

        $json = json_decode($response, true);
        if (!is_array($json) || empty($json['access_token']) || empty($json['user'])) {
            return false;
        }

        $_SESSION['sb_access_token'] = $json['access_token'];
        $_SESSION['sb_refresh_token'] = $json['refresh_token'] ?? null;
        $_SESSION['sb_user'] = $json['user'];
        $_SESSION['sb_login_time'] = time();
        return true;
    }

    public static function getCurrentUser(): ?array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['sb_user'] ?? null;
    }

    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return !empty($_SESSION['sb_access_token']) && !empty($_SESSION['sb_user']);
    }

    public static function adminCreateUser(string $email, string $password, array $userMeta = [], bool $emailConfirmed = true): bool {
        $projectUrl = rtrim(SupabaseConfig::getProjectUrl(), '/');
        $serviceKey = SupabaseConfig::getServiceRoleKey();
        if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
            error_log('SupabaseAuth adminCreateUser: SERVICE_ROLE_KEY missing');
            return false;
        }

        $url = $projectUrl . '/auth/v1/admin/users';
        $payload = json_encode([
            'email' => $email,
            'password' => $password,
            'email_confirm' => $emailConfirmed,
            'user_metadata' => $userMeta,
        ]);

        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $serviceKey,
            'Authorization: Bearer ' . $serviceKey,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log('SupabaseAuth adminCreateUser curl error: ' . $error);
            return false;
        }
        // 201 Created, 200 OK, 409 Conflict(既に存在)
        if ($httpCode === 409) return true;
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('SupabaseAuth adminCreateUser http ' . $httpCode . ' ' . $response);
            return false;
        }
        return true;
    }

    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
}
?>


