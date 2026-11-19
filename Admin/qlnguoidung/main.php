<?php
$page_title = 'Quản lý người dùng - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page">
    <h1>Quản lý người dùng & Phân quyền</h1>
</div>

<!-- FORM THÊM TÀI KHOẢN NẰM NGANG Ở TRÊN -->
<div style="background: #fff; padding: 20px 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #e9ecef;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 15px; text-transform: uppercase; color: #2c3e50; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="fa fa-user-plus text-success"></i> Thêm tài khoản mới
    </h3>

    <form method="post" action="?action=them" autocomplete="off">
        <!-- Ngăn trình duyệt tự động điền tài khoản đã lưu (autofill) -->
        <input type="text" style="display:none;" tabindex="-1" autocomplete="username">
        <input type="password" style="display:none;" tabindex="-1" autocomplete="current-password">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; align-items: flex-end;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Họ và tên *</label>
                <input type="text" name="hoten" required autocomplete="off" placeholder="Ví dụ: Nguyễn Văn A" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Tên đăng nhập *</label>
                <input type="text" name="tendangnhap" required autocomplete="off" placeholder="Ví dụ: nguyenvana" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Mật khẩu *</label>
                <div style="position: relative;">
                    <input type="password" name="matkhau" id="input-matkhau" required autocomplete="new-password" placeholder="Nhập mật khẩu..." readonly onfocus="this.removeAttribute('readonly');" style="width: 100%; padding: 9px 38px 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
                    <i class="fa fa-eye" id="toggle-matkhau" onclick="togglePasswordVisibility()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                </div>
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Email *</label>
                <input type="email" name="email" required autocomplete="off" placeholder="email@example.com" readonly onfocus="this.removeAttribute('readonly');" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Số điện thoại</label>
                <input type="text" name="dienthoai" autocomplete="off" placeholder="Ví dụ: 0912345678" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #495057;">Phân quyền</label>
                <select name="quyenhan" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
                    <option value="2">Khách hàng</option>
                    <option value="3">NV Kho & Hàng hóa</option>
                    <option value="4">NV Bán hàng & CSKH</option>
                    <option value="5">Kế toán / Tài chính</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-add" style="width: 100%; padding: 10px 14px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                    <i class="fa fa-user-plus"></i> Tạo tài khoản
                </button>
            </div>
        </div>
    </form>
</div>

<!-- BẢNG DANH SÁCH NGƯỜI DÙNG NẰM DƯỚI TOÀN MÀN HÌNH -->
<div class="table-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f1f3f5;">
        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #2c3e50; text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-users text-secondary"></i> Danh sách tài khoản người dùng (<?= count($users) ?> tài khoản)
        </h3>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="width: 70px; text-align: center;">ID</th>
                <th style="width: 250px;">Họ tên & Tài khoản</th>
                <th>Email & SĐT</th>
                <th style="width: 160px;">Quyền hạn</th>
                <th style="width: 130px; text-align: center;">Trạng thái</th>
                <th style="width: 150px; text-align: center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): 
                    $isAdmin = ($u['QuyenHan'] == 1);
                    $isLocked = ($u['Khoa'] == 1);
                ?>
                    <tr style="<?= $isLocked ? 'background: #fff5f5; opacity: 0.88;' : '' ?>">
                        <td style="text-align: center; font-weight: bold; color: #6c757d;">#<?= $u['IdNguoiDung'] ?></td>
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
                                    <select name="quyenhan" data-current="<?= $u['QuyenHan'] ?>" onchange="confirmChangeRole(this, '<?= htmlspecialchars($u['TenDangNhap'], ENT_QUOTES) ?>')" style="padding: 5px 8px; border-radius: 6px; font-size: 12.5px; border: 1px solid #ced4da; cursor: pointer;">
                                        <option value="2" <?= ($u['QuyenHan'] == 2) ? 'selected' : '' ?>>Khách hàng</option>
                                        <option value="3" <?= ($u['QuyenHan'] == 3) ? 'selected' : '' ?>>NV Kho & Hàng hóa</option>
                                        <option value="4" <?= ($u['QuyenHan'] == 4) ? 'selected' : '' ?>>NV Bán hàng & CSKH</option>
                                        <option value="5" <?= ($u['QuyenHan'] == 5) ? 'selected' : '' ?>>Kế toán / Tài chính</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($isLocked): ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-lock"></i> Bị khóa
                                </span>
                            <?php else: ?>
                                <span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-check-circle"></i> Hoạt động
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <?php if (!$isAdmin): ?>
                                <a href="?action=khoa&id=<?= $u['IdNguoiDung'] ?>" class="btn" style="background: <?= $isLocked ? '#28a745' : '#ffc107' ?>; color: <?= $isLocked ? '#fff' : '#000' ?>; padding: 6px 10px;" onclick="return confirm('<?= $isLocked ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?' ?>');" title="<?= $isLocked ? 'Mở khóa' : 'Khóa' ?>">
                                    <?= $isLocked ? '<i class="fa fa-unlock"></i> Mở' : '<i class="fa fa-lock"></i> Khóa' ?>
                                </a>
                                <a href="?action=xoa&id=<?= $u['IdNguoiDung'] ?>" class="btn btn-delete" style="padding: 6px 10px;" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');" title="Xóa tài khoản">
                                    <i class="fa fa-trash"></i> Xóa
                                </a>
                            <?php else: ?>
                                <small style="color: #999; font-style: italic;">Mặc định</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; color: #888; padding: 40px;">Chưa có tài khoản người dùng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmChangeRole(selectEl, username) {
    var roleName = selectEl.options[selectEl.selectedIndex].text;
    var oldVal = selectEl.getAttribute('data-current');
    if (confirm('Bạn có chắc chắn muốn đổi quyền hạn của tài khoản @' + username + ' sang "' + roleName + '" không?')) {
        selectEl.form.submit();
    } else {
        selectEl.value = oldVal; // Khôi phục lại lựa chọn ban đầu nếu người dùng bấm Hủy
    }
}

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

