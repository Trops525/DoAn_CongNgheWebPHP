<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/danhmuc.php';
require_once __DIR__ . '/../model/mathang.php';
require_once __DIR__ . '/../model/giohang.php';
require_once __DIR__ . '/../model/nguoidung.php';
require_once __DIR__ . '/../model/donhang.php';
require_once __DIR__ . '/../model/cskh.php';
require_once __DIR__ . '/common.php';

// Khởi tạo các đối tượng Model
$dm = new DANHMUC();
$mh = new MATHANG();
$gh = new GIOHANG();
$nd = new NGUOIDUNG();
$dh = new DONHANG();
$cskh = new CSKH();
$dg = new DANHGIA();

// Lấy danh mục dùng chung
$danhmuc = $dm->laydanhmuc();

// Xác định hành động
$action = $_REQUEST['action'] ?? 'null';

switch ($action) {
    case 'null':
    case 'trangchu':
        // Trang chủ chính
        include __DIR__ . '/main.php';
        break;

    case 'group':
        // Xem mặt hàng theo nhóm danh mục
        include __DIR__ . '/group.php';
        break;

    case 'detail':
        // Xem chi tiết mặt hàng
        include __DIR__ . '/chitiet.php';
        break;

    case 'chovaogio':
        // Cho hàng vào giỏ
        $id = (int)($_REQUEST['id'] ?? 0);
        $soluong = (int)($_REQUEST['soluong'] ?? 1);
        $size = $_REQUEST['size'] ?? 'Freesize';
        if (!isLoggedIn()) {
            $redirect = urlencode("chitiet.php?id=$id");
            header("Location: dangnhap.php?next=$redirect");
            exit;
        }
        if ($id > 0) {
            $gh->themhang($id, $soluong, $size, 'cart');
            $_SESSION['flash_message'] = 'Sản phẩm đã được thêm vào giỏ hàng.';
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? "chitiet.php?id=$id";
        header("Location: $referer");
        exit;

    case 'buynow':
        // Mua ngay
        $id = (int)($_REQUEST['id'] ?? 0);
        $size = $_REQUEST['size'] ?? 'Freesize';
        if (!isLoggedIn()) {
            $redirect = urlencode("chitiet.php?id=$id");
            header("Location: dangnhap.php?next=$redirect");
            exit;
        }
        if ($id > 0) {
            $gh->themhang($id, 1, $size, 'buynow');
            header('Location: checkout.php?type=buynow');
            exit;
        }
        header('Location: index.php');
        exit;

    case 'cart':
    case 'xemgiohang':
        // Xem giỏ hàng
        include __DIR__ . '/cart.php';
        break;

    case 'checkout':
    case 'thanhtoan':
        // Thanh toán
        include __DIR__ . '/checkout.php';
        break;

    case 'donhang':
        // Quản lý đơn hàng của tôi
        include __DIR__ . '/donhang.php';
        break;

    case 'cskh':
        // Hỗ trợ CSKH và hỏi đáp
        include __DIR__ . '/cskh.php';
        break;

    case 'dangnhap':
        include __DIR__ . '/dangnhap.php';
        break;

    case 'dangky':
        include __DIR__ . '/dangky.php';
        break;

    case 'dangxuat':
        include __DIR__ . '/dangxuat.php';
        break;

    case 'doimatkhau':
        include __DIR__ . '/doi_mat_khau.php';
        break;

    default:
        include __DIR__ . '/main.php';
        break;
}
?>
