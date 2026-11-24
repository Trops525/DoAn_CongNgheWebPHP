<?php
require_once __DIR__ . '/database.php';

class CSKH {
    // 1. Lấy danh sách câu hỏi FAQ
    public function layfaq($danhmuc = 'TatCa') {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_cskh_faq WHERE TrangThai = 1";
            if ($danhmuc !== 'TatCa') {
                $sql .= " AND DanhMuc = :dm";
            }
            $sql .= " ORDER BY ThuTu ASC, IdFAQ DESC";
            $cmd = $db->prepare($sql);
            if ($danhmuc !== 'TatCa') {
                $cmd->bindValue(':dm', $danhmuc);
            }
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 2. Lấy tin nhắn chat của 1 khách hàng
    public function laytinnhanchat($id_nguoidung) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_cskh_chat WHERE IdNguoiDung = :uid ORDER BY ThoiGian ASC";
            $cmd = $db->prepare($sql);
            $cmd->execute([':uid' => (int)$id_nguoidung]);
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 3. Gửi tin nhắn (NguoiGui: 1 = Khách, 2 = Shop)
    public function guitinnhan($id_nguoidung, $nguoigui, $noidung) {
        $db = DATABASE::connect();
        try {
            $sql = "INSERT INTO tbl_cskh_chat (IdNguoiDung, NguoiGui, NoiDung, ThoiGian) VALUES (:uid, :nguoigui, :noidung, NOW())";
            $cmd = $db->prepare($sql);
            return $cmd->execute([
                ':uid' => (int)$id_nguoidung,
                ':nguoigui' => (int)$nguoigui,
                ':noidung' => trim($noidung)
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 4. Lấy danh sách khách hàng đang chat (cho Admin)
    public function danhsachkhachchat() {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT nd.IdNguoiDung, nd.HoTen, nd.TenDangNhap, nd.Email, 
                           COUNT(ct.IdChat) AS SoTinNhan, MAX(ct.ThoiGian) AS LastTime
                    FROM tbl_nguoidung nd
                    JOIN tbl_cskh_chat ct ON nd.IdNguoiDung = ct.IdNguoiDung
                    GROUP BY nd.IdNguoiDung
                    ORDER BY LastTime DESC";
            $cmd = $db->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // 5. Xóa hội thoại chat
    public function xoahoithoai($danhsach_id_nguoidung) {
        $db = DATABASE::connect();
        try {
            if (empty($danhsach_id_nguoidung)) return false;
            $ids = array_map('intval', $danhsach_id_nguoidung);
            $inIds = implode(',', $ids);
            return $db->query("DELETE FROM tbl_cskh_chat WHERE IdNguoiDung IN ($inIds)");
        } catch (PDOException $e) {
            return false;
        }
    }
}

class DANHGIA {
    // Kiểm tra xem khách đã mua và đơn đã giao chưa để mở quyền đánh giá
    public function kiemtramuahang($id_nguoidung, $id_sanpham) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT 1 FROM tbl_donhang dh 
                    JOIN tbl_chitietdonhang ct ON dh.IdDonHang = ct.IdDonHang 
                    WHERE dh.IdNguoiDung = :uid 
                    AND ct.IdSanPham = :idsp 
                    AND dh.TinhTrang = 'Đã giao' LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->execute([':uid' => (int)$id_nguoidung, ':idsp' => (int)$id_sanpham]);
            return ($cmd->rowCount() > 0);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Thêm đánh giá
    public function themdanhgia($id_sanpham, $id_nguoidung, $sosao, $noidung) {
        $db = DATABASE::connect();
        try {
            $sql = "INSERT INTO tbl_danhgia (IdSanPham, IdNguoiDung, SoSao, NoiDung, NgayDanhGia) 
                    VALUES (:idsp, :uid, :sao, :nd, NOW())";
            $cmd = $db->prepare($sql);
            return $cmd->execute([
                ':idsp' => (int)$id_sanpham,
                ':uid' => (int)$id_nguoidung,
                ':sao' => max(1, min(5, (int)$sosao)),
                ':nd' => trim($noidung)
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Lấy danh sách đánh giá của sản phẩm (chỉ lấy các đánh giá đang hiển thị, không bị ẩn)
    public function laydanhgiatheosanpham($id_sanpham) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT dg.*, nd.HoTen, nd.TenDangNhap 
                    FROM tbl_danhgia dg 
                    LEFT JOIN tbl_nguoidung nd ON dg.IdNguoiDung = nd.IdNguoiDung 
                    WHERE dg.IdSanPham = :idsp 
                    AND COALESCE(dg.Khoa, 0) = 0 
                    ORDER BY dg.NgayDanhGia DESC";
            $cmd = $db->prepare($sql);
            $cmd->execute([':idsp' => (int)$id_sanpham]);
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy tất cả đánh giá cho trang Admin (kèm bộ lọc sao, tìm kiếm và trạng thái hiển thị/ẩn)
    public function laytatcadanhgia($so_sao = 0, $search = '', $trang_thai = 'all') {
        $db = DATABASE::connect();
        try {
            $where = [];
            $params = [];

            if ($so_sao > 0) {
                $where[] = "dg.SoSao = :sao";
                $params[':sao'] = (int)$so_sao;
            }

            if (!empty($search)) {
                $where[] = "(sp.TenSanPham LIKE :s OR sp.MaSanPham LIKE :s OR nd.HoTen LIKE :s OR nd.TenDangNhap LIKE :s OR dg.NoiDung LIKE :s)";
                $params[':s'] = "%" . trim($search) . "%";
            }

            if ($trang_thai === 'visible') {
                $where[] = "COALESCE(dg.Khoa, 0) = 0";
            } elseif ($trang_thai === 'hidden') {
                $where[] = "dg.Khoa = 1";
            }

            $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            $sql = "SELECT dg.*, sp.TenSanPham, sp.MaSanPham, sp.HinhAnh, nd.HoTen, nd.TenDangNhap, nd.Email 
                    FROM tbl_danhgia dg 
                    LEFT JOIN tbl_sanpham sp ON dg.IdSanPham = sp.IdSanPham 
                    LEFT JOIN tbl_nguoidung nd ON dg.IdNguoiDung = nd.IdNguoiDung 
                    $whereSql 
                    ORDER BY dg.NgayDanhGia DESC";
            $cmd = $db->prepare($sql);
            $cmd->execute($params);
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Đổi trạng thái Ẩn / Hiện của 1 đánh giá
    public function doitrangthai($id_danhgia) {
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE tbl_danhgia SET Khoa = 1 - COALESCE(Khoa, 0) WHERE IdDanhGia = :id";
            $cmd = $db->prepare($sql);
            return $cmd->execute([':id' => (int)$id_danhgia]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Ẩn hoặc Hiện nhiều đánh giá cùng lúc
    public function an_nhieudanhgia(array $ids, $khoa = 1) {
        if (empty($ids)) return false;
        $db = DATABASE::connect();
        try {
            $ids = array_map('intval', $ids);
            $inClause = implode(',', $ids);
            $khoaVal = ($khoa == 1) ? 1 : 0;
            $sql = "UPDATE tbl_danhgia SET Khoa = $khoaVal WHERE IdDanhGia IN ($inClause)";
            return $db->exec($sql);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Xóa một đánh giá
    public function xoadanhgia($id_danhgia) {
        $db = DATABASE::connect();
        try {
            $sql = "DELETE FROM tbl_danhgia WHERE IdDanhGia = :id";
            $cmd = $db->prepare($sql);
            return $cmd->execute([':id' => (int)$id_danhgia]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Xóa nhiều đánh giá
    public function xoanhieudanhgia(array $ids) {
        if (empty($ids)) return false;
        $db = DATABASE::connect();
        try {
            $ids = array_map('intval', $ids);
            $inClause = implode(',', $ids);
            $sql = "DELETE FROM tbl_danhgia WHERE IdDanhGia IN ($inClause)";
            return $db->exec($sql);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Thống kê đánh giá
    public function thongkedanhgia() {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT 
                        COUNT(*) as Tong,
                        ROUND(AVG(SoSao), 1) as TrungBinh,
                        SUM(CASE WHEN SoSao = 5 THEN 1 ELSE 0 END) as Sao5,
                        SUM(CASE WHEN SoSao = 4 THEN 1 ELSE 0 END) as Sao4,
                        SUM(CASE WHEN SoSao = 3 THEN 1 ELSE 0 END) as Sao3,
                        SUM(CASE WHEN SoSao <= 2 THEN 1 ELSE 0 END) as SaoThap,
                        SUM(CASE WHEN COALESCE(Khoa, 0) = 0 THEN 1 ELSE 0 END) as HienThi,
                        SUM(CASE WHEN Khoa = 1 THEN 1 ELSE 0 END) as DaAn
                    FROM tbl_danhgia";
            $res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $res ?: ['Tong' => 0, 'TrungBinh' => 0, 'Sao5' => 0, 'Sao4' => 0, 'Sao3' => 0, 'SaoThap' => 0, 'HienThi' => 0, 'DaAn' => 0];
        } catch (PDOException $e) {
            return ['Tong' => 0, 'TrungBinh' => 0, 'Sao5' => 0, 'Sao4' => 0, 'Sao3' => 0, 'SaoThap' => 0, 'HienThi' => 0, 'DaAn' => 0];
        }
    }
}
?>

