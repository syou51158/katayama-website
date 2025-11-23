<?php
require_once '../../lib/SupabaseClient.php';
require_once '../includes/auth.php';

checkAuth();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            throw new Exception('未対応のHTTPメソッドです');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

function handleGet() {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $rep = SupabaseClient::select('representatives', ['id' => $id]);
        if ($rep && count($rep) > 0) {
            echo json_encode(['success' => true, 'data' => $rep[0]], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => '代表者が見つかりません'], JSON_UNESCAPED_UNICODE);
        }
        return;
    }
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);
    $list = SupabaseClient::select('representatives', [], [
        'order' => 'sort_order.asc,created_at.asc',
        'limit' => $limit,
        'offset' => $offset
    ]);
    echo json_encode(['success' => true, 'data' => $list ?: []], JSON_UNESCAPED_UNICODE);
}

function normalizeInput(array $input): array {
    $career = $input['career'] ?? [];
    $education = $input['education'] ?? [];
    if (is_string($career)) $career = array_values(array_filter(array_map('trim', explode("\n", $career))));
    if (is_string($education)) $education = array_values(array_filter(array_map('trim', explode("\n", $education))));
    $qualifications = $input['qualifications'] ?? [];
    if (is_string($qualifications)) $qualifications = array_values(array_filter(array_map('trim', explode("\n", $qualifications))));
    $biography = [ 'career' => $career, 'education' => $education ];
    $data = [
        'name' => $input['name'] ?? '',
        'position' => $input['position'] ?? null,
        'greeting_title' => $input['greeting_title'] ?? null,
        'greeting_content' => $input['greeting_content'] ?? null,
        'photo_url' => $input['photo_url'] ?? null,
        'signature_url' => $input['signature_url'] ?? null,
        'biography' => $biography,
        'qualifications' => $qualifications,
        'sort_order' => isset($input['sort_order']) ? (int)$input['sort_order'] : 0,
        'status' => $input['status'] ?? 'active'
    ];
    return $data;
}

function handlePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('無効なJSONデータです');
    if (empty($input['name'])) throw new Exception('氏名は必須です');
    $data = normalizeInput($input);
    $res = SupabaseClient::insert('representatives', $data);
    if ($res) echo json_encode(['success' => true, 'data' => $res], JSON_UNESCAPED_UNICODE);
    else throw new Exception('代表者の作成に失敗しました');
}

function handlePut() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) throw new Exception('IDが指定されていません');
    $id = $input['id']; unset($input['id']);
    $data = normalizeInput($input);
    $res = SupabaseClient::update('representatives', $data, ['id' => $id]);
    if ($res) echo json_encode(['success' => true, 'data' => $res], JSON_UNESCAPED_UNICODE);
    else throw new Exception('代表者の更新に失敗しました');
}

function handleDelete() {
    $id = $_GET['id'] ?? null;
    if (!$id) throw new Exception('IDが指定されていません');
    $res = SupabaseClient::delete('representatives', ['id' => $id]);
    if ($res !== false) echo json_encode(['success' => true, 'message' => '代表者を削除しました'], JSON_UNESCAPED_UNICODE);
    else throw new Exception('代表者の削除に失敗しました');
}
?>