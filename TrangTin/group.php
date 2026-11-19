<?php
require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/../model/danhmuc.php';
require_once __DIR__ . '/../model/mathang.php';

$id_danhmuc = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$dm = new DANHMUC();
$mh = new MATHANG();

$danhmuc_hientai = $dm->laydanhmuctheoid($id_danhmuc);
$mathang = $mh->laymathangtheodanhmuc($id_danhmuc);
$danhmuc_all = $dm->laydanhmuc();
?>

<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <div style="border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <span style="font-size: 13px; color: #888; text-transform: uppercase;"><a href="index.php" style="color: #666; text-decoration: none;">Trang chủ</a> / Danh mục</span>
            <h2 style="font-size: 24px; font-weight: 700; text-transform: uppercase; margin-top: 5px; color: #111;">
                <?= htmlspecialchars($danhmuc_hientai['TenDanhMuc'] ?? 'Tất cả sản phẩm') ?>
            </h2>
        </div>
        <div style="font-size: 14px; color: #666;">
            Có <strong><?= count($mathang) ?></strong> sản phẩm
        </div>
    </div>

    <!-- DANH MỤC NHANH -->
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 25px;">
        <?php foreach ($danhmuc_all as $d): ?>
            <a href="index.php?action=group&id=<?= $d['IdDanhMuc'] ?>" 
               style="padding: 8px 16px; border: 1px solid <?= ($d['IdDanhMuc'] == $id_danhmuc) ? '#000' : '#ddd' ?>; background: <?= ($d['IdDanhMuc'] == $id_danhmuc) ? '#000' : '#fff' ?>; color: <?= ($d['IdDanhMuc'] == $id_danhmuc) ? '#fff' : '#333' ?>; border-radius: 20px; font-size: 13px; text-decoration: none; font-weight: 500; transition: all 0.2s;">
                <?= htmlspecialchars($d['TenDanhMuc']) ?>
            </a>
        <?php endforeach; ?>
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
        echo '<p style="text-align:center; grid-column: span 4; padding: 40px 20px; color: #888;">Chưa có sản phẩm nào thuộc danh mục này.</p>';
    }
    ?>
    </div>
</div>

<?php require_once __DIR__ . '/inc/bottom.php'; ?>

