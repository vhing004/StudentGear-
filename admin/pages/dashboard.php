<?php
session_start();
require_once '../config/db.php';

// 1. Kiểm tra quyền truy cập Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// 2. Truy vấn dữ liệu thống kê (Logic giữ nguyên)
$sql_stats = "SELECT 
    COUNT(id) as total_orders, 
    SUM(total_price) as total_revenue,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders
    FROM orders";
$res_stats = $conn->query($sql_stats)->fetch_assoc();

$sql_users = "SELECT COUNT(id) as total_customers FROM users WHERE is_active = 1";
$res_users = $conn->query($sql_users)->fetch_assoc();

$sql_products = "SELECT COUNT(id) as total_products FROM products";
$res_products = $conn->query($sql_products)->fetch_assoc();

$sql_recent_orders = "SELECT o.*, u.fullname 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($sql_recent_orders);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body class="admin-body">

    <div class="admin-wrapper">
        <?php include_once 'sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <div class="header-title">
                    <h2>Tổng quan hệ thống</h2>
                    <p>Chào mừng bạn quay trở lại, hệ thống đang hoạt động ổn định.</p>
                </div>
                <div class="user-profile">
                    <span>Xin chào, <strong><?= htmlspecialchars($_SESSION['fullname']); ?></strong></span>
                    <small><?= ucfirst($_SESSION['role']); ?></small>
                </div>
            </header>

            <section class="stats-grid">
                <div class="card-counter bg-revenue">
                    <i class="fas fa-money-bill-wave"></i>
                    <div class="card-counter__info">
                        <h3><?= number_format($res_stats['total_revenue'] ?? 0, 0, ',', '.'); ?>₫</h3>
                        <p>Tổng doanh thu</p>
                    </div>
                </div>

                <div class="card-counter bg-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <div class="card-counter__info">
                        <h3><?= $res_stats['total_orders']; ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                </div>

                <div class="card-counter bg-customers">
                    <i class="fas fa-user-friends"></i>
                    <div class="card-counter__info">
                        <h3><?= $res_users['total_customers']; ?></h3>
                        <p>Khách hàng</p>
                    </div>
                </div>

                <div class="card-counter bg-products">
                    <i class="fas fa-box-open"></i>
                    <div class="card-counter__info">
                        <h3><?= $res_products['total_products']; ?></h3>
                        <p>Sản phẩm</p>
                    </div>
                </div>
            </section>

            <section class="table-container">
                <div class="table-header">
                    <h5>Đơn đặt hàng gần đây</h5>
                    <a href="orders.php" class="view-all">Xem tất cả</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['order_code']; ?></strong></td>
                                <td><?= htmlspecialchars($row['fullname']); ?></td>
                                <td><?= number_format($row['total_price'], 0, ',', '.'); ?>₫</td>
                                <td>
                                    <span class="status-badge badge-<?= ($row['status'] == 'pending') ? 'warning' : 'success'; ?>">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?= $row['id']; ?>" class="action-link">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>

</html>