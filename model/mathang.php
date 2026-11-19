<?php
require_once __DIR__ . '/database.php';

class MATHANG {
    public $id;
    public $masanpham;
    public $tenmathang;
    public $danhmuc_id;
    public $gioitinh;
    public $hinhanh;
    public $giagoc;
    public $giamgia;
    public $mota;
    public $luotxem;
    public $luotmua;

    public function __construct() {
        // Tự động kiểm tra bổ sung cột LuotXem nếu chưa có
        $db = DATABASE::connect();
        try {
            $check = $db->query("SHOW COLUMNS FROM tbl_sanpham LIKE 'LuotXem'");
            if ($check && $check->rowCount() == 0) {
                $db->query("ALTER TABLE tbl_sanpham ADD COLUMN LuotXem INT(11) NOT NULL DEFAULT 0 AFTER LuotMua");
            }
        } catch (Exception $e) {}
    }

    // Lấy tất cả mặt hàng
    public function laymathang($limit = null, $offset = 0) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT sp.*, dm.TenDanhMuc 
                    FROM tbl_sanpham sp 
                    LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc 
                    ORDER BY sp.IdSanPham DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            }
            $cmd = $db->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            echo "<p>Lỗi truy vấn mặt hàng: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    // Lấy mặt hàng theo danh mục
    public function laymathangtheodanhmuc($danhmuc_id, $limit = null) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_sanpham WHERE IdDanhMuc = :danhmuc_id ORDER BY IdSanPham DESC";
            if ($limit !== null) {
                $sql .= " LIMIT " . (int)$limit;
            }
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":danhmuc_id", (int)$danhmuc_id, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            echo "<p>Lỗi truy vấn mặt hàng theo danh mục: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    // Hàm hỗ trợ phân tích từ khóa tìm kiếm thông minh
    private function buildSearchClauses($gioitinh, $search) {
        $conds = ["1=1", "COALESCE(sp.Khoa, 0) = 0", "(dm.Khoa IS NULL OR dm.Khoa = 0)"];
        $binds = [];
        $idx = 0;

        $search = trim($search);
        if ($search !== '') {
            $words = preg_split('/\s+/', $search);
            foreach ($words as $w) {
                $w = trim($w);
                if ($w === '') continue;
                $wLower = mb_strtolower($w, 'UTF-8');
                $paramName = ":tok_" . $idx++;

                if ($wLower === 'nam') {
                    $conds[] = "(sp.GioiTinh = 'Nam' OR CONCAT(' ', sp.TenSanPham, ' ') LIKE $paramName)";
                    $binds[$paramName] = "% nam %";
                } elseif ($wLower === 'nữ' || $wLower === 'nu') {
                    $conds[] = "(sp.GioiTinh = 'Nữ' OR CONCAT(' ', sp.TenSanPham, ' ') LIKE $paramName)";
                    $binds[$paramName] = "% nữ %";
                } elseif ($wLower === 'áo') {
                    $conds[] = "(CONCAT(' ', sp.TenSanPham, ' ') LIKE $paramName OR sp.TenSanPham LIKE 'Áo %' OR dm.TenDanhMuc LIKE '%Áo%')";
                    $binds[$paramName] = "% Áo %";
                } elseif ($wLower === 'quần') {
                    $conds[] = "(CONCAT(' ', sp.TenSanPham, ' ') LIKE $paramName OR sp.TenSanPham LIKE 'Quần %' OR dm.TenDanhMuc LIKE '%Quần%')";
                    $binds[$paramName] = "% Quần %";
                } elseif ($wLower === 'váy' || $wLower === 'đầm') {
                    $conds[] = "(CONCAT(' ', sp.TenSanPham, ' ') LIKE $paramName OR sp.TenSanPham LIKE 'Váy %' OR dm.TenDanhMuc LIKE '%Váy%' OR dm.TenDanhMuc LIKE '%Đầm%')";
                    $binds[$paramName] = "% Váy %";
                } else {
                    $conds[] = "(sp.TenSanPham LIKE $paramName OR sp.MoTa LIKE $paramName OR dm.TenDanhMuc LIKE $paramName OR sp.MaSanPham LIKE $paramName)";
                    $binds[$paramName] = "%" . $w . "%";
                }
            }
        }

        if ($gioitinh === 'Nam') {
            $conds[] = "sp.GioiTinh = 'Nam'";
        } elseif ($gioitinh === 'Nu' || $gioitinh === 'Nữ') {
            $conds[] = "sp.GioiTinh = 'Nữ'";
        }

        return [$conds, $binds];
    }

    // Lấy mặt hàng theo giới tính (Nam / Nữ / Phụ Kiện) - mặc định sắp theo lượt mua giảm dần
    public function laymathangtheogioitinh($gioitinh, $limit = 12, $offset = 0, $search = '', $orderby = 'LuotMua DESC, IdSanPham ASC') {
        $db = DATABASE::connect();
        try {
            list($conds, $binds) = $this->buildSearchClauses($gioitinh, $search);
            $whereSql = "WHERE " . implode(" AND ", $conds);

            $sql = "SELECT sp.*, dm.TenDanhMuc 
                    FROM tbl_sanpham sp 
                    LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc 
                    $whereSql 
                    ORDER BY " . $orderby . " LIMIT :limit OFFSET :offset";
            
            $cmd = $db->prepare($sql);
            foreach ($binds as $k => $v) {
                $cmd->bindValue($k, $v);
            }
            $cmd->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
            $cmd->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            echo "<p>Lỗi truy vấn: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    // Đếm tổng số mặt hàng theo điều kiện
    public function demtongmathang($gioitinh = '', $search = '') {
        $db = DATABASE::connect();
        try {
            list($conds, $binds) = $this->buildSearchClauses($gioitinh, $search);
            $whereSql = "WHERE " . implode(" AND ", $conds);

            $sql = "SELECT COUNT(*) as total 
                    FROM tbl_sanpham sp 
                    LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc 
                    $whereSql";
            $cmd = $db->prepare($sql);
            foreach ($binds as $k => $v) {
                $cmd->bindValue($k, $v);
            }
            $cmd->execute();
            $res = $cmd->fetch();
            return $res ? (int)$res['total'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Lấy 1 mặt hàng theo ID
    public function laymathangtheoid($id) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT sp.*, dm.TenDanhMuc 
                    FROM tbl_sanpham sp 
                    LEFT JOIN tbl_danhmuc dm ON sp.IdDanhMuc = dm.IdDanhMuc 
                    WHERE sp.IdSanPham = :id LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetch();
        } catch (PDOException $e) {
            echo "<p>Lỗi: " . $e->getMessage() . "</p>";
            return null;
        }
    }

    // Tăng lượt xem cho 1 mặt hàng (BTTH03 & BTTH04)
    public function tangluotxem($id) {
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE tbl_sanpham SET LuotXem = LuotXem + 1 WHERE IdSanPham = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Lấy mặt hàng nổi bật (theo lượt mua hoặc lượt xem cao nhất) - BTTH04
    public function laymathangnoibat($limit = 4) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_sanpham ORDER BY (LuotMua * 2 + LuotXem) DESC, IdSanPham DESC LIMIT :limit";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy các biến thể màu sắc, kích thước của sản phẩm
    public function laybienthetheosanpham($id_sanpham) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_bienthe WHERE IdSanPham = :id ORDER BY IdBienThe ASC";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id_sanpham, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Lấy danh sách màu duy nhất của sản phẩm
    public function laymausactheosanpham($id_sanpham) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT DISTINCT MauSac FROM tbl_bienthe WHERE IdSanPham = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id_sanpham, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Sắp xếp danh sách size theo chuẩn thời trang (XS -> S -> M -> L -> XL -> XXL -> 3XL -> Freesize hoặc số 28 -> 29 -> 30...)
    public static function sapxepkichthuoc(&$sizes) {
        $order = [
            '3xs' => 1, '2xs' => 2, 'xs' => 3,
            's' => 4, 'm' => 5, 'l' => 6,
            'xl' => 7, '2xl' => 8, 'xxl' => 9,
            '3xl' => 10, 'xxxl' => 11, '4xl' => 12, '5xl' => 13,
            'freesize' => 99
        ];
        usort($sizes, function($a, $b) use ($order) {
            $keyA = is_array($a) ? ($a['KichThuoc'] ?? '') : (is_object($a) ? ($a->KichThuoc ?? '') : (string)$a);
            $keyB = is_array($b) ? ($b['KichThuoc'] ?? '') : (is_object($b) ? ($b->KichThuoc ?? '') : (string)$b);
            $normA = strtolower(trim($keyA));
            $normB = strtolower(trim($keyB));

            $rankA = $order[$normA] ?? (is_numeric($normA) ? 20 + (int)$normA : 50);
            $rankB = $order[$normB] ?? (is_numeric($normB) ? 20 + (int)$normB : 50);

            if ($rankA === $rankB) {
                return strcmp($keyA, $keyB);
            }
            return ($rankA < $rankB) ? -1 : 1;
        });
    }

    // Lấy danh sách size duy nhất của sản phẩm kèm số lượng tồn (sắp xếp chuẩn thời trang)
    public function laykichthuoctheosanpham($id_sanpham) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT KichThuoc, SUM(SoLuong) as TongSoLuong 
                    FROM tbl_bienthe 
                    WHERE IdSanPham = :id 
                    GROUP BY KichThuoc
                    ORDER BY 
                        CASE UPPER(TRIM(KichThuoc))
                            WHEN '3XS' THEN 1
                            WHEN '2XS' THEN 2
                            WHEN 'XS' THEN 3
                            WHEN 'S' THEN 4
                            WHEN 'M' THEN 5
                            WHEN 'L' THEN 6
                            WHEN 'XL' THEN 7
                            WHEN '2XL' THEN 8
                            WHEN 'XXL' THEN 9
                            WHEN '3XL' THEN 10
                            WHEN 'XXXL' THEN 11
                            WHEN '4XL' THEN 12
                            WHEN '5XL' THEN 13
                            WHEN 'FREESIZE' THEN 99
                            ELSE 50
                        END,
                        CAST(KichThuoc AS UNSIGNED) ASC,
                        KichThuoc ASC";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id_sanpham, PDO::PARAM_INT);
            $cmd->execute();
            $res = $cmd->fetchAll();
            self::sapxepkichthuoc($res);
            return $res;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Thêm mặt hàng mới (BTTH06)
    public function themmathang($data, $bienthe = []) {
        $db = DATABASE::connect();
        try {
            $sql = "INSERT INTO tbl_sanpham (MaSanPham, TenSanPham, IdDanhMuc, GioiTinh, HinhAnh, DonGia, TiLeGiamGia, MoTa, LuotMua, LuotXem) 
                    VALUES (:masp, :tensp, :iddm, :gioitinh, :hinhanh, :dongia, :giamgia, :mota, 0, 0)";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":masp", $data['MaSanPham'] ?? $data['masanpham'] ?? '');
            $cmd->bindValue(":tensp", $data['TenSanPham'] ?? $data['tenmathang'] ?? '');
            $cmd->bindValue(":iddm", (int)($data['IdDanhMuc'] ?? $data['danhmuc_id'] ?? 1), PDO::PARAM_INT);
            $cmd->bindValue(":gioitinh", $data['GioiTinh'] ?? $data['gioitinh'] ?? 'Nam');
            $cmd->bindValue(":hinhanh", $data['HinhAnh'] ?? $data['hinhanh'] ?? '');
            $cmd->bindValue(":dongia", (int)($data['DonGia'] ?? $data['giagoc'] ?? 0), PDO::PARAM_INT);
            $cmd->bindValue(":giamgia", (int)($data['TiLeGiamGia'] ?? $data['giamgia'] ?? 0), PDO::PARAM_INT);
            $cmd->bindValue(":mota", $data['MoTa'] ?? $data['mota'] ?? '');
            
            if ($cmd->execute()) {
                $id_vua_tao = (int)$db->lastInsertId();
                if (!empty($bienthe)) {
                    $sql_bt = "INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong) VALUES (:idsp, :mau, :size, :sl)";
                    $cmd_bt = $db->prepare($sql_bt);
                    foreach ($bienthe as $bt) {
                        if (!empty($bt['MauSac']) && !empty($bt['KichThuoc'])) {
                            $cmd_bt->bindValue(":idsp", $id_vua_tao, PDO::PARAM_INT);
                            $cmd_bt->bindValue(":mau", $bt['MauSac']);
                            $cmd_bt->bindValue(":size", $bt['KichThuoc']);
                            $cmd_bt->bindValue(":sl", (int)$bt['SoLuong'], PDO::PARAM_INT);
                            $cmd_bt->execute();
                        }
                    }
                }
                return $id_vua_tao;
            }
            return false;
        } catch (PDOException $e) {
            echo "<p>Lỗi thêm mặt hàng: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Cập nhật mặt hàng (BTTH06)
    public function suamathang($id, $data, $bienthe = null) {
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE tbl_sanpham SET 
                    MaSanPham = :masp, 
                    TenSanPham = :tensp, 
                    IdDanhMuc = :iddm, 
                    GioiTinh = :gioitinh, 
                    DonGia = :dongia, 
                    TiLeGiamGia = :giamgia, 
                    MoTa = :mota";
            if (!empty($data['HinhAnh'])) {
                $sql .= ", HinhAnh = :hinhanh";
            }
            $sql .= " WHERE IdSanPham = :id";

            $cmd = $db->prepare($sql);
            $cmd->bindValue(":masp", $data['MaSanPham']);
            $cmd->bindValue(":tensp", $data['TenSanPham']);
            $cmd->bindValue(":iddm", (int)$data['IdDanhMuc'], PDO::PARAM_INT);
            $cmd->bindValue(":gioitinh", $data['GioiTinh']);
            $cmd->bindValue(":dongia", (int)$data['DonGia'], PDO::PARAM_INT);
            $cmd->bindValue(":giamgia", (int)$data['TiLeGiamGia'], PDO::PARAM_INT);
            $cmd->bindValue(":mota", $data['MoTa']);
            if (!empty($data['HinhAnh'])) {
                $cmd->bindValue(":hinhanh", $data['HinhAnh']);
            }
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            
            $res = $cmd->execute();

            if ($bienthe !== null) {
                // Xóa biến thể cũ và chèn biến thể mới
                $db->prepare("DELETE FROM tbl_bienthe WHERE IdSanPham = :id")->execute([':id' => (int)$id]);
                if (!empty($bienthe)) {
                    $cmd_bt = $db->prepare("INSERT INTO tbl_bienthe (IdSanPham, MauSac, KichThuoc, SoLuong) VALUES (:idsp, :mau, :size, :sl)");
                    foreach ($bienthe as $bt) {
                        if (!empty($bt['MauSac']) && !empty($bt['KichThuoc'])) {
                            $cmd_bt->execute([
                                ':idsp' => (int)$id,
                                ':mau' => $bt['MauSac'],
                                ':size' => $bt['KichThuoc'],
                                ':sl' => (int)$bt['SoLuong']
                            ]);
                        }
                    }
                }
            }
            return $res;
        } catch (PDOException $e) {
            echo "<p>Lỗi sửa mặt hàng: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Xóa mặt hàng (BTTH06)
    public function xoamathang($id) {
        $db = DATABASE::connect();
        try {
            $id = (int)$id;
            // Xóa biến thể trước
            $db->prepare("DELETE FROM tbl_bienthe WHERE IdSanPham = :id")->execute([':id' => $id]);
            // Xóa mặt hàng
            $cmd = $db->prepare("DELETE FROM tbl_sanpham WHERE IdSanPham = :id");
            return $cmd->execute([':id' => $id]);
        } catch (PDOException $e) {
            echo "<p>Lỗi xóa mặt hàng: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Đổi trạng thái Khóa / Mở sản phẩm
    public function doitrangthai($id) {
        $db = DATABASE::connect();
        try {
            $cmd = $db->prepare("UPDATE tbl_sanpham SET Khoa = 1 - COALESCE(Khoa, 0) WHERE IdSanPham = :id");
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Tìm kiếm và lọc nâng cao (cho trang sanpham.php / group.php)
    public function locmathang($params, $limit = 12, $offset = 0) {
        $db = DATABASE::connect();
        try {
            $where = ["1=1"];
            $binds = [];
            $join_bienthe = false;

            if (!empty($params['q'])) {
                $where[] = "sp.TenSanPham LIKE :q";
                $binds[':q'] = "%" . trim($params['q']) . "%";
            }
            if (!empty($params['cats']) && is_array($params['cats'])) {
                $cats = array_map('intval', $params['cats']);
                $where[] = "sp.IdDanhMuc IN (" . implode(',', $cats) . ")";
            } elseif (!empty($params['cat'])) {
                $where[] = "sp.IdDanhMuc = :cat";
                $binds[':cat'] = (int)$params['cat'];
            }
            if (!empty($params['genders']) && is_array($params['genders'])) {
                $inG = [];
                foreach ($params['genders'] as $idx => $g) {
                    $key = ":g_" . $idx;
                    $inG[] = $key;
                    $binds[$key] = $g;
                }
                $where[] = "sp.GioiTinh IN (" . implode(',', $inG) . ")";
            } elseif (!empty($params['gender'])) {
                $where[] = "sp.GioiTinh = :gender";
                $binds[':gender'] = ($params['gender'] == 'Nu') ? 'Nữ' : 'Nam';
            }
            if (!empty($params['sizes']) && is_array($params['sizes'])) {
                $inS = [];
                foreach ($params['sizes'] as $idx => $s) {
                    $key = ":s_" . $idx;
                    $inS[] = $key;
                    $binds[$key] = $s;
                }
                $where[] = "bt.KichThuoc IN (" . implode(',', $inS) . ")";
                $join_bienthe = true;
            }
            if (!empty($params['colors']) && is_array($params['colors'])) {
                $inC = [];
                foreach ($params['colors'] as $idx => $c) {
                    $key = ":c_" . $idx;
                    $inC[] = $key;
                    $binds[$key] = $c;
                }
                $where[] = "bt.MauSac IN (" . implode(',', $inC) . ")";
                $join_bienthe = true;
            }
            if (!empty($params['prices']) && is_array($params['prices'])) {
                $price_conds = [];
                foreach ($params['prices'] as $pr) {
                    $parts = explode('-', $pr);
                    if (count($parts) == 2) {
                        $min = (int)$parts[0];
                        $max = (int)$parts[1];
                        $price_conds[] = "(sp.DonGia BETWEEN $min AND $max)";
                    } elseif ($pr == '500000') {
                        $price_conds[] = "(sp.DonGia >= 500000)";
                    }
                }
                if (!empty($price_conds)) {
                    $where[] = "(" . implode(" OR ", $price_conds) . ")";
                }
            }

            $fromSql = "FROM tbl_sanpham sp";
            if ($join_bienthe) {
                $fromSql .= " JOIN tbl_bienthe bt ON sp.IdSanPham = bt.IdSanPham";
            }
            $whereSql = "WHERE " . implode(" AND ", $where);

            // Đếm tổng
            $countSql = "SELECT COUNT(DISTINCT sp.IdSanPham) as total $fromSql $whereSql";
            $cmdCount = $db->prepare($countSql);
            foreach ($binds as $k => $v) {
                $cmdCount->bindValue($k, $v);
            }
            $cmdCount->execute();
            $total = (int)($cmdCount->fetch()['total'] ?? 0);

            // Sắp xếp
            $sort = $params['sort'] ?? 'newest';
            $orderSql = "ORDER BY sp.IdSanPham DESC";
            if ($sort === 'price_asc') {
                $orderSql = "ORDER BY (sp.DonGia - (sp.DonGia * sp.TiLeGiamGia / 100)) ASC";
            } elseif ($sort === 'price_desc') {
                $orderSql = "ORDER BY (sp.DonGia - (sp.DonGia * sp.TiLeGiamGia / 100)) DESC";
            } elseif ($sort === 'bestseller') {
                $orderSql = "ORDER BY sp.LuotMua DESC";
            }

            // Lấy danh sách
            $selectSql = "SELECT DISTINCT sp.* $fromSql $whereSql $orderSql LIMIT :limit OFFSET :offset";
            $cmdSelect = $db->prepare($selectSql);
            foreach ($binds as $k => $v) {
                $cmdSelect->bindValue($k, $v);
            }
            $cmdSelect->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
            $cmdSelect->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
            $cmdSelect->execute();
            $items = $cmdSelect->fetchAll();

            return ['items' => $items, 'total' => $total];
        } catch (PDOException $e) {
            echo "<p>Lỗi lọc: " . $e->getMessage() . "</p>";
            return ['items' => [], 'total' => 0];
        }
    }
}
?>

