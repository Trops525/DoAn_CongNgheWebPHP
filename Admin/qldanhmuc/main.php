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

<!-- FORM THÊM / SỬA DANH MỤC NẰM NGANG Ở TRÊN -->
<div style="background: #fff; padding: 20px 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #e9ecef;">
    <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 15px; text-transform: uppercase; color: #2c3e50; font-weight: 700; display: flex; align-items: center; gap: 8px;">
        <i class="fa <?= !empty($category['IdDanhMuc']) ? 'fa-edit text-primary' : 'fa-plus-circle text-success' ?>"></i> 
        <?= !empty($category['IdDanhMuc']) ? 'Cập nhật danh mục: #' . $category['IdDanhMuc'] : 'Thêm danh mục mới' ?>
    </h3>
    
    <form method="post" action="?action=<?= !empty($category['IdDanhMuc']) ? 'sua&id=' . $category['IdDanhMuc'] : 'them' ?>">
        <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1.2; min-width: 260px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #495057;">Tên danh mục *</label>
                <input type="text" name="TenDanhMuc" required value="<?= htmlspecialchars($category['TenDanhMuc'] ?? '') ?>" placeholder="Ví dụ: Áo Khoác Nam, Giày Thể Thao..." style="width: 100%; padding: 10px 14px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px;">
            </div>

            <div style="flex: 2.2; min-width: 320px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #495057;">Mô tả danh mục</label>
                <input type="text" name="MoTa" value="<?= htmlspecialchars($category['MoTa'] ?? '') ?>" placeholder="Nhập mô tả ngắn gọn cho danh mục..." style="width: 100%; padding: 10px 14px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px;">
            </div>

            <div style="display: flex; gap: 10px; align-items: center; padding-bottom: 1px;">
                <button type="submit" class="btn btn-add" style="border: none; cursor: pointer; padding: 10px 22px; font-weight: 600; font-size: 14px; white-space: nowrap; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa fa-save"></i> <?= !empty($category['IdDanhMuc']) ? 'Lưu thay đổi' : 'Thêm danh mục' ?>
                </button>
                <?php if (!empty($category['IdDanhMuc'])): ?>
                    <a href="?" class="btn" style="background: #6c757d; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa fa-times"></i> Hủy
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- BẢNG DANH SÁCH DANH MỤC NẰM DƯỚI TOÀN MÀN HÌNH -->
<div class="table-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e9ecef;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f1f3f5;">
        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #2c3e50; text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-list text-secondary"></i> Danh sách danh mục (<?= count($danhmuc) ?> danh mục)
        </h3>
    </div>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="width: 70px; text-align: center;">ID</th>
                <th style="width: 220px;">Tên danh mục</th>
                <th style="width: 120px; text-align: center;">Số lượng SP</th>
                <th>Mô tả</th>
                <th style="width: 190px; text-align: center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($danhmuc)): ?>
                <?php foreach ($danhmuc as $row): 
                    $isLocked = !empty($row['Khoa']);
                    $spCount = (int)($row['SoLuongSanPham'] ?? 0);
                ?>
                    <tr style="<?= $isLocked ? 'background: #fff5f5; opacity: 0.88;' : '' ?>">
                        <td style="text-align: center; font-weight: bold; color: #6c757d;">#<?= $row['IdDanhMuc'] ?></td>
                        <td style="font-weight: 600; color: #111;">
                            <?= htmlspecialchars($row['TenDanhMuc']) ?>
                            <?php if ($isLocked): ?>
                                <span style="font-size: 11px; background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-weight: normal; display: inline-block;">
                                    <i class="fa fa-lock"></i> Đã khóa
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12.5px; font-weight: 600; background: <?= $spCount > 0 ? '#e7f1ff' : '#f8f9fa' ?>; color: <?= $spCount > 0 ? '#0d6efd' : '#adb5bd' ?>; border: 1px solid <?= $spCount > 0 ? '#b6d4fe' : '#dee2e6' ?>;">
                                <?= $spCount ?> sản phẩm
                            </span>
                        </td>
                        <td style="color: #555; line-height: 1.5;">
                            <?= !empty(trim($row['MoTa'] ?? '')) ? htmlspecialchars($row['MoTa']) : '<em style="color:#aaa;">(Chưa có mô tả)</em>' ?>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="?action=sua&id=<?= $row['IdDanhMuc'] ?>" class="btn btn-edit" style="padding: 6px 10px;"><i class="fa fa-edit"></i> Sửa</a>
                            <a href="?action=khoa&id=<?= $row['IdDanhMuc'] ?>" class="btn" style="background: <?= $isLocked ? '#28a745' : '#ffc107' ?>; color: <?= $isLocked ? '#fff' : '#000' ?>; padding: 6px 10px;" onclick="return confirm('<?= $isLocked ? 'Mở khóa hiển thị danh mục này?' : 'Khóa (tạm ẩn) danh mục này?' ?>');" title="<?= $isLocked ? 'Mở khóa' : 'Khóa danh mục' ?>">
                                <?= $isLocked ? '<i class="fa fa-unlock"></i> Mở' : '<i class="fa fa-lock"></i> Khóa' ?>
                            </a>
                            <?php if ($userRole == 1): ?>
                                <?php if ($spCount > 0): ?>
                                    <button type="button" class="btn" style="background: #e9ecef; color: #adb5bd; padding: 6px 10px; cursor: not-allowed; border: 1px solid #ced4da;" onclick="alert('Không thể xóa! Danh mục này hiện đang có <?= $spCount ?> sản phẩm. Bạn hãy dùng tính năng Khóa để ẩn danh mục, hoặc chuyển hết sản phẩm sang danh mục khác trước khi xóa.');" title="Còn sản phẩm, không thể xóa">
                                        <i class="fa fa-trash"></i> Xóa
                                    </button>
                                <?php else: ?>
                                    <a href="?action=xoa&id=<?= $row['IdDanhMuc'] ?>" class="btn btn-delete" style="padding: 6px 10px;" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');" title="Xóa danh mục trống này">
                                        <i class="fa fa-trash"></i> Xóa
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align: center; color: #888; padding: 40px;">Chưa có danh mục nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../inc/bottom.php'; ?>
