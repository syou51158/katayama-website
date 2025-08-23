<?php
/**
 * 施工実績管理用CRUD API
 */

require_once '../../lib/SupabaseClient.php';
require_once '../includes/auth.php';

// 認証チェック
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
        $work = SupabaseClient::select('works', ['id' => $id]);
        if ($work && count($work) > 0) {
            echo json_encode(['success' => true, 'data' => $work[0]], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => '施工実績が見つかりません'], JSON_UNESCAPED_UNICODE);
        }
        return;
    }

    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);
    $category = $_GET['category'] ?? null;

    $filters = [];
    if ($category && $category !== 'all') { $filters['category'] = $category; }

    $works = SupabaseClient::select('works', $filters, [
        'order' => 'created_at.desc',
        'limit' => $limit,
        'offset' => $offset
    ]);

    echo json_encode([
        'success' => true,
        'data' => $works ?: [],
        'pagination' => ['limit' => $limit, 'offset' => $offset, 'count' => count($works ?: [])]
    ], JSON_UNESCAPED_UNICODE);
}

function handlePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) { throw new Exception('無効なJSONデータです'); }

    // 必須フィールド
    $required = ['title', 'category'];
    foreach ($required as $f) { if (empty($input[$f])) throw new Exception("必須フィールド '{$f}' が入力されていません"); }

    $data = [
        'title' => $input['title'],
        'description' => $input['description'] ?? null,
        'category' => $input['category'],
        'featured_image' => $input['featured_image'] ?? null,
        'location' => $input['location'] ?? null,
        'completion_date' => $input['completion_date'] ?? null,
        'construction_period' => $input['construction_period'] ?? null,
        'floor_area' => $input['floor_area'] ?? null,
        'status' => $input['status'] ?? 'draft'
    ];

    // gallery_images カラムの存在チェック（未適用環境を考慮）
    if (hasWorksColumn('gallery_images') && isset($input['gallery_images']) && is_array($input['gallery_images'])) {
        $data['gallery_images'] = $input['gallery_images'];
    }

    $result = SupabaseClient::insert('works', $data);
    if ($result) {
        echo json_encode(['success' => true, 'data' => $result, 'message' => '施工実績を作成しました'], JSON_UNESCAPED_UNICODE);
    } else { throw new Exception('施工実績の作成に失敗しました'); }
}

function handlePut() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['id'])) { throw new Exception('IDが指定されていません'); }
    $id = $input['id']; unset($input['id']);

    $allowed = ['title','description','category','featured_image','location','completion_date','construction_period','floor_area','status'];
    if (hasWorksColumn('gallery_images')) {
        $allowed[] = 'gallery_images';
    }
    $data = [];
    foreach ($allowed as $f) { if (isset($input[$f])) $data[$f] = $input[$f]; }
    if (empty($data)) { throw new Exception('更新するデータがありません'); }

    $result = SupabaseClient::update('works', $data, ['id' => $id]);
    if ($result) {
        echo json_encode(['success' => true, 'data' => $result, 'message' => '施工実績を更新しました'], JSON_UNESCAPED_UNICODE);
    } else { throw new Exception('施工実績の更新に失敗しました'); }
}

/**
 * worksテーブルにカラムが存在するか簡易チェック
 */
function hasWorksColumn(string $column): bool {
    // 該当カラムのみ選択してみて失敗したら存在しないと判断
    $result = SupabaseClient::select('works', [], [ 'select' => $column, 'limit' => 1 ]);
    return $result !== false;
}

function handleDelete() {
    $id = $_GET['id'] ?? null;
    if (!$id) { throw new Exception('IDが指定されていません'); }
    $result = SupabaseClient::delete('works', ['id' => $id]);
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => '施工実績を削除しました'], JSON_UNESCAPED_UNICODE);
    } else { throw new Exception('施工実績の削除に失敗しました'); }
}
?>


