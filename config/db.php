
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
define('BASE_URL', 'http://localhost/studentgear/');
