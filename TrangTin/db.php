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
?>
