<?php
session_start();
require_once '../config/db.php'; // Khởi tạo kết nối $conn

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../pages/profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : null;
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
$address = isset($_POST['address']) ? trim($_POST['address']) : null;

// 1. Lấy thông tin avatar cũ từ CSDL để phòng trường hợp không đổi ảnh mới
$stmt_old = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt_old->bind_param("i", $user_id);
$stmt_old->execute();
$user_old = $stmt_old->get_result()->fetch_assoc();
$avatar_path = $user_old['avatar'];

// 2. Kiểm tra và xử lý tệp tin tải lên (Upload Avatar)
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['avatar']['tmp_name'];
    $file_name = $_FILES['avatar']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Khởi tạo các định dạng ảnh hợp lệ
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($file_ext, $allowed_extensions)) {
        // Tạo thư mục uploads/avatars nếu chưa tồn tại
        $upload_dir = '../assets/images/avatars/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Đặt tên ảnh duy nhất tránh trùng lặp: ví dụ user_1_171123456.jpg
        $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_path)) {
            // Xóa file ảnh cũ trên ổ đĩa nếu có (tránh rác hệ thống, trừ ảnh mặc định)
            if (!empty($avatar_path) && file_exists('../' . $avatar_path)) {
                @unlink('../' . $avatar_path);
            }

            // Lưu đường dẫn tương đối vào DB để hiển thị ở Client thuận tiện
            $avatar_path = 'assets/images/avatars/' . $new_file_name;
        }
    } else {
        // Đẩy về nếu file tải lên không phải là ảnh
        header("Location: ../pages/profile.php?status=invalid_file");
        exit();
    }
}

// 3. Thực hiện cập nhật dữ liệu an toàn vào CSDL bằng Prepared Statement
$sql_update = "UPDATE users SET fullname = ?, phone = ?, address = ?, avatar = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ssssi", $fullname, $phone, $address, $avatar_path, $user_id);

if ($stmt_update->execute()) {
    header("Location: ../pages/profile.php?status=success");
} else {
    header("Location: ../pages/profile.php?status=error");
}
exit();
