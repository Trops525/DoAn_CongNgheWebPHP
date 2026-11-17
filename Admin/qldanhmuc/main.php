<?php
$page_title = 'Quản lý danh mục - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page">
    <h1>Quản lý danh mục</h1>
</div>

<?php if (!empty($success)): ?>
    <div style="padding: 10px 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="padding: 10px 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: minmax(300px, 340px) minmax(0, 1fr); gap: 25px; align-items: start;">
    <!-- FORM THÊM / SỬA -->
    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px; text-transform: uppercase;">
            <?= !empty($category['IdDanhMuc']) ? 'Cập nhật danh mục' : 'Thêm danh mục mới' ?>
        </h3>
        
        <form method="post" action="?action=<?= !empty($category['IdDanhMuc']) ? 'sua&id=' . $category['IdDanhMuc'] : 'them' ?>">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Tên danh mục *</label>
                <input type="text" name="TenDanhMuc" required value="<?= htmlspecialchars($category['TenDanhMuc'] ?? '') ?>" placeholder="Ví dụ: Áo Khoác Nam..." style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">Mô tả</label>
                <textarea name="MoTa" rows="4" placeholder="Nhập mô tả cho danh mục..." style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box; resize: vertical;"><?= htmlspecialchars($category['MoTa'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-add" style="border: none; cursor: pointer; padding: 10px 20px;">
                    <?= !empty($category['IdDanhMuc']) ? 'Lưu thay đổi' : 'Thêm mới' ?>
                </button>
                <?php if (!empty($category['IdDanhMuc'])): ?>
                    <a href="?" class="btn" style="background: #6c757d; color: #fff; padding: 10px 15px;">Hủy bỏ</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH -->
    <div class="table-container" style="min-width: 0;">
        <table style="min-width: 500px;">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th style="width: 200px;">Tên danh mục</th>
                    <th>Mô tả</th>
                    <th style="width: 140px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($danhmuc)): ?>
                    <?php foreach ($danhmuc as $row): ?>
                        <tr>
                            <td>#<?= $row['IdDanhMuc'] ?></td>
                            <td style="font-weight: 600; color: #111;"><?= htmlspecialchars($row['TenDanhMuc']) ?></td>
                            <td style="color: #555; line-height: 1.5;">
                                <?= !empty(trim($row['MoTa'] ?? '')) ? htmlspecialchars($row['MoTa']) : '<em style="color:#aaa;">(Chưa có mô tả)</em>' ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="?action=sua&id=<?= $row['IdDanhMuc'] ?>" class="btn btn-edit"><i class="fa fa-edit"></i> Sửa</a>
                                <a href="?action=xoa&id=<?= $row['IdDanhMuc'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"><i class="fa fa-trash"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: #888;">Chưa có danh mục nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>
