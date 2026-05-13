<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $order_id = intval($_POST['id']);
    $new_status = $_POST['status'];

    // 1. Kiểm tra trạng thái hiện tại của đơn hàng
    $check_sql = "SELECT payment_status FROM orders WHERE id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $order_id);
    $stmt_check->execute();
    $current_order = $stmt_check->get_result()->fetch_assoc();
    $current_payment_status = $current_order['payment_status'];

    // 2. Thiết lập các cột thời gian và trạng thái thanh toán
    $update_fields = "status = ?";
    $params = [$new_status];
    $types = "s";

    // Nếu chuyển sang 'delivered' (Đã giao), ép buộc chuyển sang 'paid'
    if ($new_status === 'delivered') {
        $update_fields .= ", payment_status = 'paid', delivered_at = NOW()";
    }
    // Nếu là các trạng thái khác, ta giữ nguyên payment_status hiện tại 
    // (Nếu đã là 'paid' thì vẫn sẽ là 'paid')

    // Thêm các mốc thời gian khác dựa trên ENUM của bảng orders
    if ($new_status === 'confirmed') $update_fields .= ", confirmed_at = NOW()";
    if ($new_status === 'shipping')  $update_fields .= ", shipped_at = NOW()";

    $sql = "UPDATE orders SET $update_fields WHERE id = ?";

    // Thêm ID vào cuối mảng tham số
    $params[] = $order_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header("Location: ../pages/orders.php?msg=updated");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
