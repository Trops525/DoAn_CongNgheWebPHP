-- Tắt kiểm tra khóa ngoại để tránh lỗi #1217 khi DROP TABLE
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tạo Database mới
CREATE DATABASE IF NOT EXISTS `shop_thoitrang` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `shop_thoitrang`;

-- --------------------------------------------------------
-- 2. Bảng TÀI KHOẢN (tbl_nguoidung)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_nguoidung`;
CREATE TABLE IF NOT EXISTS `tbl_nguoidung` (
  `IdNguoiDung` int(10) NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TenDangNhap` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `MatKhau` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `DienThoai` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `QuyenHan` tinyint(1) NOT NULL DEFAULT '2', -- 1: Admin, 2: Khách hàng, 3: Nhân viên
  `Khoa` tinyint(1) NOT NULL DEFAULT '0',     -- 0: Hoạt động, 1: Bị khóa
  PRIMARY KEY (`IdNguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `tbl_nguoidung` (`HoTen`, `TenDangNhap`, `MatKhau`, `Email`, `DienThoai`, `QuyenHan`, `Khoa`) VALUES
('Quản trị viên', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@quality.com', '0999999999', 1, 0),
('Tạ Hồng Quân', 'Quan', 'e10adc3949ba59abbe56e057f20f883e', 'quan.khachhang@gmail.com', '0888888888', 2, 0),
('Trần Đăng Khoa', 'Khoa', 'e10adc3949ba59abbe56e057f20f883e', 'khoa.nhanvien@gmail.com', '0888888889', 3, 0),
('Nhân viên Kho', 'nvkho', 'e10adc3949ba59abbe56e057f20f883e', 'nvkho@quality.com', '0901000001', 3, 0),
('Nhân viên CSKH', 'nvcskh', 'e10adc3949ba59abbe56e057f20f883e', 'nvcskh@quality.com', '0901000002', 4, 0),
('Nhân viên Tài chính', 'nvtaichinh', 'e10adc3949ba59abbe56e057f20f883e', 'nvtaichinh@quality.com', '0901000003', 5, 0),
('Nguyễn Thị Mai', 'mainguyen', 'e10adc3949ba59abbe56e057f20f883e', 'mai.nguyen@gmail.com', '0934567890', 2, 0);


-- --------------------------------------------------------
-- 3. Bảng DANH MỤC (tbl_danhmuc)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_danhmuc`;
CREATE TABLE IF NOT EXISTS `tbl_danhmuc` (
  `IdDanhMuc` int(10) NOT NULL AUTO_INCREMENT,
  `TenDanhMuc` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `MoTa` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IdDanhMuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `tbl_danhmuc` (`IdDanhMuc`, `TenDanhMuc`, `MoTa`, `Khoa`) VALUES 
(1, 'Áo Thun Nam', 'Áo thun nam chất liệu 100% Cotton thoáng mát, co giãn 4 chiều, thiết kế basic hiện đại.', 0),
(2, 'Quần Short Nam', 'Quần short nam trẻ trung, năng động, phong cách thể thao và dạo phố thoải mái.', 0),
(3, 'Áo Sơ Mi Nam', 'Áo sơ mi nam công sở, thiết kế thanh lịch, chống nhăn, tôn dáng phái mạnh.', 0),
(4, 'Áo Polo Nam', 'Áo polo nam lịch lãm, vải cá sấu dệt cao cấp, mềm mịn và thấm hút mồ hôi tối ưu.', 0),
(5, 'Quần Dài Nam', 'Quần âu tây, quần kaki, jogger dáng ôm thời thượng, chỉn chu mọi góc nhìn.', 0),
(6, 'Áo Thun / Áo Thể Thao Nữ', 'Áo thun nữ, croptop và đồ tập thể thao tôn dáng, co giãn linh hoạt, thoáng mát.', 0),
(7, 'Váy / Đầm Nữ', 'Váy đầm nữ thời trang, đầm xòe, đầm suông, maxi dạo phố và dự tiệc duyên dáng.', 0),
(8, 'Quần Dài / Short Nữ', 'Quần dài, short nữ kiểu dáng baggy, ống rộng bắt kịp xu hướng thời trang trẻ.', 0),
(9, 'Phụ Kiện', 'Phụ kiện cao cấp: nón lưỡi trai, nón bucket, thắt lưng, tất vớ tạo điểm nhấn cá tính.', 0),
(10, 'Áo Khoác Nữ', 'Áo khoác gió thể thao, áo khoác track jacket, hoodie và áo khoác studio nữ cản gió, giữ ấm và thời trang.', 0);

-- --------------------------------------------------------
-- 4. Bảng SẢN PHẨM (tbl_sanpham)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_sanpham`;
CREATE TABLE IF NOT EXISTS `tbl_sanpham` (
  `IdSanPham` int(10) NOT NULL AUTO_INCREMENT,
  `MaSanPham` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `TenSanPham` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `IdDanhMuc` int(10) NOT NULL,
  `GioiTinh` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `HinhAnh` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `DonGia` int(10) NOT NULL,
  `TiLeGiamGia` int(4) NOT NULL DEFAULT '0',
  `MoTa` text COLLATE utf8_unicode_ci NOT NULL,
  `LuotMua` int(11) NOT NULL DEFAULT '0',
  `LuotXem` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdSanPham`),
  KEY `IdDanhMuc` (`IdDanhMuc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 5. Bảng BIẾN THỂ (tbl_bienthe)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_bienthe`;
CREATE TABLE IF NOT EXISTS `tbl_bienthe` (
  `IdBienThe` int(10) NOT NULL AUTO_INCREMENT,
  `IdSanPham` int(10) NOT NULL,
  `MauSac` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `KichThuoc` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `SoLuong` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdBienThe`),
  KEY `IdSanPham` (`IdSanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 6. Bảng ĐƠN HÀNG CHUNG (tbl_donhang)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_donhang`;
CREATE TABLE IF NOT EXISTS `tbl_donhang` (
  `IdDonHang` int(10) NOT NULL AUTO_INCREMENT,
  `IdNguoiDung` int(10) NOT NULL,
  `IdNhanVienTao` int(10),
  `NgayDat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TongTien` int(12) NOT NULL DEFAULT 0,
  `DiaChi` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DienThoai` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `TinhTrang` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Đang xử lý',
  `PhuongThucThanhToan` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'COD',
  `GhiChu` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`IdDonHang`),
  KEY `IdNguoiDung` (`IdNguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 7. Bảng CHI TIẾT ĐƠN HÀNG (tbl_chitietdonhang)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `tbl_chitietdonhang`;
CREATE TABLE IF NOT EXISTS `tbl_chitietdonhang` (
  `IdChiTiet` int(10) NOT NULL AUTO_INCREMENT,
  `IdDonHang` int(10) NOT NULL,
  `IdSanPham` int(10) NOT NULL,
  `KichThuoc` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SoLuong` int(10) NOT NULL,
  `DonGia` int(12) NOT NULL,
  `TongTien` int(12) NOT NULL,
  PRIMARY KEY (`IdChiTiet`),
  KEY `IdDonHang` (`IdDonHang`),
  KEY `IdSanPham` (`IdSanPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- --------------------------------------
-- Bang  CSHK
-- --------------------------------------
DROP TABLE IF EXISTS `tbl_cskh_faq`;
CREATE TABLE IF NOT EXISTS `tbl_cskh_faq` (
  `IdFAQ` int(11) NOT NULL AUTO_INCREMENT,
  `DanhMuc` varchar(100) NOT NULL, -- Ví dụ: 'Mua hàng', 'Đơn hàng', 'Đổi trả'
  `CauHoi` varchar(255) NOT NULL,
  `TraLoi` text NOT NULL,
  `TrangThai` tinyint(1) DEFAULT 1, -- 1: Hiển thị, 0: Ẩn
  `ThuTu` int(11) DEFAULT 0, -- Để sắp xếp ưu tiên hiển thị
  PRIMARY KEY (`IdFAQ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------
DROP TABLE IF EXISTS `tbl_cskh_chat`;
CREATE TABLE IF NOT EXISTS `tbl_cskh_chat` (
  `IdChat` int(10) NOT NULL AUTO_INCREMENT,
  `IdNguoiDung` int(10) NOT NULL,
  `NguoiGui` tinyint(1) NOT NULL DEFAULT '1',
  `NoiDung` text NOT NULL,
  `ThoiGian` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrangThai` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`IdChat`),
  KEY (`IdNguoiDung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------
-- Danh gia sp
-- -------------------------------------
DROP TABLE IF EXISTS `tbl_danhgia`;
CREATE TABLE IF NOT EXISTS `tbl_danhgia` (
  `IdDanhGia` int(11) NOT NULL AUTO_INCREMENT,
  `IdSanPham` int(11) NOT NULL,
  `IdNguoiDung` int(11) NOT NULL,
  `SoSao` int(11) NOT NULL DEFAULT 5,
  `NoiDung` text NOT NULL,
  `NgayDanhGia` datetime DEFAULT current_timestamp(),
  `Khoa` tinyint(1) NOT NULL DEFAULT 0, -- 0: Hiển thị, 1: Bị ẩn
  PRIMARY KEY (`IdDanhGia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_danhgia` (`IdSanPham`, `IdNguoiDung`, `SoSao`, `NoiDung`, `NgayDanhGia`, `Khoa`) VALUES
(1, 2, 5, 'Quần mặc rất êm và co giãn tốt, form dáng thể thao chuẩn chỉ. Shop tư vấn nhiệt tình, đóng gói hộp chỉn chu!', '2026-11-20 15:30:00', 0),
(2, 7, 5, 'Mua tặng bạn trai, bạn mình khen mặc rất thích! Vải dày dặn, màu trắng tinh khôi trẻ trung, giặt máy không lo xù lông.', '2026-11-21 16:20:00', 0),
(3, 3, 4, 'Form quần đẹp, bo thun vừa vặn không bị cấn chân khi vận động. Giao hàng nhanh, giá cả rất hợp lý.', '2026-11-21 17:45:00', 0),
(4, 2, 5, 'Chất vải thể thao co giãn 4 chiều mềm mát, mặc đi tập gym hay chạy bộ đều cực kỳ thoải mái. Sẽ ủng hộ Quality dài dài!', '2026-11-22 09:50:00', 0),
(25, 3, 5, 'Áo sơ mi vải oxford đứng form, đường may tỉ mỉ, chống nhăn tốt, mặc đi làm rất sang và thanh lịch.', '2026-11-22 10:15:00', 0),
(109, 7, 5, 'Phụ kiện găng tay chống tia UV ôm vừa tay, chất thun lạnh mát rượi khi đi xe máy trời nắng gắt.', '2026-11-22 11:00:00', 0);

INSERT INTO `tbl_cskh_faq` (`DanhMuc`, `CauHoi`, `TraLoi`, `TrangThai`, `ThuTu`) VALUES
('MuaHang', 'Làm thế nào để đặt hàng trên Website?', 'Để đặt hàng, bạn chỉ cần chọn sản phẩm yêu thích, chọn size mong muốn, bấm "Thêm vào giỏ hàng" và điền thông tin thanh toán là hoàn tất nhé!', 1, 1),
('MuaHang', 'Cần nhận gấp đơn hàng trong ngày thì làm như thế nào?', 'Đối với đơn hàng trong nội thành TP. HCM và Hà Nội (giờ hành chính), bạn vui lòng liên hệ hotline để được hỗ trợ giao hàng hỏa tốc.', 1, 2),
('MuaHang', 'Tôi có thể thanh toán bằng phương thức nào?', 'Chúng tôi hỗ trợ thanh toán COD (thanh toán khi nhận hàng), chuyển khoản ngân hàng, và các ví điện tử như Momo, ZaloPay.', 1, 3),
('MuaHang', 'Làm sao để áp dụng mã giảm giá?', 'Khi thanh toán, bạn nhập mã giảm giá vào ô "Mã khuyến mãi" và bấm "Áp dụng" để giảm giá tự động.', 1, 4),
('DonHang', 'Làm sao để biết đơn hàng của tôi đã được xác nhận?', 'Sau khi đặt hàng thành công, hệ thống sẽ tự động chuyển bạn đến trang quản lý Đơn hàng. Bạn có thể theo dõi tiến trình đơn hàng tại đó.', 1, 1),
('DonHang', 'Thời gian giao hàng là bao lâu?', 'Đơn hàng nội thành TP.HCM/Hà Nội: 1-2 ngày. Các tỉnh khác: 3-5 ngày. Thời gian có thể thay đổi tùy vào địa điểm và số lượng hàng.', 1, 2),
('DonHang', 'Tôi có thể hủy đơn hàng không?', 'Bạn có thể hủy đơn hàng trong vòng 24h sau khi đặt. Sau thời gian này, vui lòng liên hệ hotline để được hỗ trợ.', 1, 3),
('DoiTra', 'Tôi có thể đổi hàng trong bao nhiêu ngày?', 'Quality hỗ trợ bạn đổi trả sản phẩm trong vòng 7 ngày kể từ ngày nhận hàng với điều kiện tem mác còn nguyên vẹn.', 1, 1),
('DoiTra', 'Làm thế nào để đổi/trả hàng?', 'Bạn vui lòng gói lại sản phẩm cẩn thận và gửi về địa chỉ kho của Quality. Sau khi nhận được hàng, chúng tôi sẽ xử lý đổi size/màu hoặc hoàn tiền cho bạn.', 1, 2),
('DoiTra', 'Phí đổi trả là bao nhiêu?', 'Đổi size/màu trong cùng sản phẩm: miễn phí. Đổi sang sản phẩm khác hoặc hoàn tiền: phí vận chuyển 2 chiều do khách hàng chịu.', 1, 3),
('DoiTra', 'Sản phẩm bị lỗi thì sao?', 'Nếu sản phẩm bị lỗi do nhà sản xuất, chúng tôi sẽ đổi mới hoàn toàn miễn phí và chịu toàn bộ chi phí vận chuyển.', 1, 4);
-- --------------------------------------------------------
-- 8. NHẬP LÔ HÀNG VÀO BẢNG SẢN PHẨM
-- --------------------------------------------------------
INSERT INTO `tbl_sanpham` (`MaSanPham`, `TenSanPham`, `IdDanhMuc`, `GioiTinh`, `HinhAnh`, `DonGia`, `TiLeGiamGia`, `MoTa`) VALUES
-- QUẦN DÀI NAM
('JOGGER_FLEX1', 'Jogger Thể Thao Flexline Đỏ', 5, 'Nam', 'images/Nam/Jogger thể thao Flexline Active_Red.jpg', 350000, 0, 'Form ôm thể thao'),
('JOGGER_FLEX1', 'Jogger Thể Thao Flexline Trắng', 5, 'Nam', 'images/Nam/Jogger thể thao Flexline Active_White.jpg', 350000, 0, 'Form ôm thể thao'),
('JOGGER_FLEX2', 'Quần Joggers Thể Thao Flexline Đen', 5, 'Nam', 'images/Nam/Quần joggers thể thao nam Flexline Active_Black.jpg', 350000, 0, 'Form ôm thể thao'),
('JOGGER_FLEX2', 'Quần Joggers Thể Thao Flexline Xanh', 5, 'Nam', 'images/Nam/Quần joggers thể thao nam Flexline Active_Blue.jpg', 350000, 0, 'Form ôm thể thao'),
('KAKI_STRAIGHT', 'Quần Dài Nam Kaki Excool Straight Đen', 5, 'Nam', 'images/Nam/Quần Dài Nam Kaki Excool dáng Straight_Black.jpg', 420000, 10, 'Kaki chống nhăn'),
('KAKI_STRAIGHT', 'Quần Dài Nam Kaki Excool Straight Xám', 5, 'Nam', 'images/Nam/Quần Dài Nam Kaki Excool dáng Straight_Grey.jpg', 420000, 10, 'Kaki chống nhăn'),
('KAKI_EXCOOL', 'Quần Dài Nam Kaki Excool Đen', 5, 'Nam', 'images/Nam/Quần Dài Nam Kaki Excool_Black.jpg', 390000, 0, 'Ống vừa vặn'),
('KAKI_EXCOOL', 'Quần Dài Nam Kaki Excool Xanh', 5, 'Nam', 'images/Nam/Quần Dài Nam Kaki Excool_Blue.jpg', 390000, 0, 'Ống vừa vặn'),
('KAKI_EXCOOL', 'Quần Dài Nam Kaki Excool Xám', 5, 'Nam', 'images/Nam/Quần Dài Nam Kaki Excool_Grey.jpg', 390000, 0, 'Ống vừa vặn'),
('JEAN_BASIC', 'Quần Jeans Nam Basics Straight Đen', 5, 'Nam', 'images/Nam/Quần Jeans Nam Basics dáng Straight_Black.jpg', 499000, 0, 'Jeans nam tính'),
('JEAN_BASIC', 'Quần Jeans Nam Basics Straight Xanh', 5, 'Nam', 'images/Nam/Quần Jeans Nam Basics dáng Straight_Blue.jpg', 499000, 0, 'Jeans nam tính'),
('JOGGER_DAILY', 'Quần Jogger Nam Daily Wear Đen', 5, 'Nam', 'images/Nam/Quần Jogger Nam Daily Wear.jpg', 320000, 0, 'Mặc hàng ngày cực thoải mái'),
-- QUẦN SHORT NAM
('SHORT_PROMAX', 'Quần Short Nam Thể Thao Promax-S1 Đen', 2, 'Nam', 'images/Nam/Quần Short Nam Thể Thao Promax-S1_Black.jpg', 250000, 0, 'Ngắn vừa phải, co giãn tốt'),
('SHORT_PROMAX', 'Quần Short Nam Thể Thao Promax-S1 Xanh', 2, 'Nam', 'images/Nam/Quần Short Nam Thể Thao Promax-S1_Blue.jpg', 250000, 0, 'Ngắn vừa phải, co giãn tốt'),
('SHORT_KAKI', 'Quần Shorts Nam Kaki Excool Be', 2, 'Nam', 'images/Nam/Quần Shorts Nam Kaki Excool_Be.jpg', 280000, 0, 'Mặc đi chơi cực chất'),
('SHORT_KAKI', 'Quần Shorts Nam Kaki Excool Đen', 2, 'Nam', 'images/Nam/Quần Shorts Nam Kaki Excool_Black.jpg', 280000, 0, 'Mặc đi chơi cực chất'),
('SHORT_KAKI', 'Quần Shorts Nam Kaki Excool Xanh', 2, 'Nam', 'images/Nam/Quần Shorts Nam Kaki Excool_Blue.jpg', 280000, 0, 'Mặc đi chơi cực chất'),
('SHORT_EXDRY', 'Quần Shorts Nam Logo Basic Exdry Đen', 2, 'Nam', 'images/Nam/Quần shorts nam logo basic Exdry ProActive World Cup_Black.jpg', 199000, 0, 'Siêu nhẹ, thoát mồ hôi'),
('SHORT_EXDRY', 'Quần Shorts Nam Logo Basic Exdry Xanh', 2, 'Nam', 'images/Nam/Quần shorts nam logo basic Exdry ProActive World Cup_Blue.jpg', 199000, 0, 'Siêu nhẹ, thoát mồ hôi'),
('SHORT_EXDRY', 'Quần Shorts Nam Logo Basic Exdry Trắng', 2, 'Nam', 'images/Nam/Quần shorts nam logo basic Exdry ProActive World Cup_White.jpg', 199000, 0, 'Siêu nhẹ, thoát mồ hôi'),
('SHORT_SUMMER', 'Quần Shorts Summer Cool 7inch Đen', 2, 'Nam', 'images/Nam/Quần Shorts Summer Cool 7inch 2 lớp_Black.jpg', 220000, 0, 'Có quần lót bảo vệ bên trong'),
('SHORT_SUMMER', 'Quần Shorts Summer Cool 7inch Xanh', 2, 'Nam', 'images/Nam/Quần Shorts Summer Cool 7inch 2 lớp_Blue.jpg', 220000, 0, 'Có quần lót bảo vệ bên trong'),
('SHORT_SUMMER', 'Quần Shorts Summer Cool 7inch Trắng', 2, 'Nam', 'images/Nam/Quần Shorts Summer Cool 7inch 2 lớp_White.jpg', 220000, 0, 'Có quần lót bảo vệ bên trong'),
('SHORT_MOVING', 'Quần Shorts Thể Thao 5 Moving Be', 2, 'Nam', 'images/Nam/Quần Shorts Thể Thao 5 Moving_Be.jpg', 210000, 0, 'Cho các bài tập Cardio'),
('SHORT_MOVING', 'Quần Shorts Thể Thao 5 Moving Đen', 2, 'Nam', 'images/Nam/Quần Shorts Thể Thao 5 Moving_Black.jpg', 210000, 0, 'Cho các bài tập Cardio'),
('SHORT_MOVING', 'Quần Shorts Thể Thao 5 Moving Xanh', 2, 'Nam', 'images/Nam/Quần Shorts Thể Thao 5 Moving_Blue.jpg', 210000, 0, 'Cho các bài tập Cardio'),
('SHORT_ULTRA', 'Quần Thể Thao Nam 7 Ultra Shorts Đen', 2, 'Nam', 'images/Nam/Quần Thể Thao Nam 7 Ultra Shorts_Black.jpg', 250000, 0, 'Bền bỉ, chống xước'),
('SHORT_ULTRA', 'Quần Thể Thao Nam 7 Ultra Shorts Xanh', 2, 'Nam', 'images/Nam/Quần Thể Thao Nam 7 Ultra Shorts_Blue.jpg', 250000, 0, 'Bền bỉ, chống xước'),
('SHORT_ULTRA', 'Quần Thể Thao Nam 7 Ultra Shorts Xanh Lá', 2, 'Nam', 'images/Nam/Quần Thể Thao Nam 7 Ultra Shorts_Green.jpg', 250000, 0, 'Bền bỉ, chống xước'),
-- ÁO POLO NAM
('POLO_CAFE', 'Áo Polo Nam Cafe Đen', 4, 'Nam', 'images/Nam/Áo Polo Nam Cafe_Black.jpg', 350000, 0, 'Sợi nilon bã cafe khử mùi'),
('POLO_CAFE', 'Áo Polo Nam Cafe Xanh', 4, 'Nam', 'images/Nam/Áo Polo Nam Cafe_Blue.jpg', 350000, 0, 'Sợi nilon bã cafe khử mùi'),
('POLO_CAFE', 'Áo Polo Nam Cafe Trắng', 4, 'Nam', 'images/Nam/Áo Polo Nam Cafe_White.jpg', 350000, 0, 'Sợi nilon bã cafe khử mùi'),
('POLO_PIQUE', 'Áo Polo Nam Pique Cotton Đen', 4, 'Nam', 'images/Nam/Áo Polo Nam Pique Cotton_Black.jpg', 299000, 10, 'Cotton cá sấu thoáng mát'),
('POLO_PIQUE', 'Áo Polo Nam Pique Cotton Xanh', 4, 'Nam', 'images/Nam/Áo Polo Nam Pique Cotton_Blue.jpg', 299000, 10, 'Cotton cá sấu thoáng mát'),
('POLO_PIQUE', 'Áo Polo Nam Pique Cotton Trắng', 4, 'Nam', 'images/Nam/Áo Polo Nam Pique Cotton_White.jpg', 299000, 10, 'Cotton cá sấu thoáng mát'),
('POLO_PROMAX', 'Áo Polo Nam Thể Thao Promax-S1 Đen', 4, 'Nam', 'images/Nam/Áo Polo Nam Thể Thao Promax-S1_Black.jpg', 320000, 0, 'Chất liệu thể thao mỏng nhẹ'),
('POLO_PROMAX', 'Áo Polo Nam Thể Thao Promax-S1 Xanh', 4, 'Nam', 'images/Nam/Áo Polo Nam Thể Thao Promax-S1_Blue.jpg', 320000, 0, 'Chất liệu thể thao mỏng nhẹ'),
('POLO_PROMAX', 'Áo Polo Nam Thể Thao Promax-S1 Trắng', 4, 'Nam', 'images/Nam/Áo Polo Nam Thể Thao Promax-S1_White.jpg', 320000, 0, 'Chất liệu thể thao mỏng nhẹ'),
-- ÁO SƠ MI NAM
('SOMI_ESS', 'Áo Sơ Mi Dài Tay Essentials Cotton Đen', 3, 'Nam', 'images/Nam/Áo Sơ Mi Dài Tay Essentials Cotton_Black.jpg', 450000, 0, 'Form tiêu chuẩn thanh lịch'),
('SOMI_ESS', 'Áo Sơ Mi Dài Tay Essentials Cotton Xanh', 3, 'Nam', 'images/Nam/Áo Sơ Mi Dài Tay Essentials Cotton_Blue.jpg', 450000, 0, 'Form tiêu chuẩn thanh lịch'),
('SOMI_ESS', 'Áo Sơ Mi Dài Tay Essentials Cotton Trắng', 3, 'Nam', 'images/Nam/Áo Sơ Mi Dài Tay Essentials Cotton_White.jpg', 450000, 0, 'Form tiêu chuẩn thanh lịch'),
('SOMI_MODAL', 'Áo Sơ Mi Dài Tay Modal Essential Đen', 3, 'Nam', 'images/Nam/Áo sơ mi dài tay Modal Essential_Black.jpg', 490000, 15, 'Vải sợi sồi Modal siêu mềm'),
('SOMI_MODAL', 'Áo Sơ Mi Dài Tay Modal Essential Xanh', 3, 'Nam', 'images/Nam/Áo sơ mi dài tay Modal Essential_Blue.jpg', 490000, 15, 'Vải sợi sồi Modal siêu mềm'),
('SOMI_MODAL', 'Áo Sơ Mi Dài Tay Modal Essential Trắng', 3, 'Nam', 'images/Nam/Áo sơ mi dài tay Modal Essential_White.jpg', 490000, 15, 'Vải sợi sồi Modal siêu mềm'),
('SOMI_FLAN', 'Áo Sơ Mi Flannel 100 Cotton Be', 3, 'Nam', 'images/Nam/Áo sơ mi Flannel 100 Cotton_Be.jpg', 399000, 0, 'Caro phong cách đường phố'),
('SOMI_FLAN', 'Áo Sơ Mi Flannel 100 Cotton Đen', 3, 'Nam', 'images/Nam/Áo sơ mi Flannel 100 Cotton_Black.jpg', 399000, 0, 'Caro phong cách đường phố'),
('SOMI_FLAN', 'Áo Sơ Mi Flannel 100 Cotton Đỏ', 3, 'Nam', 'images/Nam/Áo sơ mi Flannel 100 Cotton_Red.jpg', 399000, 0, 'Caro phong cách đường phố'),
-- ÁO THUN NAM
('THUN_JACQ', 'Áo Thun Cổ V Jacquard Exdry Be', 1, 'Nam', 'images/Nam/Áo thun cổ V Jacquard Exdry ProActive World Cup_Be.jpg', 250000, 0, 'Vải Exdry khô nhanh'),
('THUN_JACQ', 'Áo Thun Cổ V Jacquard Exdry Đen', 1, 'Nam', 'images/Nam/Áo thun cổ V Jacquard Exdry ProActive World Cup_Black.jpg', 250000, 0, 'Vải Exdry khô nhanh'),
('THUN_JACQ', 'Áo Thun Cổ V Jacquard Exdry Đỏ', 1, 'Nam', 'images/Nam/Áo thun cổ V Jacquard Exdry ProActive World Cup_Red.jpg', 250000, 0, 'Vải Exdry khô nhanh'),
('THUN_JACQ', 'Áo Thun Cổ V Jacquard Exdry Trắng', 1, 'Nam', 'images/Nam/Áo thun cổ V Jacquard Exdry ProActive World Cup_White.jpg', 250000, 0, 'Vải Exdry khô nhanh'),
('THUN_GRAPHIC', 'Áo Thun Nam Chạy Bộ Graphic Dot Xanh', 1, 'Nam', 'images/Nam/Áo Thun Nam Chạy Bộ Graphic Dot_Blue.jpg', 199000, 0, 'Áo tập siêu nhẹ'),
('THUN_GRAPHIC', 'Áo Thun Nam Chạy Bộ Graphic Dot Xanh Lá', 1, 'Nam', 'images/Nam/Áo Thun Nam Chạy Bộ Graphic Dot_Green.jpg', 199000, 0, 'Áo tập siêu nhẹ'),
('THUN_GRAPHIC', 'Áo Thun Nam Chạy Bộ Graphic Dot Đỏ', 1, 'Nam', 'images/Nam/Áo Thun Nam Chạy Bộ Graphic Dot_Red.jpg', 199000, 0, 'Áo tập siêu nhẹ'),
('THUN_DINK', 'Áo Thun Pickleball Dinkshot Đen', 1, 'Nam', 'images/Nam/Áo thun Pickleball Dinkshot Essentials_Black.jpg', 220000, 0, 'Chuyên dụng Pickleball'),
('THUN_DINK', 'Áo Thun Pickleball Dinkshot Xanh', 1, 'Nam', 'images/Nam/Áo thun Pickleball Dinkshot Essentials_Blue.jpg', 220000, 0, 'Chuyên dụng Pickleball'),
('THUN_DINK', 'Áo Thun Pickleball Dinkshot Trắng', 1, 'Nam', 'images/Nam/Áo thun Pickleball Dinkshot Essentials_White.jpg', 220000, 0, 'Chuyên dụng Pickleball'),
('SET_PICKLE', 'Đồ Tập Pickleball 1 Đen', 1, 'Nam', 'images/Nam/Đồ tập Pickleball 1_Black.jpg', 450000, 10, 'Nguyên set tập cao cấp'),
('SET_PICKLE', 'Đồ Tập Pickleball 1 Xanh', 1, 'Nam', 'images/Nam/Đồ tập Pickleball 1_Blue.jpg', 450000, 10, 'Nguyên set tập cao cấp'),
('SET_PICKLE', 'Đồ Tập Pickleball 1 Trắng', 1, 'Nam', 'images/Nam/Đồ tập Pickleball 1_White.jpg', 450000, 10, 'Nguyên set tập cao cấp'),
('TSHIRT_FLEX', 'T-shirt Thể Thao Nam FlexLine Đen', 1, 'Nam', 'images/Nam/T-shirt thể thao nam FlexLine Active_Black.jpg', 250000, 0, 'Co giãn 4 chiều'),
('TSHIRT_FLEX', 'T-shirt Thể Thao Nam FlexLine Xanh', 1, 'Nam', 'images/Nam/T-shirt thể thao nam FlexLine Active_Blue.jpg', 250000, 0, 'Co giãn 4 chiều'),
('TSHIRT_FLEX', 'T-shirt Thể Thao Nam FlexLine Xanh Lá', 1, 'Nam', 'images/Nam/T-shirt thể thao nam FlexLine Active_Green.jpg', 250000, 0, 'Co giãn 4 chiều'),
('TSHIRT_PROMAX', 'Tshirt Thể Thao Promax Flexline Xanh', 1, 'Nam', 'images/Nam/Tshirt thể thao Promax Flexline Active_Blue.jpg', 280000, 0, 'Mẫu mới cực chất'),
('TSHIRT_PROMAX', 'Tshirt Thể Thao Promax Flexline Đỏ', 1, 'Nam', 'images/Nam/Tshirt thể thao Promax Flexline Active_Red.jpg', 280000, 0, 'Mẫu mới cực chất'),
('TSHIRT_PROMAX', 'Tshirt Thể Thao Promax Flexline Trắng', 1, 'Nam', 'images/Nam/Tshirt thể thao Promax Flexline Active_White.jpg', 280000, 0, 'Mẫu mới cực chất'),
-- ÁO NỮ
('CROP_EXDRY', 'Áo Crop Cổ V Exdry Be', 6, 'Nữ', 'images/Nu/Áo crop cổ V Exdry ProActive World Cup_Be.jpg', 199000, 0, 'Áo crop khoe eo thon'),
('CROP_EXDRY', 'Áo Crop Cổ V Exdry Xanh Lá', 6, 'Nữ', 'images/Nu/Áo crop cổ V Exdry ProActive World Cup_Green.jpg', 199000, 0, 'Áo crop khoe eo thon'),
('CROP_EXDRY', 'Áo Crop Cổ V Exdry Trắng', 6, 'Nữ', 'images/Nu/Áo crop cổ V Exdry ProActive World Cup_White.jpg', 199000, 0, 'Áo crop khoe eo thon'),
('LONG_VITAL', 'Áo Dài Tay Thể Thao Vital Seamless Đen', 6, 'Nữ', 'images/Nu/Áo dài tay thể thao Vital Seamless_Black.jpg', 299000, 0, 'Ôm sát body, giữ nhiệt'),
('LONG_VITAL', 'Áo Dài Tay Thể Thao Vital Seamless Hồng', 6, 'Nữ', 'images/Nu/Áo dài tay thể thao Vital Seamless_Pink.jpg', 299000, 0, 'Ôm sát body, giữ nhiệt'),
('JACKET_TRACK', 'Áo Khoác Nữ Track Jacket Be', 10, 'Nữ', 'images/Nu/Áo khoác nữ Track Jacket Windbreaker khoá kéo_Be.jpg', 550000, 15, 'Chống gió cực đỉnh'),
('JACKET_TRACK', 'Áo Khoác Nữ Track Jacket Đen', 10, 'Nữ', 'images/Nu/Áo khoác nữ Track Jacket Windbreaker khoá kéo_Black.jpg', 550000, 15, 'Chống gió cực đỉnh'),
('JACKET_TRACK', 'Áo Khoác Nữ Track Jacket Xanh Lá', 10, 'Nữ', 'images/Nu/Áo khoác nữ Track Jacket Windbreaker khoá kéo_Green.jpg', 550000, 15, 'Chống gió cực đỉnh'),
('JACKET_STUDIO', 'Áo Khoác Thể Thao Nữ Studio Be', 10, 'Nữ', 'images/Nu/Áo khoác thể thao nữ Studio_Be.jpg', 450000, 0, 'Thời trang tập Yoga/Gym'),
('JACKET_STUDIO', 'Áo Khoác Thể Thao Nữ Studio Xanh', 10, 'Nữ', 'images/Nu/Áo khoác thể thao nữ Studio_Blue.jpg', 450000, 0, 'Thời trang tập Yoga/Gym'),
('POLO_NU_PRO', 'Áo Polo Thể Thao Nữ Promax Đen', 6, 'Nữ', 'images/Nu/Áo Polo thể thao nữ Promax Exdry_Black.jpg', 280000, 0, 'Phù hợp Golf, Tennis'),
('POLO_NU_PRO', 'Áo Polo Thể Thao Nữ Promax Xanh', 6, 'Nữ', 'images/Nu/Áo Polo thể thao nữ Promax Exdry_Blue.jpg', 280000, 0, 'Phù hợp Golf, Tennis'),
('POLO_NU_PRO', 'Áo Polo Thể Thao Nữ Promax Trắng', 6, 'Nữ', 'images/Nu/Áo Polo thể thao nữ Promax Exdry_White.jpg', 280000, 0, 'Phù hợp Golf, Tennis'),
('THUN_CO_TRON', 'Áo Thun Cổ Tròn Exdry Đen', 6, 'Nữ', 'images/Nu/Áo thun cổ tròn Exdry ProActive World Cup_Black.jpg', 180000, 0, 'Thoáng mát, dễ phối đồ'),
('THUN_CO_TRON', 'Áo Thun Cổ Tròn Exdry Xanh', 6, 'Nữ', 'images/Nu/Áo thun cổ tròn Exdry ProActive World Cup_Blue.jpg', 180000, 0, 'Thoáng mát, dễ phối đồ'),
('THUN_CO_TRON', 'Áo Thun Cổ Tròn Exdry Đỏ', 6, 'Nữ', 'images/Nu/Áo thun cổ tròn Exdry ProActive World Cup_Red.jpg', 180000, 0, 'Thoáng mát, dễ phối đồ'),
('THUN_COMPACT', 'Áo Thun Cotton Compact 7th Đen', 6, 'Nữ', 'images/Nu/Áo thun Cotton Compact_Black.jpg', 220000, 0, 'Cotton siêu mát'),
('THUN_COMPACT', 'Áo Thun Cotton Compact 7th Trắng', 6, 'Nữ', 'images/Nu/Áo thun Cotton Compact_White.jpg', 220000, 0, 'Cotton siêu mát'),
('THUN_CORE_TEE', 'Áo Thun Nữ Chạy Bộ Core Tee Slim Đen', 6, 'Nữ', 'images/Nu/Áo thun nữ chạy bộ Core Tee Slim_Black.jpg', 210000, 0, 'Dáng slim fit ôm gọn'),
('THUN_CORE_TEE', 'Áo Thun Nữ Chạy Bộ Core Tee Slim Xám', 6, 'Nữ', 'images/Nu/Áo thun nữ chạy bộ Core Tee Slim_Grey.jpg', 210000, 0, 'Dáng slim fit ôm gọn'),
('THUN_CORE_TEE', 'Áo Thun Nữ Chạy Bộ Core Tee Slim Đỏ', 6, 'Nữ', 'images/Nu/Áo thun nữ chạy bộ Core Tee Slim_Red.jpg', 210000, 0, 'Dáng slim fit ôm gọn'),
('THUN_COOLMATE', 'Áo T-shirt Chạy Bộ Light Weight Đen', 6, 'Nữ', 'images/Nu/NTOXCOOLMATE Áo T-shirt chạy bộ Light Weight_Black.jpg', 190000, 0, 'Hợp tác độc quyền'),
('THUN_COOLMATE', 'Áo T-shirt Chạy Bộ Light Weight Xanh Lá', 6, 'Nữ', 'images/Nu/NTOXCOOLMATE Áo T-shirt chạy bộ Light Weight_Green.jpg', 190000, 0, 'Hợp tác độc quyền'),
('TSHIRT_QUICK', 'Tshirt Thể Thao Nữ Quick Dry Tight Đen', 6, 'Nữ', 'images/Nu/Tshirt thể thao nữ Quick Dry Tight_Black.jpg', 250000, 0, 'Mau khô, không mùi'),
('TSHIRT_QUICK', 'Tshirt Thể Thao Nữ Quick Dry Tight Đỏ', 6, 'Nữ', 'images/Nu/Tshirt thể thao nữ Quick Dry Tight_Red.jpg', 250000, 0, 'Mau khô, không mùi'),
('TSHIRT_QUICK', 'Tshirt Thể Thao Nữ Quick Dry Tight Trắng', 6, 'Nữ', 'images/Nu/Tshirt thể thao nữ Quick Dry Tight_White.jpg', 250000, 0, 'Mau khô, không mùi'),
-- QUẦN DÀI / SHORT NỮ
('PANT_TRACK', 'Quần Dài Nữ Track Pants Windbreaker Be', 8, 'Nữ', 'images/Nu/Quần dài nữ Track Pants Windbreaker_Be.jpg', 380000, 0, 'Phong cách thể thao đường phố'),
('PANT_TRACK', 'Quần Dài Nữ Track Pants Windbreaker Đen', 8, 'Nữ', 'images/Nu/Quần dài nữ Track Pants Windbreaker_Black.jpg', 380000, 0, 'Phong cách thể thao đường phố'),
('PANT_TRACK', 'Quần Dài Nữ Track Pants Windbreaker Xanh Lá', 8, 'Nữ', 'images/Nu/Quần dài nữ Track Pants Windbreaker_Green.jpg', 380000, 0, 'Phong cách thể thao đường phố'),
('JEAN_NU1', 'Quần Jean Nữ Ống Suông', 8, 'Nữ', 'images/Nu/Quần jean ống suông.jpg', 450000, 0, 'Che khuyết điểm chân'),
('JEAN_NU2', 'Quần Jean Xanh Phối Chỉ Xám', 8, 'Nữ', 'images/Nu/Quần jean xanh phối chỉ xám.jpg', 480000, 0, 'Thiết kế độc đáo'),
('QUAN_SUONG', 'Quần Suông Xếp Ly Nữ', 8, 'Nữ', 'images/Nu/Quần suông xếp ly.jpg', 350000, 0, 'Mặc đi làm hay đi chơi đều hợp'),
('SHORT_NU_EXDRY', 'Quần Shorts Nữ Exdry Đen', 8, 'Nữ', 'images/Nu/Quần shorts nữ Exdry ProActive World Cup_Black.jpg', 190000, 0, 'Quần tập cạp cao'),
('SHORT_NU_EXDRY', 'Quần Shorts Nữ Exdry Xanh', 8, 'Nữ', 'images/Nu/Quần shorts nữ Exdry ProActive World Cup_Blue.jpg', 190000, 0, 'Quần tập cạp cao'),
('SHORT_NU_EXDRY', 'Quần Shorts Nữ Exdry Trắng', 8, 'Nữ', 'images/Nu/Quần shorts nữ Exdry ProActive World Cup_White.jpg', 190000, 0, 'Quần tập cạp cao'),
-- VÁY ĐẦM NỮ
('VAY_TIEC', 'Váy Đi Tiệc Xanh Nhạt', 7, 'Nữ', 'images/Nu/Váy đi tiệc.jpg', 650000, 20, 'Váy liền thân sang trọng'),
('VAY_TIEC2', 'Váy Đi Tiệc Cổ Chữ V', 7, 'Nữ', 'images/Nu/Váy đi tiệc2.jpg', 680000, 20, 'Thanh lịch và nổi bật'),
('VAY_PICKLE1', 'Váy Pickleball LunaFlex Xanh', 7, 'Nữ', 'images/Nu/Váy pickleball LunaFlex Exdry_Blue.jpg', 390000, 0, 'Váy thể thao kèm quần an toàn'),
('VAY_PICKLE1', 'Váy Pickleball LunaFlex Trắng', 7, 'Nữ', 'images/Nu/Váy pickleball LunaFlex Exdry_White.jpg', 390000, 0, 'Váy thể thao kèm quần an toàn'),
('VAY_PICKLE2', 'Váy Polo Pickleball Dink Shot Đen', 7, 'Nữ', 'images/Nu/Váy Polo Pickleball Dink Shot_Black.jpg', 420000, 0, 'Cổ polo thể thao chuyên nghiệp'),
('VAY_PICKLE2', 'Váy Polo Pickleball Dink Shot Xanh', 7, 'Nữ', 'images/Nu/Váy Polo Pickleball Dink Shot_Blue.jpg', 420000, 0, 'Cổ polo thể thao chuyên nghiệp'),
('VAY_PICKLE2', 'Váy Polo Pickleball Dink Shot Trắng', 7, 'Nữ', 'images/Nu/Váy Polo Pickleball Dink Shot_White.jpg', 420000, 0, 'Cổ polo thể thao chuyên nghiệp'),
-- PHỤ KIỆN
('PK_BOCHAN', 'Bó Chân Essentials Coolmate', 9, 'Unisex', 'images/PhuKien/Bó chân Essentials Coolmate.jpg', 150000, 0, 'Bảo vệ cơ bắp khi chạy bộ'),
('PK_BAG1', 'Coolmate Sporty Bag', 9, 'Unisex', 'images/PhuKien/Coolmate Sporty Bag.jpg', 299000, 0, 'Túi thể thao chống nước'),
('PK_GANG', 'Găng Tay Chống Tia UV', 9, 'Unisex', 'images/PhuKien/Găng tay chống tia UV.jpg', 120000, 0, 'Bảo vệ da dưới trời nắng gắt'),
('PK_KHAN', 'Khăn Tập Gym Siêu Thấm', 9, 'Unisex', 'images/PhuKien/Khăn Tập Gym.jpg', 99000, 0, 'Vải cotton thấm hút mồ hôi'),
('PK_MU', 'Mũ Lưỡi Trai Dáng Thể Thao', 9, 'Unisex', 'images/PhuKien/Mũ Lưỡi Trai Dáng Thể Thao.jpg', 150000, 0, 'Form đứng, khóa điều chỉnh size'),
('PK_TAT1', 'Tất Cổ Trung Cầu Lông', 9, 'Unisex', 'images/PhuKien/Tất cổ trung cầu lông.jpg', 50000, 0, 'Đệm xù giảm xóc cực tốt'),
('PK_TAT2', 'Tất Pickleball Cổ Trung Court Black', 9, 'Unisex', 'images/PhuKien/Tất Pickleball Cổ Trung Court Black.jpg', 60000, 0, 'Bám giày, chống trượt'),
('PK_TUIHONG', 'Túi Đeo Hông Đa Năng', 9, 'Unisex', 'images/PhuKien/Túi đeo hông đa năng.jpg', 250000, 0, 'Đựng điện thoại, chìa khóa khi chạy'),
('PK_TOTE', 'Túi Tote Bag Disney Spring', 9, 'Unisex', 'images/PhuKien/Túi Tote bag Disney Spring Blessing.jpg', 180000, 0, 'Họa tiết dễ thương, tiện lợi'),
('PK_TUITRONG1', 'Túi Trống Tập Gym Đen', 9, 'Unisex', 'images/PhuKien/Túi trống Tập Gym.jpg', 350000, 0, 'Ngăn chứa rộng rãi, khóa zip mượt'),
('PK_TUITRONG2', 'Túi UT Duffle Size Vừa 18L', 9, 'Unisex', 'images/PhuKien/Túi UT Duffle size vừa 18L.jpg', 420000, 0, 'Đựng đồ du lịch ngắn ngày');

-- --------------------------------------------------------
-- 9. NHẬP LIỆU BIẾN THỂ TỰ ĐỘNG (CROSS JOIN)
-- --------------------------------------------------------
-- Biến thể quần áo thời trang (Size S, M, L, XL, XXL)
INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong)
SELECT 
  IdSanPham, 
  SUBSTRING_INDEX(TenSanPham, ' ', -1) AS MauSac,
  Size.KichThuoc,
  10 AS SoLuong
FROM tbl_sanpham
CROSS JOIN (
  SELECT 'S' AS KichThuoc UNION ALL
  SELECT 'M' UNION ALL
  SELECT 'L' UNION ALL
  SELECT 'XL' UNION ALL
  SELECT 'XXL'
) AS Size
WHERE IdDanhMuc != 9;

-- Biến thể phụ kiện (Freesize và màu sắc thực tế)
INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong)
SELECT IdSanPham, CASE WHEN TenSanPham LIKE '%Đen%' THEN 'Đen' WHEN TenSanPham LIKE '%Trắng%' THEN 'Trắng' WHEN TenSanPham LIKE '%Đỏ%' THEN 'Đỏ' ELSE 'Xám' END, 'Freesize', 20
FROM tbl_sanpham WHERE IdDanhMuc = 9;

-- --------------------------------------------------------
-- DỮ LIỆU ĐƠN HÀNG VÀ CHI TIẾT ĐƠN HÀNG MẪU
-- --------------------------------------------------------
INSERT INTO `tbl_donhang` (`IdDonHang`, `IdNguoiDung`, `IdNhanVienTao`, `NgayDat`, `TongTien`, `DiaChi`, `DienThoai`, `TinhTrang`, `PhuongThucThanhToan`, `GhiChu`) VALUES
(1, 2, 5, '2026-11-20 09:30:00', 700000, 'Số 123 Đường Nguyễn Trãi, Phường 2, Quận 5, TP. Hồ Chí Minh', '0888888888', 'Đã giao', 'COD', 'Giao hàng trong giờ hành chính'),
(2, 3, 1, '2026-11-21 14:15:00', 700000, 'Tòa Landmark 81, 720A Điện Biên Phủ, Quận Bình Thạnh, TP. Hồ Chí Minh', '0888888889', 'Đang giao', 'Chuyển khoản', 'Gọi trước khi giao hàng 15 phút'),
(3, 7, NULL, '2026-11-22 08:45:00', 700000, 'Số 45 Lê Duẩn, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh', '0934567890', 'Đang xử lý', 'COD', 'Đóng gói hộp quà cẩn thận giúp mình nhé shop'),
(4, 2, 5, '2026-11-22 11:20:00', 350000, 'Số 123 Đường Nguyễn Trãi, Quận 5, TP. Hồ Chí Minh', '0888888888', 'Đang giao', 'Chuyển khoản', 'Đã chuyển khoản qua Vietcombank'),
(5, 3, 5, '2026-11-19 10:00:00', 350000, 'Số 88 Trần Phú, Hà Đông, Hà Nội', '0888888889', 'Yêu cầu trả hàng', 'COD', 'Khách yêu cầu đổi size vì mặc hơi chật'),
(6, 7, NULL, '2026-11-18 16:30:00', 350000, 'Số 45 Lê Duẩn, Quận 1, TP. Hồ Chí Minh', '0934567890', 'Đã hủy', 'COD', 'Khách đổi ý muốn đặt mẫu khác');

INSERT INTO `tbl_chitietdonhang` (`IdDonHang`, `IdSanPham`, `KichThuoc`, `SoLuong`, `DonGia`, `TongTien`) VALUES
(1, 1, 'L', 1, 350000, 350000),
(1, 2, 'XL', 1, 350000, 350000),
(2, 3, 'M', 2, 350000, 700000),
(3, 1, 'M', 1, 350000, 350000),
(3, 4, 'L', 1, 350000, 350000),
(4, 2, 'L', 1, 350000, 350000),
(5, 4, 'M', 1, 350000, 350000),
(6, 3, 'S', 1, 350000, 350000);

-- --------------------------------------------------------
-- DỮ LIỆU CHAT CSKH MẪU
-- --------------------------------------------------------
INSERT INTO `tbl_cskh_chat` (`IdNguoiDung`, `NguoiGui`, `NoiDung`, `ThoiGian`) VALUES
(2, 1, 'Chào shop, mẫu Quần Jogger Flexline màu đỏ còn size L không ạ?', '2026-11-22 09:10:00'),
(2, 2, 'Dạ chào bạn Quân! Mẫu Jogger Flexline Đỏ size L bên shop vẫn còn sẵn hàng bạn nhé.', '2026-11-22 09:12:00'),
(2, 1, 'Mình cao 1m72 nặng 68kg thì mặc size L có vừa vặn không shop?', '2026-11-22 09:14:00'),
(2, 2, 'Dạ với chiều cao cân nặng của bạn thì size L mặc vừa chuẩn form thể thao rất tôn dáng luôn ạ.', '2026-11-22 09:15:30'),
(2, 1, 'Cảm ơn shop nhé, mình vừa bấm đặt đơn hàng qua web rồi đó ạ.', '2026-11-22 09:18:00'),
(2, 2, 'Dạ shop đã nhận được đơn của bạn và đang tiến hành đóng gói để gửi ngay hôm nay ạ. Cảm ơn bạn đã tin tưởng Quality!', '2026-11-22 09:20:00'),
(3, 1, 'Shop ơi cho mình hỏi đơn hàng vận chuyển về khu vực Hà Nội thì bao lâu nhận được?', '2026-11-21 15:00:00'),
(3, 2, 'Dạ chào bạn Khoa, đơn hàng giao tuyến TP.HCM - Hà Nội thời gian vận chuyển tiêu chuẩn từ 2 - 3 ngày làm việc bạn nhé.', '2026-11-21 15:05:00'),
(3, 1, 'Lúc nhận hàng mình có được đồng kiểm xem hàng trước không shop?', '2026-11-21 15:07:00'),
(3, 2, 'Dạ hoàn toàn được ạ! Quality hỗ trợ kiểm tra hàng khi nhận và hỗ trợ đổi size miễn phí trong 7 ngày nếu không vừa form ạ.', '2026-11-21 15:09:00'),
(7, 1, 'Shop ơi shop có gợi ý đầm xòe hoặc áo croptop nào xinh để đi chơi cuối tuần không tư vấn giúp mình với!', '2026-11-22 10:20:00'),
(7, 2, 'Dạ Quality chào bạn Mai! Bên shop hiện đang có mẫu Váy Đầm Xòe Dạo Phố và Áo Thể Thao Nữ chất liệu cotton lạnh cực kỳ mềm mịn, thoáng mát và tôn dáng rất xinh ạ.', '2026-11-22 10:22:30'),
(7, 1, 'Chất vải giặt máy có bị xù lông hay phai màu không shop ơi?', '2026-11-22 10:25:00'),
(7, 2, 'Dạ bạn yên tâm nha, các dòng sản phẩm của Quality đều được xử lý bề mặt chống xù lông và giữ màu bền đẹp qua nhiều lần giặt ạ.', '2026-11-22 10:27:00');

-- --------------------------------------------------------
-- 10. RÀNG BUỘC KHÓA NGOẠI (FOREIGN KEYS)
-- --------------------------------------------------------
ALTER TABLE `tbl_sanpham` ADD CONSTRAINT `fk_sp_dm` FOREIGN KEY (`IdDanhMuc`) REFERENCES `tbl_danhmuc` (`IdDanhMuc`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `tbl_bienthe` ADD CONSTRAINT `fk_bt_sp` FOREIGN KEY (`IdSanPham`) REFERENCES `tbl_sanpham` (`IdSanPham`) ON DELETE CASCADE;
ALTER TABLE `tbl_donhang` ADD CONSTRAINT `fk_dh_nd` FOREIGN KEY (`IdNguoiDung`) REFERENCES `tbl_nguoidung` (`IdNguoiDung`) ON DELETE CASCADE;
ALTER TABLE `tbl_chitietdonhang` ADD CONSTRAINT `fk_ct_dh` FOREIGN KEY (`IdDonHang`) REFERENCES `tbl_donhang` (`IdDonHang`) ON DELETE CASCADE;
ALTER TABLE `tbl_chitietdonhang` ADD CONSTRAINT `fk_ct_sp` FOREIGN KEY (`IdSanPham`) REFERENCES `tbl_sanpham` (`IdSanPham`) ON DELETE CASCADE;

-- Bật lại kiểm tra khóa ngoại sau khi thực thi xong
SET FOREIGN_KEY_CHECKS = 1;