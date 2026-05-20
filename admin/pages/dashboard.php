<?php
// 1. Kiểm tra quyền truy cập Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// 2. Truy vấn dữ liệu thống kê tổng quát
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

// 3. Thống kê doanh thu theo tháng (12 tháng gần nhất)
$sql_monthly_revenue = "SELECT 
    DATE_FORMAT(created_at, '%m/%Y') as month,
    MONTH(created_at) as month_num,
    YEAR(created_at) as year,
    COUNT(id) as order_count,
    SUM(total_price) as total_revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%m/%Y')
    ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC";
$monthly_revenue = $conn->query($sql_monthly_revenue);
$monthly_data = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $monthly_data[] = $row;
}

// 4. Khách hàng chi tiêu nhiều nhất (Top 5)
$sql_top_customers = "SELECT 
    u.id,
    u.fullname,
    u.email,
    COUNT(o.id) as order_count,
    SUM(o.total_price) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 5";
$top_customers = $conn->query($sql_top_customers);

// 5. Sản phẩm được mua nhiều nhất (Top 5)
$sql_top_products = "SELECT 
    p.id,
    p.name,
    p.image,
    SUM(oi.quantity) as total_sold,
    COUNT(DISTINCT o.id) as order_count,
    SUM(oi.total_price) as total_revenue
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE p.is_active = 1
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5";
$top_products = $conn->query($sql_top_products);

// 6. Đơn hàng gần đây
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

        <!-- Biểu đồ thống kê theo tháng -->
        <section class="charts-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="chart-card">
                <h4 style="margin-bottom: 15px;">Doanh thu theo tháng</h4>
                <canvas id="revenueChart" height="80"></canvas>
            </div>
            <div class="chart-card">
                <h4 style="margin-bottom: 15px;">Số đơn hàng theo tháng</h4>
                <canvas id="ordersChart" height="80"></canvas>
            </div>
        </section>

        <!-- Bảng khách hàng chi tiêu nhiều nhất -->
        <section class="table-container">
            <div class="table-header">
                <h5>Khách hàng chi tiêu nhiều nhất</h5>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên khách hàng</th>
                        <th>Email</th>
                        <th>Số đơn</th>
                        <th>Tổng chi tiêu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $top_customers->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fullname']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><strong><?= $row['order_count']; ?></strong></td>
                            <td style="color: #d0021c; font-weight: bold;"><?= number_format($row['total_spent'] ?? 0, 0, ',', '.'); ?>₫</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Bảng sản phẩm được mua nhiều nhất -->
        <section class="table-container" style="margin-top: 30px;">
            <div class="table-header">
                <h5>Sản phẩm được mua nhiều nhất</h5>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Hình ảnh</th>
                        <th>Số lượng bán</th>
                        <th>Số đơn hàng</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['name']); ?>" style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <span style="color: #999;">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= $row['total_sold'] ?? 0; ?></strong></td>
                            <td><?= $row['order_count'] ?? 0; ?></td>
                            <td style="color: #28a745; font-weight: bold;"><?= number_format($row['total_revenue'] ?? 0, 0, ',', '.'); ?>₫</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Đơn hàng gần đây -->
        <section class="table-container" style="margin-top: 30px;">
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
                    <?php
                    // Reset con trỏ để lấy lại dữ liệu
                    $recent_orders = $conn->query($sql_recent_orders);
                    while ($row = $recent_orders->fetch_assoc()):
                    ?>
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
    <script>
        // Chuẩn bị dữ liệu cho biểu đồ
        const monthlyData = <?php echo json_encode($monthly_data); ?>;

        // Tạo mảng nhãn tháng và dữ liệu
        const labels = monthlyData.map(item => item.month);
        const revenueData = monthlyData.map(item => parseInt(item.total_revenue) || 0);
        const orderData = monthlyData.map(item => item.order_count);

        // Biểu đồ Doanh thu theo tháng
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (₫)',
                    data: revenueData,
                    borderColor: '#d0021c',
                    backgroundColor: 'rgba(208, 2, 28, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#d0021c',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });

        // Biểu đồ Số đơn hàng theo tháng
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: orderData,
                    backgroundColor: '#28a745',
                    borderColor: '#20c997',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>