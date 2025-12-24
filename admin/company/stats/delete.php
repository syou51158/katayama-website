<?php
require_once __DIR__ . '/../../../lib/SupabaseClient.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        $result = SupabaseClient::delete('company_stats', ['id' => $id]);

        if ($result !== false) {
            header('Location: index.php?success=1');
            exit;
        } else {
            header('Location: index.php?error=' . urlencode('削除に失敗しました: ' . SupabaseClient::getLastError()));
            exit;
        }
    }
}

header('Location: index.php');
exit;
