<?php
session_start();
require_once '../config/db.php';

// Nếu đã đăng nhập thì không cho vào trang đăng ký
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    $fullname = trim($_POST['fullname']);

    // 1. Kiểm tra trống
    if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc!";
    }
    // 2. Kiểm tra mật khẩu khớp nhau
    elseif ($password !== $re_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    }
    // 3. Kiểm tra độ dài mật khẩu
    elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        // 4. Kiểm tra username hoặc email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Tên đăng nhập hoặc Email đã được sử dụng!";
        } else {
            // 5. Mã hóa mật khẩu và lưu vào DB
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, fullname, is_active) VALUES (?, ?, ?, ?, 1)");
            $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $fullname);

            if ($insert_stmt->execute()) {
                $success = "Đăng ký thành công! Đang chuyển hướng sang đăng nhập...";
                echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại sau!";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - StudentGear</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <style>
        /* CSS dùng chung với login để đồng bộ */
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f4f4f4;
            padding: 20px;
        }

        .auth-form {
            width: 450px;
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
            text-transform: uppercase;
        }

        .auth-form__group {
            margin-bottom: 15px;
        }

        .auth-form__input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            outline: none;
        }

        .auth-form__input:focus {
            border-color: #d0021c;
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

        .auth-form__success {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #c8e6c9;
        }

        .btn--primary {
            width: 100%;
            height: 45px;
            background: #d0021c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }

        .auth-form__switch {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .auth-form__link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <div class="auth-form">
            <h2 class="auth-form__heading">ĐĂNG KÝ</h2>

            <?php if (!empty($error)): ?>
                <div class="auth-form__error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="auth-form__success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="auth-form__group">
                    <input type="text" name="fullname" class="auth-form__input" placeholder="Họ và tên (*)" value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                </div>
                <div class="auth-form__group">
                    <input type="text" name="username" class="auth-form__input" placeholder="Tên đăng nhập (*)" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                </div>
                <div class="auth-form__group">
                    <input type="email" name="email" class="auth-form__input" placeholder="Email (*)" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                <div class="auth-form__group">
                    <input type="password" name="password" class="auth-form__input" placeholder="Mật khẩu (*)" required>
                </div>
                <div class="auth-form__group">
                    <input type="password" name="re_password" class="auth-form__input" placeholder="Nhập lại mật khẩu (*)" required>
                </div>

                <button type="submit" name="reg_submit" class="btn--primary">ĐĂNG KÝ NGAY</button>
            </form>

            <div class="auth-form__switch">
                Đã có tài khoản? <a href="login.php" class="auth-form__link">Đăng nhập ngay</a>
            </div>
        </div>
    </div>

</body>

</html>