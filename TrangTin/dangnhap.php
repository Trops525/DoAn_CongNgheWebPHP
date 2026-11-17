<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/nguoidung.php';

$nd = new NGUOIDUNG();
$error = '';
$success = '';
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Đăng ký thành công. Vui lòng đăng nhập để tiếp tục.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đủ tên đăng nhập và mật khẩu.';
    } else {
        if ($nd->kiemtranguoidunghople($username, $password)) {
            $user = $nd->laythongtinnguoidung($username);
            if ($user && $user['Khoa'] == 1) {
                $error = 'Tài khoản này đang bị khoá. Vui lòng liên hệ quản trị.';
            } elseif ($user) {
                $_SESSION['user_id'] = $user['IdNguoiDung'];
                $_SESSION['user_name'] = $user['HoTen'];
                $_SESSION['user_role'] = $user['QuyenHan'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_phone'] = $user['DienThoai'];

                $next = trim($_POST['next'] ?? $_GET['next'] ?? 'index.php');
                if ($user['QuyenHan'] == 1 || $user['QuyenHan'] == 3) {
                    if (!empty($next) && $next !== 'index.php') {
                        header('Location: ' . $next);
                    } else {
                        header('Location: ../Admin/index.php');
                    }
                } else {
                    header('Location: ' . ($next ?: 'index.php'));
                }
                exit;
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<main class="auth-box">
    <h2>Đăng nhập</h2>
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="dangnhap.php<?= isset($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>" autocomplete="off">
        <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next'] ?? $_POST['next'] ?? '') ?>">
        
        <!-- Input giả ngăn Chrome tự động điền tài khoản cũ -->
        <input type="text" style="display:none;" aria-hidden="true">
        <input type="password" style="display:none;" aria-hidden="true">

        <label for="username">Tên đăng nhập hoặc Email</label>
        <input id="username" name="username" type="text" value="<?= htmlspecialchars($_POST['username'] ?? $_GET['username'] ?? '') ?>" required autocomplete="off">

        <label for="password">Mật khẩu</label>
        <div style="position: relative; width: 100%;">
            <input id="password" name="password" type="password" required autocomplete="new-password" style="padding-right: 48px; width: 100%; box-sizing: border-box;">
            <span onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 18px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; z-index: 5;" title="Ẩn/Hiện mật khẩu">
                <i class="fa-regular fa-eye"></i>
            </span>
        </div>

        <button type="submit">Đăng nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="dangky.php<?= !empty($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>">Đăng ký ngay</a></p>
</main>

<script>
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
</script>
<?php include 'footer.php'; ?>
