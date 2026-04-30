<?php
// session_start();
// require_once '../config/db.php';

// // Kiểm tra đăng nhập
// if (!isset($_SESSION['user_id'])) {
//     die("Bạn cần đăng nhập để thực hiện hành động này.");
// }

// $user_id = $_SESSION['user_id'];
// $cart_id = isset($_GET['cart_id']) ? intval($_GET['cart_id']) : 0;

// if ($cart_id > 0) {
//     // Xóa sản phẩm nhưng phải khớp với user_id để tránh xóa nhầm của người khác
//     $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ii", $cart_id, $user_id);

//     if ($stmt->execute()) {
//         if ($stmt->affected_rows > 0) {
//             echo "<script>alert('Đã xóa sản phẩm khỏi giỏ hàng!'); window.location.href='../pages/cart.php';</script>";
//         } else {
//             echo "<script>alert('Lỗi: Sản phẩm không tồn tại hoặc không thuộc về bạn.'); window.location.href='../pages/cart.php';</script>";
//         }
//     } else {
//         echo "<script>alert('Lỗi hệ thống: Không thể xóa sản phẩm.'); window.location.href='../pages/cart.php';</script>";
//     }
//     $stmt->close();
// } else {
//     echo "<script>alert('ID sản phẩm không hợp lệ.'); window.location.href='../pages/cart.php';</script>";
// }


// <?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Nhận cart_id từ GET (do dùng thẻ <a>)
$cart_id = isset($_GET['cart_id']) ? (int)$_GET['cart_id'] : 0;

if ($cart_id <= 0) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ!";
    header("Location: " . BASE_URL . "pages/cart.php");
    exit;
}

// Kiểm tra xem cart_id có thuộc về user hiện tại không (an toàn)
$sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Đã xóa sản phẩm khỏi giỏ hàng!";
    } else {
        $_SESSION['error'] = "Không tìm thấy sản phẩm trong giỏ hàng!";
    }
} else {
    $_SESSION['error'] = "Có lỗi xảy ra khi xóa sản phẩm!";
}

header("Location: " . BASE_URL . "pages/cart.php");
exit;
