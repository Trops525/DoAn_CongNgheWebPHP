<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../model/database.php';

$db = DATABASE::connect();
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Nếu ô tìm kiếm đang trống, không trả về gợi ý nào
if ($q === '') {
    echo json_encode([
        'query' => '',
        'is_empty' => true,
        'keywords' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy danh sách sản phẩm THỰC TẾ trong CSDL khớp với từ khóa
// Ưu tiên:
// 1. Tên bắt đầu bằng $q (ví dụ: 't%')
// 2. Chứa từ bắt đầu bằng $q (ví dụ: '% t%')
// 3. Chứa $q ở bất kỳ đâu
$sql = "SELECT DISTINCT TenSanPham 
        FROM tbl_sanpham 
        WHERE Khoa = 0 AND TenSanPham LIKE :q_any
        ORDER BY 
            (CASE 
                WHEN TenSanPham LIKE :q_start THEN 1
                WHEN TenSanPham LIKE :q_word THEN 2
                ELSE 3
            END),
            LuotMua DESC, IdSanPham DESC
        LIMIT 30";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':q_any' => '%' . $q . '%',
    ':q_start' => $q . '%',
    ':q_word' => '% ' . $q . '%'
]);

$rawProducts = $stmt->fetchAll(PDO::FETCH_COLUMN);

$suggestKeywords = [];
$seen = [];

// 1. Dòng tìm kiếm đầu tiên: [từ khóa] - Tìm kiếm trên Quality
$suggestKeywords[] = [
    'type' => 'main',
    'text' => $q,
    'full_display' => htmlspecialchars($q) . ' <span class="search-provider">- Tìm kiếm trên Quality</span>'
];
$seen[mb_strtolower($q, 'UTF-8')] = true;

// 2. Các dòng gợi ý từ chính các sản phẩm có thật trong CSDL
$limit = 8;
foreach ($rawProducts as $name) {
    $name = trim($name);
    $name_lower = mb_strtolower($name, 'UTF-8');

    if (!isset($seen[$name_lower])) {
        $seen[$name_lower] = true;

        // Bôi đậm phần hậu tố mở rộng giống Google
        $pos = mb_stripos($name, $q, 0, 'UTF-8');
        if ($pos !== false) {
            $prefix = mb_substr($name, 0, $pos + mb_strlen($q, 'UTF-8'), 'UTF-8');
            $suffix = mb_substr($name, $pos + mb_strlen($q, 'UTF-8'), null, 'UTF-8');
            $display = htmlspecialchars($prefix) . '<strong>' . htmlspecialchars($suffix) . '</strong>';
        } else {
            $display = htmlspecialchars($name);
        }

        $suggestKeywords[] = [
            'type' => 'suggest',
            'text' => $name,
            'full_display' => $display
        ];

        if (count($suggestKeywords) >= $limit + 1) { // +1 vì đã có dòng main
            break;
        }
    }
}

echo json_encode([
    'query' => $q,
    'is_empty' => false,
    'keywords' => $suggestKeywords
], JSON_UNESCAPED_UNICODE);
