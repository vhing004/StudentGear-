<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Tạo slug tự động
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // XỬ LÝ ẢNH
    $image_path = "assets/images/no-image.png"; // Ảnh mặc định nếu lỗi

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Tạo tên file duy nhất để không bị trùng (dùng timestamp + tên gốc đã lọc)
            $new_filename = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $filename);
            $target_dir = "../../assets/images/categories/";

            // Tạo thư mục nếu chưa có
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_filename)) {
                $image_path = "../../assets/images/categories/" . $new_filename;
            }
        }
    }

    $sql = "INSERT INTO categories (name, description, image, slug) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $description, $image_path, $slug);

    if ($stmt->execute()) {
        header("Location: ../pages/categories.php?success=1");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
