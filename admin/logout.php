<?php
require_once '../cms/includes/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;
?>
