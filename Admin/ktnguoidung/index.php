<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/nguoidung.php';

$nd = new NGUOIDUNG();
$action = $_REQUEST['action'] ?? 'macdinh';

switch ($action) {
    case 'dangnhap':
        header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php');
        exit;

    case 'dangxuat':
        session_destroy();
        header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php');
        exit;

    case 'hoso':
    case 'macdinh':
        if (empty($_SESSION['user_id'])) {
            header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php');
            exit;
        }
        $user = $nd->laynguoidungtheoid($_SESSION['user_id']);
        include 'profile.php';
        break;

    case 'xlhoso':
        if (empty($_SESSION['user_id'])) {
            header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php');
            exit;
        }
        $hoten = trim($_POST['hoten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['dienthoai'] ?? '');
        
        $db = DATABASE::connect();
        $cmd = $db->prepare("UPDATE tbl_nguoidung SET HoTen = :ht, Email = :em, DienThoai = :dt WHERE IdNguoiDung = :id");
        $cmd->execute([':ht' => $hoten, ':em' => $email, ':dt' => $phone, ':id' => (int)$_SESSION['user_id']]);
        $_SESSION['user_name'] = $hoten;
        $_SESSION['flash_success'] = 'Cập nhật thông tin hồ sơ thành công.';
        header('Location: index.php?action=hoso');
        exit;

    case 'doimatkhau':
        header('Location: index.php?action=hoso');
        exit;

    case 'xldoimatkhau':
        if (empty($_SESSION['user_id'])) {
            header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php');
            exit;
        }
        $old = trim($_POST['matkhaucu'] ?? '');
        $new = trim($_POST['matkhaumoi'] ?? '');
        $confirm = trim($_POST['xacnhan'] ?? '');

        if ($new !== $confirm) {
            $_SESSION['flash_error'] = 'Mật khẩu xác nhận không khớp.';
        } elseif ($nd->doimatkhau($_SESSION['user_id'], $old, $new)) {
            $_SESSION['flash_success'] = 'Đổi mật khẩu thành công.';
        } else {
            $_SESSION['flash_error'] = 'Mật khẩu cũ không chính xác.';
        }
        header('Location: index.php?action=doimatkhau');
        exit;

    default:
        header('Location: ../index.php');
        exit;
}
?>

