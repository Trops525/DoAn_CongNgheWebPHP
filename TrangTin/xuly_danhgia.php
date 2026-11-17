<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/cskh.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Vui lòng đăng nhập!");
    }

    $id_nguoidung = (int)$_SESSION['user_id'];
    $id_sanpham = (int)($_POST['id_sanpham'] ?? 0);
    $so_sao = (int)($_POST['so_sao'] ?? 5);
    $noi_dung = trim($_POST['noi_dung'] ?? '');

    $dg = new DANHGIA();
    if ($dg->themdanhgia($id_sanpham, $id_nguoidung, $so_sao, $noi_dung)) {
        header("Location: chitiet.php?id=" . $id_sanpham);
        exit;
    } else {
        echo "Lỗi khi lưu đánh giá!";
    }
} else {
    header("Location: index.php");
    exit;
}
?>
