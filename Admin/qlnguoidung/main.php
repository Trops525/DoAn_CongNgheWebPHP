<?php
$page_title = 'Quản lý người dùng - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page">
    <h1>Quản lý người dùng & Phân quyền</h1>
</div>

<div style="display: grid; grid-template-columns: minmax(300px, 340px) minmax(0, 1fr); gap: 25px; align-items: start;">
    <!-- FORM THÊM TÀI KHOẢN -->
    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; text-transform: uppercase;">
            <i class="fa fa-user-plus"></i> Thêm tài khoản mới
        </h3>

        <form method="post" action="?action=them" autocomplete="off">
            <!-- Ngăn trình duyệt tự động điền tài khoản đã lưu (autofill) -->
            <input type="text" style="display:none;" tabindex="-1" autocomplete="username">
            <input type="password" style="display:none;" tabindex="-1" autocomplete="current-password">

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Họ và tên *</label>
                <input type="text" name="hoten" required autocomplete="off" placeholder="Ví dụ: Nguyễn Văn A" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Tên đăng nhập *</label>
                <input type="text" name="tendangnhap" required autocomplete="off" placeholder="Ví dụ: nguyenvana" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mật khẩu *</label>
                <div style="position: relative;">
                    <input type="password" name="matkhau" id="input-matkhau" required autocomplete="new-password" placeholder="Nhập mật khẩu..." readonly onfocus="this.removeAttribute('readonly');" style="width: 100%; padding: 8px 38px 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <i class="fa fa-eye" id="toggle-matkhau" onclick="togglePasswordVisibility()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Email *</label>
                <input type="email" name="email" required autocomplete="off" placeholder="email@example.com" readonly onfocus="this.removeAttribute('readonly');" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Số điện thoại</label>
                <input type="text" name="dienthoai" autocomplete="off" placeholder="Ví dụ: 0912345678" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Phân quyền</label>
                <select name="quyenhan" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
                    <option value="2">Khách hàng</option>
                    <option value="3">Nhân viên</option>
                </select>
            </div>

            <button type="submit" class="btn btn-add" style="width: 100%; padding: 10px; border: none; cursor: pointer; font-size: 14px;">
                Tạo tài khoản
            </button>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH -->
    <div class="table-container" style="min-width: 0;">
        <table style="min-width: 650px;">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Họ tên & Tài khoản</th>
                    <th>Email & SĐT</th>
                    <th style="width: 140px;">Quyền hạn</th>
                    <th style="width: 110px; text-align: center;">Trạng thái</th>
                    <th style="width: 130px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): 
                        $isAdmin = ($u['QuyenHan'] == 1);
                    ?>
                        <tr>
                            <td>#<?= $u['IdNguoiDung'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($u['HoTen']) ?></strong><br>
                                <small style="color: #666;">@<?= htmlspecialchars($u['TenDangNhap']) ?></small>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($u['Email']) ?></div>
                                <small style="color: #666;"><?= htmlspecialchars($u['DienThoai'] ?: 'Chưa cập nhật') ?></small>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <span style="background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Quản trị viên</span>
                                <?php else: ?>
                                    <form method="post" action="?action=quyen" style="margin: 0;">
                                        <input type="hidden" name="id" value="<?= $u['IdNguoiDung'] ?>">
                                        <select name="quyenhan" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #ddd;">
                                            <option value="2" <?= ($u['QuyenHan'] == 2) ? 'selected' : '' ?>>Khách hàng</option>
                                            <option value="3" <?= ($u['QuyenHan'] == 3) ? 'selected' : '' ?>>Nhân viên</option>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($u['Khoa'] == 1): ?>
                                    <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold;">Bị khóa</span>
                                <?php else: ?>
                                    <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: bold;">Hoạt động</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php if (!$isAdmin): ?>
                                    <a href="?action=khoa&id=<?= $u['IdNguoiDung'] ?>" class="btn" style="background: <?= ($u['Khoa'] == 1) ? '#28a745' : '#ffc107' ?>; color: #000; padding: 5px 8px;">
                                        <?= ($u['Khoa'] == 1) ? '<i class="fa fa-unlock"></i> Mở' : '<i class="fa fa-lock"></i> Khóa' ?>
                                    </a>
                                    <a href="?action=xoa&id=<?= $u['IdNguoiDung'] ?>" class="btn btn-delete" style="padding: 5px 8px;" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <small style="color: #999;">Mặc định</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    var pw = document.getElementById('input-matkhau');
    var icon = document.getElementById('toggle-matkhau');
    if (!pw) return;
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Xóa triệt để nếu trình duyệt cố tình autofill sau khi render
window.addEventListener('load', function() {
    setTimeout(function() {
        var pw = document.getElementById('input-matkhau');
        var email = document.querySelector('input[name="email"]');
        if (pw && pw.hasAttribute('readonly') && pw.value) {
            pw.value = '';
        }
        if (email && email.hasAttribute('readonly') && email.value === 'root') {
            email.value = '';
        }
    }, 100);
});
</script>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>

