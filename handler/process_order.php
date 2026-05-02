<?php
// session_start();
// require_once '../config/db.php';

// if (isset($_POST['btn_order']) && isset($_SESSION['user_id'])) {
//     $user_id = $_SESSION['user_id'];
//     $order_code = 'ORD-' . strtoupper(uniqid()); // Tạo mã đơn hàng duy nhất
//     $shipping_name = $conn->real_escape_string($_POST['shipping_name']);
//     $shipping_phone = $conn->real_escape_string($_POST['shipping_phone']);
//     $shipping_address = $conn->real_escape_string($_POST['shipping_address']);
//     $payment_method = $_POST['payment_method'];
//     $note = $conn->real_escape_string($_POST['note']);
//     $total_price = $_POST['total_price'];
//     $shipping_fee = $_POST['shipping_fee'];

//     // 1. Bắt đầu Transaction để đảm bảo dữ liệu an toàn
//     $conn->begin_transaction();

//     try {
//         // 2. Chèn vào bảng orders (Mặc định status = 'pending')
//         $sql_order = "INSERT INTO orders (user_id, order_code, total_price, shipping_fee, shipping_address, shipping_phone, shipping_name, payment_method, note, status) 
//                       VALUES ('$user_id', '$order_code', '$total_price', '$shipping_fee', '$shipping_address', '$shipping_phone', '$shipping_name', '$payment_method', '$note', 'pending')";

//         if ($conn->query($sql_order)) {
//             $order_id = $conn->insert_id; // Lấy ID vừa tạo

//             // 3. Chèn chi tiết vào bảng order_items
//             foreach ($_SESSION['checkout_items'] as $item) {
//                 $p_id = $item['id'];
//                 $p_name = $conn->real_escape_string($item['name']);
//                 $qty = $item['buy_qty'];
//                 $price = $item['price'] * (1 - ($item['discount_percent'] / 100));
//                 $subtotal = $price * $qty;

//                 $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total_price) 
//                              VALUES ('$order_id', '$p_id', '$p_name', '$qty', '$price', '$subtotal')";
//                 $conn->query($sql_item);
//             }

//             // 4. Nếu mua từ giỏ hàng -> Xóa các sản phẩm đó trong bảng cart
//             if ($_SESSION['checkout_type'] === 'cart') {
//                 $conn->query("DELETE FROM cart WHERE user_id = $user_id");
//             }

//             // 5. Hoàn tất lưu dữ liệu
//             $conn->commit();

//             // Xóa session thanh toán và thông báo thành công
//             unset($_SESSION['checkout_items']);
//             unset($_SESSION['checkout_type']);

//             $_SESSION['success_order'] = $order_code;
//             header('Location: ../pages/history_order.php');
//             exit();
//         }
//     } catch (Exception $e) {
//         $conn->rollback(); // Hủy bỏ nếu có lỗi xảy ra
//         die("Lỗi đặt hàng: " . $e->getMessage());
//     }
// } else {
//     header('Location: ../index.php');
// }

// <?php
session_start();
require_once '../config/db.php';

if (isset($_POST['btn_order']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $order_code = 'ORD-' . strtoupper(uniqid());
    $shipping_name = $conn->real_escape_string($_POST['shipping_name']);
    $shipping_phone = $conn->real_escape_string($_POST['shipping_phone']);
    $shipping_address = $conn->real_escape_string($_POST['shipping_address']);
    $payment_method = $_POST['payment_method'];
    $note = $conn->real_escape_string($_POST['note']);
    $total_price = $_POST['total_price'];
    $shipping_fee = $_POST['shipping_fee'];

    $conn->begin_transaction();

    try {
        // 1. Chèn vào bảng orders (Mặc định status = 'pending')
        $sql_order = "INSERT INTO orders (user_id, order_code, total_price, shipping_fee, shipping_address, shipping_phone, shipping_name, payment_method, note, status) 
                      VALUES ('$user_id', '$order_code', '$total_price', '$shipping_fee', '$shipping_address', '$shipping_phone', '$shipping_name', '$payment_method', '$note', 'pending')";

        if ($conn->query($sql_order)) {
            $order_id = $conn->insert_id;

            // 2. Ghi lại lịch sử trạng thái đầu tiên (Hành động của người dùng)
            $sql_init_history = "INSERT INTO order_status_history (order_id, old_status, new_status, note) 
                                 VALUES ('$order_id', NULL, 'pending', 'Khách hàng đặt hàng thành công')";
            $conn->query($sql_init_history);

            // 3. Chèn chi tiết vào bảng order_items
            foreach ($_SESSION['checkout_items'] as $item) {
                $p_id = $item['id'];
                $p_name = $conn->real_escape_string($item['name']);
                $qty = $item['buy_qty'];
                $price = $item['price'] * (1 - ($item['discount_percent'] / 100));
                $subtotal = $price * $qty;

                $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total_price) 
                             VALUES ('$order_id', '$p_id', '$p_name', '$qty', '$price', '$subtotal')";
                $conn->query($sql_item);
            }

            // 4. Xóa giỏ hàng nếu thanh toán từ Cart
            if ($_SESSION['checkout_type'] === 'cart') {
                $conn->query("DELETE FROM cart WHERE user_id = $user_id");
            }

            $conn->commit();
            unset($_SESSION['checkout_items'], $_SESSION['checkout_type']);
            $_SESSION['success_order'] = $order_code;
            header('Location: ../pages/history_order.php');
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        die("Lỗi hệ thống: " . $e->getMessage());
    }
}
