<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Kiểm tra quyền Admin hoặc Nhân viên
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: ../TrangTin/dangnhap.php?next=../Admin/QuanLyDonHang.php');
    exit;
}

// Kiểm tra xem có truyền ID đơn hàng không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: QuanLyDonHang.php');
    exit;
}

$orderId = (int)$_GET['id'];

// 1. Lấy thông tin chung của Đơn hàng và Người dùng
$sqlOrder = "SELECT dh.*, nd.HoTen, nd.TenDangNhap, nd.Email, nv.HoTen AS NhanVien_HoTen, nv.TenDangNhap AS NhanVien_TenDangNhap
             FROM tbl_donhang dh 
             LEFT JOIN tbl_nguoidung nd ON dh.IdNguoiDung = nd.IdNguoiDung 
             LEFT JOIN tbl_nguoidung nv ON dh.IdNhanVienTao = nv.IdNguoiDung
             WHERE dh.IdDonHang = $orderId";
$resOrder = $ket_noi->query($sqlOrder);

if (!$resOrder || $resOrder->num_rows === 0) {
    echo "<script>alert('Không tìm thấy đơn hàng!'); window.location.href='QuanLyDonHang.php';</script>";
    exit;
}
$order = $resOrder->fetch_assoc();

// 2. Lấy danh sách sản phẩm trong đơn hàng này
$sqlItems = "SELECT ct.*, sp.TenSanPham, sp.HinhAnh, sp.MaSanPham 
             FROM tbl_chitietdonhang ct 
             JOIN tbl_sanpham sp ON ct.IdSanPham = sp.IdSanPham 
             WHERE ct.IdDonHang = $orderId";
