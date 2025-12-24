<?php
/**
 * Supabase設定ファイル
 */

class SupabaseConfig {
    // Supabase接続情報
    public const PROJECT_URL = 'https://iygiuutslpnvrheqbqgv.supabase.co';
    public const ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml5Z2l1dXRzbHBudnJoZXFicWd2Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDcyNjkzNzksImV4cCI6MjA2Mjg0NTM3OX0.skNBoqKqMl69jLLCyGvfS6CUY7TiCftaUOuLlrdUl10';
    public const SERVICE_ROLE_KEY = 'YOUR_SERVICE_ROLE_KEY_HERE'; // サービスロールキーは管理者のみが設定
    
    /**
     * Supabase APIエンドポイントのベースURL
     */
    public static function getApiUrl(): string {
        return self::PROJECT_URL . '/rest/v1/';
    }
    
    /**
     * 認証ヘッダーを取得
     */
    public static function getHeaders(bool $useServiceRole = false): array {
        $apiKey = $useServiceRole ? self::SERVICE_ROLE_KEY : self::ANON_KEY;
        return [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
    }
}
?>
