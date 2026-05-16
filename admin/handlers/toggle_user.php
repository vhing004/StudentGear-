<?php
session_start();
require_once '../../config/db.php';

// 1. Kiểm tra quyền bảo mật: Chỉ Admin/Staff mới có quyền thao tác hành động này
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// 2. Kiểm tra và lọc dữ liệu ID truyền lên từ URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id > 0) {
    // 3. Truy vấn lấy trạng thái hoạt động hiện tại của User
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Đảo ngược trạng thái: Nếu đang là 1 (mở) thì thành 0 (khóa), nếu là 0 thì thành 1
        $new_status = ($user['is_active'] == 1) ? 0 : 1;

        // 4. Tiến hành cập nhật trạng thái mới vào cơ sở dữ liệu
        $stmt_update = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $new_status, $user_id);

        if ($stmt_update->execute()) {
            // Cập nhật thành công, quay về trang danh sách kèm thông báo
            header("Location: ../pages/users.php?msg=toggle_success");
            exit();
        } else {
            // Lỗi câu lệnh UPDATE
            header("Location: ../pages/users.php?msg=error");
            exit();
        }
    } else {
        // Không tìm thấy User với ID tương ứng
        header("Location: ../pages/users.php?msg=user_not_found");
        exit();
    }
} else {
    // ID không hợp lệ
    header("Location: ../pages/users.php?msg=invalid_id");
    exit();
}
