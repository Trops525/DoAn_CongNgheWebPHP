<?php
$page_title = 'Thống kê Doanh thu - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page" style="margin-bottom: 20px;">
    <h1>Báo cáo Thống kê Doanh thu</h1>
</div>

<div class="dashboard-wrapper">
    <div style="font-size: 20px; font-weight: bold; margin-bottom: 20px; color: #333;">
        Dữ liệu Tháng <?= $currentMonth ?>/<?= $currentYear ?>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #d0021b;">
            <div>
                <h4 style="margin: 0; font-size: 13px; color: #666; text-transform: uppercase;">Doanh Thu Thực Tế</h4>
                <h2 style="margin: 10px 0 0; font-size: 26px; color: #333;"><?= number_format($doanhThuThang, 0, ',', '.') ?> đ</h2>
            </div>
            <div style="font-size: 36px; color: #d0021b;"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #3498db;">
            <div>
                <h4 style="margin: 0; font-size: 13px; color: #666; text-transform: uppercase;">Đơn Hàng Đã Xử Lý</h4>
                <h2 style="margin: 10px 0 0; font-size: 26px; color: #333;"><?= number_format($soDonHang) ?> đơn</h2>
            </div>
            <div style="font-size: 36px; color: #3498db;"><i class="fas fa-shopping-cart"></i></div>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #2ecc71;">
            <div>
                <h4 style="margin: 0; font-size: 13px; color: #666; text-transform: uppercase;">Sản Phẩm Đã Bán</h4>
                <h2 style="margin: 10px 0 0; font-size: 26px; color: #333;"><?= number_format($tongSanPham) ?> sp</h2>
            </div>
            <div style="font-size: 36px; color: #2ecc71;"><i class="fas fa-box-open"></i></div>
        </div>
    </div>

    <div class="table-container" style="padding: 25px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
            <i class="fa fa-trophy" style="color: #ffc107; margin-right: 8px;"></i>Top 10 Sản Phẩm Bán Chạy Nhất
        </h3>
        <table style="min-width: 650px;">
            <thead>
                <tr>
                    <th style="width: 90px;">Xếp hạng</th>
                    <th>Sản phẩm</th>
                    <th style="text-align: center; width: 150px;">Số lượng đã bán</th>
                    <th style="text-align: right; width: 200px;">Doanh thu mang lại</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topBanChay)): ?>
                    <?php $stt = 1; foreach ($topBanChay as $row): 
                        $link_anh = str_replace(' ', '%20', $row['HinhAnh']);
                    ?>
                        <tr>
                            <td>
                                <?php if ($stt <= 3): ?>
                                    <span style="background: #ffc107; color: #000; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">Top <?= $stt ?></span>
                                <?php else: ?>
                                    <span style="font-weight: bold; color: #888; margin-left: 8px;">#<?= $stt ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="/Thoi-Trang-Quality/TrangTin/<?= htmlspecialchars($link_anh) ?>" class="img-product" alt="SP">
                                    <span>
                                        <strong><?= htmlspecialchars($row['TenSanPham']) ?></strong><br>
                                        <small style="color: #888;">Mã: <?= htmlspecialchars($row['MaSanPham'] ?? ('SP_' . $row['IdSanPham'])) ?></small>
                                    </span>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #3498db; font-size: 15px;">
                                <?= $row['TongDaBan'] ?>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #d0021b; font-size: 15px;">
                                <?= number_format($row['DoanhThuSP'], 0, ',', '.') ?> đ
                            </td>
                        </tr>
                    <?php $stt++; endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 40px; color: #888;">Chưa có sản phẩm nào được giao thành công trong tháng này.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>

