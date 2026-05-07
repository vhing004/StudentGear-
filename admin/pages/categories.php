<?php
// 1. Khởi động session và kết nối cơ sở dữ liệu
session_start();
require_once '../../config/db.php';


/**
 * 2. LOGIC BẢO MẬT: Chỉ Admin/Staff mới được vào
 * Kiểm tra user_id (đã đăng nhập) và role (là tài khoản admin)
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Nếu không phải admin, đẩy về trang login ở thư mục gốc
    header("Location: " . BASE_URL . "../auth/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản trị - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>

<body class="admin-body">

    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <div class="header-title">
                    <h2>Danh mục sản phẩm</h2>
                    <p>Quản lý danh mục sản phẩm của cửa hàng.</p>
                </div>
                <div class="user-profile">
                    <span>Chào, <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong></span>
                    <small><?= strtoupper($_SESSION['role']) ?></small>
                </div>
            </header>

        </main>
    </div>

</body>

</html>