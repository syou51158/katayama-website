<?php
require_once __DIR__ . '/lib/SupabaseClient.php';
require_once __DIR__ . '/config/supabase.secrets.php';

header('Content-Type: application/json; charset=utf-8');

$serviceKey = SupabaseConfig::getServiceRoleKey();
if (!$serviceKey || $serviceKey === 'YOUR_SERVICE_ROLE_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'SUPABASE_SERVICE_ROLE_KEY が未設定です'], JSON_UNESCAPED_UNICODE);
    exit;
}

function hasWorksGalleryColumn(): bool {
    $r = SupabaseClient::select('works', [], ['select' => 'gallery_images', 'limit' => 1]);
    return $r !== false;
}

function insertRows(string $table, array $rows): int {
    $c = 0;
    foreach ($rows as $row) {
        $res = SupabaseClient::insert($table, $row);
        if ($res !== false) $c++;
    }
    return $c;
}

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');

$newsCategories = ['お知らせ','イベント','施工事例','コラム'];
$newsRows = [];
for ($i = 1; $i <= 20; $i++) {
    $cat = $newsCategories[($i - 1) % count($newsCategories)];
    $status = $i % 6 === 0 ? 'draft' : 'published';
    $newsRows[] = [
        'title' => 'サンプルニュース ' . $i,
        'content' => 'これはサンプルニュース本文です。番号: ' . $i,
        'excerpt' => 'サンプル抜粋 ' . $i,
        'category' => $cat,
        'featured_image' => null,
        'published_date' => date('Y-m-d', strtotime("-{$i} days")),
        'status' => $status
    ];
}

$worksCategories = ['Residential','Commercial','Public','Renovation'];
$hasGallery = hasWorksGalleryColumn();
$worksRows = [];
for ($i = 1; $i <= 12; $i++) {
    $cat = $worksCategories[($i - 1) % count($worksCategories)];
    $status = $i % 5 === 0 ? 'draft' : 'published';
    $row = [
        'title' => '施工実績サンプル ' . $i,
        'description' => '施工説明のサンプル ' . $i,
        'category' => $cat,
        'featured_image' => 'assets/img/works_01.jpg',
        'location' => '香川県高松市',
        'completion_date' => date('Y-m-d', strtotime("-{$i} months")),
        'construction_period' => ($i + 1) . 'ヶ月',
        'floor_area' => (80 + $i) . '㎡',
        'status' => $status
    ];
    if ($hasGallery) {
        $row['gallery_images'] = ['assets/img/works_01.jpg', 'assets/img/works_02.jpg'];
    }
    $worksRows[] = $row;
}

$newsInserted = insertRows('news', $newsRows);
$worksInserted = insertRows('works', $worksRows);

echo json_encode([
    'ok' => true,
    'inserted' => [
        'news' => $newsInserted,
        'works' => $worksInserted
    ],
    'timestamp' => $now,
    'project_url' => SupabaseConfig::getProjectUrl()
], JSON_UNESCAPED_UNICODE);
?>
