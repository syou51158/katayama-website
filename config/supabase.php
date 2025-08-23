<?php
/**
 * Supabase設定ファイル
 */

class SupabaseConfig {
    // Supabase接続情報（環境変数があれば優先）
    public const PROJECT_URL = 'https://iygiuutslpnvrheqbqgv.supabase.co';
    public const ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml5Z2l1dXRzbHBudnJoZXFicWd2Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDcyNjkzNzksImV4cCI6MjA2Mjg0NTM3OX0.skNBoqKqMl69jLLCyGvfS6CUY7TiCftaUOuLlrdUl10';
    public const SERVICE_ROLE_KEY = 'YOUR_SERVICE_ROLE_KEY_HERE';
    
    /**
     * Supabase APIエンドポイントのベースURL
     */
    private static function getSecrets(): ?array {
        static $secrets = null;
        if ($secrets !== null) return $secrets;
        $file = __DIR__ . '/supabase.secrets.php';
        if (file_exists($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $secrets = $loaded;
                return $secrets;
            }
        }
        $secrets = null;
        return null;
    }

    public static function getProjectUrl(): string {
        $secrets = self::getSecrets();
        if ($secrets && !empty($secrets['SUPABASE_PROJECT_URL'])) return $secrets['SUPABASE_PROJECT_URL'];
        $envUrl = getenv('SUPABASE_PROJECT_URL') ?: ($_SERVER['SUPABASE_PROJECT_URL'] ?? ($_ENV['SUPABASE_PROJECT_URL'] ?? ''));
        return $envUrl ?: self::PROJECT_URL;
    }

    public static function getAnonKey(): string {
        $secrets = self::getSecrets();
        if ($secrets && !empty($secrets['SUPABASE_ANON_KEY'])) return $secrets['SUPABASE_ANON_KEY'];
        $env = getenv('SUPABASE_ANON_KEY') ?: ($_SERVER['SUPABASE_ANON_KEY'] ?? ($_ENV['SUPABASE_ANON_KEY'] ?? ''));
        return $env ?: self::ANON_KEY;
    }

    public static function getServiceRoleKey(): string {
        $secrets = self::getSecrets();
        if ($secrets && !empty($secrets['SUPABASE_SERVICE_ROLE_KEY'])) return $secrets['SUPABASE_SERVICE_ROLE_KEY'];
        $env = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: ($_SERVER['SUPABASE_SERVICE_ROLE_KEY'] ?? ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? ''));
        return $env ?: self::SERVICE_ROLE_KEY;
    }
    
    public static function getApiUrl(): string {
        return self::getProjectUrl() . '/rest/v1/';
    }
    
    /**
     * 認証ヘッダーを取得
     */
    public static function getHeaders(bool $useServiceRole = false): array {
        $anon = self::getAnonKey();
        $service = self::getServiceRoleKey();
        $apiKey = $useServiceRole ? $service : $anon;
        return [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
    }
}
?>
