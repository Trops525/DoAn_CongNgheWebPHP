<?php
require_once __DIR__ . '/header.php';

// 1. LẤY CÁC THAM SỐ TỪ URL (LỌC & SẮP XẾP & PHÂN TRANG)
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 12;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$selected_cats = isset($_GET['cats']) ? $_GET['cats'] : [];
$selected_sizes = isset($_GET['sizes']) ? $_GET['sizes'] : [];
$selected_genders = isset($_GET['genders']) ? $_GET['genders'] : [];
$selected_colors = isset($_GET['colors']) ? $_GET['colors'] : [];
$selected_prices = isset($_GET['prices']) ? $_GET['prices'] : [];

// Tính offset
$offset = ($page - 1) * $items_per_page;

// 2. XÂY DỰNG CÂU LỆNH SQL DỰA TRÊN BỘ LỌC
$filters = ["COALESCE(sp.Khoa, 0) = 0"];
$join_bienthe = false; // Biến kiểm tra xem có cần JOIN với bảng biến thể không

if ($q !== '') {
    $safe_q = $ket_noi->real_escape_string($q);
    $filters[] = "(sp.TenSanPham LIKE '%{$safe_q}%' OR sp.MaSanPham LIKE '%{$safe_q}%')";
}

if (!empty($selected_cats)) {
    $cat_ids = array_map('intval', $selected_cats);
    $filters[] = "sp.IdDanhMuc IN (" . implode(',', $cat_ids) . ")";
}

if (!empty($selected_genders)) {
    $genders = array_map([$ket_noi, 'real_escape_string'], $selected_genders);
    $filters[] = "sp.GioiTinh IN ('" . implode("','", $genders) . "')";
}

// Lọc theo Size và Màu (Nằm ở bảng tbl_bienthe)
if (!empty($selected_sizes)) {
    $sizes = array_map([$ket_noi, 'real_escape_string'], $selected_sizes);
    $filters[] = "bt.KichThuoc IN ('" . implode("','", $sizes) . "')";
    $join_bienthe = true;
}

if (!empty($selected_colors)) {
    $colors = array_map([$ket_noi, 'real_escape_string'], $selected_colors);
    $filters[] = "bt.MauSac IN ('" . implode("','", $colors) . "')";
    $join_bienthe = true;
}

// Lọc theo khoảng giá
if (!empty($selected_prices)) {
    $price_conds = [];
    foreach ($selected_prices as $pr) {
        $parts = explode('-', $pr);
        if (count($parts) == 2) {
            $min = (int)$parts[0];
            $max = (int)$parts[1];
            $price_conds[] = "(sp.DonGia BETWEEN $min AND $max)";
        } elseif ($pr == '500000') {
            $price_conds[] = "(sp.DonGia >= 500000)";
        }
    }
    if (!empty($price_conds)) {
        $filters[] = "(" . implode(' OR ', $price_conds) . ")";
    }
}

// Lắp ráp câu WHERE
$where_sql = "";
if (!empty($filters)) {
    $where_sql = "WHERE " . implode(' AND ', $filters);
}

// Lắp ráp câu JOIN
$join_sql = $join_bienthe ? "JOIN tbl_bienthe bt ON sp.IdSanPham = bt.IdSanPham" : "";
$select_cols = $join_bienthe ? "DISTINCT sp.*" : "sp.*"; // Dùng DISTINCT để tránh trùng lặp sp khi JOIN

// Lắp ráp câu ORDER BY (Sắp xếp)
$order_sql = "ORDER BY sp.IdSanPham DESC"; // Mặc định: Mới nhất
if ($sort === 'bestseller') $order_sql = "ORDER BY sp.LuotMua DESC, sp.IdSanPham DESC";
if ($sort === 'sale') $order_sql = "ORDER BY sp.TiLeGiamGia DESC";
if ($sort === 'price_asc') $order_sql = "ORDER BY sp.DonGia ASC";
if ($sort === 'price_desc') $order_sql = "ORDER BY sp.DonGia DESC";

// Câu lệnh SQL hoàn chỉnh để lấy sản phẩm
$sql_products = "SELECT $select_cols FROM tbl_sanpham sp $join_sql $where_sql $order_sql LIMIT $items_per_page OFFSET $offset";
$result_products = $ket_noi->query($sql_products);

// Đếm tổng số sản phẩm (Dành cho phân trang)
$sql_count = "SELECT COUNT(DISTINCT sp.IdSanPham) AS total FROM tbl_sanpham sp $join_sql $where_sql";
$result_count = $ket_noi->query($sql_count);
$total_products = ($result_count) ? $result_count->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_products / $items_per_page);
?>

