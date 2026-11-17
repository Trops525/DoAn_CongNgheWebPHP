<?php
require_once __DIR__ . '/common.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: donhang.php');
    exit;
}

$sqlOrder = "SELECT * FROM tbl_donhang WHERE IdDonHang = $orderId AND IdNguoiDung = $userId LIMIT 1";
$resOrder = $ket_noi->query($sqlOrder);

if (!$resOrder || $resOrder->num_rows === 0) {
    header('Location: donhang.php');
    exit;
}

$order = $resOrder->fetch_assoc();

if ($order['TinhTrang'] !== 'Đang xử lý') {
    $_SESSION['flash_message'] = 'Chỉ có thể cập nhật thông tin đơn hàng ở trạng thái "Đang xử lý".';
    header('Location: donhang.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diachi = trim($_POST['diachi'] ?? '');
    $dienthoai = trim($_POST['dienthoai'] ?? '');
    $ghichu = trim($_POST['ghichu'] ?? '');

    if ($diachi === '' || $dienthoai === '') {
        $error = 'Vui lòng điền đầy đủ địa chỉ và số điện thoại.';
    } else if (!preg_match('/^\d{10}$/', $dienthoai)) {
        $error = 'Số điện thoại phải là 10 chữ số.';
    } else {
        $safeAddress = $ket_noi->real_escape_string($diachi);
        $safePhone = $ket_noi->real_escape_string($dienthoai);
        $safeNote = $ket_noi->real_escape_string($ghichu);

        $updateSql = "UPDATE tbl_donhang SET DiaChi = '$safeAddress', DienThoai = '$safePhone', GhiChu = '$safeNote' WHERE IdDonHang = $orderId";
        
        if ($ket_noi->query($updateSql)) {
            $_SESSION['flash_message'] = 'Cập nhật thông tin đơn hàng thành công!';
            header('Location: donhang.php');
            exit;
        } else {
            $error = 'Không thể cập nhật. Vui lòng thử lại sau.';
        }
    }
}

include 'header.php';
?>

<main style="max-width: 600px; margin: 40px auto; padding: 0 15px; font-family: sans-serif;">
    <h1 style="text-align: center; margin-bottom: 30px;">Cập nhật thông tin đơn hàng</h1>

    <div style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: #cce5ff; border-left: 4px solid #0d6efd; padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; color: #004085;">
            <strong>Đơn hàng #<?= $orderId ?></strong> - Trạng thái: <span style="color: #0d6efd; font-weight: bold;">Đang xử lý</span>
        </div>

        <?php if ($error): ?>
            <div style="background: #fdecea; color: #d0021b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="cap_nhat_donhang.php?id=<?= $orderId ?>">
            <label style="display: block; font-weight: 700; margin-bottom: 8px;">Họ và tên</label>
            <input type="text" value="<?= htmlspecialchars($order['IdNguoiDung']) ?>" disabled style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f5f5f5; margin-bottom: 20px; box-sizing: border-box;">

            <label style="display: block; font-weight: 700; margin-bottom: 8px;">Địa chỉ nhận hàng <span style="color: red;">*</span></label>
            <input type="text" name="diachi" value="<?= htmlspecialchars($order['DiaChi']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box;" placeholder="Ví dụ: Số 10, Đường ABC, Quận XYZ, TP.HCM">

            <label style="display: block; font-weight: 700; margin-bottom: 8px;">Số điện thoại <span style="color: red;">*</span></label>
            <input type="text" name="dienthoai" value="<?= htmlspecialchars($order['DienThoai']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box;" placeholder="Nhập 10 chữ số" pattern="^[0-9]{10}$" title="Số điện thoại phải là 10 chữ số">

            <label style="display: block; font-weight: 700; margin-bottom: 8px;">Ghi chú (Tùy chọn)</label>
            <textarea name="ghichu" rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; resize: vertical;" placeholder="Ghi chú về thời gian giao hàng hoặc chỉ dẫn thêm..."><?= htmlspecialchars($order['GhiChu']) ?></textarea>

            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="submit" style="padding: 12px 32px; background: #000; color: #fff; border: none; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s;">Lưu thay đổi</button>
                <a href="donhang.php" style="padding: 12px 32px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 8px; font-weight: bold; font-size: 15px; text-decoration: none; display: inline-block; transition: 0.2s;">Hủy</a>
            </div>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
