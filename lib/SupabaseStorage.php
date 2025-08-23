<?php
require_once __DIR__ . '/../config/supabase.php';

class SupabaseStorage {
    public static function getStorageBaseUrl(): string {
        return rtrim(SupabaseConfig::getProjectUrl(), '/') . '/storage/v1';
    }

    public static function getPublicObjectUrl(string $bucket, string $path): string {
        $base = self::getStorageBaseUrl() . '/object/public/';
        // bucket/path をエンコードしすぎないように処理
        return $base . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($path));
    }

    public static function ensureBucket(string $bucket, bool $isPublic = true): bool {
        $serviceKey = SupabaseConfig::getServiceRoleKey();
        if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
            error_log('SupabaseStorage: SERVICE_ROLE_KEY is not set');
            return false;
        }

        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $serviceKey,
            'Authorization: Bearer ' . $serviceKey,
        ];

        // 1) 既存確認
        $checkUrl = self::getStorageBaseUrl() . '/bucket/' . rawurlencode($bucket);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $checkUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200) return true; // 存在

        // 2) 作成
        $createUrl = self::getStorageBaseUrl() . '/bucket';
        $payload = json_encode(['name' => $bucket, 'public' => $isPublic]);
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL => $createUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch2);
        $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        if ($code2 >= 200 && $code2 < 300) return true;
        if ($code2 === 409) return true; // 既に存在
        error_log('SupabaseStorage ensureBucket error: ' . $code2 . ' ' . $response);
        return false;
    }

    public static function upload(string $bucket, string $objectPath, string $contents, string $contentType = 'application/octet-stream', bool $upsert = true): bool {
        $serviceKey = SupabaseConfig::getServiceRoleKey();
        if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
            error_log('SupabaseStorage: SERVICE_ROLE_KEY is not set');
            return false;
        }

        $url = self::getStorageBaseUrl() . '/object/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($objectPath));
        $headers = [
            'Content-Type: ' . $contentType,
            'x-upsert: ' . ($upsert ? 'true' : 'false'),
            'apikey: ' . $serviceKey,
            'Authorization: Bearer ' . $serviceKey,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $contents,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 60,
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('SupabaseStorage upload curl error: ' . $err);
            return false;
        }
        if ($code < 200 || $code >= 300) {
            error_log('SupabaseStorage upload http ' . $code . ' ' . $response);
            return false;
        }
        return true;
    }
}
?>


