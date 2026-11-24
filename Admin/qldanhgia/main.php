<?php
$page_title = 'Quản lý đánh giá - Quality Admin';
require_once __DIR__ . '/../inc/top.php';
?>

<div class="header-page">
    <h1>Quản lý đánh giá & phản hồi sản phẩm</h1>
</div>

<!-- THỐNG KÊ TỔNG QUAN -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div style="background: #fff; padding: 18px 20px; border-radius: 8px; border-left: 4px solid #3498db; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 12.5px; color: #7f8c8d; font-weight: 600; text-transform: uppercase;">Tổng đánh giá</div>
        <div style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-top: 6px;"><?= number_format($thongKe['Tong']) ?></div>
    </div>
    <div style="background: #fff; padding: 18px 20px; border-radius: 8px; border-left: 4px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 12.5px; color: #7f8c8d; font-weight: 600; text-transform: uppercase;">Điểm trung bình</div>
        <div style="font-size: 24px; font-weight: 700; color: #f39c12; margin-top: 6px;">
            <?= $thongKe['TrungBinh'] ?: 5.0 ?> <i class="fa fa-star" style="font-size: 18px;"></i>
        </div>
    </div>
    <div style="background: #fff; padding: 18px 20px; border-radius: 8px; border-left: 4px solid #2ecc71; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 12.5px; color: #7f8c8d; font-weight: 600; text-transform: uppercase;">Đang hiển thị</div>
        <div style="font-size: 24px; font-weight: 700; color: #27ae60; margin-top: 6px;"><?= number_format($thongKe['HienThi']) ?></div>
    </div>
    <div style="background: #fff; padding: 18px 20px; border-radius: 8px; border-left: 4px solid #e74c3c; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="font-size: 12.5px; color: #7f8c8d; font-weight: 600; text-transform: uppercase;">Đã ẩn</div>
        <div style="font-size: 24px; font-weight: 700; color: #c0392b; margin-top: 6px;"><?= number_format($thongKe['DaAn']) ?></div>
    </div>
</div>

<!-- BỘ LỌC VÀ TÌM KIẾM -->
<div style="background: #fff; padding: 16px 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 2; min-width: 240px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tên sản phẩm, mã sp, khách hàng, nội dung..." style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
        </div>

        <div style="min-width: 150px;">
            <select name="status" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
                <option value="all" <?= ($status === 'all') ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="visible" <?= ($status === 'visible') ? 'selected' : '' ?>>Đang hiển thị</option>
                <option value="hidden" <?= ($status === 'hidden') ? 'selected' : '' ?>>Đã ẩn</option>
            </select>
        </div>

        <div style="min-width: 160px;">
            <select name="sao" style="width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 13.5px;">
                <option value="0" <?= ($so_sao === 0) ? 'selected' : '' ?>>Tất cả số sao (⭐)</option>
                <option value="5" <?= ($so_sao === 5) ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 sao)</option>
                <option value="4" <?= ($so_sao === 4) ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 sao)</option>
                <option value="3" <?= ($so_sao === 3) ? 'selected' : '' ?>>⭐⭐⭐ (3 sao)</option>
                <option value="2" <?= ($so_sao === 2) ? 'selected' : '' ?>>⭐⭐ (2 sao)</option>
                <option value="1" <?= ($so_sao === 1) ? 'selected' : '' ?>>⭐ (1 sao)</option>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" style="background: #0d6efd; color: white; border: none; padding: 9px 18px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                <i class="fa fa-filter"></i> Lọc
            </button>
            <a href="<?= $currentScript ?>" style="background: #6c757d; color: white; text-decoration: none; padding: 9px 14px; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center;">
                <i class="fa fa-redo"></i> Đặt lại
            </a>
        </div>
    </form>
</div>

