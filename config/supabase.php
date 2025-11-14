<?php
/**
 * Supabase設定ファイル
 */

class SupabaseConfig {
    // Supabase接続情報（環境変数があれば優先）
    public const PROJECT_URL = 'https://kmdoqdsftiorzmjczzyk.supabase.co';
    public const ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImttZG9xZHNmdGlvcnptamN6enlrIiwicm9zZSI6ImFub24iLCJpYXQiOjE3NjI5NTIyODIsImV4cCI6MjA3ODUyODI4Mn0.ZoztxEfNKUX1iMuvV0czfywvyNuxMXY2fhRFeoycBIQ';
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
        $secret = $secrets['SUPABASE_PROJECT_URL'] ?? '';
        if ($secret && $secret !== 'CHANGE_ME') return $secret;
        $envUrl = getenv('SUPABASE_PROJECT_URL') ?: ($_SERVER['SUPABASE_PROJECT_URL'] ?? ($_ENV['SUPABASE_PROJECT_URL'] ?? ''));
        return $envUrl ?: self::PROJECT_URL;
    }

    public static function getAnonKey(): string {
        $secrets = self::getSecrets();
        $secret = $secrets['SUPABASE_ANON_KEY'] ?? '';
        if ($secret && $secret !== 'CHANGE_ME') return $secret;
        $env = getenv('SUPABASE_ANON_KEY') ?: ($_SERVER['SUPABASE_ANON_KEY'] ?? ($_ENV['SUPABASE_ANON_KEY'] ?? ''));
        return $env ?: self::ANON_KEY;
    }

    public static function getServiceRoleKey(): string {
        $secrets = self::getSecrets();
        $secret = $secrets['SUPABASE_SERVICE_ROLE_KEY'] ?? '';
        if ($secret && $secret !== 'CHANGE_ME') return $secret;
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
