<?php
require_once '../config/db.php';
session_start();

// 1. Kiểm tra tham số và đăng nhập
if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: ../pages/history_order.php');
    exit();
}

$order_id = intval($_GET['order_id']); // Ép kiểu số nguyên để an toàn
$user_id = $_SESSION['user_id'];

// 2. Truy vấn lấy thông tin chi tiết sản phẩm từ đơn hàng cũ
// Phải JOIN với bảng products để lấy đầy đủ thông tin (ảnh, giá hiện tại, discount)
$sql = "SELECT p.id, p.name, p.image, p.price, p.discount_percent, oi.quantity as buy_qty
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.order_id = '$order_id' AND o.user_id = '$user_id'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // 3. Xóa các dữ liệu thanh toán cũ để chuẩn bị cho đơn mới
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_type']);

    $reorder_items = [];
    while ($row = $result->fetch_assoc()) {
        $reorder_items[] = $row; // Đưa sản phẩm vào mảng
    }

    // 4. Lưu vào Session mà trang checkout.php đang chờ đợi
    $_SESSION['checkout_items'] = $reorder_items;
    $_SESSION['checkout_type'] = 'reorder';

    // 5. Chuyển hướng thẳng đến trang checkout
    header('Location: ../pages/checkout.php');
    exit();
} else {
    // Nếu bị lỗi "Đơn hàng không tồn tại", bạn hãy bỏ comment dòng dưới để xem lỗi thật
    // die("Lỗi: Không tìm thấy sản phẩm. SQL: " . $sql);
    header('Location: ../pages/history_order.php?error=notfound');
    exit();
}
