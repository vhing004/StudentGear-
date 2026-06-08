<?php
// Khởi động session để kiểm tra đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Kiểm tra nếu là Admin thì không cho phép truy cập trang này
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'moderator', 'staff'])) {
    // Điều hướng admin về trang quản trị của họ
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}

// 1. Truy vấn lấy 8 danh mục đang hoạt động từ Database
$sql_categories = "SELECT * FROM categories WHERE is_active = 1 LIMIT 8";
$res_categories = $conn->query($sql_categories);

// Lấy giá trị search từ URL nếu có
$search_value = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';

// 2. Tính tổng số lượng sản phẩm trong giỏ hàng của người dùng hiện tại VÀ LẤY AVATAR
$cart = 0;
$user_avatar = ''; // Thêm biến lưu đường dẫn avatar

if (isset($_SESSION['user_id'])) {
    $u_id = (int)$_SESSION['user_id'];

    // Tối ưu gộp chung câu lệnh lấy cả tổng giỏ hàng và ảnh đại diện cùng lúc
    $sql_user_info = "SELECT 
                        (SELECT SUM(quantity) FROM cart WHERE user_id = $u_id) AS total_quantity,
                        (SELECT avatar FROM users WHERE id = $u_id) AS avatar";

    $result_info = $conn->query($sql_user_info);
    if ($result_info && $row_info = $result_info->fetch_assoc()) {
        $cart = $row_info['total_quantity'] ?? 0;
        $user_avatar = $row_info['avatar'] ?? ''; // Lấy dữ liệu trường avatar
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>assets/images/favicon.png" type="image/x-icon">
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
                        <input type="text" name="search" value="<?php echo $search_value; ?>" placeholder="Tìm kiếm sản phẩm..." required />
                        <button class="header_search-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>

                    <div class="header_menu">
                        <a href="<?php echo BASE_URL; ?>pages/profile.php" class="header_menu-btn header_cart">
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
                                        <?php if (!empty($user_avatar)): ?>
                                            <img class="header-avatar" src="<?php echo BASE_URL . htmlspecialchars($user_avatar); ?>"
                                                alt="Avatar">
                                        <?php else: ?>
                                            <i class="fa-solid fa-user-graduate"></i>
                                        <?php endif; ?>
                                        <span class="header_user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                                    </div>

                                    <ul class="header_user-menu">
                                        <li class="header_user-item">
                                            <a href="<?php echo BASE_URL; ?>pages/profile.php">
                                                <i class="fa-regular fa-user"></i> Tài khoản của tôi
                                            </a>
                                        </li>
                                        <li class="header_user-item">
                                            <a href="<?php echo BASE_URL; ?>pages/history_order.php">
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
                            echo '<li class="header_list-item"><a href="' . BASE_URL . 'pages/category.php?id=' . $cat['id'] . '" class="header_list-item--link">' . $cat['name'] . '</a></li>';
                        }
                    }
                    ?>
                    <li class="header_list-item"><a href="<?php echo BASE_URL; ?>contact.php" class="header_list-item--link">Liên hệ</a></li>
                    <li class="header_list-item"><a href="<?php echo BASE_URL; ?>policy.php" class="header_list-item--link">Chính sách</a></li>
                </ul>
            </div>
        </div>
    </header>
    <div id="global-toast" class="global-toast-alert toast-hidden">
        <i id="global-toast-icon" class="fas fa-check-circle"></i>
        <span id="global-toast-msg"></span>
    </div>

    <script>
        // 1. Hàm hiển thị Toast bằng JavaScript (Dành cho luồng chạy AJAX phản hồi)
        function showToast(message, type = 'success') {
            const toast = document.getElementById('global-toast');
            const toastMsg = document.getElementById('global-toast-msg');
            const toastIcon = document.getElementById('global-toast-icon');

            if (!toast || !toastMsg) return;

            // Đổ nội dung thông báo động
            toastMsg.innerText = message;

            // Đổi màu sắc giao diện tương ứng theo type
            if (type === 'success') {
                toastIcon.className = "fas fa-check-circle";
                toast.className = "global-toast-alert alert-success";
            } else {
                toastIcon.className = "fas fa-exclamation-circle";
                toast.className = "global-toast-alert alert-danger";
            }

            // Hiện Toast gỡ bỏ class ẩn
            toast.classList.remove('toast-hidden');

            // Đặt bộ đếm thời gian: Đúng 3 giây (3000ms) sẽ tự động biến mất
            if (window.globalToastTimeout) clearTimeout(window.globalToastTimeout);
            window.globalToastTimeout = setTimeout(function() {
                toast.classList.add('toast-hidden');
            }, 3000);
        }

        // 2. Tự động kiểm tra hiển thị khi trang vừa load xong (Dành cho luồng đồng bộ PHP Session)
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success'])): ?>
                showToast("<?= $_SESSION['success'] ?>", 'success');
                <?php unset($_SESSION['success']); ?>
            <?php elseif (isset($_SESSION['error'])): ?>
                showToast("<?= $_SESSION['error'] ?>", 'error');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
    </script>