<!-- NÚT THAO TÁC HÀNG LOẠT -->
<form method="POST" id="bulk-action-form">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div style="font-weight: 600; color: #495057;">
            Hiển thị: <strong><?= count($reviews) ?></strong> đánh giá
        </div>
        <div id="bulk-buttons" style="display: none; gap: 8px; align-items: center;">
            <span style="font-size: 13px; color: #666; margin-right: 4px;">Đã chọn (<span id="selected-count">0</span>):</span>
            <button type="submit" name="hide_selected" class="btn" style="background: #ffc107; color: #000; padding: 7px 12px; font-size: 12.5px;" onclick="return confirm('Ẩn các đánh giá đã chọn khỏi website?');">
                <i class="fa fa-eye-slash"></i> Ẩn các mục chọn
            </button>
            <button type="submit" name="unhide_selected" class="btn" style="background: #28a745; color: #fff; padding: 7px 12px; font-size: 12.5px;" onclick="return confirm('Hiện lại các đánh giá đã chọn trên website?');">
                <i class="fa fa-eye"></i> Hiện các mục chọn
            </button>
            <button type="submit" name="delete_selected" class="btn btn-delete" style="padding: 7px 12px; font-size: 12.5px;" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn các đánh giá đã chọn?');">
                <i class="fa fa-trash"></i> Xóa mục chọn
            </button>
        </div>
    </div>

    <!-- DANH SÁCH BẢNG ĐÁNH GIÁ -->
    <div class="table-container" style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <table class="table-wide" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="check-all" onchange="toggleSelectAll(this)">
                    </th>
                    <th style="width: 50px; text-align: center;">ID</th>
                    <th style="width: 240px;">Sản phẩm</th>
                    <th style="width: 170px;">Khách hàng</th>
                    <th style="width: 130px; text-align: center;">Đánh giá</th>
                    <th>Nội dung nhận xét</th>
                    <th style="width: 110px; text-align: center;">Trạng thái</th>
                    <th style="width: 130px; text-align: center;">Thời gian</th>
                    <th style="width: 130px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rv): 
                        $link_anh = str_replace(' ', '%20', $rv['HinhAnh'] ?? '');
                        $is_hidden = !empty($rv['Khoa']);
                    ?>
                        <tr style="<?= $is_hidden ? 'background: #fff5f5; opacity: 0.88;' : '' ?>">
                            <td style="text-align: center;">
                                <input type="checkbox" name="selected_ids[]" value="<?= $rv['IdDanhGia'] ?>" class="item-checkbox" onchange="updateSelectedCount()">
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #6c757d;">#<?= $rv['IdDanhGia'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if (!empty($rv['HinhAnh'])): ?>
                                        <img src="/Thoi-Trang-Quality/TrangTin/<?= $link_anh ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                                    <?php endif; ?>
                                    <div>
                                        <a href="/Thoi-Trang-Quality/TrangTin/chitiet.php?id=<?= $rv['IdSanPham'] ?>" target="_blank" style="font-weight: 600; color: #212529; text-decoration: none; font-size: 13.5px;" title="Xem sản phẩm trên web">
                                            <?= htmlspecialchars($rv['TenSanPham'] ?? 'Sản phẩm #' . $rv['IdSanPham']) ?>
                                        </a>
                                        <br>
                                        <small style="color: #888;">Mã: <?= htmlspecialchars($rv['MaSanPham'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="color: #2c3e50; font-size: 13.5px;"><?= htmlspecialchars($rv['HoTen'] ?: $rv['TenDangNhap']) ?></strong>
                                <br>
                                <small style="color: #6c757d;">@<?= htmlspecialchars($rv['TenDangNhap']) ?></small>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="color: #ffc107; font-size: 14px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star<?= ($i <= $rv['SoSao']) ? '' : '-o' ?>" style="<?= ($i > $rv['SoSao']) ? 'color: #ddd;' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span style="font-size: 11.5px; font-weight: 600; color: #495057;">(<?= $rv['SoSao'] ?>/5 sao)</span>
                            </td>
                            <td style="line-height: 1.5; color: #333; font-size: 13.5px;">
                                <?= nl2br(htmlspecialchars($rv['NoiDung'])) ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <?php if ($is_hidden): ?>
                                    <span style="background: #dc3545; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">
                                        <i class="fa fa-eye-slash"></i> Đã ẩn
                                    </span>
                                <?php else: ?>
                                    <span style="background: #28a745; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">
                                        <i class="fa fa-check-circle"></i> Hiển thị
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-size: 12px; color: #6c757d; white-space: nowrap;">
                                <?= date('d/m/Y H:i', strtotime($rv['NgayDanhGia'])) ?>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <!-- Nút Ẩn / Hiện -->
                                    <a href="?action=khoa&id=<?= $rv['IdDanhGia'] ?>" class="btn" style="background: <?= $is_hidden ? '#28a745' : '#ffc107' ?>; color: <?= $is_hidden ? '#fff' : '#000' ?>; padding: 5px 8px; font-size: 12px;" onclick="return confirm('<?= $is_hidden ? 'Hiện lại đánh giá này trên website?' : 'Ẩn đánh giá này khỏi website?' ?>');" title="<?= $is_hidden ? 'Hiện đánh giá' : 'Ẩn đánh giá' ?>">
                                        <i class="fa <?= $is_hidden ? 'fa-eye' : 'fa-eye-slash' ?>"></i> <?= $is_hidden ? 'Hiện' : 'Ẩn' ?>
                                    </a>
                                    <!-- Nút Xóa -->
                                    <a href="?action=xoa&id=<?= $rv['IdDanhGia'] ?>" class="btn btn-delete" style="padding: 5px 8px; font-size: 12px;" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đánh giá này?');" title="Xóa đánh giá">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #888;">
                            <i class="fa fa-comment-slash" style="font-size: 36px; color: #ccc; margin-bottom: 10px; display: block;"></i>
                            Không tìm thấy đánh giá nào phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</form>

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    const count = checkboxes.length;
    const btnBox = document.getElementById('bulk-buttons');
    const countSpan = document.getElementById('selected-count');
    
    countSpan.textContent = count;
    if (count > 0) {
        btnBox.style.display = 'inline-flex';
    } else {
        btnBox.style.display = 'none';
        document.getElementById('check-all').checked = false;
    }
}
</script>
