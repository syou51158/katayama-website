<?php
// パスワードハッシュ生成スクリプト

$password = 'password'; // デフォルトパスワード
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "パスワード: {$password}\n";
echo "ハッシュ: {$hash}\n\n";

echo "settings.jsonの更新:\n";
echo '"password": "' . $hash . '"' . "\n";
?>






