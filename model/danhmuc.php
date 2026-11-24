<?php
require_once __DIR__ . '/database.php';

class DANHMUC {
    public $id;
    public $tendanhmuc;
    public $mota;

    // Lấy toàn bộ danh mục
    public function laydanhmuc() {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_danhmuc ORDER BY IdDanhMuc ASC";
            $cmd = $db->prepare($sql);
            $cmd->execute();
            return $cmd->fetchAll();
        } catch (PDOException $e) {
            echo "<p>Lỗi truy vấn danh mục: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    // Lấy 1 danh mục theo ID
    public function laydanhmuctheoid($id) {
        $db = DATABASE::connect();
        try {
            $sql = "SELECT * FROM tbl_danhmuc WHERE IdDanhMuc = :id LIMIT 1";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", (int)$id, PDO::PARAM_INT);
            $cmd->execute();
            return $cmd->fetch();
        } catch (PDOException $e) {
            echo "<p>Lỗi truy vấn: " . $e->getMessage() . "</p>";
            return null;
        }
    }

    // Thêm danh mục mới (hỗ trợ truyền object hoặc tham số rời)
    public function themdanhmuc($danhmuc, $mota = '') {
        $db = DATABASE::connect();
        try {
            $ten = is_object($danhmuc) ? ($danhmuc->tendanhmuc ?? '') : $danhmuc;
            $mt = is_object($danhmuc) ? ($danhmuc->mota ?? '') : $mota;

            $sql = "INSERT INTO tbl_danhmuc (TenDanhMuc, MoTa) VALUES (:tendanhmuc, :mota)";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":tendanhmuc", $ten);
            $cmd->bindValue(":mota", $mt);
            return $cmd->execute();
        } catch (PDOException $e) {
            echo "<p>Lỗi thêm danh mục: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Cập nhật danh mục
    public function suadanhmuc($danhmuc, $tendanhmuc = '', $mota = '') {
        $db = DATABASE::connect();
        try {
            if (is_object($danhmuc)) {
                $id = $danhmuc->id ?? $danhmuc->IdDanhMuc;
                $ten = $danhmuc->tendanhmuc ?? $danhmuc->TenDanhMuc;
                $mt = $danhmuc->mota ?? $danhmuc->MoTa ?? '';
            } else {
                $id = (int)$danhmuc;
                $ten = $tendanhmuc;
                $mt = $mota;
            }

            $sql = "UPDATE tbl_danhmuc SET TenDanhMuc = :tendanhmuc, MoTa = :mota WHERE IdDanhMuc = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":tendanhmuc", $ten);
            $cmd->bindValue(":mota", $mt);
            $cmd->bindValue(":id", $id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            echo "<p>Lỗi sửa danh mục: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Đổi trạng thái Khóa / Mở danh mục (đồng thời khóa / mở các sản phẩm thuộc danh mục đó)
    public function doitrangthai($id) {
        $db = DATABASE::connect();
        try {
            $id = (int)$id;
            // 1. Lấy trạng thái hiện tại của danh mục
            $check = $db->prepare("SELECT Khoa FROM tbl_danhmuc WHERE IdDanhMuc = :id LIMIT 1");
            $check->execute([':id' => $id]);
            $current = $check->fetch(PDO::FETCH_ASSOC);
            if (!$current) {
                return false;
            }

            // Trạng thái mới: nếu đang 0 (mở) thì chuyển thành 1 (khóa), ngược lại thành 0
            $newStatus = (!empty($current['Khoa'])) ? 0 : 1;

            // 2. Cập nhật trạng thái danh mục
            $cmd = $db->prepare("UPDATE tbl_danhmuc SET Khoa = :status WHERE IdDanhMuc = :id");
            $cmd->bindValue(":status", $newStatus, PDO::PARAM_INT);
            $cmd->bindValue(":id", $id, PDO::PARAM_INT);
            $cmd->execute();

            // 3. Cập nhật đồng bộ trạng thái cho tất cả sản phẩm thuộc danh mục này
            $cmdSp = $db->prepare("UPDATE tbl_sanpham SET Khoa = :status WHERE IdDanhMuc = :id");
            $cmdSp->bindValue(":status", $newStatus, PDO::PARAM_INT);
            $cmdSp->bindValue(":id", $id, PDO::PARAM_INT);
            $cmdSp->execute();

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Xóa danh mục
    public function xoadanhmuc($danhmuc) {
        $db = DATABASE::connect();
        try {
            $id = is_object($danhmuc) ? ($danhmuc->id ?? $danhmuc->IdDanhMuc) : (int)$danhmuc;
            $sql = "DELETE FROM tbl_danhmuc WHERE IdDanhMuc = :id";
            $cmd = $db->prepare($sql);
            $cmd->bindValue(":id", $id, PDO::PARAM_INT);
            return $cmd->execute();
        } catch (PDOException $e) {
            echo "<p>Lỗi xóa danh mục: " . $e->getMessage() . "</p>";
            return false;
        }
    }
}
?>

