<?php
$page_title = 'Hồ sơ cá nhân - Quality Admin';
require_once __DIR__ . '/../inc/top.php';

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="header-page">
    <h1>Hồ sơ tài khoản</h1>
</div>

<?php if ($flash_success): ?>
    <div style="padding: 12px 15px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px;">
        <?= htmlspecialchars($flash_success) ?>
    </div>
<?php endif; ?>

<?php if ($flash_error): ?>
    <div style="padding: 12px 15px; background: #f8d7da; color: #721c24; border-radius: 6px; margin-bottom: 20px;">
        <?= htmlspecialchars($flash_error) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; max-width: 900px;">
    <!-- CẬP NHẬT HỒ SƠ -->
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <i class="fa fa-user-edit"></i> Thông tin cá nhân
        </h3>
        
        <form method="post" action="index.php?action=xlhoso">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Tên đăng nhập</label>
                <input type="text" value="<?= htmlspecialchars($user['TenDangNhap'] ?? '') ?>" disabled style="width: 100%; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Họ và tên *</label>
                <input type="text" name="hoten" required value="<?= htmlspecialchars($user['HoTen'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Email *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($user['Email'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Số điện thoại</label>
                <input type="text" name="dienthoai" value="<?= htmlspecialchars($user['DienThoai'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn btn-add" style="padding: 10px 25px; border: none; cursor: pointer; font-size: 14px;">
                Cập nhật thông tin
            </button>
        </form>
    </div>

    <!-- ĐỔI MẬT KHẨU -->
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <i class="fa fa-key"></i> Đổi mật khẩu
        </h3>

        <form method="post" action="index.php?action=xldoimatkhau" autocomplete="off">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mật khẩu hiện tại *</label>
                <input type="password" name="matkhaucu" required autocomplete="current-password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mật khẩu mới *</label>
                <input type="password" name="matkhaumoi" required autocomplete="new-password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Xác nhận mật khẩu mới *</label>
                <input type="password" name="xacnhan" required autocomplete="new-password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn" style="padding: 10px 25px; background: #d0021b; color: #fff; border: none; cursor: pointer; font-size: 14px;">
                Đổi mật khẩu
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>

