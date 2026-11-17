<?php
$page_title = 'Quản lý đơn hàng - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
require_once __DIR__ . '/../../model/donhang.php';

$dh = new DONHANG();
$loc_status = $_GET['status'] ?? '';
$orders = $dh->laytatcadonhang($loc_status);
?>

<div class="header-page">
    <h1>Danh sách đơn hàng</h1>
</div>

<!-- LỌC THEO TRẠNG THÁI -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
    <?php
    $statuses = [
        '' => 'Tất cả',
        'Đang xử lý' => 'Đang xử lý',
        'Đang giao' => 'Đang giao',
        'Đã giao' => 'Đã giao',
        'Yêu cầu trả hàng' => 'Yêu cầu trả hàng',
        'Hoàn trả' => 'Hoàn trả',
        'Từ chối trả hàng' => 'Từ chối trả hàng',
        'Đã hủy' => 'Đã hủy'
    ];
    foreach ($statuses as $stKey => $stLabel):
        $activeClass = ($loc_status === $stKey);
    ?>
        <a href="?status=<?= urlencode($stKey) ?>" 
           style="padding: 6px 14px; border-radius: 20px; font-size: 13px; text-decoration: none; font-weight: 500; border: 1px solid <?= $activeClass ? '#212529' : '#ddd' ?>; background: <?= $activeClass ? '#212529' : '#fff' ?>; color: <?= $activeClass ? '#fff' : '#444' ?>;">
            <?= $stLabel ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="table-container">
    <table class="table-wide">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Khách hàng</th>
                <th>Số điện thoại</th>
                <th>Địa chỉ</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>PTTT</th>
                <th style="text-align: center; width: 200px; min-width: 180px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $row): 
                    $tt = $row['TinhTrang'];
                    $color = ($tt == 'Đang giao' ? '#004085' : ($tt == 'Đã giao' ? '#1e7e34' : ($tt == 'Đang xử lý' ? '#856404' : '#d0021b')));
                ?>
                    <tr>
                        <td>#<?= $row['IdDonHang'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['HoTen'] ?: $row['TenDangNhap'] ?: 'Khách hàng') ?></strong>
                        </td>
                        <td><?= htmlspecialchars($row['DienThoai']) ?></td>
                        <td style="max-width: 200px;"><?= htmlspecialchars($row['DiaChi']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['NgayDat'])) ?></td>
                        <td><strong style="color: #d0021b;"><?= number_format($row['TongTien'], 0, ',', '.') ?> đ</strong></td>
                        <td style="font-weight: bold; color: <?= $color ?>;">
                            <?= htmlspecialchars($tt) ?>
                        </td>
                        <td><?= ($row['PhuongThucThanhToan'] === 'Chuyển khoản' || $row['PhuongThucThanhToan'] === 'BANK') ? '🏦 CK' : '💳 COD' ?></td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                <a href="/Thoi-Trang-Quality/Admin/ChiTietDonHang.php?id=<?= $row['IdDonHang'] ?>" class="btn" style="background: #17a2b8; color: #fff; padding: 5px 8px;">Xem</a>
                                
                                <?php if ($tt === 'Đang xử lý'): ?>
                                    <a href="?action=xacnhan&id=<?= $row['IdDonHang'] ?>" class="btn" style="background: #28a745; color: #fff; padding: 5px 8px;" onclick="return confirm('Xác nhận duyệt giao và TRỪ KHO?')">Duyệt</a>
                                <?php endif; ?>
                                
                                <?php if ($tt === 'Yêu cầu trả hàng'): ?>
                                    <a href="?action=hoantra&id=<?= $row['IdDonHang'] ?>" class="btn" style="background: #ffc107; color: #000; padding: 5px 8px;" onclick="return confirm('Chấp nhận hoàn trả và CỘNG LẠI KHO?')">Hoàn kho</a>
                                    <a href="?action=tuchoi&id=<?= $row['IdDonHang'] ?>" class="btn" style="background: #6c757d; color: #fff; padding: 5px 8px;" onclick="return confirm('Từ chối yêu cầu hoàn trả?')">Từ chối</a>
                                <?php endif; ?>
                                
                                <a href="?action=xoa&id=<?= $row['IdDonHang'] ?>" class="btn btn-delete" style="padding: 5px 8px;" onclick="return confirm('Xóa vĩnh viễn đơn hàng này?');">Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align: center; padding: 40px; color: #888;">Không tìm thấy đơn hàng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>

