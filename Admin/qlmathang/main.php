<?php
$page_title = 'Quản lý mặt hàng - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
require_once __DIR__ . '/../../model/mathang.php';
require_once __DIR__ . '/../../model/danhmuc.php';

$mh = new MATHANG();
$dm = new DANHMUC();

// Xử lý xóa hàng loạt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_selected' && isset($_POST['selected_ids'])) {
    foreach ($_POST['selected_ids'] as $id) {
        $mh->xoamathang((int)$id);
    }
    echo "<script>alert('Xóa các sản phẩm đã chọn thành công!'); window.location.href='index.php';</script>";
    exit;
}

// Bộ lọc
$search = trim($_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';

$items_per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $items_per_page;

// Xây dựng điều kiện lọc
$db = DATABASE::connect();
$where_clauses = [];
$binds = [];

if (!empty($search)) {
    $search_lower = mb_strtolower($search, 'UTF-8');
    if ($search_lower === 'ao' || $search_lower === 'áo') {
        // Tìm riêng các sản phẩm Áo (tránh dính đuôi "thao" trong "thể thao" của mũ/quần do bảng mã MySQL không phân biệt dấu)
        $where_clauses[] = "(LOWER(CONVERT(sp.TenSanPham USING utf8mb4)) COLLATE utf8mb4_bin LIKE :s_bin OR sp.TenSanPham LIKE 'Áo %' OR dm.TenDanhMuc LIKE '%Áo%')";
        $binds[':s_bin'] = "%áo%";
    } elseif ($search_lower === 'quan' || $search_lower === 'quần') {
        // Tìm riêng các sản phẩm Quần
        $where_clauses[] = "(LOWER(CONVERT(sp.TenSanPham USING utf8mb4)) COLLATE utf8mb4_bin LIKE :s_bin OR sp.TenSanPham LIKE 'Quần %' OR dm.TenDanhMuc LIKE '%Quần%')";
        $binds[':s_bin'] = "%quần%";
    } else {
        $has_accent = (bool)preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/ui', $search);
        if ($has_accent) {
            $where_clauses[] = "(LOWER(CONVERT(sp.TenSanPham USING utf8mb4)) COLLATE utf8mb4_bin LIKE :s_bin OR sp.MaSanPham LIKE :s)";
            $binds[':s_bin'] = "%" . $search_lower . "%";
            $binds[':s'] = "%$search%";
        } else {
            $where_clauses[] = "(sp.TenSanPham LIKE :s OR sp.MaSanPham LIKE :s)";
            $binds[':s'] = "%$search%";
        }
    }
}

if ($category > 0) {
    $where_clauses[] = "sp.IdDanhMuc = :cat";
    $binds[':cat'] = $category;
}

if ($status !== 'all') {
    if ($status === 'active') {
        $where_clauses[] = "COALESCE(sp.Khoa, 0) = 0";
    } elseif ($status === 'locked') {
        $where_clauses[] = "sp.Khoa = 1";
    } elseif ($status === 'sold_out') {
        $where_clauses[] = "COALESCE((SELECT SUM(SoLuong) FROM tbl_bienthe WHERE IdSanPham = sp.IdSanPham), 0) = 0";
    } elseif ($status === 'sold') {
        $where_clauses[] = "sp.LuotMua > 0";
    } elseif ($status === 'unsold') {
        $where_clauses[] = "sp.LuotMua = 0";
    }
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$order_sql = "ORDER BY sp.IdSanPham DESC";
if ($sort == 'price_asc') {
    $order_sql = "ORDER BY sp.DonGia ASC";
} elseif ($sort == 'price_desc') {
    $order_sql = "ORDER BY sp.DonGia DESC";
}

// Tổng số sản phẩm
$count_cmd = $db->prepare("SELECT COUNT(*) as total FROM tbl_sanpham sp LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc $where_sql");
foreach ($binds as $k => $v) { $count_cmd->bindValue($k, $v); }
$count_cmd->execute();
$total_items = (int)($count_cmd->fetch()['total'] ?? 0);
$total_pages = ceil($total_items / $items_per_page);

// Lấy danh sách sản phẩm
$select_cmd = $db->prepare("SELECT sp.*, dm.TenDanhMuc FROM tbl_sanpham sp LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc $where_sql $order_sql LIMIT :limit OFFSET :offset");
foreach ($binds as $k => $v) { $select_cmd->bindValue($k, $v); }
$select_cmd->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$select_cmd->bindValue(':offset', $offset, PDO::PARAM_INT);
$select_cmd->execute();
$products = $select_cmd->fetchAll();

// Lấy danh mục cho bộ lọc
$categories = $dm->laydanhmuc();
?>

<div class="header-page">
    <h1>Danh sách sản phẩm thời trang</h1>
    <?php if (in_array($userRole, [1, 3])): ?>
    <a href="/Thoi-Trang-Quality/Admin/them_sanpham.php" class="btn btn-add"><i class="fa fa-plus"></i> Thêm sản phẩm mới</a>
    <?php endif; ?>
</div>

<!-- THANH CÔNG CỤ TÌM KIẾM & LỌC -->
<div style="background: #f9f9f9; padding: 18px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e0e0e0; box-sizing: border-box;">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 180px;">
            <label style="font-weight: 600; display: block; margin-bottom: 5px; font-size: 13px;">🔍 Tìm kiếm</label>
            <input type="text" name="search" placeholder="Nhập tên hoặc mã sản phẩm..." value="<?= htmlspecialchars($search) ?>" style="width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-weight: 600; display: block; margin-bottom: 5px; font-size: 13px;">📦 Danh mục</label>
            <select name="category" style="width: 100%; padding: 9px 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                <option value="0">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $cat_row): ?>
                    <option value="<?= $cat_row['IdDanhMuc'] ?>" <?= ($category == $cat_row['IdDanhMuc']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat_row['TenDanhMuc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-weight: 600; display: block; margin-bottom: 5px; font-size: 13px;">📊 Trạng thái</label>
            <select name="status" style="width: 100%; padding: 9px 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                <option value="all" <?= ($status == 'all') ? 'selected' : '' ?>>-- Tất cả --</option>
                <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Đang mở bán</option>
                <option value="locked" <?= ($status == 'locked') ? 'selected' : '' ?>>Đã khóa</option>
                <option value="sold_out" <?= ($status == 'sold_out') ? 'selected' : '' ?>>Bán hết</option>
                <option value="sold" <?= ($status == 'sold') ? 'selected' : '' ?>>Đã bán</option>
                <option value="unsold" <?= ($status == 'unsold') ? 'selected' : '' ?>>Chưa bán</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 130px;">
            <label style="font-weight: 600; display: block; margin-bottom: 5px; font-size: 13px;">📊 Sắp xếp</label>
            <select name="sort" style="width: 100%; padding: 9px 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
                <option value="newest" <?= ($sort == 'newest') ? 'selected' : '' ?>>Mới nhất</option>
                <option value="price_asc" <?= ($sort == 'price_asc') ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc" <?= ($sort == 'price_desc') ? 'selected' : '' ?>>Giá giảm dần</option>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #3498db; color: white; padding: 9px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                <i class="fa fa-filter"></i> Lọc
            </button>
            <a href="?" style="background: #95a5a6; color: white; padding: 9px 16px; border-radius: 5px; text-decoration: none; font-weight: 600; white-space: nowrap; display: inline-flex; align-items: center;">
                <i class="fa fa-redo"></i> Đặt lại
            </a>
        </div>
    </form>
</div>

<!-- NÚT THAO TÁC HÀNG LOẠT (CHỈ QUẢN TRỊ VIÊN) -->
<?php if ($userRole == 1): ?>
<div id="bulk-actions" style="margin-bottom: 15px; display: none;">
    <form method="POST" style="display: flex; gap: 10px;">
        <input type="hidden" name="action" value="delete_selected">
        <div id="selected-count" style="padding: 10px; background: #e3f2fd; border-radius: 5px; font-weight: 600;">
            Đã chọn: <span id="count">0</span> sản phẩm
        </div>
        <button type="submit" class="btn btn-delete" style="padding: 10px 20px;" onclick="return confirm('Bạn có chắc chắn muốn xóa các sản phẩm đã chọn?')">
            <i class="fa fa-trash"></i> Xóa các mục đã chọn
        </button>
    </form>
</div>
<?php endif; ?>

<div class="table-container">
    <form id="products-form">
        <table class="table-wide">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="check-all" onchange="toggleAllCheckboxes()">
                    </th>
                    <th>ID</th>
                    <th>Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá gốc</th>
                    <th>Giảm (%)</th>
                    <th>Lượt mua</th>
                    <th>Size & Số lượng</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $row): 
                        $link_anh = str_replace(' ', '%20', $row['HinhAnh']);
                        $sizes = $mh->laykichthuoctheosanpham($row['IdSanPham']);
                        $size_html = '';
                        if (!empty($sizes)) {
                            foreach ($sizes as $s) {
                                $size_html .= '<span style="display:block; margin: 3px 0;">' 
                                    . htmlspecialchars($s['KichThuoc']) . ': <strong>' . $s['TongSoLuong'] . '</strong></span>';
                            }
                        } else {
                            $size_html = '<span style="color: #e74c3c;">Chưa có size</span>';
                        }
                    ?>
                    <?php 
                        $isLocked = !empty($row['Khoa']);
                    ?>
                        <tr style="<?= $isLocked ? 'background: #fff5f5; opacity: 0.88;' : '' ?>">
                            <td style="text-align: center;"><input type="checkbox" name="selected_ids[]" value="<?= $row['IdSanPham'] ?>" onchange="updateBulkActions()"></td>
                            <td><?= $row['IdSanPham'] ?></td>
                            <td><img src="/Thoi-Trang-Quality/TrangTin/<?= $link_anh ?>" class="img-product"></td>
                            <td>
                                <strong><?= htmlspecialchars($row['TenSanPham']) ?></strong>
                                <?php if ($isLocked): ?>
                                    <span style="font-size: 11px; background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-weight: normal; display: inline-block;">
                                        <i class="fa fa-lock"></i> Đã khóa
                                    </span>
                                <?php endif; ?>
                                <br>
                                <small style="color:#888;">Mã: <?= htmlspecialchars($row['MaSanPham']) ?> | DM: <?= htmlspecialchars($row['TenDanhMuc'] ?? '') ?></small>
                            </td>
                            <td><?= number_format($row['DonGia'], 0, ',', '.') ?>đ</td>
                            <td><span style="color:red;">-<?= $row['TiLeGiamGia'] ?>%</span></td>
                            <td><?= $row['LuotMua'] ?></td>
                            <td style="padding: 10px; font-size: 12px;"><?= $size_html ?></td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php if ($userRole == 1): ?>
                                    <a href="/Thoi-Trang-Quality/Admin/SuaSanPham.php?id=<?= $row['IdSanPham'] ?>" class="btn btn-edit"><i class="fa fa-edit"></i> Sửa</a>
                                    <a href="?action=khoa&id=<?= $row['IdSanPham'] ?>" class="btn" style="background: <?= $isLocked ? '#28a745' : '#ffc107' ?>; color: <?= $isLocked ? '#fff' : '#000' ?>; padding: 6px 9px;" onclick="return confirm('<?= $isLocked ? 'Mở khóa bán sản phẩm này?' : 'Khóa (tạm dừng kinh doanh / ẩn) sản phẩm này?' ?>');" title="<?= $isLocked ? 'Mở khóa bán' : 'Khóa sản phẩm' ?>">
                                        <?= $isLocked ? '<i class="fa fa-unlock"></i> Mở' : '<i class="fa fa-lock"></i> Khóa' ?>
                                    </a>
                                    <a href="?action=xoa&id=<?= $row['IdSanPham'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');"><i class="fa fa-trash"></i> Xóa</a>
                                <?php elseif ($userRole == 3): ?>
                                    <a href="/Thoi-Trang-Quality/Admin/SuaSanPham.php?id=<?= $row['IdSanPham'] ?>" class="btn btn-edit"><i class="fa fa-edit"></i> Sửa kho</a>
                                    <a href="?action=khoa&id=<?= $row['IdSanPham'] ?>" class="btn" style="background: <?= $isLocked ? '#28a745' : '#ffc107' ?>; color: <?= $isLocked ? '#fff' : '#000' ?>; padding: 6px 9px;" onclick="return confirm('<?= $isLocked ? 'Mở khóa bán sản phẩm này?' : 'Khóa (tạm dừng kinh doanh / ẩn) sản phẩm này?' ?>');" title="<?= $isLocked ? 'Mở khóa bán' : 'Khóa sản phẩm' ?>">
                                        <?= $isLocked ? '<i class="fa fa-unlock"></i> Mở' : '<i class="fa fa-lock"></i> Khóa' ?>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: #6c757d; font-style: italic;"><i class="fa fa-eye"></i> Chỉ xem</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center; padding: 50px;">Chưa có sản phẩm nào trong kho hàng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<!-- PHÂN TRANG -->
<?php if ($total_pages > 1): ?>
<div style="display: flex; justify-content: center; gap: 10px; margin-top: 30px; flex-wrap: wrap;">
    <?php
    $query_params = '?page=PAGE';
    if (!empty($search)) $query_params .= '&search=' . urlencode($search);
    if ($category > 0) $query_params .= '&category=' . $category;
    if ($status !== 'all') $query_params .= '&status=' . urlencode($status);
    if (!empty($sort)) $query_params .= '&sort=' . $sort;
    
    if ($page > 1) {
        echo '<a href="' . str_replace('PAGE', $page - 1, $query_params) . '" class="btn" style="padding: 8px 12px;"><i class="fa fa-chevron-left"></i></a>';
    }
    
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page) {
            echo '<span class="btn" style="background: #d0021b; color: white; padding: 8px 15px;">' . $i . '</span>';
        } else {
            echo '<a href="' . str_replace('PAGE', $i, $query_params) . '" class="btn" style="background: white; border: 1px solid #ddd; padding: 8px 15px; color: #333;">' . $i . '</a>';
        }
    }
    
    if ($page < $total_pages) {
        echo '<a href="' . str_replace('PAGE', $page + 1, $query_params) . '" class="btn" style="padding: 8px 12px;"><i class="fa fa-chevron-right"></i></a>';
    }
    ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>

