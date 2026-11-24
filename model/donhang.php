<?php
require_once __DIR__ . '/database.php';

class DONHANG {
    // 1. Tạo đơn hàng mới từ giỏ hàng
    public function taodonhang($id_nguoidung, $tongtien, $diachi, $dienthoai, $pttt, $ghichu, $items) {
        $db = DATABASE::connect();
        try {
            $db->beginTransaction();

            $sql = "INSERT INTO tbl_donhang (IdNguoiDung, NgayDat, TongTien, DiaChi, DienThoai, TinhTrang, PhuongThucThanhToan, GhiChu) 
                    VALUES (:uid, NOW(), :tongtien, :diachi, :dienthoai, 'Đang xử lý', :pttt, :ghichu)";
            $cmd = $db->prepare($sql);
            $cmd->execute([
                ':uid' => (int)$id_nguoidung,
                ':tongtien' => (int)$tongtien,
                ':diachi' => $diachi,
                ':dienthoai' => $dienthoai,
                ':pttt' => $pttt,
                ':ghichu' => $ghichu
            ]);

            $id_donhang = (int)$db->lastInsertId();

            // Lưu từng chi tiết đơn hàng
            $sql_ct = "INSERT INTO tbl_chitietdonhang (IdDonHang, IdSanPham, KichThuoc, SoLuong, DonGia, TongTien) 
                       VALUES (:iddh, :idsp, :size, :sl, :dongia, :tongtien)";
            $cmd_ct = $db->prepare($sql_ct);

            foreach ($items as $item) {
                $cmd_ct->execute([
                    ':iddh' => $id_donhang,
                    ':idsp' => (int)$item['IdSanPham'],
                    ':size' => $item['size'] ?? 'Freesize',
                    ':sl' => (int)$item['quantity'],
                    ':dongia' => (int)$item['final_price'],
                    ':tongtien' => (int)$item['total_price']
                ]);
            }

            $db->commit();
            return $id_donhang;
        } catch (PDOException $e) {
            $db->rollBack();
            echo "<p>Lỗi tạo đơn hàng: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // 2. Lấy thông tin đơn hàng theo ID
    public function laydonhangtheoid($id) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT dh.*, nd.HoTen, nd.TenDangNhap, nd.Email, nv.HoTen AS NhanVien_HoTen, nv.TenDangNhap AS NhanVien_TenDangNhap 
                    FROM tbl_donhang dh 
                    LEFT JOIN tbl_nguoidung nd ON dh.IdNguoiDung = nd.IdNguoiDung 
                    LEFT JOIN tbl_nguoidung nv ON dh.IdNhanVienTao = nv.IdNguoiDung 
                    WHERE dh.IdDonHang = :id LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->execute([':id' => (int)$id]);
            return $cmd->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. Lấy danh sách sản phẩm trong chi tiết đơn hàng
    public function laychitietdonhang($id_donhang) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT ct.*, sp.TenSanPham, sp.HinhAnh, sp.MaSanPham 
                    FROM tbl_chitietdonhang ct 
                    JOIN tbl_sanpham sp ON ct.IdSanPham = sp.IdSanPham 
                    WHERE ct.IdDonHang = :id";
            $cmd = $db->prepare($sql);
            $cmd->execute([':id' => (int)$id_donhang]);
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 4. Lấy danh sách đơn hàng của khách hàng (active hoặc history)
    public function laydonhangtheonguoidung($id_nguoidung, $tab = 'active') {
        $db = DATABASE::connect();
        try {
            if ($tab === 'history') {
                $sql = "SELECT * FROM tbl_donhang 
                        WHERE IdNguoiDung = :uid 
                        AND TinhTrang IN ('Đã giao', 'Đã hủy', 'Hoàn trả', 'Từ chối trả hàng') 
                        ORDER BY NgayDat DESC";
            } else {
                $sql = "SELECT * FROM tbl_donhang 
                        WHERE IdNguoiDung = :uid 
                        AND TinhTrang IN ('Đang xử lý', 'Đang giao', 'Yêu cầu trả hàng') 
                        ORDER BY NgayDat DESC";
            }
            $cmd = $db->prepare($sql);
            $cmd->execute([':uid' => (int)$id_nguoidung]);
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 5. Đếm số đơn hàng đang hoạt động của khách hàng
    public function demdonhanghoatdong($id_nguoidung) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT COUNT(*) as total FROM tbl_donhang 
                    WHERE IdNguoiDung = :uid 
                    AND TinhTrang IN ('Đang xử lý', 'Đang giao', 'Yêu cầu trả hàng')";
            $cmd = $db->prepare($sql);
            $cmd->execute([':uid' => (int)$id_nguoidung]);
            return (int)($cmd->fetch()['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 6. Lấy toàn bộ đơn hàng (cho Admin)
    public function laytatcadonhang($loc_trangthai = '') {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT dh.*, nd.HoTen, nd.TenDangNhap, 
                           nv.HoTen AS NhanVien_HoTen, nv.TenDangNhap AS NhanVien_TenDangNhap 
                    FROM tbl_donhang dh 
                    LEFT JOIN tbl_nguoidung nd ON dh.IdNguoiDung = nd.IdNguoiDung 
                    LEFT JOIN tbl_nguoidung nv ON dh.IdNhanVienTao = nv.IdNguoiDung";
            if (!empty($loc_trangthai)) {
                $sql .= " WHERE dh.TinhTrang = :tt";
            }
            $sql .= " ORDER BY dh.NgayDat DESC";
            $cmd = $db->prepare($sql);
            if (!empty($loc_trangthai)) {
                $cmd->bindValue(':tt', $loc_trangthai);
            }
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 7. Cập nhật trạng thái và điều chỉnh kho tương ứng
    public function capnhattrangthai($id_donhang, $trangthai_moi, $id_nhanvien = null) {
        $db = DATABASE::connect();
        try {
            $id = (int)$id_donhang;
            $don = $this->laydonhangtheoid($id);
            if (!$don) return false;

            $items = $this->laychitietdonhang($id);

            // A. Xác nhận giao -> TRỪ KHO
            if ($trangthai_moi === 'Đang giao' && $don['TinhTrang'] === 'Đang xử lý') {
                $cmdTruKho = $db->prepare("UPDATE tbl_bienthe SET SoLuong = SoLuong - :sl WHERE IdSanPham = :idsp AND KichThuoc = :size");
                foreach ($items as $item) {
                    $cmdTruKho->execute([
                        ':sl' => (int)$item['SoLuong'],
                        ':idsp' => (int)$item['IdSanPham'],
                        ':size' => $item['KichThuoc']
                    ]);
                }
            }

            // B. Khách xác nhận "Đã nhận hàng" -> TĂNG LƯỢT MUA
            if ($trangthai_moi === 'Đã giao' && $don['TinhTrang'] === 'Đang giao') {
                $cmdTangLuotMua = $db->prepare("UPDATE tbl_sanpham SET LuotMua = LuotMua + :sl WHERE IdSanPham = :idsp");
                foreach ($items as $item) {
                    $cmdTangLuotMua->execute([
                        ':sl' => (int)$item['SoLuong'],
                        ':idsp' => (int)$item['IdSanPham']
                    ]);
                }
            }

            // C. Hoàn trả hàng thành công -> CỘNG LẠI KHO
            if ($trangthai_moi === 'Hoàn trả' && in_array($don['TinhTrang'], ['Yêu cầu trả hàng', 'Đang giao', 'Đã giao'])) {
                $cmdCongKho = $db->prepare("UPDATE tbl_bienthe SET SoLuong = SoLuong + :sl WHERE IdSanPham = :idsp AND KichThuoc = :size");
                foreach ($items as $item) {
                    $cmdCongKho->execute([
                        ':sl' => (int)$item['SoLuong'],
                        ':idsp' => (int)$item['IdSanPham'],
                        ':size' => $item['KichThuoc']
                    ]);
                }
            }

            // Cập nhật tình trạng đơn
            $sql = "UPDATE tbl_donhang SET TinhTrang = :tt";
            $params = [':tt' => $trangthai_moi, ':id' => $id];
            if ($id_nhanvien !== null) {
                $sql .= ", IdNhanVienTao = :nv";
                $params[':nv'] = (int)$id_nhanvien;
            }
            $sql .= " WHERE IdDonHang = :id";
            $cmd = $db->prepare($sql);
            return $cmd->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 8. Xóa đơn hàng (cộng lại kho nếu đơn đã từng xuất giao)
    public function xoadonhang($id_donhang) {
        $db = DATABASE::connect();
        try {
            $id = (int)$id_donhang;
            $don = $this->laydonhangtheoid($id);
            if (!$don) return false;

            $da_tru_kho = ['Đang giao', 'Đã giao', 'Yêu cầu trả hàng', 'Từ chối trả hàng'];
            if (in_array($don['TinhTrang'], $da_tru_kho)) {
                $items = $this->laychitietdonhang($id);
                $cmdCongKho = $db->prepare("UPDATE tbl_bienthe SET SoLuong = SoLuong + :sl WHERE IdSanPham = :idsp AND KichThuoc = :size");
                foreach ($items as $item) {
                    $cmdCongKho->execute([
                        ':sl' => (int)$item['SoLuong'],
                        ':idsp' => (int)$item['IdSanPham'],
                        ':size' => $item['KichThuoc']
                    ]);
                }
            }

            $db->prepare("DELETE FROM tbl_chitietdonhang WHERE IdDonHang = :id")->execute([':id' => $id]);
            return $db->prepare("DELETE FROM tbl_donhang WHERE IdDonHang = :id")->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 9. Thống kê báo cáo (cho trang thongke.php của Admin)
    public function thongkebaochao($thang, $nam) {
        $db = DATABASE::connect();
        try {
            $trang_thai_tinh_tien = "('Đã giao', 'Yêu cầu trả hàng', 'Từ chối trả hàng')";

            // Tổng quan doanh thu & số đơn
            $sql_tq = "SELECT SUM(TongTien) as DoanhThu, COUNT(IdDonHang) as SoDonHang 
                       FROM tbl_donhang 
                       WHERE MONTH(NgayDat) = :m AND YEAR(NgayDat) = :y 
                       AND TinhTrang IN $trang_thai_tinh_tien";
            $cmd_tq = $db->prepare($sql_tq);
            $cmd_tq->execute([':m' => (int)$thang, ':y' => (int)$nam]);
            $tongquan = $cmd_tq->fetch();

            // Tổng sản phẩm bán ra
            $sql_sp = "SELECT SUM(ct.SoLuong) as TongSanPham 
                       FROM tbl_chitietdonhang ct 
                       JOIN tbl_donhang dh ON ct.IdDonHang = dh.IdDonHang 
                       WHERE MONTH(dh.NgayDat) = :m AND YEAR(dh.NgayDat) = :y 
                       AND dh.TinhTrang IN $trang_thai_tinh_tien";
            $cmd_sp = $db->prepare($sql_sp);
            $cmd_sp->execute([':m' => (int)$thang, ':y' => (int)$nam]);
            $tong_sp = $cmd_sp->fetch()['TongSanPham'] ?? 0;

            // Top 10 bán chạy
            $sql_bc = "SELECT sp.IdSanPham, sp.MaSanPham, sp.TenSanPham, sp.HinhAnh, 
                              SUM(ct.SoLuong) as TongDaBan, 
                              SUM(ct.TongTien) as DoanhThuSP 
                       FROM tbl_chitietdonhang ct 
                       JOIN tbl_sanpham sp ON ct.IdSanPham = sp.IdSanPham 
                       JOIN tbl_donhang dh ON ct.IdDonHang = dh.IdDonHang 
                       WHERE MONTH(dh.NgayDat) = :m AND YEAR(dh.NgayDat) = :y 
                       AND dh.TinhTrang IN $trang_thai_tinh_tien 
                       GROUP BY ct.IdSanPham 
                       ORDER BY TongDaBan DESC LIMIT 10";
            $cmd_bc = $db->prepare($sql_bc);
            $cmd_bc->execute([':m' => (int)$thang, ':y' => (int)$nam]);
            $top_banchay = $cmd_bc->fetchAll();

            return [
                'DoanhThu' => $tongquan['DoanhThu'] ?? 0,
                'SoDonHang' => $tongquan['SoDonHang'] ?? 0,
                'TongSanPham' => $tong_sp,
                'TopBanChay' => $top_banchay
            ];
        } catch (PDOException $e) {
            return ['DoanhThu' => 0, 'SoDonHang' => 0, 'TongSanPham' => 0, 'TopBanChay' => []];
        }
    }
}
?>

