<?php
session_start();
require_once '../config/db.php';

// Xóa dữ liệu cũ để làm mới phiên thanh toán
unset($_SESSION['checkout_items']);
unset($_SESSION['checkout_type']);

// TRƯỜNG HỢP 1: Mua ngay từ trang Detail (Gửi qua POST hoặc GET)
if (isset($_REQUEST['product_id']) && isset($_REQUEST['quantity'])) {
    $p_id = intval($_REQUEST['product_id']);
    $qty = intval($_REQUEST['quantity']);

    $sql = "SELECT id, name, image, price, discount_percent FROM products WHERE id = $p_id";
    $res = $conn->query($sql);

    if ($row = $res->fetch_assoc()) {
        $row['buy_qty'] = $qty; // Gán số lượng khách chọn từ trang Detail

        // QUAN TRỌNG: Đưa vào mảng để Checkout lặp được
        $_SESSION['checkout_items'][] = $row;
        $_SESSION['checkout_type'] = 'buy_now';

        header('Location: ../pages/checkout.php');
        exit();
    }
}

// TRƯỜNG HỢP 2: Thanh toán từ Cart (Sử dụng link ?from_cart=1 của bạn)
if (isset($_GET['from_cart']) && $_GET['from_cart'] == 1) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT c.quantity as buy_qty, p.id, p.name, p.image, p.price, p.discount_percent 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = $user_id";
    $res = $conn->query($sql);

    while ($row = $res->fetch_assoc()) {
        $_SESSION['checkout_items'][] = $row;
    }
    $_SESSION['checkout_type'] = 'cart';

    header('Location: ../pages/checkout.php');
    exit();
}
