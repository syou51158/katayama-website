<?php
require_once __DIR__ . '/../lib/SupabaseAuth.php';
SupabaseAuth::logout();
header('Location: /admin/login.php');
exit;
