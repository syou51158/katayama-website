<?php
require_once __DIR__ . '/../config/supabase.php';

/**
 * Supabaseクライアントクラス
 */
class SupabaseClient {
    
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
     * PATCHリクエストを実行（UPDATE）
     * 
     * @param string $table テーブル名
     * @param array $data 更新するデータ
     * @param array $filters 更新条件
     * @return array|false
     */
    public static function update(string $table, array $data, array $filters) {
        $url = SupabaseConfig::getApiUrl() . $table;
        
        // フィルター条件をクエリパラメータに追加
        $queryParams = [];
        foreach ($filters as $column => $value) {
            $queryParams[$column] = 'eq.' . $value;
        }
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        return self::makeRequest('PATCH', $url, $data, true);
    }
    
    /**
     * DELETEリクエストを実行
     * 
     * @param string $table テーブル名
     * @param array $filters 削除条件
     * @return array|false
     */
    public static function delete(string $table, array $filters) {
        $url = SupabaseConfig::getApiUrl() . $table;
        
        // フィルター条件をクエリパラメータに追加
        $queryParams = [];
        foreach ($filters as $column => $value) {
            $queryParams[$column] = 'eq.' . $value;
        }
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        return self::makeRequest('DELETE', $url, null, true);
    }
    
    /**
     * HTTPリクエストを実行
     * 
     * @param string $method HTTPメソッド
     * @param string $url URL
     * @param array|null $data 送信データ
     * @param bool $useServiceRole サービスロールキーを使用するか
     * @return array|false
     */
    private static function makeRequest(string $method, string $url, array $data = null, bool $useServiceRole = false) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => SupabaseConfig::getHeaders($useServiceRole),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("cURL Error: " . $error);
            return false;
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("HTTP Error {$httpCode}: " . $response);
            error_log("Request URL: " . $url);
            error_log("Request Method: " . $method);
            if ($data !== null) {
                error_log("Request Data: " . json_encode($data));
            }
            return false;
        }
        
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            return false;
        }
        
        return $decodedResponse;
    }
    
    /**
     * 公開済みニュースを取得
     */
    public static function getPublishedNews(int $limit = 10, int $offset = 0): array {
        $result = self::select('news', 
            ['status' => 'published'], 
            [
                'order' => 'published_date.desc,created_at.desc',
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
        return $result ?: [];
    }
    
    /**
     * 公開済み施工実績を取得
     */
    public static function getPublishedWorks(string $category = null, int $limit = 20, int $offset = 0): array {
        $filters = ['status' => 'published'];
        if ($category) {
            $filters['category'] = $category;
        }
        
        $result = self::select('works', 
            $filters, 
            [
                'order' => 'completion_date.desc,created_at.desc',
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
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
            ['status' => 'active'], 
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
        $result = self::select('company_stats', 
            ['status' => 'active'], 
            [
                'order' => 'sort_order.asc,created_at.asc'
            ]
        );
        
        return $result ?: [];
    }
    
    /**
     * アクティブなパートナー企業を取得
     */
    public static function getActivePartners(): array {
        $result = self::select('partners', 
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
}
?>

