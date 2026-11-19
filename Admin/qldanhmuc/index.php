<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/danhmuc.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$dm = new DANHMUC();
$action = $_REQUEST['action'] ?? 'macdinh';
$error = '';
$success = '';
$category = ['TenDanhMuc' => '', 'MoTa' => ''];
$currentScript = basename($_SERVER['PHP_SELF']);

switch ($action) {
    case 'macdinh':
        $danhmuc = $dm->laydanhmuc();
        include 'main.php';
        break;

    case 'them':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['TenDanhMuc'] ?? '');
            $desc = trim($_POST['MoTa'] ?? '');
            if ($name === '') {
                $error = 'Tên danh mục không được để trống.';
            } else {
                if ($dm->themdanhmuc($name, $desc)) {
                    $success = 'Thêm danh mục mới thành công.';
                    header('Location: ' . $currentScript);
                    exit;
                } else {
                    $error = 'Không thể thêm danh mục mới.';
                }
            }
        }
        $danhmuc = $dm->laydanhmuc();
        include 'main.php';
        break;

    case 'sua':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['TenDanhMuc'] ?? '');
            $desc = trim($_POST['MoTa'] ?? '');
            if ($name === '') {
                $error = 'Tên danh mục không được để trống.';
            } else {
                if ($dm->suadanhmuc($id, $name, $desc)) {
                    $success = 'Cập nhật danh mục thành công.';
                    header('Location: ' . $currentScript);
                    exit;
                } else {
                    $error = 'Không thể cập nhật danh mục.';
                }
            }
        }
        $cat = $dm->laydanhmuctheoid($id);
        if ($cat) {
            $category = $cat;
        }
        $danhmuc = $dm->laydanhmuc();
        include 'main.php';
        break;

    case 'khoa':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $dm->doitrangthai($id);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? $currentScript;
        header('Location: ' . $referer);
        exit;

    case 'xoa':
        if (($_SESSION['user_role'] ?? 2) != 1) {
            echo "<script>alert('Chỉ Quản trị viên mới có quyền xóa danh mục vĩnh viễn!'); window.location.href='" . $currentScript . "';</script>";
            exit;
        }
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $dm->xoadanhmuc($id);
        }
        header('Location: ' . $currentScript);
        exit;

    default:
        $danhmuc = $dm->laydanhmuc();
        include 'main.php';
        break;
}
?>
