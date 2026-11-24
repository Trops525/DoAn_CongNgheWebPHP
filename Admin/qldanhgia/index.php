<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../model/cskh.php';

// Kiểm tra quyền Admin (1) hoặc NV Bán hàng & CSKH (4)
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 4])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$dg = new DANHGIA();
$currentScript = basename($_SERVER['PHP_SELF']);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Xử lý đổi trạng thái Ẩn / Hiện của 1 đánh giá
if (($action === 'khoa' || $action === 'an') && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $dg->doitrangthai($id);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? $currentScript;
    header('Location: ' . $referer);
    exit;
}

// Xử lý xóa 1 đánh giá
if ($action === 'xoa' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $dg->xoadanhgia($id);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? $currentScript;
    header('Location: ' . $referer);
    exit;
}

// Xử lý thao tác hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
        if (isset($_POST['delete_selected'])) {
            $dg->xoanhieudanhgia($_POST['selected_ids']);
        } elseif (isset($_POST['hide_selected'])) {
            $dg->an_nhieudanhgia($_POST['selected_ids'], 1);
        } elseif (isset($_POST['unhide_selected'])) {
            $dg->an_nhieudanhgia($_POST['selected_ids'], 0);
        }
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? $currentScript;
    header('Location: ' . $referer);
    exit;
}

// Bộ lọc
$so_sao = isset($_GET['sao']) ? (int)$_GET['sao'] : 0;
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'all';

$thongKe = $dg->thongkedanhgia();
$reviews = $dg->laytatcadanhgia($so_sao, $search, $status);

include __DIR__ . '/main.php';
