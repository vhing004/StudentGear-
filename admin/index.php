<?php
// 1. Khởi động session và kết nối cơ sở dữ liệu
session_start();
require_once '../config/db.php';

/**
 * 2. LOGIC BẢO MẬT: Chỉ Admin/Staff mới được vào
 * Kiểm tra user_id (đã đăng nhập) và role (là tài khoản admin)
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Nếu không phải admin, đẩy về trang login ở thư mục gốc
    header("Location: " . BASE_URL . "../auth/login.php");
    exit();
}

// 3. TRUY VẤN DỮ LIỆU THỐNG KÊ (Dashboard Logic)
// Lấy tổng quan doanh thu, đơn hàng từ database
$sql_stats = "SELECT 
    COUNT(id) as total_orders, 
    SUM(total_price) as total_revenue
    FROM orders";
$res_stats = $conn->query($sql_stats)->fetch_assoc();

// Lấy tổng số khách hàng
$sql_users = "SELECT COUNT(id) as total_customers FROM users WHERE is_active = 1";
$res_users = $conn->query($sql_users)->fetch_assoc();

// Lấy danh sách đơn hàng mới nhất
$sql_recent = "SELECT o.*, u.fullname FROM orders o 
               JOIN users u ON o.user_id = u.id 
               ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản trị - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-body">

    <div class="admin-wrapper">
        <?php include_once './includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <div class="header-title">
                    <h2>Dashboard</h2>
                    <p>Thống kê hiệu quả kinh doanh của cửa hàng.</p>
                </div>
                <div class="user-profile">
                    <span>Chào, <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong></span>
                    <small><?= strtoupper($_SESSION['role']) ?></small>
                </div>
            </header>

            <section class="stats-grid">
                <div class="card-counter bg-revenue">
                    <i class="fas fa-money-bill-wave"></i>
                    <div class="card-counter__info">
                        <h3><?= number_format($res_stats['total_revenue'] ?? 0, 0, ',', '.') ?>₫</h3>
                        <p>Doanh thu</p>
                    </div>
                </div>
                <div class="card-counter bg-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <div class="card-counter__info">
                        <h3><?= $res_stats['total_orders'] ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                </div>
                <div class="card-counter bg-customers">
                    <i class="fas fa-user-friends"></i>
                    <div class="card-counter__info">
                        <h3><?= $res_users['total_customers'] ?></h3>
                        <p>Khách hàng</p>
                    </div>
                </div>
            </section>

            <section class="table-container">
                <h5>Đơn hàng mới nhất</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Giá trị</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['order_code'] ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= number_format($row['total_price'], 0, ',', '.') ?>₫</td>
                                <td>
                                    <span class="status-badge badge-<?= ($row['status'] == 'pending') ? 'warning' : 'success' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td><a href="order_detail.php?id=<?= $row['id'] ?>" class="action-link">Xem chi tiết</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>

</html>