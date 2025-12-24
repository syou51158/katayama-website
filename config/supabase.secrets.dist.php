<?php
class SupabaseConfig {
    private const PROJECT_URL = '';
    private const ANON_KEY = '';
    private const SERVICE_ROLE_KEY = '';

    public static function getProjectUrl(): string { return self::PROJECT_URL; }
    public static function getAnonKey(): string { return self::ANON_KEY; }
    public static function getServiceRoleKey(): string { return self::SERVICE_ROLE_KEY; }
    public static function getApiUrl(): string { return rtrim(self::getProjectUrl(), '/') . '/rest/v1/'; }
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

    public static function isOfflineMode(): bool {
        $v = getenv('SUPABASE_OFFLINE');
        if ($v === false) return false;
        $v = strtolower(trim((string)$v));
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }
}
