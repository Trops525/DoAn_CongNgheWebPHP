<?php
require_once __DIR__ . '/common.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality - Fashion Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Quality Fashion Custom Styles -->
    <link rel="stylesheet" href="assets/style.css?v=<?= @filemtime(__DIR__ . '/assets/style.css') ?: time() ?>">
    <script defer src="assets/script.js?v=<?= @filemtime(__DIR__ . '/assets/script.js') ?: time() ?>"></script>
    <style>
        /* Toast Notification guaranteed styles */
        .toast-container {
            position: fixed !important;
            top: 25px !important;
            right: 25px !important;
            z-index: 9999999 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            pointer-events: none !important;
            max-width: calc(100vw - 40px) !important;
        }
        .toast-popup {
            pointer-events: auto !important;
            width: 380px !important;
            max-width: 100% !important;
            background: #ffffff !important;
            border-radius: 14px !important;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.16), 0 2px 10px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid #eef2f6 !important;
            border-left: 5px solid #10b981 !important;
            padding: 16px 18px !important;
            display: flex !important;
            align-items: flex-start !important;
            gap: 14px !important;
            position: relative !important;
            overflow: hidden !important;
            animation: slideInToast 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
            transition: transform 0.3s ease, opacity 0.3s ease !important;
            box-sizing: border-box !important;
            font-family: inherit !important;
        }
        .toast-popup.toast-fade-out {
            transform: translateX(110%) !important;
            opacity: 0 !important;
        }
        .toast-popup.toast-error {
            border-left-color: #ef4444 !important;
        }
        .toast-popup.toast-warning {
            border-left-color: #f59e0b !important;
        }
        .toast-icon-wrap {
            width: 38px !important;
            height: 38px !important;
            border-radius: 50% !important;
            background: #ecfdf5 !important;
            color: #10b981 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 19px !important;
            flex-shrink: 0 !important;
        }
        .toast-error .toast-icon-wrap {
            background: #fef2f2 !important;
            color: #ef4444 !important;
        }
        .toast-warning .toast-icon-wrap {
            background: #fffbeb !important;
            color: #f59e0b !important;
        }
        .toast-body {
            flex: 1 !important;
            padding-right: 15px !important;
        }
        .toast-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            margin: 0 0 3px 0 !important;
            line-height: 1.3 !important;
        }
        .toast-msg {
            font-size: 13.5px !important;
            color: #4b5563 !important;
            line-height: 1.45 !important;
            margin: 0 0 10px 0 !important;
        }
        .toast-btn-cart {
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            background: #111827 !important;
            color: #ffffff !important;
            padding: 7px 16px !important;
            border-radius: 30px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: background 0.2s, transform 0.15s !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
        }
        .toast-btn-cart:hover {
            background: #374151 !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }
        .toast-close-btn {
            position: absolute !important;
            top: 12px !important;
            right: 12px !important;
            background: transparent !important;
            border: none !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            color: #9ca3af !important;
            font-size: 18px !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            padding: 0 !important;
            transition: background 0.15s, color 0.15s !important;
        }
        .toast-close-btn:hover {
            background: #f3f4f6 !important;
            color: #1f2937 !important;
        }
        .toast-progress-bar {
            position: absolute !important;
            bottom: 0 !important;
            left: 0 !important;
            height: 3px !important;
            background: #10b981 !important;
            width: 100% !important;
            animation: toastProgress 3.8s linear forwards !important;
        }
        .toast-error .toast-progress-bar {
            background: #ef4444 !important;
        }
        .toast-warning .toast-progress-bar {
            background: #f59e0b !important;
        }
        @keyframes slideInToast {
            from {
                transform: translateX(110%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>
<header class="site-header">
   <div class="top-bar" style="justify-content: flex-end;"> 
        <div class="top-actions">
            <?php if (isLoggedIn()): ?>
                <span>Xin chào, <?= htmlspecialchars(getCurrentUser()) ?></span>
                <a href="doi_mat_khau.php">Đổi mật khẩu</a>
                <a href="dangxuat.php">Đăng xuất</a>
            <?php else: ?>
                <?php 
                    $loginNext = '';
                    if ($current_page === 'chitiet.php' && !empty($_GET['id'])) {
                        $targetUrl = 'chitiet.php?id=' . (int)$_GET['id'];
                        if (!empty($_GET['size'])) {
                            $targetUrl .= '&size=' . urlencode($_GET['size']);
                        }
                        $loginNext = '?next=' . urlencode($targetUrl);
                    }
                ?>
                <a href="dangnhap.php<?= $loginNext ?>">Đăng nhập</a>
                <a href="dangky.php<?= $loginNext ?>">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="main-header">
        <div class="logo">
            <a href="index.php"><img src="images/Quality_Logo.jpg" alt="QUALITY"></a>
        </div>
        <nav class="main-nav">
            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active-link' : '' ?>">Trang chủ</a>
            <a href="sanpham.php" class="<?= ($current_page == 'sanpham.php') ? 'active-link' : '' ?>">Sản phẩm</a>
            <a href="cskh.php" class="<?= ($current_page == 'cskh.php') ? 'active-link' : '' ?>">CSKH</a>
            <?php if (!empty($_SESSION['user_role']) && in_array($_SESSION['user_role'], [1, 3, 4, 5])): ?>
                <a href="../Admin/index.php" class="admin-btn">Quản lý</a>
            <?php endif; ?>
        </nav>
        <div class="header-right">
            <form class="search-bar" method="get" action="index.php">
                <input name="q" type="text" placeholder="Tìm kiếm sản phẩm..." aria-label="Tìm kiếm sản phẩm" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
            <div class="icons">
                <?php if (isLoggedIn()): ?>
                    <a href="cart.php" class="icon-badge" title="Giỏ hàng">
                        <i class="fa fa-shopping-cart"></i>
                        <?php $cartCount = getCartCount(); if ($cartCount > 0): ?>
                            <span><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="donhang.php" class="icon-badge" title="Đơn hàng">
                        <i class="fa fa-box"></i>
                        <?php $orderCount = getUserOrderCount(); if ($orderCount > 0): ?>
                            <span><?= $orderCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="dangnhap.php<?= $loginNext ?>" title="Tài khoản"><i class="fa fa-user"></i></a>
                    <a href="dangnhap.php<?= $loginNext ?>" title="Giỏ hàng"><i class="fa fa-shopping-cart"></i></a>
                <?php endif; ?>
                <a href="cskh.php?openChat=1" class="icon-badge" id="chat-toggle" title="Chat hỗ trợ">
                    <i class="fa fa-comments"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="black-divider">
        <div class="marquee-wrapper">
        <span class="marquee-text">Chào mừng đến với Quality Fashion</span>
        </div>
    </div>
</header>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toast-container">
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="toast-popup" id="session-toast">
            <div class="toast-icon-wrap"><i class="fa-solid fa-circle-check"></i></div>
            <div class="toast-body">
                <div class="toast-title">Thành công!</div>
                <div class="toast-msg"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
                <a href="cart.php" class="toast-btn-cart">Xem giỏ hàng <i class="fa fa-arrow-right"></i></a>
            </div>
            <button type="button" class="toast-close-btn" onclick="const p = this.closest('.toast-popup'); p.classList.add('toast-fade-out'); setTimeout(() => p.remove(), 300);">&times;</button>
            <div class="toast-progress-bar"></div>
        </div>
        <script>
            setTimeout(() => {
                const t = document.getElementById('session-toast');
                if (t) {
                    t.classList.add('toast-fade-out');
                    setTimeout(() => t.remove(), 320);
                }
            }, 3800);
        </script>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
</div>