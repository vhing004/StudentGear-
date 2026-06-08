<?php
session_start();
require_once '../config/db.php'; // Đường dẫn tới file kết nối CSDL của bạn

// Kiểm tra nếu người dùng chưa đăng nhập thì đẩy về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin mới nhất của người dùng từ CSDL
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Tài khoản không tồn tại hoặc đã bị khóa.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản cá nhân - StudentGear</title>
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>assets/images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/header.php' ?>
    <div class="profile-container">
        <div class="profile-header">
            <h2>Thông tin tài khoản cá nhân</h2>
            <p style="color: #6b7280;">Quản lý và cập nhật thông tin cá nhân của bạn trên StudentGear</p>
        </div>

        <!-- Notification Message -->
        <?php if (isset($_GET['status'])): ?>
            <?php
            $toast_class = 'alert-success';
            $toast_icon = 'fa-check-circle';
            $toast_msg = '';

            if ($_GET['status'] === 'success') {
                $toast_msg = 'Cập nhật thông tin cá nhân thành công!';
                $toast_class = 'alert-success';
                $toast_icon = 'fa-check-circle';
            } elseif ($_GET['status'] === 'error') {
                $toast_msg = 'Có lỗi xảy ra trong quá trình cập nhật. Vui lòng thử lại.';
                $toast_class = 'alert-danger';
                $toast_icon = 'fa-exclamation-circle';
            } elseif ($_GET['status'] === 'invalid_file') {
                $toast_msg = 'Định dạng file không hợp lệ (Chỉ nhận .jpg, .jpeg, .png, .webp).';
                $toast_class = 'alert-danger';
                $toast_icon = 'fa-image';
            }
            ?>

            <?php if (!empty($toast_msg)): ?>
                <div id="toast-notification" class="alert <?= $toast_class ?>">
                    <i class="fas <?= $toast_icon ?>"></i> <?= htmlspecialchars($toast_msg) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form action="../handler/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-header">
                <div class="avatar-wrapper">
                    <?php
                    $avatar_path = !empty($user['avatar']) ? $user['avatar'] : 'assets/images/default-avatar.png';
                    ?>
                    <img id="avatar-preview" src="../<?= htmlspecialchars($avatar_path) ?>" alt="Avatar">

                    <label for="avatar-input" class="avatar-upload-btn">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="avatar" id="avatar-input" style="display: none;" accept="image/*">
                </div>
                <strong>@<?= htmlspecialchars($user['username']) ?></strong>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Tên đăng nhập (Username)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Địa chỉ Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="fullname">Họ và tên</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" placeholder="Nhập họ và tên">
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                </div>
            </div>

            <button type="submit" class="btn-submit">Lưu thay đổi</button>
        </form>
    </div>
    <?php include '../includes/footer.php' ?>

</body>
<script src="../assets/js/profile.js"></script>

</html>