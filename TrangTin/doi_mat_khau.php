<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/nguoidung.php';
requireLogin();

$nd = new NGUOIDUNG();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Vui lòng điền đầy đủ các trường.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
    } else {
        $userId = $_SESSION['user_id'];
        if ($nd->doimatkhau($userId, $currentPassword, $newPassword)) {
            $success = 'Đổi mật khẩu thành công.';
        } else {
            $error = 'Mật khẩu hiện tại không đúng hoặc xảy ra lỗi.';
        }
    }
}
?>
<?php include 'header.php'; ?>
<main class="auth-box">
    <h2>Đổi mật khẩu</h2>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="doi_mat_khau.php">
        <label for="current_password">Mật khẩu hiện tại</label>
        <input id="current_password" name="current_password" type="password" required autocomplete="current-password">

        <label for="new_password">Mật khẩu mới</label>
        <input id="new_password" name="new_password" type="password" required autocomplete="new-password">

        <label for="confirm_password">Xác nhận mật khẩu mới</label>
        <input id="confirm_password" name="confirm_password" type="password" required autocomplete="new-password">

        <button type="submit">Cập nhật mật khẩu</button>
    </form>
</main>
<?php include 'footer.php'; ?>
