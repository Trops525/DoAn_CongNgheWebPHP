<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $next = urlencode($_SERVER['REQUEST_URI']);
        header("Location: dangnhap.php?next=$next");
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || ($_SESSION['user_role'] ?? 2) != 1) {
        $next = urlencode($_SERVER['REQUEST_URI']);
        header("Location: dangnhap.php?next=$next");
        exit;
    }
}

function getCart() {
    return isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

function getCartCount() {
    $count = 0;
    foreach (getCart() as $quantity) {
        $count += max(0, (int)$quantity);
    }
    return $count;
}

function addToCart($productId, $quantity = 1) {
    global $ket_noi;
    $productId = (int)$productId;
    $quantity = max(1, (int)$quantity);

    $sql = "SELECT IdSanPham FROM tbl_sanpham WHERE IdSanPham = $productId LIMIT 1";
    $result = $ket_noi->query($sql);
    if (!$result || $result->num_rows === 0) {
        return false;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    return true;
}

function setCartItem($productId, $quantity) {
    $productId = (int)$productId;
    $quantity = (int)$quantity;

    if ($quantity <= 0) {
        removeCartItem($productId);
        return;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][$productId] = $quantity;
}

function removeCartItem($productId) {
    $productId = (int)$productId;
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart() {
    unset($_SESSION['cart']);
}

function getUserOrderCount() {
    global $ket_noi;
    if (!isLoggedIn()) {
        return 0;
    }
    
    $userId = (int)$_SESSION['user_id'];
    
    $sql = "SELECT TinhTrang FROM tbl_donhang WHERE IdNguoiDung = $userId";
    $result = $ket_noi->query($sql);
    
    $count = 0;
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Lấy dữ liệu từ cột TinhTrang
            $tinhTrang = mb_strtolower(trim((string)$row['TinhTrang']), 'UTF-8');
            
            // Đếm các đơn hàng KHÔNG bị hủy
            if (strpos($tinhTrang, 'hủy') === false && 
                strpos($tinhTrang, 'huỷ') === false && 
                $tinhTrang !== '3' && 
                $tinhTrang !== '4') {
                $count++;
            }
        }
    }
    
    return $count;
}