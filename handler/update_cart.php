<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Trường hợp cập nhật từ form số lượng (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'], $_POST['quantity'])) {

    $cart_id  = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($cart_id > 0 && $quantity > 0) {
        // Kiểm tra stock trước khi cập nhật
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

            if ($quantity > $product['stock']) {
                $quantity = $product['stock']; // Giới hạn theo stock
            }

            $sql_update = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Giỏ hàng đã được cập nhật!";
            } else {
                $_SESSION['error'] = "Cập nhật thất bại!";
            }
        }
    }
}

// Trường hợp cập nhật tất cả (từ nút <a> Cập nhật giỏ hàng)
elseif (isset($_GET['update_all'])) {
    $_SESSION['success'] = "Giỏ hàng đã được cập nhật!";
    // Hiện tại chỉ thông báo, sau này có thể mở rộng để xử lý nhiều sản phẩm
}

// Quay về trang giỏ hàng
header("Location: " . BASE_URL . "pages/cart.php");
exit;