<div class="shop-layout">
    
    <aside class="filter-sidebar">
        <form id="filterForm" method="GET" action="sanpham.php">
            <?php if($q !== ''): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
            <?php endif; ?>
            
            <div class="filter-block">
                <div style="display: flex; justify-content: space-between; align-items: center; height: 38px; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 16px; line-height: 1; display: flex; align-items: center;">Danh mục</h3>
                    <a href="sanpham.php" style="font-size: 12px; color: #dc3545; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; font-weight: 500; padding: 4px 8px; border: 1px solid #dc3545; border-radius: 4px; transition: all 0.2s; line-height: 1;" onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='#fff';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#dc3545';" title="Xóa tất cả bộ lọc">
                        <i class="fa-solid fa-rotate-left" style="font-size: 11px; line-height: 1;"></i>
                        <span style="line-height: 1;">Xóa bộ lọc</span>
                    </a>
                </div>
                <ul class="filter-list">
                    <?php
                    $sql_dm = "SELECT * FROM tbl_danhmuc";
                    $res_dm = $ket_noi->query($sql_dm);
                    while ($dm = $res_dm->fetch_assoc()):
                        $checked = in_array($dm['IdDanhMuc'], $selected_cats) ? 'checked' : '';
                    ?>
                        <li>
                            <label class="filter-label">
                                <input type="checkbox" name="cats[]" value="<?= $dm['IdDanhMuc'] ?>" <?= $checked ?>>
                                <?= htmlspecialchars($dm['TenDanhMuc']) ?>
                            </label>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="filter-block">
                <h3>Kích thước</h3>
                <ul class="filter-list">
                    <?php 
                    $arr_sizes = ['S', 'M', 'L', 'XL', 'XXL', 'Freesize'];
                    foreach($arr_sizes as $sz):
                        $checked = in_array($sz, $selected_sizes) ? 'checked' : '';
                    ?>
                        <li>
                            <label class="filter-label">
                                <input type="checkbox" name="sizes[]" value="<?= $sz ?>" <?= $checked ?>>
                                <?= $sz ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-block">
                <h3>Giới tính</h3>
                <ul class="filter-list">
                    <?php 
                    $arr_genders = ['Nam', 'Nữ', 'Unisex'];
                    foreach($arr_genders as $gd):
                        $checked = in_array($gd, $selected_genders) ? 'checked' : '';
                    ?>
                        <li>
                            <label class="filter-label">
                                <input type="checkbox" name="genders[]" value="<?= $gd ?>" <?= $checked ?>>
                                <?= $gd ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-block">
                <h3>Màu sắc</h3>
                <ul class="filter-list">
                    <?php 
                    $arr_colors = ['Đen', 'Trắng', 'Đỏ', 'Xanh', 'Be', 'Xám'];
                    foreach($arr_colors as $cl):
                        $checked = in_array($cl, $selected_colors) ? 'checked' : '';
                    ?>
                        <li>
                            <label class="filter-label">
                                <input type="checkbox" name="colors[]" value="<?= $cl ?>" <?= $checked ?>>
                                <?= $cl ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-block">
                <h3>Mức giá</h3>
                <ul class="filter-list">
                    <?php 
                    $arr_prices = [
                        '0-199000' => 'Dưới 200.000đ',
                        '200000-299000' => '200.000đ - 299.000đ',
                        '300000-499000' => '300.000đ - 499.000đ',
                        '500000' => 'Trên 500.000đ'
                    ];
                    foreach($arr_prices as $val => $lbl):
                        $checked = in_array($val, $selected_prices) ? 'checked' : '';
                    ?>
                        <li>
                            <label class="filter-label">
                                <input type="checkbox" name="prices[]" value="<?= $val ?>" <?= $checked ?>>
                                <?= $lbl ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <input type="hidden" id="hiddenSort" name="sort" value="<?= htmlspecialchars($sort) ?>">
        </form>
    </aside>

    <main class="main-products">
        
        <div class="products-header">
            <div>
                <?php if ($q !== ''): ?>
                    <div style="font-size: 15px; margin-bottom: 6px; color: #333;">
                        Kết quả lọc theo từ khóa: <strong style="color: #d0021b;">"<?= htmlspecialchars($q) ?>"</strong>
                        <a href="sanpham.php" style="margin-left: 10px; font-size: 12px; color: #666; text-decoration: underline;" title="Bỏ lọc tìm kiếm">(Bỏ lọc)</a>
                    </div>
                <?php endif; ?>
                <div class="result-count">Hiển thị <strong><?= min($items_per_page, max(0, $total_products - ($page - 1) * $items_per_page)) ?></strong> / <?= $total_products ?> kết quả (Trang <?= $page ?> / <?= max(1, $total_pages) ?>)</div>
            </div>
            <div class="sort-box">
                <label style="font-weight: bold; font-size: 14px;">Phân loại:</label>
                <select class="sort-select" onchange="document.getElementById('hiddenSort').value = this.value; document.getElementById('filterForm').submit();">
                    <option value="newest" <?= ($sort == 'newest') ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="bestseller" <?= ($sort == 'bestseller') ? 'selected' : '' ?>>Bán chạy</option>
                    <option value="sale" <?= ($sort == 'sale') ? 'selected' : '' ?>>Đang Sale</option>
                    <option value="price_asc" <?= ($sort == 'price_asc') ? 'selected' : '' ?>>Giá: Thấp đến cao</option>
                    <option value="price_desc" <?= ($sort == 'price_desc') ? 'selected' : '' ?>>Giá: Cao đến thấp</option>
                </select>
            </div>
        </div>

        <?php if ($result_products && $result_products->num_rows > 0): ?>
            <div class="product-grid-3">
                <?php 
                while ($row = $result_products->fetch_assoc()): 
                    $gia_goc = $row['DonGia'];
                    $ti_le = $row['TiLeGiamGia'];
                    $gia_ban = $gia_goc - ($gia_goc * $ti_le / 100);
                ?>
                    <div class="prod-card">
                        <a href="chitiet.php?id=<?= $row['IdSanPham'] ?>" style="text-decoration: none; color: inherit;">
                            <img src="<?= htmlspecialchars($row['HinhAnh']) ?>" alt="<?= htmlspecialchars($row['TenSanPham']) ?>" class="prod-img">
                            <div class="prod-info">
                                <h4 class="prod-title"><?= htmlspecialchars($row['TenSanPham']) ?></h4>
                                <div class="prod-price">
                                    <?= number_format($gia_ban, 0, ',', '.') ?> đ
                                    <?php if ($ti_le > 0): ?>
                                        <span class="prod-old-price"><?= number_format($gia_goc, 0, ',', '.') ?> đ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- PHÂN TRANG -->
            <div style="display: flex; justify-content: center; gap: 8px; margin-top: 40px; flex-wrap: wrap;">
                <?php
                // Xây dựng query string với tất cả filter & sort
                $base_query = 'sanpham.php?page=PAGE';
                if (!empty($q)) $base_query .= '&q=' . urlencode($q);
                if (!empty($selected_cats)) {
                    foreach ($selected_cats as $cat) {
                        $base_query .= '&cats[]=' . urlencode($cat);
                    }
                }
                if (!empty($selected_genders)) {
                    foreach ($selected_genders as $gd) {
                        $base_query .= '&genders[]=' . urlencode($gd);
                    }
                }
                if (!empty($selected_sizes)) {
                    foreach ($selected_sizes as $sz) {
                        $base_query .= '&sizes[]=' . urlencode($sz);
                    }
                }
                if (!empty($selected_colors)) {
                    foreach ($selected_colors as $cl) {
                        $base_query .= '&colors[]=' . urlencode($cl);
                    }
                }
                if (!empty($selected_prices)) {
                    foreach ($selected_prices as $pr) {
                        $base_query .= '&prices[]=' . urlencode($pr);
                    }
                }
                if (!empty($sort)) $base_query .= '&sort=' . urlencode($sort);
                
                // Nút Trang trước
                if ($page > 1) {
                    echo '<a href="' . str_replace('PAGE', $page - 1, $base_query) . '" style="padding: 8px 12px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; transition: 0.2s;" onmouseover="this.style.background=\'#333\'; this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#333\';"><i class="fa fa-chevron-left"></i></a>';
                }
                
                // Nút các trang
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="' . str_replace('PAGE', 1, $base_query) . '" style="padding: 8px 12px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; transition: 0.2s;" onmouseover="this.style.background=\'#333\'; this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#333\';">1</a>';
                    if ($start_page > 2) echo '<span style="padding: 8px 5px; color: #999;">...</span>';
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo '<span style="padding: 8px 12px; border: 1px solid #333; background: #333; color: #fff; border-radius: 4px;">' . $i . '</span>';
                    } else {
                        echo '<a href="' . str_replace('PAGE', $i, $base_query) . '" style="padding: 8px 12px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; transition: 0.2s;" onmouseover="this.style.background=\'#333\'; this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#333\';">' . $i . '</a>';
                    }
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<span style="padding: 8px 5px; color: #999;">...</span>';
                    echo '<a href="' . str_replace('PAGE', $total_pages, $base_query) . '" style="padding: 8px 12px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; transition: 0.2s;" onmouseover="this.style.background=\'#333\'; this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#333\';">' . $total_pages . '</a>';
                }
                
                // Nút Trang sau
                if ($page < $total_pages) {
                    echo '<a href="' . str_replace('PAGE', $page + 1, $base_query) . '" style="padding: 8px 12px; border: 1px solid #333; text-decoration: none; color: #333; border-radius: 4px; transition: 0.2s;" onmouseover="this.style.background=\'#333\'; this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'#333\';"><i class="fa fa-chevron-right"></i></a>';
                }
                ?>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 50px 0; border: 1px dashed #ccc; border-radius: 10px;">
                <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy sản phẩm phù hợp!</h3>
                <p style="color: #999; font-size: 14px;">Vui lòng thử bỏ bớt bộ lọc để có thêm kết quả.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'footer.php'; ?>