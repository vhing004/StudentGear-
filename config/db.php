
<?php
$localhost = 'localhost';
$username = 'root';
$password = '';
$dbname = 'studentgear';

$conn = new mysqli($localhost, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đường dẫn gốc dự án
// define('BASE_URL', 'http://localhost/studentgear/');


// 1. Định nghĩa PATH_ROOT: Đường dẫn vật lý trên ổ đĩa (dùng cho include/require)
// dirname(__DIR__) sẽ trỏ về thư mục gốc của dự án nếu db.php nằm trong thư mục config/
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(__DIR__));
}

// 2. Định nghĩa BASE_URL: Đường dẫn URL (dùng cho link, hình ảnh, CSS, JS)
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/studentgear/');
}
?>