<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

// Kiểm tra quyền Admin hoặc các nhóm Nhân viên chuyên trách (1: Admin, 3: Kho, 4: CSKH/Sale, 5: Kế toán)
$userRole = (int)($_SESSION['user_role'] ?? 2);
if (empty($_SESSION['user_id']) || !in_array($userRole, [1, 3, 4, 5])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$roleTitles = [
    1 => 'Quản trị viên',
    3 => 'NV Kho & Hàng hóa',
    4 => 'NV Bán hàng & CSKH',
    5 => 'Kế toán / Tài chính'
];
$displayRole = $roleTitles[$userRole] ?? 'Nhân viên';

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
    <!-- Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Quality Admin Custom CSS -->
    <link rel="stylesheet" href="/Thoi-Trang-Quality/Admin/assets/admin.css?v=<?= @filemtime(__DIR__ . '/../assets/admin.css') ?: time() ?>">
    <script defer src="/Thoi-Trang-Quality/Admin/assets/admin.js"></script>
</head>
<body>

<div class="sidebar">
    <h2>QUALITY ADMIN</h2>
    <div style="padding: 0 25px 15px; color: #ffc107; font-size: 13px; border-bottom: 1px solid #2c3136;">
        <i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
        <span style="font-size: 11px; display: block; color: #17a2b8; margin-top: 2px; font-weight: 600;">
            (<?= $displayRole ?>)
        </span>
    </div>

    <!-- 1. Quản lý sản phẩm: Admin, Kho, Bán hàng, Kế toán (kho sửa, sales/kế toán xem tra cứu) -->
    <a href="/Thoi-Trang-Quality/Admin/index.php" class="<?= ($current_script == 'index.php' && $current_dir == 'Admin') || $current_dir == 'qlmathang' ? 'active' : '' ?>">
        <i class="fa fa-box"></i> Quản lý sản phẩm
    </a>

    <!-- 2. Thống kê doanh thu: Chỉ Admin (1) và Kế toán (5) -->
    <?php if (in_array($userRole, [1, 5])): ?>
    <a href="/Thoi-Trang-Quality/Admin/thongke.php" class="<?= ($current_script == 'thongke.php' || $current_dir == 'thongke') ? 'active' : '' ?>">
        <i class="fa fa-chart-line"></i> Thống kê doanh thu
    </a>
    <?php endif; ?>

    <!-- 3. Quản lý danh mục: Chỉ Admin (1) và NV Kho (3) -->
    <?php if (in_array($userRole, [1, 3])): ?>
    <a href="/Thoi-Trang-Quality/Admin/QuanLyDanhMuc.php" class="<?= ($current_script == 'QuanLyDanhMuc.php' || $current_dir == 'qldanhmuc') ? 'active' : '' ?>">
        <i class="fa fa-list"></i> Quản lý danh mục
    </a>
    <?php endif; ?>

    <!-- 4. Quản lý đơn hàng: Tất cả nhân viên (CSKH duyệt đơn, Kho xem đóng gói, Kế toán đối soát) -->
    <a href="/Thoi-Trang-Quality/Admin/QuanLyDonHang.php" class="<?= ($current_script == 'QuanLyDonHang.php' || $current_dir == 'qldonhang') ? 'active' : '' ?>">
        <i class="fa fa-shopping-cart"></i> Quản lý đơn hàng
    </a>

    <!-- 5. Quản lý Chat: Chỉ Admin (1) và NV Bán hàng & CSKH (4) -->
    <?php if (in_array($userRole, [1, 4])): ?>
    <a href="/Thoi-Trang-Quality/Admin/QuanLyChat.php" class="<?= ($current_script == 'QuanLyChat.php' || $current_dir == 'qlchat') ? 'active' : '' ?>">
        <i class="fa fa-comments"></i> Quản lý Chat
    </a>
    <?php endif; ?>

    <!-- 6. Quản lý người dùng: Duy nhất Super Admin (1) -->
    <?php if ($userRole == 1): ?>
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

