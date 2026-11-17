<?php
class DATABASE {
    private static $dns = "mysql:host=localhost;dbname=shop_thoitrang;charset=utf8";
    private static $username = "root";
    private static $db;

    public static function connect() {
        if (!isset(self::$db)) {
            try {
                // 1. Thử kết nối mật khẩu rỗng (mặc định của XAMPP)
                self::$db = new PDO(self::$dns, self::$username, "", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                try {
                    // 2. Thử kết nối với mật khẩu 'vertrigo' (dành cho VertrigoServ)
                    self::$db = new PDO(self::$dns, self::$username, "vertrigo", [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                } catch (PDOException $e2) {
                    $error_message = $e2->getMessage();
                    echo "<p style='color:red;'>Lỗi kết nối CSDL: $error_message</p>";
                    exit();
                }
            }
        }
        return self::$db;
    }
}
?>
