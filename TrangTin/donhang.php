<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/donhang.php';
require_once __DIR__ . '/../model/cskh.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$dh = new DONHANG();
$dg = new DANHGIA();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'danhan') {
        // Cập nhật trạng thái thành "Đã giao" & cộng lượt mua
        $dh->capnhattrangthai($orderId, 'Đã giao');
        $_SESSION['flash_message'] = "Xác nhận đã nhận hàng thành công! (Hệ thống đã ghi nhận lượt mua).";
    }
    elseif ($action === 'trahang') {
        $dh->capnhattrangthai($orderId, 'Yêu cầu trả hàng');
        $_SESSION['flash_message'] = "Yêu cầu trả hàng đã được gửi đến shop.";
    }
    elseif ($action === 'cancel') {
        $don = $dh->laydonhangtheoid($orderId);
        if ($don && $don['IdNguoiDung'] == $userId && $don['TinhTrang'] === 'Đang xử lý') {
            $dh->capnhattrangthai($orderId, 'Đã hủy');
            $_SESSION['flash_message'] = "Đã hủy đơn hàng #$orderId thành công.";
        } else {
            $_SESSION['flash_message'] = "Không thể hủy đơn hàng này do shop đã xác nhận hoặc gửi hàng.";
        }
    }
    header("Location: donhang.php"); 
    exit;
}

$flash = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';
$orders = $dh->laydonhangtheonguoidung($userId, $tab);

include 'header.php';
?>

<main style="max-width: 900px; margin: 40px auto; padding: 0 15px; font-family: sans-serif;">
    <h1 style="text-align: center; margin-bottom: 30px; text-transform: uppercase;">Đơn hàng của tôi</h1>
    
    <div style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 2px solid #eee;">
        <a href="donhang.php?tab=active" style="padding: 12px 20px; font-weight: 600; text-decoration: none; border-bottom: 3px solid <?= ($tab === 'active') ? '#d0021b' : 'transparent' ?>; color: <?= ($tab === 'active') ? '#d0021b' : '#888' ?>; transition: all 0.3s; cursor: pointer;">
            <i class="fa fa-shopping-bag" style="margin-right: 8px;"></i>Đơn hàng
        </a>
        <a href="donhang.php?tab=history" style="padding: 12px 20px; font-weight: 600; text-decoration: none; border-bottom: 3px solid <?= ($tab === 'history') ? '#d0021b' : 'transparent' ?>; color: <?= ($tab === 'history') ? '#d0021b' : '#888' ?>; transition: all 0.3s; cursor: pointer;">
            <i class="fa fa-history" style="margin-right: 8px;"></i>Lịch sử
        </a>
    </div>
    
    <?php if ($flash): ?>
        <div style="background: #e6f4ea; color: #1e7e34; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb;"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $row): 
            $orderId = $row['IdDonHang'];
            $displayStatus = $row['TinhTrang'];
            $isCancelled = ($displayStatus === 'Đã hủy');
            $canCancel = ($displayStatus === 'Đang xử lý');
            
            if ($displayStatus === 'Đang xử lý') { $bgStatus = '#fff3cd'; $colorStatus = '#856404'; } 
            elseif ($displayStatus === 'Đã hủy' || $displayStatus === 'Hoàn trả' || $displayStatus === 'Từ chối trả hàng') { $bgStatus = '#fdecea'; $colorStatus = '#d0021b'; } 
            elseif ($displayStatus === 'Đang giao') { $bgStatus = '#cce5ff'; $colorStatus = '#004085'; } 
            elseif ($displayStatus === 'Yêu cầu trả hàng') { $bgStatus = '#fff3cd'; $colorStatus = '#fd7e14'; } 
            else { $bgStatus = '#e6f4ea'; $colorStatus = '#1e7e34'; }
        ?>
            <div style="border: 1px solid #e0e0e0; border-radius: 10px; margin-bottom: 25px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="background: #fdfdfd; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee;">
                    <div>
                        <h3 style="margin: 0; font-size: 16px; color: #333;">Đơn hàng #<?= $orderId ?></h3>
                        <span style="font-size: 13px; color: #777; margin-top: 4px; display: block;">Ngày đặt: <?= date('d/m/Y H:i', strtotime($row['NgayDat'])) ?></span>
                    </div>
                    <div style="background: <?= $bgStatus ?>; color: <?= $colorStatus ?>; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: bold;">
                        <?= $displayStatus ?>
                    </div>
                </div>

                <div style="padding: 0 20px;">
                    <?php
                    $items = $dh->laychitietdonhang($orderId);
                    if (!empty($items)):
                        foreach ($items as $item):
                            $idSp = $item['IdSanPham'];
                            $da_danh_gia = $dg->kiemtramuahang($userId, $idSp);
                    ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px dashed #eee;">
                            <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                                <img src="<?= htmlspecialchars($item['HinhAnh']) ?>" alt="Ảnh" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                <div>
                                    <div style="font-weight: 600; color: #333; font-size: 15px; margin-bottom: 5px;"><?= htmlspecialchars($item['TenSanPham']) ?></div>
                                    <div style="font-size: 13px; color: #666;">Phân loại: <?= htmlspecialchars($item['KichThuoc'] ?? 'Mặc định') ?> &nbsp;|&nbsp; Số lượng: <?= $item['SoLuong'] ?></div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 15px; font-weight: bold; color: #333;"><?= number_format($item['TongTien'], 0, ',', '.') ?> đ</span>
                                <?php if ($displayStatus === 'Đã giao'): ?>
                                    <div style="margin-top: 5px;">
                                        <a href="chitiet.php?id=<?= $idSp ?>" style="font-size: 12px; color: #d0021b; text-decoration: none; border: 1px solid #d0021b; padding: 3px 8px; border-radius: 4px;">
                                            <i class="fa fa-star"></i> <?= $da_danh_gia ? 'Đánh giá lại' : 'Đánh giá' ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <div style="padding: 15px 20px; background: #fafafa; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 10px 10px;">
                    <div>
                        <span style="font-size: 14px; color: #555;">Tổng thanh toán: </span>
                        <strong style="color: #d0021b; font-size: 18px;"><?= number_format($row['TongTien'], 0, ',', '.') ?> đ</strong>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <?php if ($displayStatus === 'Đang giao'): ?>
                            <a href="donhang.php?action=danhan&id=<?= $orderId ?>" onclick="return confirm('Bạn xác nhận đã nhận được toàn bộ gói hàng này?');" style="background: #28a745; color: #fff; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa fa-check"></i> Đã nhận hàng
                            </a>
                        <?php elseif ($displayStatus === 'Đã giao'): ?>
                            <a href="donhang.php?action=trahang&id=<?= $orderId ?>" onclick="return confirm('Bạn có chắc chắn muốn gửi yêu cầu trả hàng / hoàn tiền cho đơn này?');" style="background: #ffc107; color: #000; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="fa fa-undo"></i> Yêu cầu trả hàng
                            </a>
                        <?php elseif ($canCancel): ?>
                            <a href="donhang.php?action=cancel&id=<?= $orderId ?>" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');" style="background: #fff; color: #dc3545; border: 1px solid #dc3545; padding: 7px 15px; border-radius: 5px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                Hủy đơn hàng
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 50px 0; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">
            <i class="fa fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
            <p style="color: #777; font-size: 16px; margin-bottom: 20px;">Không có đơn hàng nào trong mục này.</p>
            <a href="index.php" style="background: #000; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: 600;">Khám phá sản phẩm</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
