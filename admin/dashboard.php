<?php
session_start();
require_once '../config/db.php';

// 1. Kiểm tra quyền truy cập (Chỉ Admin/Staff)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// 2. Truy vấn dữ liệu thống kê từ Database
// Tổng doanh thu và số đơn hàng
$sql_stats = "SELECT 
    COUNT(id) as total_orders, 
    SUM(total_price) as total_revenue,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders
    FROM orders";
$res_stats = $conn->query($sql_stats)->fetch_assoc();

// Tổng số khách hàng
$sql_users = "SELECT COUNT(id) as total_customers FROM users WHERE is_active = 1";
$res_users = $conn->query($sql_users)->fetch_assoc();

// Tổng số sản phẩm
$sql_products = "SELECT COUNT(id) as total_products FROM products";
$res_products = $conn->query($sql_products)->fetch_assoc();

// Danh sách 5 đơn hàng mới nhất
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
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-body">

    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar__brand">
                <h4>Student<span style="color: #d0021c;">Gear</span></h4>
            </div>
            <nav class="sidebar__nav">
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="products.php"><i class="fas fa-laptop"></i> Sản phẩm</a>
                <a href="categories.php"><i class="fas fa-list"></i> Danh mục</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a>
                <a href="users.php"><i class="fas fa-users"></i> Khách hàng</a>
                <div class="sidebar__divider"></div>
                <a href="../auth/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Tổng quan hệ thống</h2>
                <div class="user-profile">
                    <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
                    <small>(<?php echo ucfirst($_SESSION['role']); ?>)</small>
                </div>
            </header>

            <section class="stats-grid">
                <div class="card-counter bg-revenue">
                    <i class="fas fa-money-bill-wave"></i>
                    <div class="card-counter__info">
                        <h3><?php echo number_format($res_stats['total_revenue'] ?? 0, 0, ',', '.'); ?>₫</h3>
                        <p>Tổng doanh thu</p>
                    </div>
                </div>

                <div class="card-counter bg-orders">
                    <i class="fas fa-shopping-basket"></i>
                    <div class="card-counter__info">
                        <h3><?php echo $res_stats['total_orders']; ?></h3>
                        <p>Tổng đơn hàng</p>
                    </div>
                </div>

                <div class="card-counter bg-customers">
                    <i class="fas fa-user-friends"></i>
                    <div class="card-counter__info">
                        <h3><?php echo $res_users['total_customers']; ?></h3>
                        <p>Khách hàng</p>
                    </div>
                </div>

                <div class="card-counter bg-products">
                    <i class="fas fa-box-open"></i>
                    <div class="card-counter__info">
                        <h3><?php echo $res_products['total_products']; ?></h3>
                        <p>Sản phẩm</p>
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
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $row['order_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo number_format($row['total_price'], 0, ',', '.'); ?>₫</td>
                                <td>
                                    <span class="status-badge badge-<?php echo ($row['status'] == 'pending') ? 'warning' : 'success'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $row['id']; ?>" class="action-link">Chi tiết</a>
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