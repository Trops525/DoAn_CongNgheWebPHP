<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/donhang.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 5])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$dh = new DONHANG();
$currentMonth = date('m');
$currentYear = date('Y');

$stats = $dh->thongkebaochao($currentMonth, $currentYear);
$doanhThuThang = $stats['DoanhThu'];
$soDonHang = $stats['SoDonHang'];
$tongSanPham = $stats['TongSanPham'];
$topBanChay = $stats['TopBanChay'];

include 'main.php';
?>

