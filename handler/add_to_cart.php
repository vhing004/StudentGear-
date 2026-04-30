<?php
session_start();
require_once '../config/db.php'; // Chứa kết nối $conn và BASE_URL

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!";
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_to_cart'])) {
    header("Location: " . BASE_URL);
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Kiểm tra dữ liệu hợp lệ
if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['error'] = "Dữ liệu không hợp lệ!";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Kiểm tra sản phẩm tồn tại và còn hàng
$sql_check = "SELECT id, stock, name FROM products WHERE id = ? AND is_active = 1";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Sản phẩm không tồn tại hoặc đã ngừng bán!";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$product = $result->fetch_assoc();

if ($product['stock'] < $quantity) {
    $_SESSION['error'] = "Sản phẩm chỉ còn " . $product['stock'] . " cái. Không đủ số lượng yêu cầu!";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
$sql_check_cart = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql_check_cart);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows > 0) {
    // Sản phẩm đã có trong giỏ → Cập nhật số lượng
    $cart_item = $cart_result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + $quantity;

    // Không cho vượt quá stock
    if ($new_quantity > $product['stock']) {
        $new_quantity = $product['stock'];
    }

    $sql_update = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Đã cập nhật số lượng sản phẩm trong giỏ hàng!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật giỏ hàng!";
    }
} else {
    // Thêm mới vào giỏ hàng
    $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Đã thêm sản phẩm vào giỏ hàng thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi thêm vào giỏ hàng!";
    }
}

// Quay lại trang chi tiết sản phẩm
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
