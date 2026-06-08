<?php
session_start();
require_once '../config/db.php'; // Đảm bảo kết nối $conn và định nghĩa BASE_URL

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vui lòng đăng nhập để thực hiện hành động này!";
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra phương thức xử lý và nút bấm submit
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['update_cart_action'])) {
    header("Location: " . BASE_URL . "pages/cart.php");
    exit;
}

$errors = 0;

// Kiểm tra mảng dữ liệu gửi lên từ form
if (isset($_POST['cart_items']) && is_array($_POST['cart_items'])) {

    foreach ($_POST['cart_items'] as $item) {
        $cart_id  = (int)$item['cart_id'];
        $quantity = (int)$item['quantity'];

        if ($cart_id > 0 && $quantity > 0) {
            // 1. Kiểm tra giới hạn số lượng tồn kho của sản phẩm
            $sql_check = "SELECT p.stock 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.id = ? AND c.user_id = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();

                // Nếu số lượng người dùng chỉnh sửa vượt quá kho, tự động gán bằng max kho
                if ($quantity > $product['stock']) {
                    $quantity = $product['stock'];
                }

                // 2. Tiến hành cập nhật số lượng mới vào Database
                $sql_update = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
                $stmt_up = $conn->prepare($sql_update);
                $stmt_up->bind_param("iii", $quantity, $cart_id, $user_id);

                if (!$stmt_up->execute()) {
                    $errors++;
                }
            }
        }
    }
}

// 3. Đóng gói thông báo vào Session tương ứng dựa theo kết quả xử lý
if ($errors === 0) {
    $_SESSION['success'] = "Giỏ hàng của bạn đã được cập nhật thành công!";
} else {
    $_SESSION['error'] = "Có lỗi xảy ra trong quá trình cập nhật số lượng giỏ hàng!";
}

// 4. CHUYỂN HƯỚNG QUAY TRỞ LẠI: Hệ thống Global Toast tại header.php sẽ tự động bắt được và hiển thị
header("Location: " . BASE_URL . "pages/cart.php");
exit;
