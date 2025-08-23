<?php
require_once 'includes/auth.php';

logoutSupabase();
header('Location: login.php');
exit;
?>
