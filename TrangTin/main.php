<?php
require_once __DIR__ . '/inc/top.php';

$gender = isset($_GET['gender']) && $_GET['gender'] !== '' ? $_GET['gender'] : (empty($_GET['q']) ? 'Nam' : '');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

if (!isset($mh)) {
    require_once __DIR__ . '/../model/mathang.php';
    $mh = new MATHANG();
}

// 1. Lấy danh sách sản phẩm theo giới tính (mặc định sắp xếp theo lượt mua nhiều nhất)
$total_items = $mh->demtongmathang($gender, $q);
$total_pages = ceil($total_items / $items_per_page);
$mathang = $mh->laymathangtheogioitinh($gender, $items_per_page, $offset, $q, 'LuotMua DESC, IdSanPham ASC');

$q_param = $q !== '' ? '&q=' . urlencode($q) : '';
$gender_param = $gender !== '' ? '&gender=' . urlencode($gender) : '';
?>

<!-- BANNER SLIDER -->
<section class="shop-intro-banner">
    <div class="slider-container">
        <div class="slide fade">
            <img src="images/shop-sale-intro.jpg" alt="Quality Fashion Banner 1">
        </div>
        <div class="slide fade">
            <img src="images/banner2.jpg" alt="Quality Fashion Banner 2">
        </div>
        <div class="slide fade">
            <img src="images/banner3.jpg" alt="Quality Fashion Banner 3">
        </div>
        <div class="slide fade">
            <img src="images/banner4.jpg" alt="Quality Fashion Banner 4">
        </div>
        <div class="slider-arrow prev-arrow"><i class="fa fa-chevron-left"></i></div>
        <div class="slider-arrow next-arrow"><i class="fa fa-chevron-right"></i></div>
    </div>
</section>

<div class="container">

    <!-- TABS NAM / NỮ -->
    <div class="category-tabs">
        <a href="index.php?gender=Nam&limit=12<?= $q_param ?>" class="tab-btn <?= ($gender == 'Nam') ? 'active' : '' ?>">NAM</a>
        <a href="index.php?gender=Nu&limit=12<?= $q_param ?>" class="tab-btn <?= ($gender == 'Nu') ? 'active' : '' ?>">NỮ</a>
    </div>

    <!-- PRODUCT GRID -->
    <div class="product-grid">
    <?php
    if (!empty($mathang)) {
        foreach ($mathang as $row) {
            $gia_ban = $row['DonGia'] - ($row['DonGia'] * $row['TiLeGiamGia'] / 100);
            ?>
            <div class="product-item">
                <a href="chitiet.php?id=<?= $row['IdSanPham'] ?>" class="product-link">
                    <div class="product-thumb">
                        <img src="<?= htmlspecialchars($row['HinhAnh']) ?>" alt="<?= htmlspecialchars($row['TenSanPham']) ?>">
                    </div>
                    <div class="product-title"><?= htmlspecialchars($row['TenSanPham']) ?></div>
                    <div class="product-price">
                        <?php if ($row['TiLeGiamGia'] > 0): ?>
                            <span class="old-price"><?= number_format($row['DonGia'], 0, ',', '.') ?> đ</span>
                            <span><?= number_format($gia_ban, 0, ',', '.') ?> đ</span>
                        <?php else: ?>
                            <span><?= number_format($row['DonGia'], 0, ',', '.') ?> đ</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
    <?php
        }
    } else {
        echo '<p style="text-align:center; grid-column: span 4; padding: 20px;">Hiện tại chưa có sản phẩm nào cho mục này.</p>';
    }
    ?>
    </div>

    <!-- PHÂN TRANG -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php
        $query_params = '?page=PAGE';
        if (!empty($gender)) $query_params .= '&gender=' . urlencode($gender);
        if (!empty($q)) $query_params .= '&q=' . urlencode($q);
        
        // Nút Trang trước
        if ($page > 1) {
            echo '<a href="' . str_replace('PAGE', $page - 1, $query_params) . '"><i class="fa fa-chevron-left"></i></a>';
        }
        
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if ($start_page > 1) {
            echo '<a href="' . str_replace('PAGE', 1, $query_params) . '">1</a>';
            if ($start_page > 2) echo '<span>...</span>';
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="active">' . $i . '</span>';
            } else {
                echo '<a href="' . str_replace('PAGE', $i, $query_params) . '">' . $i . '</a>';
            }
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) echo '<span>...</span>';
            echo '<a href="' . str_replace('PAGE', $total_pages, $query_params) . '">' . $total_pages . '</a>';
        }
        
        // Nút Trang sau
        if ($page < $total_pages) {
            echo '<a href="' . str_replace('PAGE', $page + 1, $query_params) . '"><i class="fa fa-chevron-right"></i></a>';
        }
        ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/inc/bottom.php'; ?>

