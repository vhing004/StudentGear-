<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra quyền truy cập (Admin/Staff)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Kiểm tra xem sản phẩm có nằm trong đơn hàng nào không
    // Dựa trên bảng order_items trong studentgear.sql
    $check_sql = "SELECT COUNT(*) as total FROM order_items WHERE product_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        // TRƯỜNG HỢP 1: Sản phẩm đã có trong đơn hàng -> Không được xóa cứng
        // Chuyển hướng về với thông báo lỗi
        header("Location: ../pages/products.php?msg=error_has_order");
        exit();
    } else {
        // TRƯỜNG HỢP 2: Sản phẩm chưa có đơn hàng nào -> Cho phép xóa

        // Lấy đường dẫn ảnh để xóa file vật lý trên server (tối ưu dung lượng)
        $img_sql = "SELECT image FROM products WHERE id = ?";
        $stmt_img = $conn->prepare($img_sql);
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $product = $stmt_img->get_result()->fetch_assoc();

        // Thực hiện xóa trong DB
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $stmt_del = $conn->prepare($delete_sql);
        $stmt_del->bind_param("i", $id);

        if ($stmt_del->execute()) {
            // Xóa file ảnh vật lý nếu không phải link web
            if ($product['image'] && !str_starts_with($product['image'], 'http')) {
                if (file_exists("../../" . $product['image'])) {
                    unlink("../../" . $product['image']);
                }
            }
            header("Location: ../pages/products.php?msg=deleted");
        } else {
            header("Location: ../pages/products.php?msg=error_delete");
        }
    }
} else {
    header("Location: ../pages/products.php");
}
