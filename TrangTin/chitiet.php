<?php
require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/../model/mathang.php';
require_once __DIR__ . '/../model/cskh.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$mh = new MATHANG();
$dg = new DANHGIA();

// Lấy thông tin chung của sản phẩm hiện tại
$sp = $mh->laymathangtheoid($id);

if (!$sp) {
    echo '<main class="auth-box"><h2>Sản phẩm không tồn tại</h2><p>Quay lại <a href="index.php">trang chủ</a>.</p></main>';
    include 'footer.php';
    exit;
}

// Tăng số lượt xem cho mặt hàng (BTTH04)
$mh->tangluotxem($id);

$gia_goc = $sp['DonGia'];
$ti_le_giam = $sp['TiLeGiamGia'];
$gia_ban = ($ti_le_giam > 0) ? ($gia_goc - ($gia_goc * $ti_le_giam / 100)) : $gia_goc;
$link_anh_chinh = htmlspecialchars(str_replace(' ', '%20', $sp['HinhAnh']), ENT_QUOTES, 'UTF-8');

// --- BẮT ĐẦU: KIỂM TRA QUYỀN ĐÁNH GIÁ ---
$da_mua_hang = false;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $da_mua_hang = $dg->kiemtramuahang($user_id, $id);
}
// --- KẾT THÚC: KIỂM TRA QUYỀN ĐÁNH GIÁ ---
?>

