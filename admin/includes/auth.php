<?php
<<<<<<< HEAD
/**
 * 管理画面用認証ファイル
 */

session_start();

/**
 * 認証チェック（簡易版）
 * 本来は適切なログイン機能が必要ですが、今回は開発用として簡略化
 */
function checkAuth() {
    // 開発環境では常にtrueを返す（本番環境では適切な認証を実装）
    return true;
    
    // 本番環境用のコード例（コメントアウト）
    /*
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
=======
require_once __DIR__ . '/../../lib/SupabaseAuth.php';

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    }
}

function checkAuth() {
    if (!SupabaseAuth::isLoggedIn()) {
>>>>>>> 82c831298bb2405620692e687e44f5d7d5eb8485
        header('Location: /admin/login.php');
        exit;
    }
    return true;
<<<<<<< HEAD
    */
}

/**
 * ログイン処理
 */
function login($username, $password) {
    // 簡易認証（本番環境では適切なハッシュ化とデータベース認証が必要）
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

/**
 * ログアウト処理
 */
function logout() {
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}

/**
 * 現在のユーザー情報を取得
 */
function getCurrentUser() {
    return [
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'logged_in' => $_SESSION['admin_logged_in'] ?? true
    ];
}
?>



=======
}

function loginWithSupabase($email, $password) {
    return SupabaseAuth::signInWithPassword($email, $password);
}

function logoutSupabase() {
    SupabaseAuth::logout();
}

function getCurrentUser() {
    $user = SupabaseAuth::getCurrentUser();
    if (!$user) return [ 'username' => '管理者', 'logged_in' => false ];

    $username = $user['user_metadata']['full_name'] ?? ($user['email'] ?? '管理者');
    return [ 'username' => $username, 'logged_in' => true, 'raw' => $user ];
}
>>>>>>> 82c831298bb2405620692e687e44f5d7d5eb8485
