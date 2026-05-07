<?php
// 1. Khởi động session và kết nối DB
session_start();
require_once '../config/db.php';

// Nếu người dùng đã đăng nhập rồi thì đá về trang tương ứng
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        header("Location: " . BASE_URL . "admin/index.php"); // Đường dẫn trang admin của bạn
    } else {
        header("Location: " . BASE_URL . "index.php");
    }
    exit();
}

$error = "";

// 2. Xử lý khi người dùng nhấn nút Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // --- BƯỚC A: Kiểm tra trong bảng admin_users trước ---
        $stmt_admin = $conn->prepare("SELECT id, username, password, fullname, role, is_active FROM admin_users WHERE username = ?");
        $stmt_admin->bind_param("s", $username);
        $stmt_admin->execute();
        $res_admin = $stmt_admin->get_result();

        if ($res_admin->num_rows > 0) {
            $user = $res_admin->fetch_assoc();

            if ($user['is_active'] == 0) {
                $error = "Tài khoản Admin đã bị khóa!";
            } elseif (password_verify($password, $user['password']) || $password == $user['password']) {
                // Đăng nhập Admin thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role']; // Lưu role để phân biệt

                header("Location: " . BASE_URL . "admin/index.php"); // Chuyển hướng sang Admin
                exit();
            } else {
                $error = "Mật khẩu Admin không chính xác!";
            }
        } else {
            // --- BƯỚC B: Nếu không phải admin, kiểm tra bảng users thường ---
            $stmt = $conn->prepare("SELECT id, username, password, fullname, is_active FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if ($user['is_active'] == 0) {
                    $error = "Tài khoản của bạn đã bị khóa!";
                } else {
                    if (password_verify($password, $user['password']) || $password == $user['password']) {
                        // Đăng nhập User thành công
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['fullname'] = $user['fullname'];
                        // Không set $_SESSION['role'] hoặc set là 'customer' tùy bạn

                        header("Location: " . BASE_URL . "index.php");
                        exit();
                    } else {
                        $error = "Mật khẩu không chính xác!";
                    }
                }
            } else {
                $error = "Tên đăng nhập không tồn tại!";
            }
            $stmt->close();
        }
        $stmt_admin->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - StudentGear</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <style>
        /* ... Giữ nguyên CSS cũ của bạn ... */
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f4f4f4;
        }

        .auth-form {
            width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .auth-form__heading {
            text-align: center;
            font-size: 24px;
            color: #d0021c;
            margin-bottom: 20px;
        }

        .auth-form__group {
            margin-bottom: 15px;
        }

        .auth-form__input {
            width: 100%;
            height: 40px;
            padding: 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .auth-form__error {
            color: #d0021c;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #ffcdd2;
        }

        .btn--primary {
            width: 100%;
            height: 40px;
            background: #d0021c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .auth-form__switch {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .auth-form__link {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-form">
            <h2 class="auth-form__heading">ĐĂNG NHẬP</h2>
            <?php if (!empty($error)): ?>
                <div class="auth-form__error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="auth-form__group">
                    <input type="text" name="username" class="auth-form__input" placeholder="Tên đăng nhập" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                </div>
                <div class="auth-form__group">
                    <input type="password" name="password" class="auth-form__input" placeholder="Mật khẩu" required>
                </div>
                <button type="submit" name="login_submit" class="btn--primary">ĐĂNG NHẬP</button>
            </form>
            <div class="auth-form__switch">
                Bạn chưa có tài khoản? <a href="reg.php" class="auth-form__link">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</body>

</html>