<main class="product-detail">
    <div class="row-top">
        <div class="image-box">
            <img src="<?= $link_anh_chinh ?>" alt="<?= htmlspecialchars($sp['TenSanPham']) ?>">
        </div>

        <div class="info-box">
            <h1><?= htmlspecialchars($sp['TenSanPham']) ?></h1>
            <div class="sku">Mã sản phẩm: <strong><?= htmlspecialchars($sp['MaSanPham']) ?></strong></div>
            
            <div class="product-meta">
                <span><strong>Danh mục:</strong> <?= htmlspecialchars($sp['TenDanhMuc'] ?? 'Chưa rõ') ?></span>
                <span><strong>Đã bán:</strong> <?= htmlspecialchars($sp['LuotMua']) ?></span>
                <?php if (isset($sp['LuotXem'])): ?>
                <span><strong>Lượt xem:</strong> <?= htmlspecialchars($sp['LuotXem']) ?></span>
                <?php endif; ?>
            </div>

            <div class="price-box">
                <?php if ($ti_le_giam > 0): ?>
                    <span class="old-price"><?= number_format($gia_goc, 0, ',', '.') ?>đ</span>
                    <span class="new-price"><?= number_format($gia_ban, 0, ',', '.') ?>đ</span>
                    <span class="badge-sale">-<?= $ti_le_giam ?>%</span>
                <?php else: ?>
                    <span class="new-price"><?= number_format($gia_goc, 0, ',', '.') ?>đ</span>
                <?php endif; ?>
            </div>

            <div class="selection-section">
                <?php
                // Xác định size được chọn: ưu tiên $_GET['size'] nếu có
                $sizes = $mh->laykichthuoctheosanpham($id);
                $selected_size = $_GET['size'] ?? '';
                $size_names = !empty($sizes) ? array_column($sizes, 'KichThuoc') : [];

                if (empty($selected_size) || !in_array($selected_size, $size_names)) {
                    $selected_size = !empty($size_names) ? $size_names[0] : 'Freesize';
                }
                ?>

                <div class="select-group">
                    <label>Màu sắc hiện có:</label>
                    <div class="options color-options">
                        <?php 
                        $ma_hien_tai = $sp['MaSanPham'];
                        $sql_mau = "SELECT DISTINCT sp.IdSanPham, bt.MauSac 
                                    FROM tbl_sanpham sp 
                                    JOIN tbl_bienthe bt ON sp.IdSanPham = bt.IdSanPham 
                                    WHERE sp.MaSanPham = '$ma_hien_tai'";
                        $res_mau = $ket_noi->query($sql_mau);
                        
                        if ($res_mau && $res_mau->num_rows > 0):
                            while ($mau = $res_mau->fetch_assoc()):
                                $is_active = ($mau['IdSanPham'] == $id) ? 'active' : '';
                        ?>
                            <a href="chitiet.php?id=<?= $mau['IdSanPham'] ?>&size=<?= urlencode($selected_size) ?>" class="opt-btn <?= $is_active ?>">
                                <?= htmlspecialchars($mau['MauSac']) ?>
                            </a>
                        <?php endwhile; 
                        else: ?>
                            <button class="opt-btn active">Mặc định</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="select-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label style="margin-bottom: 0;">Kích thước:</label>
                        <a href="#product-info-section" class="size-guide-quick-link">
                            <i class="fa fa-ruler-horizontal"></i> Xem bảng size
                        </a>
                    </div>
                    <div class="options">
                        <?php 
                        if (!empty($sizes)):
                            foreach ($sizes as $s): 
                                $active_class = ($s['KichThuoc'] === $selected_size) ? 'active' : '';
                        ?>
                                <button type="button" class="opt-btn size-btn <?= $active_class ?>" data-product-id="<?= $sp['IdSanPham'] ?>" onclick="selectSize(this)">
                                    <?= htmlspecialchars($s['KichThuoc']) ?>
                                </button>
                            <?php endforeach; 
                        else: ?>
                            <button type="button" class="opt-btn active size-btn" data-product-id="<?= $sp['IdSanPham'] ?>" onclick="selectSize(this)">Freesize</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div> 
            
            <div class="action-group">
                <a href="add_to_cart.php?id=<?= $sp['IdSanPham'] ?>&size=<?= urlencode($selected_size) ?>" id="add-to-cart-btn" class="btn-add-cart">THÊM VÀO GIỎ HÀNG</a>
                <a href="add_to_cart.php?id=<?= $sp['IdSanPham'] ?>&size=<?= urlencode($selected_size) ?>&action=buynow" id="buy-now-btn" class="btn-buy-now">MUA NGAY</a>
            </div>
            
            <a href="index.php" class="back-link"><i class="fa fa-arrow-left"></i> Tiếp tục mua sắm</a>
        </div>
    </div>

    <!-- KHU VỰC THÔNG TIN SẢN PHẨM (TRÊN ĐÁNH GIÁ) -->
    <?php
    $chat_lieu_text = !empty($sp['MoTa']) ? trim($sp['MoTa']) : 'Vải Cotton dệt cao cấp, mềm mại & thoáng khí';
    $is_quan = (stripos($sp['TenDanhMuc'] ?? '', 'quần') !== false || stripos($sp['TenSanPham'] ?? '', 'quần') !== false);
    ?>
    <div id="product-info-section" class="product-info-section">
        <h2 class="section-title">THÔNG TIN SẢN PHẨM</h2>
        
        <div class="product-info-simple-grid">
            <!-- CỘT 1: BẢNG SIZE BÌNH THƯỜNG -->
            <div class="info-col size-col">
                <h3 class="info-col-heading"><i class="fa fa-ruler-combined"></i> Bảng quy đổi kích thước (Size)</h3>
                <div class="table-responsive">
                    <table class="simple-size-table">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Chiều cao</th>
                                <th>Cân nặng</th>
                                <?php if ($is_quan): ?>
                                    <th>Vòng eo</th>
                                    <th>Dài quần</th>
                                <?php else: ?>
                                    <th>Vòng ngực</th>
                                    <th>Dài áo</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($is_quan): ?>
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td>1m55 - 1m65</td>
                                    <td>48 - 55 kg</td>
                                    <td>72 - 76 cm</td>
                                    <td>94 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>M</strong></td>
                                    <td>1m60 - 1m70</td>
                                    <td>55 - 64 kg</td>
                                    <td>76 - 80 cm</td>
                                    <td>96 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>1m68 - 1m75</td>
                                    <td>65 - 72 kg</td>
                                    <td>80 - 84 cm</td>
                                    <td>98 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XL</strong></td>
                                    <td>1m73 - 1m80</td>
                                    <td>73 - 80 kg</td>
                                    <td>84 - 88 cm</td>
                                    <td>100 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XXL</strong></td>
                                    <td>1m78 - 1m88</td>
                                    <td>81 - 90 kg</td>
                                    <td>88 - 94 cm</td>
                                    <td>102 cm</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td>1m55 - 1m65</td>
                                    <td>48 - 55 kg</td>
                                    <td>96 cm</td>
                                    <td>66 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>M</strong></td>
                                    <td>1m60 - 1m70</td>
                                    <td>55 - 64 kg</td>
                                    <td>100 cm</td>
                                    <td>68 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>L</strong></td>
                                    <td>1m68 - 1m75</td>
                                    <td>65 - 72 kg</td>
                                    <td>104 cm</td>
                                    <td>70 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XL</strong></td>
                                    <td>1m73 - 1m80</td>
                                    <td>73 - 80 kg</td>
                                    <td>108 cm</td>
                                    <td>72 cm</td>
                                </tr>
                                <tr>
                                    <td><strong>XXL</strong></td>
                                    <td>1m78 - 1m88</td>
                                    <td>81 - 90 kg</td>
                                    <td>112 cm</td>
                                    <td>74 cm</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CỘT 2: TÊN SẢN PHẨM, CHẤT LIỆU, CÁCH BẢO QUẢN -->
            <div class="info-col detail-col">
                <h3 class="info-col-heading"><i class="fa fa-info-circle"></i> Chi tiết sản phẩm</h3>
                <div class="product-simple-info-content">
                    <div class="info-row">
                        <span class="info-lbl">Tên sản phẩm:</span>
                        <span class="info-val"><strong><?= htmlspecialchars($sp['TenSanPham']) ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Chất liệu:</span>
                        <span class="info-val"><?= htmlspecialchars($chat_lieu_text) ?></span>
                    </div>
                    <div class="info-row care-row">
                        <span class="info-lbl">Cách bảo quản:</span>
                        <div class="info-val">
                            <ul class="simple-care-list">
                                <li>Giặt máy ở nhiệt độ thường (dưới 40°C), chế độ nhẹ.</li>
                                <li>Lộn trái sản phẩm khi giặt và phơi để giữ màu bền lâu.</li>
                                <li>Không sử dụng chất tẩy rửa mạnh hoặc thuốc tẩy clo.</li>
                                <li>Phơi ở nơi thoáng gió, tránh phơi trực tiếp dưới ánh nắng gắt.</li>
                                <li>Ủi ở nhiệt độ trung bình (dưới 150°C), khuyên dùng bàn ủi hơi nước.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KHU VỰC ĐÁNH GIÁ -->
    <div class="review-section" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
        <h2 class="section-title">ĐÁNH GIÁ SẢN PHẨM</h2>

        <?php if ($da_mua_hang): ?>
            <div class="review-form-box" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-top: 0;">Viết đánh giá của bạn</h4>
                <form action="xuly_danhgia.php" method="POST">
                    <input type="hidden" name="id_sanpham" value="<?= $id ?>">
                    
                    <div style="margin-bottom: 15px;">
                        <label><strong>Chất lượng:</strong></label>
                        <select name="so_sao" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                            <option value="5">⭐⭐⭐⭐⭐ (Tuyệt vời)</option>
                            <option value="4">⭐⭐⭐⭐ (Tốt)</option>
                            <option value="3">⭐⭐⭐ (Bình thường)</option>
                            <option value="2">⭐⭐ (Tệ)</option>
                            <option value="1">⭐ (Rất tệ)</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <textarea name="noi_dung" rows="4" required placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này nhé..." style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;"></textarea>
                    </div>
                    
                    <button type="submit" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Gửi đánh giá</button>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <p style="color: #666; font-style: italic; margin-bottom: 20px;">Vui lòng <a href="dangnhap.php">đăng nhập</a> để đánh giá sản phẩm.</p>
        <?php else: ?>
            <p style="color: #666; font-style: italic; margin-bottom: 20px;">Bạn cần mua và nhận sản phẩm này thành công để có thể viết đánh giá.</p>
        <?php endif; ?>

        <div class="review-list">
            <?php
            $reviews = $dg->laydanhgiatheosanpham($id);
            if (!empty($reviews)):
                foreach ($reviews as $rv):
                    $so_sao = (int)$rv['SoSao'];
            ?>
                <div class="review-item" style="border-bottom: 1px dashed #ddd; padding: 15px 0;">
                    <div style="font-weight: bold; margin-bottom: 5px;">
                        <i class="fa fa-user-circle"></i> <?= htmlspecialchars($rv['HoTen'] ?: $rv['TenDangNhap']) ?>
                        <span style="color: #ffc107; font-size: 14px; margin-left: 10px;">
                            <?= str_repeat('⭐', $so_sao) ?>
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #999; margin-bottom: 10px;">
                        <?= date('d/m/Y H:i', strtotime($rv['NgayDanhGia'])) ?>
                    </div>
                    <div style="color: #333; line-height: 1.5;">
                        <?= nl2br(htmlspecialchars($rv['NoiDung'])) ?>
                    </div>
                </div>
            <?php 
                endforeach;
            else: 
            ?>
                <p style="color: #999;">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- SẢN PHẨM GỢI Ý CÙNG DANH MỤC -->
    <div class="related-section" style="margin-top: 40px;">
        <h2 class="section-title">GỢI Ý SẢN PHẨM</h2>
        <div class="product-grid">
            <?php
            $id_dm = $sp['IdDanhMuc'];
            $related = $mh->laymathangtheodanhmuc($id_dm, 5);
            $countRel = 0;
            if (!empty($related)):
                foreach ($related as $r): 
                    if ($r['IdSanPham'] == $id) continue;
                    if (++$countRel > 4) break;
                    $g_ban = $r['DonGia'] - ($r['DonGia'] * $r['TiLeGiamGia'] / 100);
            ?>
                <div class="product-item">
                    <a href="chitiet.php?id=<?= $r['IdSanPham'] ?>">
                        <img src="<?= htmlspecialchars($r['HinhAnh']) ?>" alt="<?= htmlspecialchars($r['TenSanPham']) ?>">
                        <div class="product-title"><?= htmlspecialchars($r['TenSanPham']) ?></div>
                        <div class="product-price"><?= number_format($g_ban, 0, ',', '.') ?>đ</div>
                    </a>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/inc/bottom.php'; ?>
