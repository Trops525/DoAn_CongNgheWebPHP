<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/donhang.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$dh = new DONHANG();
$action = $_REQUEST['action'] ?? 'macdinh';
$currentScript = basename($_SERVER['PHP_SELF']);

switch ($action) {
    case 'macdinh':
        include 'main.php';
        break;

    case 'xacnhan':
        $id = (int)($_REQUEST['id'] ?? 0);
        $confirmedBy = (int)$_SESSION['user_id'];
        $dh->capnhattrangthai($id, 'Đang giao', $confirmedBy);
        header('Location: ' . $currentScript);
        exit;

    case 'hoantra':
        $id = (int)($_REQUEST['id'] ?? 0);
        $dh->capnhattrangthai($id, 'Hoàn trả');
        header('Location: ' . $currentScript);
        exit;

    case 'tuchoi':
        $id = (int)($_REQUEST['id'] ?? 0);
        $dh->capnhattrangthai($id, 'Từ chối trả hàng');
        header('Location: ' . $currentScript);
        exit;

    case 'xoa':
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $dh->xoadonhang($id);
        }
        header('Location: ' . $currentScript);
        exit;

    case 'chitiet':
        include __DIR__ . '/../ChiTietDonHang.php';
        break;

    default:
        include 'main.php';
        break;
}
?>
