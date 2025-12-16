<?php
$__cfg = __DIR__ . '/../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../config/supabase.secrets.dist.php'; }

class SupabaseStorage {
    private static function curlCliRequest(string $method, string $url, array $headers, ?string $body = null, ?string $binaryFilePath = null): array {
        $curlPath = 'curl';
        $cmdHeaders = [];
        foreach ($headers as $h) {
            $cmdHeaders[] = '-H ' . escapeshellarg($h);
        }

        $bodyArg = '';
        if ($binaryFilePath !== null) {
            $bodyArg = '--data-binary ' . escapeshellarg('@' . $binaryFilePath);
        } elseif ($body !== null && $body !== '') {
            $bodyArg = '-d ' . escapeshellarg($body);
        }

        $cmd = $curlPath . ' -sS --connect-timeout 10 --max-time 30 -X ' . escapeshellarg($method) . ' ' . implode(' ', $cmdHeaders) . ' ' . $bodyArg . ' -w "\\n%{http_code}" ' . escapeshellarg($url);
        $output = [];
        $exitCode = 0;
        @exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || empty($output)) {
            return [0, '', 'curl_cli_failed'];
        }

        $codeLine = array_pop($output);
        $httpCode = (int)trim((string)$codeLine);
        $response = implode("\n", $output);
        return [$httpCode, $response, ''];
    }

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
        
        // Windows環境や特定のPHP設定でSSL検証エラーが出る場合のためのコンテキストオプション
        $contextOptions = [
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $checkUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            if ($err) {
                error_log('SupabaseStorage check bucket curl error: ' . $err);
                [$codeCli, $respCli, $errCli] = self::curlCliRequest('GET', $checkUrl, $headers);
                if ($errCli === '' && $codeCli === 200) return true;
            } else {
                if ($code === 200) return true;
            }
        } else {
            [$codeCli, $respCli, $errCli] = self::curlCliRequest('GET', $checkUrl, $headers);
            if ($errCli === '' && $codeCli === 200) return true;
        }

        // 2) 作成
        $createUrl = self::getStorageBaseUrl() . '/bucket';
        $payload = json_encode(['name' => $bucket, 'public' => $isPublic, 'file_size_limit' => null, 'allowed_mime_types' => null]);
        
        if (function_exists('curl_init')) {
            $ch2 = curl_init();
            curl_setopt_array($ch2, [
                CURLOPT_URL => $createUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($ch2);
            $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            $err = curl_error($ch2);
            curl_close($ch2);
            
            if ($err) {
                error_log('SupabaseStorage create bucket curl error: ' . $err);
                $tmpFile = tempnam(sys_get_temp_dir(), 'sb_json_');
                if ($tmpFile === false) return false;
                try {
                    if (file_put_contents($tmpFile, $payload) === false) return false;
                    [$codeCli, $respCli, $errCli] = self::curlCliRequest('POST', $createUrl, $headers, null, $tmpFile);
                    if ($errCli !== '') return false;
                    if ($codeCli >= 200 && $codeCli < 300) return true;
                    if ($codeCli === 409) return true;
                    error_log('SupabaseStorage ensureBucket error: ' . $codeCli . ' ' . $respCli);
                    return false;
                } finally {
                    @unlink($tmpFile);
                }
            }
            if ($code2 >= 200 && $code2 < 300) return true;
            if ($code2 === 409) return true; // 既に存在（念のため）
            error_log('SupabaseStorage ensureBucket error: ' . $code2 . ' ' . $response);
            return false;
        } else {
            $tmpFile = tempnam(sys_get_temp_dir(), 'sb_json_');
            if ($tmpFile === false) return false;
            try {
                if (file_put_contents($tmpFile, $payload) === false) return false;
                [$codeCli, $respCli, $errCli] = self::curlCliRequest('POST', $createUrl, $headers, null, $tmpFile);
                if ($errCli !== '') return false;
                if ($codeCli >= 200 && $codeCli < 300) return true;
                if ($codeCli === 409) return true;
                error_log('SupabaseStorage ensureBucket error: ' . $codeCli . ' ' . $respCli);
                return false;
            } finally {
                @unlink($tmpFile);
            }
        }
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

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $contents,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 60,
            ]);
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                error_log('SupabaseStorage upload curl error: ' . $err);
                $tmpFile = tempnam(sys_get_temp_dir(), 'sb_up_');
                if ($tmpFile === false) {
                    return false;
                }
                try {
                    if (file_put_contents($tmpFile, $contents) === false) {
                        return false;
                    }
                    [$codeCli, $respCli, $errCli] = self::curlCliRequest('POST', $url, $headers, null, $tmpFile);
                    if ($errCli !== '') {
                        return false;
                    }
                    if ($codeCli < 200 || $codeCli >= 300) {
                        error_log('SupabaseStorage upload http ' . $codeCli . ' ' . $respCli);
                        return false;
                    }
                    return true;
                } finally {
                    @unlink($tmpFile);
                }
            }
            if ($code < 200 || $code >= 300) {
                error_log('SupabaseStorage upload http ' . $code . ' ' . $response);
                $decoded = json_decode($response, true);
                if ($decoded && isset($decoded['message'])) {
                    error_log('Supabase Error Message: ' . $decoded['message']);
                }
                return false;
            }
            return true;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'sb_up_');
        if ($tmpFile === false) {
            return false;
        }
        try {
            if (file_put_contents($tmpFile, $contents) === false) {
                return false;
            }
            [$codeCli, $respCli, $errCli] = self::curlCliRequest('POST', $url, $headers, null, $tmpFile);
            if ($errCli !== '') {
                error_log('SupabaseStorage upload stream error');
                return false;
            }
            if ($codeCli < 200 || $codeCli >= 300) {
                error_log('SupabaseStorage upload http ' . $codeCli . ' ' . $respCli);
                $decoded = json_decode($respCli, true);
                if ($decoded && isset($decoded['message'])) {
                    error_log('Supabase Error Message: ' . $decoded['message']);
                }
                return false;
            }
            return true;
        } finally {
            @unlink($tmpFile);
        }
    }
}
