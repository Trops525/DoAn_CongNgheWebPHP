<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once __DIR__ . '/db.php';

// 1. LẤY DỮ LIỆU CŨ ĐỂ HIỂN THỊ LÊN FORM
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql_get = "SELECT * FROM tbl_sanpham WHERE IdSanPham = $id";
$ket_qua = $ket_noi->query($sql_get);

if ($ket_qua->num_rows == 0) {
    echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='index.php';</script>";
    exit;
}
$sp = $ket_qua->fetch_assoc();

// Tính ngược lại giá khuyến mãi từ % giảm giá để hiện ra ô nhập
$gia_goc = $sp['DonGia'];
$gia_khuyen_mai = ($sp['TiLeGiamGia'] > 0) ? $gia_goc - ($gia_goc * $sp['TiLeGiamGia'] / 100) : 0;

// 2. XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT "CẬP NHẬT"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_sp = $ket_noi->real_escape_string($_POST['ma_san_pham']);
    $ten_sp = $ket_noi->real_escape_string($_POST['ten_san_pham']);
    $id_dm = (int)$_POST['id_danh_muc'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $gia_goc_moi = (int)$_POST['gia_goc'];
    $gia_km_moi = (int)$_POST['gia_khuyen_mai'];
    $mo_ta = $ket_noi->real_escape_string($_POST['mo_ta']);

    // Tính lại phần trăm giảm
    $ti_le_giam = 0;
    if ($gia_km_moi > 0 && $gia_km_moi < $gia_goc_moi) {
        $ti_le_giam = round((($gia_goc_moi - $gia_km_moi) / $gia_goc_moi) * 100);
    }

    // Xử lý Ảnh - lưu vào thư mục theo giới tính
    $chuoi_sql_anh = ""; 
    
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $subdir = ($gioi_tinh == 'Nam') ? 'Nam' : (($gioi_tinh == 'Nữ') ? 'Nu' : 'PhuKien');
        $thu_muc = "../TrangTin/images/" . $subdir . "/"; 
        $ten_file = time() . '_' . basename($_FILES['hinh_anh']['name']);
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $thu_muc . $ten_file)) {
            $hinh_anh_db = "images/" . $subdir . "/" . $ten_file;
            $chuoi_sql_anh = ", HinhAnh = '$hinh_anh_db'"; 
        }
    }

    // 3. Cập nhật vào Database
    $sql_update = "UPDATE tbl_sanpham SET 
                   MaSanPham = '$ma_sp', 
                   TenSanPham = '$ten_sp', 
                   IdDanhMuc = $id_dm, 
                   GioiTinh = '$gioi_tinh', 
                   DonGia = $gia_goc_moi, 
                   TiLeGiamGia = $ti_le_giam, 
                   MoTa = '$mo_ta' 
                   $chuoi_sql_anh 
                   WHERE IdSanPham = $id";

    if ($ket_noi->query($sql_update)) {
        // Cập nhật số lượng size nếu có dữ liệu POST (cho phép cả số 0 khi hết hàng)
        if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $kichThuoc => $soLuong) {
                $soLuong = max(0, (int)$soLuong);
                $kichThuoc = $ket_noi->real_escape_string($kichThuoc);
                $sql_update_size = "UPDATE tbl_bienthe SET SoLuong = $soLuong 
                                   WHERE IdSanPham = $id AND KichThuoc = '$kichThuoc'";
                $ket_noi->query($sql_update_size);
            }
        }

        // Bổ sung size mới nếu được chọn
        if (!empty($_POST['new_size']) && isset($_POST['new_qty']) && $_POST['new_qty'] !== '') {
            $newSize = $ket_noi->real_escape_string(trim($_POST['new_size']));
            $newQty = max(0, (int)$_POST['new_qty']);
            $chk = $ket_noi->query("SELECT IdBienThe FROM tbl_bienthe WHERE IdSanPham = $id AND KichThuoc = '$newSize'");
            if ($chk && $chk->num_rows > 0) {
                $ket_noi->query("UPDATE tbl_bienthe SET SoLuong = $newQty WHERE IdSanPham = $id AND KichThuoc = '$newSize'");
            } else {
                $ket_noi->query("INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong) VALUES ($id, 'Mặc định', '$newSize', $newQty)");
            }
        }

        echo "<script>alert('Đã lưu các thay đổi thành công!'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "Lỗi truy vấn: " . $ket_noi->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sản phẩm - Admin Quality</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; padding: 30px 15px; margin: 0; color: #333; }
        .form-container { background: #fff; padding: 35px; border-radius: 12px; max-width: 860px; margin: 0 auto; box-shadow: 0 5px 25px rgba(0,0,0,0.08); width: 100%; }
        h2 { text-align: center; margin-top: 0; margin-bottom: 30px; text-transform: uppercase; color: #212529; border-bottom: 2px solid #e9ecef; padding-bottom: 12px; font-size: 22px; }
        .row { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 200px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 13.5px; color: #495057; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 11px 12px; border: 1px solid #ced4da; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus, select:focus, textarea:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 3px rgba(13,110,253,0.15); }
        .btn-submit { background: #0d6efd; color: white; padding: 14px; border: none; border-radius: 6px; width: 100%; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 20px; }
        .btn-submit:hover { background: #0b5ed7; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #495057; text-decoration: none; font-weight: 600; margin-bottom: 25px; font-size: 14px; padding: 6px 12px; background: #e9ecef; border-radius: 6px; transition: 0.2s; }
        .btn-back:hover { background: #dee2e6; color: #111; }
        .img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; margin-top: 10px; }
    </style>
</head>
<body>

<div class="form-container">
    <a href="index.php" class="btn-back"><i class="fa fa-arrow-left"></i> Quay lại danh sách sản phẩm</a>
    <h2>Sửa Thông Tin Sản Phẩm</h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="row">
            <div class="col">
                <label>Mã sản phẩm:</label>
                <input type="text" name="ma_san_pham" value="<?= $sp['MaSanPham'] ?>" required>
            </div>
            <div class="col">
                <label>Tên sản phẩm:</label>
                <input type="text" name="ten_san_pham" value="<?= $sp['TenSanPham'] ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Danh mục sản phẩm:</label>
                <select name="id_danh_muc" required>
                    <?php
                    $sql_dm = "SELECT * FROM tbl_danhmuc";
                    $res_dm = $ket_noi->query($sql_dm);
                    while($row_dm = $res_dm->fetch_assoc()) {
                        $selected = ($row_dm['IdDanhMuc'] == $sp['IdDanhMuc']) ? "selected" : "";
                        echo "<option value='{$row_dm['IdDanhMuc']}' $selected>{$row_dm['TenDanhMuc']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col">
                <label>Giới tính:</label>
                <select name="gioi_tinh" required>
                    <option value="Nam" <?= ($sp['GioiTinh'] == 'Nam') ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= ($sp['GioiTinh'] == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
                    <option value="Unisex" <?= ($sp['GioiTinh'] == 'Unisex') ? 'selected' : '' ?>>Unisex</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>Giá gốc (VNĐ):</label>
                <input type="number" name="gia_goc" value="<?= $sp['DonGia'] ?>" required>
            </div>
            <div class="col">
                <label>Giá sau giảm (Nhập 0 nếu không giảm):</label>
                <input type="number" name="gia_khuyen_mai" value="<?= $gia_khuyen_mai ?>">
            </div>
        </div>

        <div class="form-group" style="background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #eee;">
            <label style="font-weight: bold; margin-bottom: 10px; display: block;">Ảnh sản phẩm (Chỉ chọn nếu muốn đổi):</label>
            
            <label style="font-size: 13px; font-weight: 600;">📁 Upload từ máy tính:</label>
            <input type="file" name="hinh_anh" accept="image/*" style="font-size: 13px; width: 100%; margin-bottom: 15px;">
            <small style="color: #666; display: block; margin-bottom: 15px;">JPG, PNG, GIF, WebP...</small>
            
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                <div style="font-size: 13px; color: #888; margin-bottom: 5px;">Ảnh hiện tại:</div>
                <img src="../TrangTin/<?= str_replace(' ', '%20', $sp['HinhAnh']) ?>" class="img-preview" alt="Ảnh hiện tại">
            </div>
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết:</label>
            <textarea name="mo_ta" rows="4"><?= $sp['MoTa'] ?></textarea>
        </div>

        <div class="form-group" style="background: #f0f8ff; padding: 20px; border-radius: 8px; border: 1px solid #b3d9ff;">
            <label style="font-weight: bold; font-size: 15px; color: #0056b3; margin-bottom: 12px; display: block;">
                <i class="fa fa-boxes"></i> Quản lý Size & Số Lượng Tồn Kho:
            </label>
            <table class="table table-bordered table-hover" style="background: #fff; width: 100%; margin-top: 10px;">
                <thead>
                    <tr style="background: #e7f3ff;">
                        <th style="padding: 10px; text-align: left; color: #0056b3; width: 50%;">Kích cỡ (Size)</th>
                        <th style="padding: 10px; text-align: center; color: #0056b3; width: 50%;">Số Lượng Tồn Kho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_sizes = "SELECT KichThuoc, SoLuong FROM tbl_bienthe WHERE IdSanPham = $id 
                                  ORDER BY 
                                      CASE UPPER(TRIM(KichThuoc))
                                          WHEN '3XS' THEN 1 WHEN '2XS' THEN 2 WHEN 'XS' THEN 3
                                          WHEN 'S' THEN 4 WHEN 'M' THEN 5 WHEN 'L' THEN 6
                                          WHEN 'XL' THEN 7 WHEN '2XL' THEN 8 WHEN 'XXL' THEN 9
                                          WHEN '3XL' THEN 10 WHEN 'XXXL' THEN 11 WHEN '4XL' THEN 12 WHEN '5XL' THEN 13
                                          WHEN 'FREESIZE' THEN 99 ELSE 50
                                      END, CAST(KichThuoc AS UNSIGNED) ASC, KichThuoc ASC";
                    $result_sizes = $ket_noi->query($sql_sizes);
                    
                    if ($result_sizes && $result_sizes->num_rows > 0) {
                        while ($size_row = $result_sizes->fetch_assoc()) {
                            $size = htmlspecialchars($size_row['KichThuoc']);
                            $qty = (int)$size_row['SoLuong'];
                            echo '<tr>';
                            echo '<td style="padding: 10px 14px; font-weight: 600; vertical-align: middle;">' . $size . '</td>';
                            echo '<td style="padding: 10px 14px; text-align: center;">';
                            echo '<input type="number" name="quantities[' . $size . ']" value="' . $qty . '" min="0" class="form-control" style="width: 110px; margin: 0 auto; text-align: center; font-weight: bold;">';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="2" style="padding: 15px; text-align: center; color: #999;">Sản phẩm này chưa có size nào</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <!-- Bổ sung size mới -->
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px dashed #b3d9ff; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 140px;">
                    <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px; display: block; color: #0056b3;">+ Bổ sung Size mới:</label>
                    <select name="new_size" class="form-select" style="padding: 8px 12px; font-size: 13px;">
                        <option value="">-- Chọn Size để thêm --</option>
                        <option value="S">Size S</option>
                        <option value="M">Size M</option>
                        <option value="L">Size L</option>
                        <option value="XL">Size XL</option>
                        <option value="XXL">Size XXL</option>
                        <option value="3XL">Size 3XL</option>
                        <option value="Freesize">Freesize</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 120px;">
                    <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px; display: block; color: #0056b3;">Số lượng nhập:</label>
                    <input type="number" name="new_qty" min="0" placeholder="0" class="form-control" style="padding: 8px 12px; font-size: 13px;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit">CẬP NHẬT THAY ĐỔI</button>
        <a href="index.php" class="btn-back" style="display: block; text-align: center; margin-top: 15px;">← Hủy và quay lại danh sách</a>
    </form>
</div>

</body>
</html>