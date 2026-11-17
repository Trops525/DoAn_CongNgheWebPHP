<?php
require_once __DIR__ . '/database.php';

class NGUOIDUNG {
    public $id;
    public $hoten;
    public $tendangnhap;
    public $matkhau;
    public $email;
    public $dienthoai;
    public $quyenhan;
    public $khoa;

    // Kiểm tra đăng nhập (hỗ trợ nhập cả email hoặc tên đăng nhập)
    public function kiemtranguoidunghople($username_or_email, $matkhau) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_nguoidung 
                    WHERE (TenDangNhap = :user OR Email = :email) 
                    AND MatKhau = :pass 
                    AND Khoa = 0 LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":user", $username_or_email);
            $cmd->bindValue(":email", $username_or_email);
            $cmd->bindValue(":pass", md5($matkhau));
            $cmd->execute();
            return ($cmd->rowCount() == 1);
        } catch (PDOException $e) {
            echo "<p>Lỗi xác thực: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Lấy thông tin người dùng theo email hoặc tên đăng nhập
    public function laythongtinnguoidung($username_or_email) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_nguoidung WHERE TenDangNhap = :user OR Email = :email LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":user", $username_or_email);
            $cmd->bindValue(":email", $username_or_email);
            $cmd->execute();
            return $cmd->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    // Lấy thông tin người dùng theo ID
    public function laynguoidungtheoid($id) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_nguoidung WHERE IdNguoiDung = :id LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    // Lấy danh sách toàn bộ người dùng
    public function laydanhsachnguoidung() {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_nguoidung ORDER BY IdNguoiDung DESC";
            $cmd = $db->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    // Kiểm tra trùng username, email hoặc số điện thoại khi đăng ký
    public function kiemtratrung($username, $email, $phone) {
        $db = DATABASE::connect();
        try {
            $cmd1 = $db->prepare("SELECT IdNguoiDung FROM tbl_nguoidung WHERE TenDangNhap = :u LIMIT 1");
            $cmd1->execute([':u' => $username]);
            if ($cmd1->rowCount() > 0) return 'username';

            $cmd2 = $db->prepare("SELECT IdNguoiDung FROM tbl_nguoidung WHERE Email = :e LIMIT 1");
            $cmd2->execute([':e' => $email]);
            if ($cmd2->rowCount() > 0) return 'email';

            $cmd3 = $db->prepare("SELECT IdNguoiDung FROM tbl_nguoidung WHERE DienThoai = :p LIMIT 1");
            $cmd3->execute([':p' => $phone]);
            if ($cmd3->rowCount() > 0) return 'phone';

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Thêm người dùng mới (đăng ký hoặc admin thêm)
    public function themnguoidung($data) {
        $db = DATABASE::connect();
        try {
            $hoten = is_object($data) ? ($data->hoten ?? '') : ($data['HoTen'] ?? $data['hoten'] ?? '');
            $username = is_object($data) ? ($data->tendangnhap ?? '') : ($data['TenDangNhap'] ?? $data['tendangnhap'] ?? '');
            $matkhau = is_object($data) ? ($data->matkhau ?? '') : ($data['MatKhau'] ?? $data['matkhau'] ?? '');
            $email = is_object($data) ? ($data->email ?? '') : ($data['Email'] ?? $data['email'] ?? '');
            $phone = is_object($data) ? ($data->dienthoai ?? '') : ($data['DienThoai'] ?? $data['dienthoai'] ?? '');
            $quyen = is_object($data) ? ($data->quyenhan ?? 2) : ($data['QuyenHan'] ?? $data['quyenhan'] ?? 2);

            // Kiểm tra trùng username hoặc email
            $checkSql = "SELECT IdNguoiDung FROM tbl_nguoidung WHERE TenDangNhap = :user OR Email = :email LIMIT 1";
            $cmdCheck = $db->prepare($checkSql);
            $cmdCheck->execute([':user' => $username, ':email' => $email]);
            if ($cmdCheck->rowCount() > 0) {
                return false; // Đã tồn tại
            }

            $sql = "INSERT INTO tbl_nguoidung (HoTen, TenDangNhap, MatKhau, Email, DienThoai, QuyenHan, Khoa) 
                    VALUES (:hoten, :user, :pass, :email, :phone, :quyen, 0)";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":hoten", $hoten);
            $cmd->bindValue(":user", $username);
            $cmd->bindValue(":pass", md5($matkhau));
            $cmd->bindValue(":email", $email);
            $cmd->bindValue(":phone", $phone);
            $cmd->bindValue(":quyen", (int)$quyen, PDO::PARAM_INT);
            
            if ($cmd->execute()) {
                return (int)$db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            echo "<p>Lỗi thêm người dùng: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Đổi mật khẩu
    public function doimatkhau($id, $matkhau_cu, $matkhau_moi) {
        $db = DATABASE::connect();
        try {
            $user = $this->laynguoidungtheoid($id);
            if (!$user || $user['MatKhau'] !== md5($matkhau_cu)) {
                return false; // Mật khẩu cũ không khớp
            }
            $sql = "UPDATE tbl_nguoidung SET MatKhau = :pass WHERE IdNguoiDung = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":pass", md5($matkhau_moi));
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Khóa / Mở khóa tài khoản (toggle)
    public function doitrangthai($id) {
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE tbl_nguoidung SET Khoa = 1 - Khoa WHERE IdNguoiDung = :id AND QuyenHan != 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Nâng cấp / thay đổi quyền hạn
    public function phanquyen($id, $quyenhan) {
        $db = DATABASE::connect();
        try {
            $sql = "UPDATE tbl_nguoidung SET QuyenHan = :quyen WHERE IdNguoiDung = :id AND QuyenHan != 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":quyen", (int)$quyenhan, PDO::PARAM_INT);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Xóa người dùng (không được xóa Admin)
    public function xoanguoidung($id) {
        $db = DATABASE::connect();
        try {
            $sql = "DELETE FROM tbl_nguoidung WHERE IdNguoiDung = :id AND QuyenHan != 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>

