<?php
$page_title = 'Quản lý Chat - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page">
    <h1>Quản lý Chat hỗ trợ khách hàng</h1>
</div>

<form method="POST" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <button type="submit" form="delete-form" name="delete_selected" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa các cuộc trò chuyện đã chọn?')">
        <i class="fa fa-trash"></i> Xóa hội thoại đã chọn
    </button>
</form>

<div style="display: grid; grid-template-columns: minmax(280px, 320px) minmax(0, 1fr); gap: 20px; align-items: start;">
    <!-- DANH SÁCH KHÁCH HÀNG -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
        <h3 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <i class="fa fa-users"></i> Khách hàng cần hỗ trợ
        </h3>
        <form method="POST" id="delete-form">
            <div style="margin-top: 15px;">
                <?php if (!empty($userList)): ?>
                    <?php foreach ($userList as $u): ?>
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <input type="checkbox" name="selected_users[]" value="<?= $u['IdNguoiDung'] ?>" style="margin-right: 10px;">
                            <a href="?user=<?= $u['IdNguoiDung'] ?>" style="display: block; padding: 12px 14px; border-radius: 10px; text-decoration: none; color: #333; background: <?= ($selectedUserId == $u['IdNguoiDung']) ? '#e7f1ff' : '#f8f9fa' ?>; border: 1px solid <?= ($selectedUserId == $u['IdNguoiDung']) ? '#0d6efd' : '#e9ecef' ?>; flex: 1; transition: all 0.2s;">
                                <strong><?= htmlspecialchars($u['HoTen']) ?></strong><br>
                                <span style="font-size: 13px; color: #666;">@<?= htmlspecialchars($u['TenDangNhap']) ?></span>
                                <div style="font-size: 12px; color: #888; margin-top: 6px;">
                                    <?= (int)$u['SoTinNhan'] ?> tin • <?= htmlspecialchars(date('d/m H:i', strtotime($u['LastTime']))) ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; font-style: italic;">Hiện chưa có cuộc hội thoại nào.</p>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- HỘP THOẠI CHAT -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
        <?php if ($selectedUser): ?>
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <i class="fa fa-comment-dots" style="color: #0d6efd; margin-right: 8px;"></i>Trò chuyện cùng: <?= htmlspecialchars($selectedUser['HoTen']) ?>
                <span style="font-size: 13px; font-weight: normal; color: #666;">(@<?= htmlspecialchars($selectedUser['TenDangNhap']) ?>)</span>
            </h3>
            
            <div id="admin-chat-messages" style="max-height: 480px; min-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; margin-top: 18px; padding-right: 8px;">
                <?php if (!empty($chatMessages)): ?>
                    <?php foreach ($chatMessages as $message): ?>
                        <div style="max-width: 80%; padding: 12px 16px; border-radius: 16px; background: <?= $message['NguoiGui'] == 1 ? '#f1f3f5' : '#0d6efd' ?>; color: <?= $message['NguoiGui'] == 1 ? '#212529' : '#fff' ?>; align-self: <?= $message['NguoiGui'] == 1 ? 'flex-start' : 'flex-end' ?>; word-break: break-word;">
                            <div style="font-size: 14px; line-height: 1.5;"><?= nl2br(htmlspecialchars($message['NoiDung'])) ?></div>
                            <div style="margin-top: 6px; font-size: 11px; opacity: 0.75; text-align: <?= $message['NguoiGui'] == 1 ? 'left' : 'right' ?>;">
                                <?= ($message['NguoiGui'] == 2 ? 'Shop • ' : '') . date('d/m/Y H:i', strtotime($message['ThoiGian'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888; text-align: center; margin: auto;">Chưa có tin nhắn nào trong cuộc hội thoại này.</p>
                <?php endif; ?>
            </div>

            <form method="POST" action="?user=<?= $selectedUserId ?>" id="admin-reply-form" style="margin-top: 20px;">
                <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                <textarea name="reply_message" id="admin-reply-input" rows="3" placeholder="Nhập nội dung phản hồi khách hàng... (Enter để gửi, Shift+Enter xuống dòng)" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ced4da; box-sizing: border-box; resize: vertical;" required></textarea>
                <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-add" style="padding: 10px 24px; border: none; cursor: pointer; font-size: 14px;">
                        <i class="fa fa-paper-plane"></i> Gửi phản hồi
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 0; color: #888;">
                <i class="fa fa-comments" style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                <p>Chọn một khách hàng ở danh sách bên trái để xem và trả lời tin nhắn.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var chatBox = document.getElementById('admin-chat-messages');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    var replyInput = document.getElementById('admin-reply-input');
    var replyForm = document.getElementById('admin-reply-form');
    if (replyInput && replyForm) {
        replyInput.focus();
        replyInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (replyInput.value.trim() !== '') {
                    replyForm.submit();
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>
