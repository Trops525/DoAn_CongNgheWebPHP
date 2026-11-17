<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

class GIOHANG {
    // Lấy danh sách sản phẩm đầy đủ thông tin trong giỏ hàng
    public function laygiohang($type = 'cart') {
        $cart = ($type === 'buynow') ? ($_SESSION['buy_now_cart'] ?? []) : ($_SESSION['cart'] ?? []);
        if (empty($cart)) {
            return [];
        }

        $ids = [];
        foreach ($cart as $key => $qty) {
            $parts = explode('_', $key);
            if (!empty($parts[0])) {
                $ids[] = (int)$parts[0];
            }
        }

        if (empty($ids)) {
            return [];
        }

        $db = DATABASE::connect();
        $inIds = implode(',', array_unique($ids));
        try {
            $sql = "SELECT * FROM tbl_sanpham WHERE IdSanPham IN ($inIds)";
            $cmd = $db->prepare($sql);
            $cmd->execute();
            $products = [];
            while ($row = $cmd->fetch()) {
                $products[$row['IdSanPham']] = $row;
            }

            $items = [];
            foreach ($cart as $key => $quantity) {
                $parts = explode('_', $key);
                $productId = (int)$parts[0];
                $productSize = $parts[1] ?? 'Freesize';

                if (isset($products[$productId])) {
                    $item = $products[$productId];
                    $item['cart_key'] = $key;
                    $item['size'] = $productSize;
                    $item['quantity'] = max(1, (int)$quantity);
                    $item['final_price'] = $item['DonGia'] - ($item['DonGia'] * $item['TiLeGiamGia'] / 100);
                    $item['total_price'] = $item['final_price'] * $item['quantity'];
                    $items[] = $item;
                }
            }
            return $items;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Thêm mặt hàng vào giỏ (hỗ trợ chọn size & mua ngay)
    public function themhang($id, $soluong = 1, $size = 'Freesize', $type = 'cart') {
        $id = (int)$id;
        $soluong = max(1, (int)$soluong);
        $cart_key = $id . '_' . $size;

        if ($type === 'buynow') {
            $_SESSION['buy_now_cart'] = [];
            $_SESSION['buy_now_cart'][$cart_key] = $soluong;
            return true;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key] += $soluong;
        } else {
            $_SESSION['cart'][$cart_key] = $soluong;
        }
        return true;
    }

    // Cập nhật số lượng của một mặt hàng
    public function capnhatsoluong($cart_key, $soluong, $type = 'cart') {
        $soluong = (int)$soluong;
        $session_key = ($type === 'buynow') ? 'buy_now_cart' : 'cart';

        if (!isset($_SESSION[$session_key])) {
            return;
        }

        if ($soluong <= 0) {
            unset($_SESSION[$session_key][$cart_key]);
        } else {
            $_SESSION[$session_key][$cart_key] = $soluong;
        }
    }

    // Xóa một mặt hàng khỏi giỏ
    public function xoamotmathang($cart_key, $type = 'cart') {
        $session_key = ($type === 'buynow') ? 'buy_now_cart' : 'cart';
        if (isset($_SESSION[$session_key][$cart_key])) {
            unset($_SESSION[$session_key][$cart_key]);
        }
    }

    // Xóa sạch giỏ hàng
    public function xoagiohang($type = 'cart') {
        $session_key = ($type === 'buynow') ? 'buy_now_cart' : 'cart';
        unset($_SESSION[$session_key]);
    }

    // Đếm tổng số lượng mặt hàng trong giỏ
    public function demsohang($type = 'cart') {
        $cart = ($type === 'buynow') ? ($_SESSION['buy_now_cart'] ?? []) : ($_SESSION['cart'] ?? []);
        $total = 0;
        foreach ($cart as $qty) {
            $total += max(0, (int)$qty);
        }
        return $total;
    }

    // Tính tổng tiền toàn bộ giỏ hàng
    public function tinhtiengiohang($type = 'cart') {
        $items = $this->laygiohang($type);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['total_price'];
        }
        return $total;
    }
}
?>

