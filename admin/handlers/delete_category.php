<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra quyền (chỉ admin/staff mới được thực hiện)
if (!isset($_SESSION['role'])) {
    exit("Unauthorized");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Bảo mật: ép kiểu số nguyên

    // Logic Xóa mềm: Chuyển trạng thái về 0
    $stmt = $conn->prepare("UPDATE categories SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Chuyển hướng về trang danh sách với thông báo thành công
        header("Location: ../pages/categories.php?msg=deactivated");
    } else {
        header("Location: ../pages/categories.php?msg=error");
    }
    exit();
}
