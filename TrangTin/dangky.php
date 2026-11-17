<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/nguoidung.php';

$nd = new NGUOIDUNG();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $repeatPassword = trim($_POST['repeat_password'] ?? '');

    // 1. Ràng buộc Họ và tên
    if ($fullname === '') {
        $errors['fullname'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($fullname, 'UTF-8') < 2 || mb_strlen($fullname, 'UTF-8') > 50 || !preg_match('/^[\p{L}\s]+$/u', $fullname)) {
        $errors['fullname'] = 'Họ và tên không hợp lệ (tối thiểu 2 ký tự, chỉ gồm chữ cái).';
    }

    // 2. Ràng buộc Tên đăng nhập
    if ($username === '') {
        $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $username)) {
        $errors['username'] = 'Tên đăng nhập từ 4 - 30 ký tự, viết liền không dấu (chỉ dùng chữ cái, số, gạch dưới _).';
    }

    // 3. Ràng buộc Email
    if ($email === '') {
        $errors['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ Email không đúng định dạng (Ví dụ: name@gmail.com).';
    }

    // 4. Ràng buộc Số điện thoại
    if ($phone === '') {
        $errors['phone'] = 'Vui lòng nhập số điện thoại.';
    } elseif (!preg_match('/^(03|05|07|08|09)\d{8}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ (Phải đúng 10 số, đầu số: 03, 05, 07, 08, 09).';
    }

    // 5. Ràng buộc Mật khẩu
    if ($password === '') {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có tối thiểu 6 ký tự.';
    }

    // 6. Ràng buộc Xác nhận mật khẩu
    if ($repeatPassword === '') {
        $errors['repeat_password'] = 'Vui lòng xác nhận lại mật khẩu.';
    } elseif ($password !== $repeatPassword) {
        $errors['repeat_password'] = 'Mật khẩu xác nhận không khớp.';
    }

    // 7. Kiểm tra trùng lặp trong Database nếu các trường đều hợp lệ
    if (empty($errors)) {
        $trung = $nd->kiemtratrung($username, $email, $phone);
        if ($trung === 'username') {
            $errors['username'] = "Tên đăng nhập '{$username}' đã được sử dụng. Vui lòng chọn tên khác.";
        } elseif ($trung === 'email') {
            $errors['email'] = "Email '{$email}' đã được đăng ký tài khoản.";
        } elseif ($trung === 'phone') {
            $errors['phone'] = "Số điện thoại '{$phone}' đã được liên kết với tài khoản khác.";
        } else {
            $data = [
                'HoTen' => $fullname,
                'TenDangNhap' => $username,
                'Email' => $email,
                'DienThoai' => $phone,
                'MatKhau' => $password,
                'QuyenHan' => 2
            ];
            $id = $nd->themnguoidung($data);
            if ($id) {
                $nextParam = !empty($_REQUEST['next']) ? '&next=' . urlencode($_REQUEST['next']) : '';
                header('Location: dangnhap.php?registered=1&username=' . urlencode($username) . $nextParam);
                exit;
            } else {
                $errors['general'] = 'Đã xảy ra lỗi trong quá trình tạo tài khoản. Vui lòng thử lại sau.';
            }
        }
    }
}
?>
<?php include 'header.php'; ?>

<style>
    .input-error {
        border-color: #dc2626 !important;
        background-color: #fef2f2 !important;
        box-shadow: 0 0 0 1px #dc2626 !important;
    }
    .field-error {
        color: #dc2626;
        font-size: 12.5px;
        margin-top: 5px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
        line-height: 1.4;
    }
    .auth-box .form-group {
        display: flex;
        flex-direction: column;
    }
    .auth-box .form-group label {
        margin-bottom: 6px;
    }
    .password-box {
        position: relative !important;
        width: 100% !important;
    }
    .password-box input {
        width: 100% !important;
        padding-right: 48px !important;
        box-sizing: border-box !important;
    }
    .btn-toggle-eye {
        position: absolute !important;
        right: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: transparent !important;
        border: none !important;
        width: 28px !important;
        height: 28px !important;
        padding: 0 !important;
        margin: 0 !important;
        color: #888888 !important;
        font-size: 18px !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 5 !important;
        line-height: 1 !important;
    }
    .btn-toggle-eye:hover {
        color: #111111 !important;
    }
</style>

<main class="auth-box">
    <h2>Đăng ký tài khoản</h2>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="alert error" style="margin-bottom: 20px;"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="post" action="dangky.php<?= !empty($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>" autocomplete="off" id="register-form" novalidate>
        <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next'] ?? $_POST['next'] ?? '') ?>">
        
        <!-- Input giả ngăn trình duyệt tự động điền thông tin cũ -->
        <input type="text" style="display:none;" aria-hidden="true">
        <input type="password" style="display:none;" aria-hidden="true">

        <!-- HỌ VÀ TÊN -->
        <div class="form-group">
            <label for="fullname">Họ và tên <span style="color:red">*</span></label>
            <input id="fullname" name="fullname" type="text" placeholder="Ví dụ: Nguyễn Văn An" value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" autocomplete="off" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['fullname'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['fullname']) ?></div>
            <?php endif; ?>
        </div>

        <!-- TÊN ĐĂNG NHẬP -->
        <div class="form-group">
            <label for="username">Tên đăng nhập <span style="color:red">*</span></label>
            <input id="username" name="username" type="text" placeholder="Ví dụ: nguyenvana12" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="off" class="<?= isset($errors['username']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['username'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['username']) ?></div>
            <?php endif; ?>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
            <label for="email">Địa chỉ Email <span style="color:red">*</span></label>
            <input id="email" name="email" type="email" placeholder="Ví dụ: email@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="off" class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <!-- SỐ ĐIỆN THOẠI -->
        <div class="form-group">
            <label for="phone">Số điện thoại <span style="color:red">*</span></label>
            <input id="phone" name="phone" type="tel" placeholder="Ví dụ: 0912345678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" maxlength="10" autocomplete="off" class="<?= isset($errors['phone']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['phone'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['phone']) ?></div>
            <?php endif; ?>
        </div>

        <!-- MẬT KHẨU -->
        <div class="form-group">
            <label for="password">Mật khẩu <span style="color:red">*</span></label>
            <div class="password-box">
                <input id="password" name="password" type="password" placeholder="Nhập mật khẩu" autocomplete="new-password" class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
                <span class="btn-toggle-eye" onclick="togglePasswordVisibility('password', this)" title="Ẩn/Hiện mật khẩu">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            <?php if (isset($errors['password'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <!-- XÁC NHẬN MẬT KHẨU -->
        <div class="form-group">
            <label for="repeat_password">Xác nhận mật khẩu <span style="color:red">*</span></label>
            <div class="password-box">
                <input id="repeat_password" name="repeat_password" type="password" placeholder="Nhập lại mật khẩu" autocomplete="new-password" class="<?= isset($errors['repeat_password']) ? 'input-error' : '' ?>">
                <span class="btn-toggle-eye" onclick="togglePasswordVisibility('repeat_password', this)" title="Ẩn/Hiện mật khẩu">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            <?php if (isset($errors['repeat_password'])): ?>
                <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errors['repeat_password']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" style="margin-top: 10px; cursor: pointer;">Đăng ký tài khoản</button>
    </form>
    
    <p class="auth-footer" style="text-align: center; margin-top: 20px; font-size: 14px; color: #555;">
        Đã có tài khoản? <a href="dangnhap.php<?= !empty($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>" style="color: #000; font-weight: 700;">Đăng nhập ngay</a>
    </p>
</main>

<script>
// Nút Ẩn/Hiện mật khẩu
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}

// Tự động focus vào ô bị lỗi đầu tiên sau khi load lại form
document.addEventListener('DOMContentLoaded', function() {
    const firstError = document.querySelector('.input-error');
    if (firstError) {
        firstError.focus();
    }
});
</script>

<?php include 'footer.php'; ?>
