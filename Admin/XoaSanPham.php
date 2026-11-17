<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once __DIR__ . '/../model/mathang.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $mh = new MATHANG();
    if ($mh->xoamathang($id)) {
        echo "<script>alert('Đã xóa sản phẩm khỏi hệ thống!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa sản phẩm!'); window.location.href='index.php';</script>";
    }
} else {
    header('Location: index.php');
}
?>
