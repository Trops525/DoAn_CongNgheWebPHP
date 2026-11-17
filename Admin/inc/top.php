<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

// Kiểm tra quyền Admin hoặc Nhân viên
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$current_script = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Hệ thống Quản trị - Quality Admin' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Thoi-Trang-Quality/Admin/assets/admin.css?v=<?= @filemtime(__DIR__ . '/../assets/admin.css') ?: time() ?>">
    <script defer src="/Thoi-Trang-Quality/Admin/assets/admin.js"></script>
</head>
<body>

<div class="sidebar">
    <h2>QUALITY ADMIN</h2>
    <div style="padding: 0 25px 15px; color: #ffc107; font-size: 13px; border-bottom: 1px solid #2c3136;">
        <i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
        <span style="font-size: 11px; display: block; color: #adb5bd; margin-top: 2px;">
            (<?= ($_SESSION['user_role'] == 1) ? 'Quản trị viên' : 'Nhân viên' ?>)
        </span>
    </div>

    <a href="/Thoi-Trang-Quality/Admin/index.php" class="<?= ($current_script == 'index.php' && $current_dir == 'Admin') || $current_dir == 'qlmathang' ? 'active' : '' ?>">
        <i class="fa fa-box"></i> Quản lý sản phẩm
    </a>

    <?php if (($_SESSION['user_role'] ?? 2) == 1): ?>
    <a href="/Thoi-Trang-Quality/Admin/thongke.php" class="<?= ($current_script == 'thongke.php' || $current_dir == 'thongke') ? 'active' : '' ?>">
        <i class="fa fa-chart-line"></i> Thống kê doanh thu
    </a>
    <?php endif; ?>

    <a href="/Thoi-Trang-Quality/Admin/QuanLyDanhMuc.php" class="<?= ($current_script == 'QuanLyDanhMuc.php' || $current_dir == 'qldanhmuc') ? 'active' : '' ?>">
        <i class="fa fa-list"></i> Quản lý danh mục
    </a>

    <a href="/Thoi-Trang-Quality/Admin/QuanLyDonHang.php" class="<?= ($current_script == 'QuanLyDonHang.php' || $current_dir == 'qldonhang') ? 'active' : '' ?>">
        <i class="fa fa-shopping-cart"></i> Quản lý đơn hàng
    </a>

    <a href="/Thoi-Trang-Quality/Admin/QuanLyChat.php" class="<?= ($current_script == 'QuanLyChat.php' || $current_dir == 'qlchat') ? 'active' : '' ?>">
        <i class="fa fa-comments"></i> Quản lý Chat
    </a>

    <?php if (($_SESSION['user_role'] ?? 2) == 1): ?>
    <a href="/Thoi-Trang-Quality/Admin/QuanLyNguoiDung.php" class="<?= ($current_script == 'QuanLyNguoiDung.php' || $current_dir == 'qlnguoidung') ? 'active' : '' ?>">
        <i class="fa fa-users"></i> Quản lý người dùng
    </a>
    <?php endif; ?>

    <a href="/Thoi-Trang-Quality/Admin/ktnguoidung/index.php?action=hoso" class="<?= $current_dir == 'ktnguoidung' ? 'active' : '' ?>">
        <i class="fa fa-id-badge"></i> Hồ sơ cá nhân
    </a>

    <a href="/Thoi-Trang-Quality/TrangTin/index.php" style="color: #ffc107;">
        <i class="fa fa-home"></i> Xem trang chủ
    </a>

    <a href="/Thoi-Trang-Quality/Admin/dangxuat.php" style="margin-top: 30px; background: #343a40;">
        <i class="fa fa-sign-out-alt"></i> Đăng xuất
    </a>
</div>

<div class="main-content">