$resItems = $ket_noi->query($sqlItems);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= $orderId ?> - Quality Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        .detail-container { display: flex; gap: 20px; margin-top: 20px; align-items: flex-start; flex-wrap: wrap; }
        .info-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); flex: 1; min-width: 280px; border: 1px solid #eee; }
        .info-card h3 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #ddd; font-size: 16px; text-transform: uppercase; color: #333;}
        .info-line { margin-bottom: 12px; font-size: 14px; color: #555; line-height: 1.5;}
        .info-line strong { display: inline-block; width: 130px; color: #333; }
        
        .table-items { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; min-width: 650px; }
        .table-items th, .table-items td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .table-items th { background: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 13px; color: #555; }
        .item-row img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .total-row td { font-size: 16px; font-weight: bold; background: #fafafa; }
        .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; margin-bottom: 15px; }
        .btn-back:hover { background: #5a6268; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>QUALITY ADMIN</h2>
    <a href="index.php"><i class="fa fa-box"></i> Quản lý sản phẩm</a>
    <?php if (($_SESSION['user_role'] ?? 2) == 1): ?>
    <a href="thongke.php"><i class="fa fa-chart-line"></i> Thống kê doanh thu</a>
    <?php endif; ?>
    <a href="QuanLyDanhMuc.php"><i class="fa fa-list"></i> Quản lý danh mục</a>
    <a href="QuanLyDonHang.php" class="active"><i class="fa fa-shopping-cart"></i> Quản lý đơn hàng</a>
    <a href="QuanLyChat.php"><i class="fa fa-comments"></i> Quản lý Chat</a>
    <?php if (($_SESSION['user_role'] ?? 2) == 1): ?>
    <a href="QuanLyNguoiDung.php"><i class="fa fa-users"></i> Quản lý người dùng</a>
    <?php endif; ?>
    <a href="../TrangTin/index.php" style="color: #ffc107;"><i class="fa fa-external-link-alt"></i> Xem trang chủ</a>
    <a href="dangxuat.php" style="margin-top: 50px; background: #343a40;"><i class="fa fa-sign-out-alt"></i> Đăng xuất</a>
</div>

<div class="main-content">
    <a href="QuanLyDonHang.php" class="btn-back"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a>
    
    <div class="header-page" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Chi tiết Đơn hàng #<?= $orderId ?></h1>
        <span style="padding: 8px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; 
            background: <?= ($order['TinhTrang']=='Đang giao' ? '#cce5ff' : ($order['TinhTrang']=='Đã giao' ? '#e6f4ea' : ($order['TinhTrang']=='Đang xử lý' ? '#fff3cd' : '#fdecea'))) ?>;
            color: <?= ($order['TinhTrang']=='Đang giao' ? '#004085' : ($order['TinhTrang']=='Đã giao' ? '#1e7e34' : ($order['TinhTrang']=='Đang xử lý' ? '#856404' : '#d0021b'))) ?>;">
            <?= htmlspecialchars($order['TinhTrang']) ?>
        </span>
    </div>

    <div class="detail-container">
        <div class="info-card">
            <h3><i class="fa fa-truck"></i> Thông tin Giao Hàng</h3>
            <div class="info-line"><strong>Người nhận:</strong> <?= htmlspecialchars($order['HoTen'] ?: $order['TenDangNhap'] ?: 'Khách hàng') ?></div>
            <div class="info-line"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['DienThoai']) ?></div>
            <div class="info-line"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['DiaChi']) ?></div>
            <div class="info-line"><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i:s', strtotime($order['NgayDat'])) ?></div>
        </div>
        
        <div class="info-card">
            <h3><i class="fa fa-credit-card"></i> Thanh toán & Ghi chú</h3>
            <div class="info-line"><strong>Phương thức TT:</strong> <?= ($order['PhuongThucThanhToan'] === 'BANK') ? 'Chuyển khoản (BANK)' : 'Thanh toán khi nhận (COD)' ?></div>
            <div class="info-line" style="color: #d0021b;"><strong>Ghi chú từ khách:</strong><br>
                <i><?= nl2br(htmlspecialchars($order['GhiChu'] ?: 'Không có ghi chú')) ?></i>
            </div>
        </div>

        <div class="info-card">
            <h3><i class="fa fa-user-check"></i> Người xác nhận giao</h3>
            <div class="info-line"><strong>Tên:</strong> <?= htmlspecialchars($order['NhanVien_HoTen'] ?: 'Chưa xác nhận') ?></div>
            <div class="info-line"><strong>Tài khoản:</strong> <?= htmlspecialchars($order['NhanVien_TenDangNhap'] ?: 'Chưa có') ?></div>
        </div>
    </div>

    <div class="table-container" style="padding: 0; margin-top: 20px;">
        <table class="table-items">
        <thead>
            <tr>
                <th style="width: 70px;">Hình</th>
                <th>Tên sản phẩm</th>
                <th>Phân loại / Size</th>
                <th style="text-align: center;">Số lượng</th>
                <th style="text-align: right;">Đơn giá</th>
                <th style="text-align: right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resItems && $resItems->num_rows > 0): ?>
                <?php while ($item = $resItems->fetch_assoc()): ?>
                    <tr class="item-row">
                        <td>
                            <?php $link_anh = str_replace(' ', '%20', $item['HinhAnh']); ?>
                            <img src="../TrangTin/<?= htmlspecialchars($link_anh) ?>" alt="SP">
                        </td>
                        <td>
                            <strong style="color: #0056b3;"><?= htmlspecialchars($item['TenSanPham']) ?></strong><br>
                            <span style="font-size: 12px; color: #888;">Mã: <?= htmlspecialchars($item['MaSanPham'] ?? 'SP') ?></span>
                        </td>
                        <td><?= htmlspecialchars($item['KichThuoc'] ?? 'Mặc định') ?></td>
                        <td style="text-align: center; font-weight: bold;"><?= $item['SoLuong'] ?></td>
                        <td style="text-align: right;"><?= number_format($item['DonGia'], 0, ',', '.') ?> đ</td>
                        <td style="text-align: right; color: #d0021b; font-weight: bold;">
                            <?= number_format($item['TongTien'], 0, ',', '.') ?> đ
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">Không tải được chi tiết sản phẩm.</td></tr>
            <?php endif; ?>
            
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">TỔNG CỘNG HOÁ ĐƠN:</td>
                <td style="text-align: right; color: #d0021b; font-size: 18px;"><?= number_format($order['TongTien'], 0, ',', '.') ?> đ</td>
            </tr>
        </tbody>
    </table>
    </div>
    <br><br>
</div>

</body>
</html>