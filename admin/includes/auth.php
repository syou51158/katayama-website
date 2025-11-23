<?php
require_once __DIR__ . '/../../lib/SupabaseAuth.php';

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    }
}

function checkAuth() {
    if (!SupabaseAuth::isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
    return true;
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
?>

