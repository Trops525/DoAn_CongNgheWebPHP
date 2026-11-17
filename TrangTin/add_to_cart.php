<?php
require_once __DIR__ . '/common.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Lấy size từ link, nếu không có thì mặc định là Freesize
$size = isset($_GET['size']) ? $_GET['size'] : 'Freesize';

// Lấy action để xem là nút "Mua ngay" hay "Thêm giỏ hàng"
$action = isset($_GET['action']) ? $_GET['action'] : ''; 

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    $redirect = urlencode("chitiet.php?id=$id&size=" . urlencode($size));
    
    // Nếu là gọi qua AJAX từ nút Thêm vào giỏ hàng
    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'redirect',
            'redirect_url' => "dangnhap.php?next=$redirect"
        ]);
        exit;
    }
    
    header("Location: dangnhap.php?next=$redirect");
    exit;
}

// Logic lưu giỏ hàng mới: Ghép ID và Size (ví dụ: 1_M)
$cart_key = $id . "_" . $size;

// ==========================================
// XỬ LÝ ĐIỀU HƯỚNG DỰA VÀO NÚT ĐƯỢC BẤM
// ==========================================

if ($action === 'buynow') {
    // Nếu là MUA NGAY: Lưu vào 1 session riêng biệt, xóa session mua ngay cũ (nếu có)
    $_SESSION['buy_now_cart'] = [];
    $_SESSION['buy_now_cart'][$cart_key] = 1;
    
    // Chuyển thẳng sang trang Thanh toán (checkout.php) và truyền tham số type=buynow
    header('Location: checkout.php?type=buynow');
    exit;
} else {
    // Nếu là THÊM VÀO GIỎ HÀNG: Xử lý giỏ hàng chung
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Nếu đã có trong giỏ thì cộng dồn số lượng, chưa có thì set = 1
    if (!isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key] = 1; 
    } else {
        $_SESSION['cart'][$cart_key] += 1;
    }

    // Nếu là gọi qua AJAX
    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công!',
            'cart_count' => getCartCount()
        ]);
        exit;
    }

    // Lưu thông báo và quay lại đúng trang đang xem
    $_SESSION['flash_message'] = 'Sản phẩm đã được thêm vào giỏ hàng.';
    
    // Sử dụng HTTP_REFERER để lấy URL của trang trước đó (chitiet.php)
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "chitiet.php?id=$id";
    
    header("Location: $referer");
    exit;
}