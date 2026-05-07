<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $is_active = intval($_POST['is_active']); // Nhận giá trị mới từ Modal

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // ... (Logic xử lý upload ảnh như cũ) ...
        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=?, image=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssssii", $name, $slug, $description, $db_path, $is_active, $id);
    } else {
        // Cập nhật thông tin chữ và trạng thái
        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=?, is_active=? WHERE id=?");
        $stmt->bind_param("sssii", $name, $slug, $description, $is_active, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../pages/categories.php?msg=updated");
    } else {
        header("Location: ../pages/categories.php?msg=error");
    }
    $stmt->close();
}
