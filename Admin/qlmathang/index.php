<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/mathang.php';
require_once __DIR__ . '/../../model/danhmuc.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3, 4, 5])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$mh = new MATHANG();
$dm = new DANHMUC();
$action = $_REQUEST['action'] ?? 'macdinh';

switch ($action) {
    case 'macdinh':
        include 'main.php';
        break;

    case 'them':
        if (!in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
            echo "<script>alert('Bạn không có quyền thêm sản phẩm mới!'); window.location.href='index.php';</script>";
            exit;
        }
        include __DIR__ . '/../them_sanpham.php';
        break;

    case 'sua':
        if (!in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
            echo "<script>alert('Bạn không có quyền chỉnh sửa sản phẩm!'); window.location.href='index.php';</script>";
            exit;
        }
        include __DIR__ . '/../SuaSanPham.php';
        break;

    case 'khoa':
        if (!in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
            echo "<script>alert('Bạn không có quyền khóa sản phẩm!'); window.location.href='index.php';</script>";
            exit;
        }
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $mh->doitrangthai($id);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $referer);
        exit;

    case 'xoa':
        if (($_SESSION['user_role'] ?? 2) != 1) {
            echo "<script>alert('Chỉ Quản trị viên mới có quyền xóa sản phẩm vĩnh viễn!'); window.location.href='index.php';</script>";
            exit;
        }
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $mh->xoamathang($id);
        }
        header('Location: index.php');
        exit;

    case 'chitiet':
        $id = (int)($_REQUEST['id'] ?? 0);
        header('Location: ../../TrangTin/chitiet.php?id=' . $id);
        exit;

    default:
        include 'main.php';
        break;
}
?>

