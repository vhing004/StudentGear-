<?php
session_start();
require_once '../config/db.php';

// Thiết lập phản hồi trả về định dạng JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Đọc dữ liệu JSON từ yêu cầu của JavaScript
$inputData = json_decode(file_get_contents('php://input'), true);

if (isset($inputData['cart_items']) && is_array($inputData['cart_items'])) {
    $errors = 0;

    // Bắt đầu lặp qua từng sản phẩm để cập nhật
    foreach ($inputData['cart_items'] as $item) {
        $cart_id  = (int)$item['cart_id'];
        $quantity = (int)$item['quantity'];

        if ($cart_id > 0 && $quantity > 0) {
            // 2. Kiểm tra stock của sản phẩm đó trong Database[cite: 1, 2]
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

                // Giới hạn số lượng theo tồn kho thực tế[cite: 1, 2]
                if ($quantity > $product['stock']) {
                    $quantity = $product['stock'];
                }

                // 3. Thực hiện cập nhật số lượng mới
                $sql_update = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
                $stmt_up = $conn->prepare($sql_update);
                $stmt_up->bind_param("iii", $quantity, $cart_id, $user_id);

                if (!$stmt_up->execute()) {
                    $errors++;
                }
            }
        }
    }

    if ($errors === 0) {
        echo json_encode(['success' => true, 'message' => 'Giỏ hàng đã được cập nhật!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có ' . $errors . ' sản phẩm không thể cập nhật.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy dữ liệu để cập nhật.']);
}
exit;
