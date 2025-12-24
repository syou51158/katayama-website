<?php
/**
 * 環境変数管理
 */
class Env {
    private static $loaded = false;
    
    public static function load(string $file): void {
        if (!file_exists($file)) return;
        if (self::$loaded) return;
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        self::$loaded = true;
    }
    
    public static function get(string $key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}