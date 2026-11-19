<?php
require_once __DIR__ . '/../model/database.php';

$ten_may_chu = "localhost";
$tai_khoan = "root";       
$ten_csdl = "shop_thoitrang";

mysqli_report(MYSQLI_REPORT_OFF);

// Thử kết nối không mật khẩu (chuẩn mặc định của XAMPP)
$ket_noi = @new mysqli($ten_may_chu, $tai_khoan, "", $ten_csdl);

// Nếu không được, thử kết nối với mật khẩu 'vertrigo' (dành cho VertrigoServ)
if ($ket_noi->connect_error) {
    $ket_noi = @new mysqli($ten_may_chu, $tai_khoan, "vertrigo", $ten_csdl);
    if ($ket_noi->connect_error) {
        die("Kết nối cơ sở dữ liệu thất bại: " . $ket_noi->connect_error);
    }
}
$ket_noi->set_charset("utf8");

// Đảm bảo trường ghi nhận người xác nhận giao hàng tồn tại
@$ket_noi->query("ALTER TABLE tbl_donhang ADD COLUMN IF NOT EXISTS IdNhanVienTao int(10) NULL");
@$ket_noi->query("ALTER TABLE tbl_sanpham ADD COLUMN IF NOT EXISTS Khoa tinyint(1) NOT NULL DEFAULT 0");
@$ket_noi->query("ALTER TABLE tbl_danhmuc ADD COLUMN IF NOT EXISTS Khoa tinyint(1) NOT NULL DEFAULT 0");
?>
