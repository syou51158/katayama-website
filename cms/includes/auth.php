<?php
session_start();

class Auth {
    private $settingsFile = __DIR__ . '/../data/settings.json';
    
    public function __construct() {
        if (!file_exists($this->settingsFile)) {
            throw new Exception('設定ファイルが見つかりません。');
        }
    }
    
    public function login($username, $password) {
        $settings = $this->getSettings();
        
        if ($username === $settings['admin']['username'] && 
            password_verify($password, $settings['admin']['password'])) {
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['login_time'] = time();
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            return false;
        }
        
        $settings = $this->getSettings();
        $sessionTimeout = $settings['admin']['session_timeout'];
        
        if (time() - $_SESSION['login_time'] > $sessionTimeout) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }
    
    private function getSettings() {
        $content = file_get_contents($this->settingsFile);
        return json_decode($content, true);
    }
}
?>
