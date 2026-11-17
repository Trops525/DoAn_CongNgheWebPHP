<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/nguoidung.php';

// Chỉ Quản trị viên (Quyền 1) mới được vào trang này
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 2) != 1) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$nd = new NGUOIDUNG();
$action = $_REQUEST['action'] ?? 'macdinh';
$currentScript = basename($_SERVER['PHP_SELF']);

switch ($action) {
    case 'macdinh':
        $users = $nd->laydanhsachnguoidung();
        include 'main.php';
        break;

    case 'them':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'HoTen' => trim($_POST['hoten'] ?? ''),
                'TenDangNhap' => trim($_POST['tendangnhap'] ?? ''),
                'MatKhau' => trim($_POST['matkhau'] ?? ''),
                'Email' => trim($_POST['email'] ?? ''),
                'DienThoai' => trim($_POST['dienthoai'] ?? ''),
                'QuyenHan' => (int)($_POST['quyenhan'] ?? 2)
            ];
            if ($nd->themnguoidung($data)) {
                echo "<script>alert('Thêm tài khoản thành công!'); window.location.href='" . $currentScript . "';</script>";
                exit;
            } else {
                echo "<script>alert('Lỗi: Tên đăng nhập hoặc Email đã tồn tại!');</script>";
            }
        }
        $users = $nd->laydanhsachnguoidung();
        include 'main.php';
        break;

    case 'khoa':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $nd->doitrangthai($id);
        }
        header('Location: ' . $currentScript);
        exit;

    case 'quyen':
        $id = (int)($_POST['id'] ?? 0);
        $quyen = (int)($_POST['quyenhan'] ?? 2);
        if ($id > 0) {
            $nd->phanquyen($id, $quyen);
        }
        header('Location: ' . $currentScript);
        exit;

    case 'xoa':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $nd->xoanguoidung($id);
        }
        header('Location: ' . $currentScript);
        exit;

    default:
        $users = $nd->laydanhsachnguoidung();
        include 'main.php';
        break;
}
?>
