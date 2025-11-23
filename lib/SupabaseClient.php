<?php
$__cfg = __DIR__ . '/../config/supabase.secrets.php';
if (file_exists($__cfg)) { require_once $__cfg; } else { require_once __DIR__ . '/../config/supabase.secrets.dist.php'; }

/**
 * Supabaseクライアントクラス
 */
class SupabaseClient {
    private static $lastError = null;
    
    /**
     * GETリクエストを実行
     * 
     * @param string $table テーブル名
     * @param array $filters フィルター条件
     * @param array $options オプション（select, order, limit等）
     * @return array|false
     */
    public static function select(string $table, array $filters = [], array $options = []) {
        $url = SupabaseConfig::getApiUrl() . $table;
        
        // クエリパラメータの構築
        $queryParams = [];
        
        // SELECT句
        if (isset($options['select'])) {
            $queryParams['select'] = $options['select'];
        }
        
        // フィルター条件
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                // 演算子指定の場合（例: ['operator' => 'eq', 'value' => 'published']）
                $operator = $value['operator'] ?? 'eq';
                $queryParams[$column] = $operator . '.' . $value['value'];
            } else {
                // 単純な等価比較
                $queryParams[$column] = 'eq.' . $value;
            }
        }
        
        // 並び順
        if (isset($options['order'])) {
            $queryParams['order'] = $options['order'];
        }
        
        // 件数制限
        if (isset($options['limit'])) {
            $queryParams['limit'] = $options['limit'];
        }
        
        // オフセット
        if (isset($options['offset'])) {
            $queryParams['offset'] = $options['offset'];
        }
        
        // URLにクエリパラメータを追加
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        return self::makeRequest('GET', $url);
    }
    
    /**
     * POSTリクエストを実行（INSERT）
     * 
     * @param string $table テーブル名
     * @param array $data 挿入するデータ
     * @return array|false
     */
    public static function insert(string $table, array $data) {
        $url = SupabaseConfig::getApiUrl() . $table;
        return self::makeRequest('POST', $url, $data, true);
    }
    
    /**
     * PUTリクエストを実行（UPDATE）
     * 
     * @param string $table テーブル名
     * @param array $data 更新するデータ
     * @param array $filters 更新対象のフィルター条件
     * @return array|false
     */
    public static function update(string $table, array $data, array $filters = []) {
        $url = SupabaseConfig::getApiUrl() . $table;
        
        // フィルター条件をクエリパラメータに追加
        if (!empty($filters)) {
            $queryParams = [];
            foreach ($filters as $column => $value) {
                if (is_array($value)) {
                    $operator = $value['operator'] ?? 'eq';
                    $queryParams[$column] = $operator . '.' . $value['value'];
                } else {
                    $queryParams[$column] = 'eq.' . $value;
                }
            }
            $url .= '?' . http_build_query($queryParams);
        }
        
        return self::makeRequest('PATCH', $url, $data, true);
    }
    
    /**
     * DELETEリクエストを実行
     * 
     * @param string $table テーブル名
     * @param array $filters 削除対象のフィルター条件
     * @return array|false
     */
    public static function delete(string $table, array $filters = []) {
        $url = SupabaseConfig::getApiUrl() . $table;
        
        // フィルター条件をクエリパラメータに追加
        if (!empty($filters)) {
            $queryParams = [];
            foreach ($filters as $column => $value) {
                if (is_array($value)) {
                    $operator = $value['operator'] ?? 'eq';
                    $queryParams[$column] = $operator . '.' . $value['value'];
                } else {
                    $queryParams[$column] = 'eq.' . $value;
                }
            }
            $url .= '?' . http_build_query($queryParams);
        }
        
        return self::makeRequest('DELETE', $url, null, true);
    }
    
    /**
     * 実際のHTTPリクエストを実行
     * 
     * @param string $method HTTPメソッド
     * @param string $url リクエストURL
     * @param array|null $data 送信データ
     * @param bool $useServiceKey サービスロールキーを使用するか
     * @return array|false
     */
    private static function makeRequest(string $method, string $url, ?array $data = null, bool $useServiceKey = false) {
        $apiKey = $useServiceKey ? SupabaseConfig::getServiceRoleKey() : SupabaseConfig::getAnonKey();
        $headers = [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        if (in_array($method, ['POST','PUT','PATCH','DELETE'])) {
            $headers[] = 'Prefer: return=representation';
        }

        if (function_exists('curl_init')) {
            $ch = curl_init();
            $disableSsl = true; // Windows環境のためSSL検証を無効化
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => $disableSsl ? false : true,
                CURLOPT_SSL_VERIFYHOST => $disableSsl ? 0 : 2
            ]);
            if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($error) {
                self::$lastError = 'curl error: ' . $error;
                error_log('Supabase API Error: ' . $error);
                return false;
            }
            if ($httpCode >= 200 && $httpCode < 300) {
                $decoded = json_decode($response, true);
                return $decoded ?: [];
            } else {
                self::$lastError = 'HTTP ' . $httpCode . ' - ' . $response;
                error_log('Supabase API Error: HTTP ' . $httpCode . ' - Response: ' . $response . ' - URL: ' . $url . ' - Method: ' . $method . ' - Headers: ' . json_encode($headers));
                return false;
            }
        }

        $disableSsl = true; // Windows環境のためSSL検証を無効化
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) ? json_encode($data) : '',
                'ignore_errors' => true,
                'timeout' => 30,
            ],
            'ssl' => [
                'verify_peer' => $disableSsl ? false : true,
                'verify_peer_name' => $disableSsl ? false : true,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        $httpCode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/', $h, $m)) {
                    $httpCode = (int)$m[1];
                    break;
                }
            }
        }
        if ($response === false) {
            self::$lastError = 'stream error';
            error_log('Supabase API Error: stream error');
            return false;
        }
        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($response, true);
            return $decoded ?: [];
        } else {
            self::$lastError = 'HTTP ' . $httpCode . ' - ' . $response;
            error_log('Supabase API Error: HTTP ' . $httpCode . ' - Response: ' . $response . ' - URL: ' . $url . ' - Method: ' . $method . ' - Headers: ' . json_encode($headers));
            return false;
        }
    }

    public static function getLastError(): ?string {
        return self::$lastError;
    }
    
    /**
     * アクティブなニュースを取得
     */
    public static function getActiveNews(int $limit = 10, int $offset = 0, ?string $category = null): array {
        $filters = ['status' => 'published'];
        if ($category && $category !== 'all') {
            $filters['category'] = $category;
        }
        
        $result = self::select('news', $filters, [
            'order' => 'published_date.desc,created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        return $result ?: [];
    }
    
    /**
     * アクティブな施工実績を取得
     */
    public static function getActiveWorks(int $limit = 20, int $offset = 0, ?string $category = null): array {
        $filters = ['status' => 'published'];
        if ($category && $category !== 'all') {
            $filters['category'] = $category;
        }
        
        $result = self::select('works', $filters, [
            'order' => 'completion_date.desc,created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        return $result ?: [];
    }
    
    /**
     * アクティブなサービスを取得
     */
    public static function getActiveServices(): array {
        $result = self::select('services', 
            ['status' => 'active'], 
            [
                'order' => 'sort_order.asc,created_at.asc'
            ]
        );
        
        return $result ?: [];
    }
    
    /**
     * アクティブなお客様の声を取得
     */
    public static function getActiveTestimonials(int $limit = 10): array {
        $result = self::select('testimonials', 
            ['status' => 'published'], 
            [
                'order' => 'created_at.desc',
                'limit' => $limit
            ]
        );
        
        return $result ?: [];
    }
    
    /**
     * アクティブな会社統計を取得
     */
    public static function getActiveStats(): array {
        $result = self::select('company_stats', [], [
            'order' => 'created_at.asc'
        ]);
        
        return $result ?: [];
    }
    
    /**
     * アクティブなパートナー企業を取得
     */
    public static function getActivePartners(): array {
        $result = self::select('partners', ['status' => 'active'], [
            'order' => 'created_at.asc'
        ]);
        
        return $result ?: [];
    }
    
    /**
     * アクティブな代表情報を取得
     */
    public static function getActiveRepresentatives(): array {
        $result = self::select('representatives', 
            ['status' => 'active'], 
            [
                'order' => 'sort_order.asc,created_at.asc'
            ]
        );
        
        return $result ?: [];
    }
    
    /**
     * サイト設定を取得
     */
    public static function getSiteSetting(string $key): ?string {
        $result = self::select('site_settings', 
            ['setting_key' => $key], 
            [
                'select' => 'setting_value',
                'limit' => 1
            ]
        );
        
        return $result && count($result) > 0 ? $result[0]['setting_value'] : null;
    }
    
    /**
     * すべてのサイト設定を連想配列で取得
     */
    public static function getAllSiteSettings(): array {
        $result = self::select('site_settings');
        
        $settings = [];
        if ($result) {
            foreach ($result as $setting) {
                $settings[$setting['setting_key']] = $setting['setting_value'];
            }
        }
        
        return $settings;
    }
    
    /**
     * 会社情報を取得
     */
    public static function getCompanyInfo(): ?array {
        $result = self::select('company_info', [], ['limit' => 1]);
        return $result && count($result) > 0 ? $result[0] : null;
    }
    
    /**
     * 会社沿革を取得
     */
    public static function getCompanyHistory(): array {
        $result = self::select('company_history', 
            ['status' => 'active'], 
            [
                'order' => 'year.asc,month.asc'
            ]
        );
        
        return $result ?: [];
    }
}