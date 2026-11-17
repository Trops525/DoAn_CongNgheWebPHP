<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/cskh.php';

$cskh = new CSKH();
$chatUserId = $_SESSION['user_id'] ?? 0;
$chatNotice = '';
$chatOpen = isset($_GET['openChat']) && $_GET['openChat'] == 1;

// XỬ LÝ GỬI TIN NHẮN TRƯỚC KHI XUẤT HTML
if ($chatUserId && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chat_message'])) {
    $message = trim($_POST['chat_message']);
    if ($message !== '') {
        $cskh->guitinnhan($chatUserId, 1, $message);
        header('Location: cskh.php?openChat=1');
        exit;
    }
    $chatNotice = 'Vui lòng nhập nội dung câu hỏi.';
}

$chatMessages = $chatUserId ? $cskh->laytinnhanchat($chatUserId) : [];

// Xác định danh mục FAQ được chọn
$danhMucDuocChon = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : 'TatCa';
$faqList = $cskh->layfaq($danhMucDuocChon);

// Giờ mới xuất HTML thông qua header.php
require_once __DIR__ . '/header.php'; 
?>

<section class="cskh-faq-section" id="thu-vien-hoi-dap">
    <div class="container cskh-faq-layout">
        <div class="faq-sidebar">
            <h3>THƯ VIỆN VÀ HỎI ĐÁP</h3>
            <ul class="faq-menu">
                <li>
                    <a href="cskh.php?danhmuc=TatCa#thu-vien-hoi-dap" class="<?= ($danhMucDuocChon == 'TatCa') ? 'active' : '' ?>">
                        <i class="fas fa-star"></i> Có thể bạn quan tâm <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="cskh.php?danhmuc=MuaHang#thu-vien-hoi-dap" class="<?= ($danhMucDuocChon == 'MuaHang') ? 'active' : '' ?>">
                        <i class="fas fa-shopping-bag"></i> Mua hàng <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="cskh.php?danhmuc=DonHang#thu-vien-hoi-dap" class="<?= ($danhMucDuocChon == 'DonHang') ? 'active' : '' ?>">
                        <i class="fas fa-box"></i> Thông tin đơn hàng <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="cskh.php?danhmuc=DoiTra#thu-vien-hoi-dap" class="<?= ($danhMucDuocChon == 'DoiTra') ? 'active' : '' ?>">
                        <i class="fas fa-exchange-alt"></i> Đổi trả hàng <i class="fas fa-arrow-right arrow"></i>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="faq-content">
            <div class="faq-grid">
                <?php if (!empty($faqList)): ?>
                    <?php foreach ($faqList as $row): ?>
                        <div class="faq-card">
                            <h4><?= htmlspecialchars($row['CauHoi']) ?></h4>
                            <p><?= nl2br(htmlspecialchars($row['TraLoi'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; grid-column: 1 / -1; color: #888;">Hiện chưa có câu hỏi nào trong danh mục này.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- HỘP THOẠI CHAT HỖ TRỢ KHÁCH HÀNG -->
<div class="chatbox-drawer<?= $chatOpen ? ' open' : '' ?>" id="chatbox-drawer">
    <div class="chatbox-drawer-header">
        <span><i class="fa fa-comments" style="margin-right: 8px;"></i>Chat hỗ trợ khách hàng</span>
        <button type="button" class="chatbox-close" id="chatbox-close">&times;</button>
    </div>
    <div class="chatbox-messages" id="chatbox-messages-list">
        <?php if ($chatUserId): ?>
            <?php if (!empty($chatMessages)): ?>
                <?php foreach ($chatMessages as $msg): ?>
                    <div class="chat-message <?= $msg['NguoiGui'] == 1 ? 'customer' : 'admin' ?>">
                        <div><?= nl2br(htmlspecialchars($msg['NoiDung'])) ?></div>
                        <div style="margin-top: 6px; font-size: 11px; color: <?= $msg['NguoiGui'] == 1 ? '#6c757d' : 'rgba(255,255,255,0.75)' ?>; text-align: <?= $msg['NguoiGui'] == 1 ? 'right' : 'left' ?>;">
                            <?= ($msg['NguoiGui'] == 2 ? 'Shop • ' : '') . date('d/m H:i', strtotime($msg['ThoiGian'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="chatbox-empty">Chưa có tin nhắn nào. Hãy gửi câu hỏi để bắt đầu trò chuyện cùng Shop!</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="chatbox-empty">
                Bạn cần <a href="dangnhap.php?next=<?= urlencode('cskh.php?openChat=1') ?>" style="color: #0d6efd; font-weight: bold;">đăng nhập</a> để bắt đầu chat với chúng tôi.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($chatUserId): ?>
        <?php if ($chatNotice): ?>
            <div class="chatbox-error" style="color: #dc3545; padding: 0 20px 10px; font-size: 13px;"><?= htmlspecialchars($chatNotice) ?></div>
        <?php endif; ?>
        <form method="POST" action="cskh.php?openChat=1" class="chatbox-form drawer" id="chat-customer-form">
            <textarea name="chat_message" id="customer-chat-input" placeholder="Nhập câu hỏi của bạn... (Enter để gửi, Shift+Enter xuống dòng)" required rows="2" style="resize: none;"></textarea>
            <button type="submit" style="width: 100%; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fa fa-paper-plane" style="margin-right: 6px;"></i>Gửi tin nhắn
            </button>
        </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var msgList = document.getElementById('chatbox-messages-list');
    if (msgList) {
        msgList.scrollTop = msgList.scrollHeight;
    }

    var chatInput = document.getElementById('customer-chat-input');
    var chatForm = document.getElementById('chat-customer-form');
    if (chatInput && chatForm) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (chatInput.value.trim() !== '') {
                    chatForm.submit();
                }
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
