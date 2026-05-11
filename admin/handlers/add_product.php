<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $discount = $_POST['discount_percent'];
    $description = $_POST['description'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    // Tạo slug tự động
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // XỬ LÝ ẢNH
    $image_path = "assets/images/products/default.jpg"; // Ảnh mặc định nếu lỗi

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Tạo tên file duy nhất để không bị trùng (dùng timestamp + tên gốc đã lọc)
            $new_filename = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $filename);
            $target_dir = "./assets/images/products/";

            // Tạo thư mục nếu chưa có
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_filename)) {
                $image_path = "assets/images/products/" . $new_filename;
            }
        }
    }

    $sql = "INSERT INTO products (name, description, price, cost_price, stock, category_id, image, slug, is_featured, is_new, discount_percent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddiissiid", $name, $description, $price, $cost_price, $stock, $category_id, $image_path, $slug, $is_featured, $is_new, $discount);

    if ($stmt->execute()) {
        header("Location: ../pages/products.php?success=1");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
