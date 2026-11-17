<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/giohang.php';
requireLogin();

$gh = new GIOHANG();

// 1. Xử lý XÓA sản phẩm khỏi giỏ hàng
if (isset($_GET['action'], $_GET['key']) && $_GET['action'] === 'remove') {
    $gh->xoamotmathang($_GET['key'], 'cart');
    $_SESSION['flash_message'] = 'Đã xóa sản phẩm khỏi giỏ hàng.';
    header('Location: cart.php');
    exit;
}

// 2. Xử lý CẬP NHẬT số lượng và THANH TOÁN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cập nhật số lượng
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cartKey => $quantity) {
            $gh->capnhatsoluong($cartKey, (int)$quantity, 'cart');
        }
    }
    
    // Nếu khách bấm nút "Tiến hành thanh toán"
    if (isset($_POST['checkout'])) {
        if (!empty($_POST['selected_items'])) {
            $_SESSION['checkout_items'] = $_POST['selected_items'];
            header('Location: checkout.php');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!';
            header('Location: cart.php');
            exit;
        }
    }
    
    $_SESSION['flash_message'] = 'Giỏ hàng đã được cập nhật.';
    header('Location: cart.php');
    exit;
}

// Lấy danh sách sản phẩm qua Model GIOHANG
$items = $gh->laygiohang('cart');
$total = $gh->tinhtiengiohang('cart');

$flash = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
include 'header.php';
?>
<main class="cart-page">
    <div class="container">
        <h1>Giỏ hàng của bạn</h1>
        <?php if ($flash): ?>
            <div class="alert success" style="padding: 10px; background: #e6f4ea; color: #1e7e34; margin-bottom: 15px; border-radius: 5px;"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="empty-cart" style="text-align: center; padding: 50px 0;">
                <p style="margin-bottom: 20px;">Giỏ hàng của bạn hiện chưa có sản phẩm.</p>
                <a class="btn-load-more" href="index.php" style="padding: 10px 20px; background: #000; color: #fff; text-decoration: none; border-radius: 4px;">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php" id="cart-form">
                <div class="cart-table" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                                <th style="padding: 15px; width: 40px; text-align: center;">
                                    <input type="checkbox" id="check-all" checked style="width: 18px; height: 18px; cursor: pointer;">
                                </th>
                                <th>Ảnh</th>
                                <th>Sản phẩm</th>
                                <th style="text-align: center;">Size</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; text-align: center;">
                                        <input type="checkbox" name="selected_items[]" value="<?= $item['cart_key'] ?>" class="check-item" data-price="<?= $item['total_price'] ?>" checked style="width: 18px; height: 18px; cursor: pointer;">
                                    </td>
                                    
                                    <td style="padding: 15px;"><img src="<?= htmlspecialchars($item['HinhAnh']) ?>" alt="<?= htmlspecialchars($item['TenSanPham']) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;"></td>
                                    <td style="padding: 15px;">
                                        <a href="chitiet.php?id=<?= $item['IdSanPham'] ?>" style="color: #333; text-decoration: none; font-weight: bold;"><?= htmlspecialchars($item['TenSanPham']) ?></a>
                                    </td>
                                    <td style="padding: 15px; font-weight: bold; text-align: center;"><?= htmlspecialchars($item['size']) ?></td>
                                    <td style="padding: 15px;"><?= number_format($item['final_price'], 0, ',', '.') ?> đ</td>
                                    <td style="padding: 15px;"><input type="number" name="quantities[<?= $item['cart_key'] ?>]" value="<?= $item['quantity'] ?>" min="1" style="width: 60px; padding: 5px; text-align: center; border: 1px solid #ccc; border-radius: 4px;"></td>
                                    <td style="padding: 15px; font-weight: bold; color: #d0021b;"><?= number_format($item['total_price'], 0, ',', '.') ?> đ</td>
                                    <td style="padding: 15px;"><a href="cart.php?action=remove&key=<?= $item['cart_key'] ?>" class="btn btn-delete" onclick="return confirm('Xóa sản phẩm khỏi giỏ hàng?')" style="padding: 6px 12px; background: #dc3545; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">Xóa</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="cart-summary" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; background: #fdfdfd; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                    <div class="summary-line" style="font-size: 18px;">
                        <span>Tổng tiền thanh toán (<span id="selected-count"><?= count($items) ?></span> sản phẩm): </span>
                        <strong id="total-display" style="color: #d0021b; font-size: 22px;"><?= number_format($total, 0, ',', '.') ?> đ</strong>
                    </div>
                    <div class="cart-actions" style="display: flex; gap: 10px;">
                        <button type="submit" name="update_cart" class="btn" style="padding: 10px 20px; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Cập nhật giỏ hàng</button>
                        <button type="submit" name="checkout" class="btn" style="padding: 10px 25px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Tiến hành thanh toán</button>
                    </div>
                </div>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkAll = document.getElementById('check-all');
                const itemCheckboxes = document.querySelectorAll('.check-item');
                const totalDisplay = document.getElementById('total-display');
                const selectedCount = document.getElementById('selected-count');

                function updateTotal() {
                    let sum = 0;
                    let count = 0;
                    itemCheckboxes.forEach(cb => {
                        if (cb.checked) {
                            sum += parseFloat(cb.getAttribute('data-price'));
                            count++;
                        }
                    });
                    totalDisplay.innerText = new Intl.NumberFormat('vi-VN').format(sum) + ' đ';
                    selectedCount.innerText = count;
                }

                if (checkAll) {
                    checkAll.addEventListener('change', function() {
                        itemCheckboxes.forEach(cb => {
                            cb.checked = checkAll.checked;
                        });
                        updateTotal();
                    });
                }

                itemCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        if (!this.checked && checkAll) {
                            checkAll.checked = false;
                        } else if (checkAll) {
                            const allChecked = Array.from(itemCheckboxes).every(c => c.checked);
                            checkAll.checked = allChecked;
                        }
                        updateTotal();
                    });
                });
            });
            </script>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
