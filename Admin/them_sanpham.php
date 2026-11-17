<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? 2, [1, 3])) {
    header('Location: /Thoi-Trang-Quality/TrangTin/dangnhap.php?next=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. LƯU SẢN PHẨM CHÍNH
    $ma_sp = $ket_noi->real_escape_string($_POST['ma_san_pham']);
    $ten_sp = $ket_noi->real_escape_string($_POST['ten_san_pham']);
    $id_dm = (int)$_POST['id_danh_muc'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $gia_goc = (int)$_POST['gia_goc'];
    $gia_khuyen_mai = (int)$_POST['gia_khuyen_mai'];
    $mo_ta = $ket_noi->real_escape_string($_POST['mo_ta']);

    $ti_le_giam = ($gia_khuyen_mai > 0) ? round((($gia_goc - $gia_khuyen_mai) / $gia_goc) * 100) : 0;

    $hinh_anh_db = ''; 
    
    // Upload file ảnh vào thư mục theo giới tính
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $subdir = ($gioi_tinh == 'Nam') ? 'Nam' : (($gioi_tinh == 'Nữ') ? 'Nu' : 'PhuKien');
        $thu_muc = "../TrangTin/images/" . $subdir . "/";
        $ten_file_anh = time() . '_' . basename($_FILES['hinh_anh']['name']);
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $thu_muc . $ten_file_anh)) {
            $hinh_anh_db = "images/" . $subdir . "/" . $ten_file_anh; 
        }
    }

    $sql_sp = "INSERT INTO tbl_sanpham (MaSanPham, TenSanPham, IdDanhMuc, GioiTinh, HinhAnh, DonGia, TiLeGiamGia, MoTa) 
               VALUES ('$ma_sp', '$ten_sp', $id_dm, '$gioi_tinh', '$hinh_anh_db', $gia_goc, $ti_le_giam, '$mo_ta')";

    if ($ket_noi->query($sql_sp)) {
        $id_vua_tao = $ket_noi->insert_id;

        // 2. LẶP QUA DANH SÁCH SIZE/MÀU ĐỂ LƯU (Đây là phần quan trọng)
        $ds_mau = $_POST['mau_sac']; // Mảng màu
        $ds_size = $_POST['kich_thuoc']; // Mảng size
        $ds_sl = $_POST['so_luong']; // Mảng số lượng

        for ($i = 0; $i < count($ds_mau); $i++) {
            $m = $ket_noi->real_escape_string($ds_mau[$i]);
            $s = $ket_noi->real_escape_string($ds_size[$i]);
            $q = (int)$ds_sl[$i];

            if (!empty($m) && !empty($s)) {
                $sql_bt = "INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong) VALUES ($id_vua_tao, '$m', '$s', $q)";
                $ket_noi->query($sql_bt);
            }
        }
        echo "<script>alert('Thêm thành công sản phẩm và các biến thể!'); window.location.href='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới - Quality Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; padding: 30px 15px; margin: 0; color: #333; }
        .container { background: #fff; padding: 35px; border-radius: 12px; max-width: 860px; margin: 0 auto; box-shadow: 0 5px 25px rgba(0,0,0,0.08); width: 100%; }
        .row { display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap; }
        .col { flex: 1; min-width: 200px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13.5px; color: #495057; }
        input[type="text"], input[type="number"], input[type="file"], select, textarea { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ced4da; 
            border-radius: 6px; 
            font-size: 14px; 
            box-sizing: border-box; 
            transition: border-color 0.15s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .variant-row { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 12px; 
            border: 1px solid #e9ecef; 
            display: flex; 
            gap: 15px; 
            align-items: flex-end; 
            flex-wrap: wrap; 
        }
        .variant-row .col { flex: 1; min-width: 130px; }
        .btn-add-variant { 
            background: #0d6efd; 
            color: white; 
            border: none; 
            padding: 9px 18px; 
            border-radius: 6px; 
            cursor: pointer; 
            margin-bottom: 20px; 
            font-weight: 600; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            font-size: 13.5px; 
            transition: 0.2s;
        }
        .btn-add-variant:hover { background: #0b5ed7; }
        .btn-remove { 
            background: #dc3545; 
            color: white; 
            border: none; 
            padding: 10px 14px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 13px; 
            font-weight: 600; 
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-remove:hover { background: #bb2d3b; }
        .btn-save { 
            background: #198754; 
            color: #fff; 
            padding: 14px; 
            border: none; 
            width: 100%; 
            border-radius: 6px; 
            font-size: 16px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.2s; 
            margin-top: 25px; 
        }
        .btn-save:hover { background: #157347; }
        .btn-back-top { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: #495057; 
            text-decoration: none; 
            font-weight: 600; 
            margin-bottom: 25px; 
            font-size: 14px; 
            padding: 6px 12px;
            background: #e9ecef;
            border-radius: 6px;
            transition: 0.2s;
        }
        .btn-back-top:hover { 
            background: #dee2e6;
            color: #111; 
        }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back-top"><i class="fa fa-arrow-left"></i> Quay lại danh sách sản phẩm</a>

    <form action="" method="POST" enctype="multipart/form-data">
        <h2 style="text-align:center; color: #212529; margin-top: 0; margin-bottom: 25px;">THÊM SẢN PHẨM MỚI</h2>
        
        <div class="row">
            <div class="col"><label>Mã sản phẩm *:</label><input type="text" name="ma_san_pham" placeholder="VD: POLO_CAFE" required></div>
            <div class="col"><label>Tên sản phẩm *:</label><input type="text" name="ten_san_pham" placeholder="VD: Áo Polo Nam Cafe" required></div>
        </div>
        <div class="row">
            <div class="col">
                <label>Danh mục *:</label>
                <select name="id_danh_muc" required>
                    <?php $res = $ket_noi->query("SELECT * FROM tbl_danhmuc"); while($r = $res->fetch_assoc()) echo "<option value='".$r['IdDanhMuc']."'>".$r['TenDanhMuc']."</option>"; ?>
                </select>
            </div>
            <div class="col">
                <label>Giới tính *:</label>
                <select name="gioi_tinh"><option value="Nam">Nam</option><option value="Nữ">Nữ</option><option value="Unisex">Unisex</option></select>
            </div>
        </div>
        <div class="row">
            <div class="col"><label>Giá gốc (đ) *:</label><input type="number" name="gia_goc" placeholder="VD: 350000" required></div>
            <div class="col"><label>Giá khuyến mãi (đ):</label><input type="number" name="gia_khuyen_mai" value="0"></div>
        </div>

        <div style="background: #f8f9fa; padding: 18px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef;">
            <label style="font-weight: 700; margin-bottom: 8px; display: block;">Hình ảnh sản phẩm <span style="color:#dc3545">*</span></label>
            <input type="file" name="hinh_anh" accept="image/*" required style="font-size: 13.5px; width: 100%; background: #fff;">
            <small style="color: #6c757d; display: block; margin-top: 5px;">Hỗ trợ định dạng JPG, PNG, GIF, WebP...</small>
        </div>

        <h3 style="color: #198754; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; font-size: 16px;">
            <i class="fa fa-boxes"></i> Phân loại hàng (Màu sắc & Kích thước)
        </h3>
        <div id="variant-container">
            <div class="variant-row">
                <div class="col"><label>Màu sắc *:</label><input type="text" name="mau_sac[]" placeholder="VD: Đen" required></div>
                <div class="col"><label>Kích thước *:</label><input type="text" name="kich_thuoc[]" placeholder="VD: L" required></div>
                <div class="col"><label>Số lượng tồn *:</label><input type="number" name="so_luong[]" value="10" required></div>
                <button type="button" class="btn-remove" style="visibility: hidden;" tabindex="-1">Xóa</button>
            </div>
        </div>
        <button type="button" class="btn-add-variant" onclick="addVariant()"><i class="fa fa-plus"></i> Thêm phân loại (Màu/Size)</button>

        <div style="margin-top: 15px;">
            <label>Mô tả / Chất liệu sản phẩm:</label>
            <textarea name="mo_ta" rows="3" placeholder="VD: Sợi nilon bã cafe khử mùi, thoáng mát..."></textarea>
        </div>

        <button type="submit" class="btn-save"><i class="fa fa-save"></i> LƯU SẢN PHẨM</button>
    </form>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variant-container');
    const newRow = document.createElement('div');
    newRow.className = 'variant-row';
    newRow.innerHTML = `
        <div class="col"><label>Màu sắc *:</label><input type="text" name="mau_sac[]" placeholder="VD: Trắng" required></div>
        <div class="col"><label>Kích thước *:</label><input type="text" name="kich_thuoc[]" placeholder="VD: XL" required></div>
        <div class="col"><label>Số lượng tồn *:</label><input type="number" name="so_luong[]" value="10" required></div>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Xóa dòng này"><i class="fa fa-trash"></i></button>
    `;
    container.appendChild(newRow);
}
</script>

</body>
</html>