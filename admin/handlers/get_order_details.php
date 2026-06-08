<?php
require_once '../../config/db.php';

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // 1. Lấy thông tin vận chuyển + Thông tin tài khoản người đặt
    $sql_order = "SELECT o.*, u.fullname as account_name, u.email as account_email 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE o.id = ?";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $order_info = $stmt_order->get_result()->fetch_assoc();

    // 2. Lấy danh sách sản phẩm
    $sql_items = "SELECT oi.*, p.image 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Thêm vào file get_order_details.php
    // 3. Lấy lịch sử thay đổi trạng thái
    $sql_history = "SELECT h.*, a.fullname as admin_name 
                FROM order_status_history h 
                LEFT JOIN admin_users a ON h.changed_by = a.id 
                WHERE h.order_id = ? 
                ORDER BY h.created_at DESC";
    $stmt_hist = $conn->prepare($sql_history);
    $stmt_hist->bind_param("i", $order_id);
    $stmt_hist->execute();
    $history = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);

    // Trả về thêm mảng history
    echo json_encode([
        'order' => $order_info,
        'items' => $items,
        'history' => $history
    ]);
}
