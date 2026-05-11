<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $discount = $_POST['discount_percent'];
    $description = $_POST['description'];

    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Tạo slug mới dựa trên tên cập nhật
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // Xử lý ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Có upload ảnh mới -> Xử lý upload
        $file_name = time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "../../assets/images/products/" . $file_name);
        $image_path = "assets/images/products/" . $file_name;

        // Cập nhật với ảnh mới
        $sql = "UPDATE products SET name=?, description=?, price=?, cost_price=?, stock=?, category_id=?, image=?, slug=?, is_featured=?, is_new=?, is_active=?, discount_percent=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddiisssiidi", $name, $description, $price, $cost_price, $stock, $category_id, $image_path, $slug, $is_featured, $is_new, $is_active, $discount, $id);
    } else {
        // Không upload ảnh mới -> Giữ nguyên ảnh cũ
        $sql = "UPDATE products SET name=?, description=?, price=?, cost_price=?, stock=?, category_id=?, slug=?, is_featured=?, is_new=?, is_active=?, discount_percent=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddiissiidi", $name, $description, $price, $cost_price, $stock, $category_id, $slug, $is_featured, $is_new, $is_active, $discount, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../pages/products.php?msg=updated");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
