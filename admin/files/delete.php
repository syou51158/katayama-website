<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../lib/SupabaseClient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    // Ideally we should delete from storage too, but SupabaseStorage class doesn't have delete method yet.
    // So just delete from DB.
    $result = SupabaseClient::delete('files', ['id' => $id]);
    if ($result === false) {
        header('Location: index.php?error=' . urlencode(SupabaseClient::getLastError()));
    } else {
        header('Location: index.php?success=deleted');
    }
} else {
    header('Location: index.php');
}
exit;
