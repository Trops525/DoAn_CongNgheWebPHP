<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/cskh.php';
require_once __DIR__ . '/../../model/nguoidung.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 4])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$cskh = new CSKH();
$nd = new NGUOIDUNG();

// Xác định file hiện tại (QuanLyChat.php hoặc index.php)
$currentScript = basename($_SERVER['PHP_SELF']);

$selectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Gửi phản hồi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'], $_POST['user_id'])) {
    $replyText = trim($_POST['reply_message']);
    $userId = (int)$_POST['user_id'];
    if ($replyText !== '' && $userId > 0) {
        $cskh->guitinnhan($userId, 2, $replyText);
    }
    header('Location: ' . $currentScript . '?user=' . $userId);
    exit;
}

// Xóa tin nhắn đã chọn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_users']) && is_array($_POST['selected_users'])) {
        $cskh->xoahoithoai($_POST['selected_users']);
    }
    header('Location: ' . $currentScript);
    exit;
}

$userList = $cskh->danhsachkhachchat();

// Nếu chưa chọn khách hàng cụ thể thì tự động mở cuộc trò chuyện mới nhất
if ($selectedUserId <= 0 && !empty($userList)) {
    $selectedUserId = (int)$userList[0]['IdNguoiDung'];
}

$selectedUser = null;
$chatMessages = [];
if ($selectedUserId > 0) {
    $selectedUser = $nd->laynguoidungtheoid($selectedUserId);
    if ($selectedUser) {
        $chatMessages = $cskh->laytinnhanchat($selectedUserId);
    }
}

include 'main.php';
?>
