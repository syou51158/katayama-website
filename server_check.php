<?php
header('Content-Type: text/plain');

echo "=== Server Information ===\n";
echo "Current User: " . exec('whoami') . "\n";
echo "Current Directory (getcwd): " . getcwd() . "\n";
echo "Script Path: " . __FILE__ . "\n";

echo "\n=== File List (Current Directory) ===\n";
system('ls -la');

echo "\n=== File List (Parent Directory) ===\n";
system('ls -la ..');

echo "\n=== File List (Root / ) ===\n";
system('ls -la /');

echo "\n=== File List (Home /home/users/2/deci.jp-trendcompany/web) ===\n";
system('ls -la /home/users/2/deci.jp-trendcompany/web');
?>
