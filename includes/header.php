<?php
// Khởi động session để kiểm tra đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'C:/xampp/htdocs/StudentGear/config/db.php';

// 1. Truy vấn lấy 8 danh mục đang hoạt động từ Database
$sql_categories = "SELECT name, slug FROM categories WHERE is_active = 1 LIMIT 8";
$res_categories = $conn->query($sql_categories);

// 2. Tính tổng số lượng sản phẩm trong giỏ hàng của người dùng hiện tại
$cart = 0;
if (isset($_SESSION['user_id'])) {
    $u_id = (int)$_SESSION['user_id'];
    $sql_cart = "SELECT SUM(quantity) AS total_quantity FROM cart WHERE user_id = $u_id";
    $result_cart = $conn->query($sql_cart);
    if ($result_cart && $row_cart = $result_cart->fetch_assoc()) {
        $cart = $row_cart['total_quantity'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>assets/images/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <title>StudentGear - Phụ kiện sinh viên</title>
</head>

<body>
    <header class="header">
        <div class="header_container">
            <nav class="header_nav">
                <div class="container header_nav-wrapper">
                    <a href="<?php echo BASE_URL; ?>index.php" class="header_logo">
                        Student<span style="color: #d0021c;">Gear</span>
                    </a>

                    <form action="<?php echo BASE_URL; ?>pages/category.php" class="header_search" method="GET">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." required />
                        <button class="header_search-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>

                    <div class="header_menu">
                        <a href="<?php echo BASE_URL; ?>pages/cart.php" class="header_menu-btn header_cart">
                            <div class="header_cart-wrap">
                                <i class="fa-solid fa-shopping-cart"></i>
                                <span class="header_cart-notice">
                                    <?php
                                    echo $cart;
                                    ?>
                                </span>
                            </div>
                            <span class="header_menu-text">Giỏ hàng</span>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="header_user">
                                    <div class="header_user-info">
                                        <i class="fa-solid fa-circle-user"></i>
                                        <span class="header_user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                    </div>

                                    <ul class="header_user-menu">
                                        <li class="header_user-item">
                                            <a href="<?php echo BASE_URL; ?>profile.php">
                                                <i class="fa-regular fa-user"></i> Tài khoản của tôi
                                            </a>
                                        </li>
                                        <li class="header_user-item">
                                            <a href="<?php echo BASE_URL; ?>order_history.php">
                                                <i class="fa-solid fa-clipboard-list"></i> Đơn mua
                                            </a>
                                        </li>
                                        <li class="header_user-item header_user-item--separate">
                                            <a href="<?php echo BASE_URL; ?>auth/logout.php">
                                                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>auth/login.php" class="header_menu-btn" id="openLogin">
                                    <i class="fa-regular fa-circle-user"></i>
                                    <span class="header_menu-text">Đăng nhập</span>
                                </a>
                            <?php endif; ?>
                    </div>
                </div>
            </nav>

            <div class="header_category">
                <ul class="header_list container">
                    <?php
                    if ($res_categories && $res_categories->num_rows > 0) {
                        while ($cat = $res_categories->fetch_assoc()) {
                            echo '<li class="header_list-item"><a href="' . BASE_URL . 'category.php?slug=' . $cat['slug'] . '" class="header_list-item--link">' . $cat['name'] . '</a></li>';
                        }
                    }
                    ?>
                    <li class="header_list-item"><a href="<?php echo BASE_URL; ?>contact.php" class="header_list-item--link">Liên hệ</a></li>
                    <li class="header_list-item"><a href="<?php echo BASE_URL; ?>policy.php" class="header_list-item--link">Chính sách</a></li>
                </ul>
            </div>
        </div>
    </header>