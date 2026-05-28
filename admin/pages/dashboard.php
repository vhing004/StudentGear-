<?php
// 1. Kiểm tra quyền truy cập Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// --- LOGIC BỘ LỌC CHỌN NĂM ---
$sql_years = "SELECT DISTINCT YEAR(created_at) as order_year FROM orders ORDER BY order_year DESC";
$res_years = $conn->query($sql_years);
$available_years = [];
while ($y_row = $res_years->fetch_assoc()) {
    $available_years[] = $y_row['order_year'];
}
if (empty($available_years)) {
    $available_years[] = (int)date('Y');
}

$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : (int)date('Y');

// --- THỐNG KÊ DOANH THU & SỐ ĐƠN HÀNG ĐỒNG BỘ 12 THÁNG ---
// Doanh thu: Chỉ tính đơn 'delivered' | Số đơn hàng: Tính toàn bộ đơn trừ đơn 'cancelled'
$sql_monthly_data = "SELECT 
    m.month_num,
    COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.total_price ELSE 0 END), 0) as total_revenue,
    COUNT(CASE WHEN o.status != 'cancelled' AND o.id IS NOT NULL THEN o.id END) as total_orders
FROM (
    SELECT 1 AS month_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
    UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
) m
LEFT JOIN orders o ON MONTH(o.created_at) = m.month_num 
                  AND YEAR(o.created_at) = $selected_year
GROUP BY m.month_num
ORDER BY m.month_num ASC";

$res_monthly = $conn->query($sql_monthly_data);

$chart_labels = [];
$chart_revenue = [];
$chart_orders = [];

while ($row = $res_monthly->fetch_assoc()) {
    $chart_labels[] = "Tháng " . $row['month_num'];
    $chart_revenue[] = (float)$row['total_revenue'];
    $chart_orders[] = (int)$row['total_orders']; // Mảng số lượng đơn hàng của từng tháng
}

// 2. Truy vấn dữ liệu thống kê tổng quát
$sql_stats = "SELECT 
    COUNT(id) as total_orders, 
    COALESCE(SUM(CASE WHEN status = 'delivered' THEN total_price ELSE 0 END), 0) as total_revenue,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders
    FROM orders";
$res_stats = $conn->query($sql_stats)->fetch_assoc();

$sql_users = "SELECT COUNT(id) as total_customers FROM users WHERE is_active = 1";
$res_users = $conn->query($sql_users)->fetch_assoc();

$sql_products = "SELECT COUNT(id) as total_products FROM products";
$res_products = $conn->query($sql_products)->fetch_assoc();

// 4. Khách hàng chi tiêu nhiều nhất (Top 5)
// ĐÃ UPDATE LOGIC: Chỉ tính số đơn và tổng tiền chi tiêu từ các đơn hàng ĐÃ GIAO THÀNH CÔNG (status = 'delivered')
$sql_top_customers = "SELECT 
    u.id,
    u.fullname,
    u.email,
    COUNT(o.id) as order_count, -- Thay đổi tại đây: Đếm toàn bộ đơn đặt hàng của user
    COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN o.total_price ELSE 0 END), 0) as total_spent
    FROM users u
    INNER JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    HAVING total_spent > 0
    ORDER BY total_spent DESC
    LIMIT 5";
$top_customers = $conn->query($sql_top_customers);


// 5. Sản phẩm được mua nhiều nhất (Top 5)
// ĐÃ UPDATE LOGIC: Loại bỏ các đơn hàng bị hủy (status != 'cancelled'), chỉ đếm sản lượng tiêu thụ thực tế
$sql_top_products = "SELECT 
    p.id,
    p.name,
    p.image,
    COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN oi.quantity ELSE 0 END), 0) as total_sold,
    COUNT(DISTINCT CASE WHEN o.status != 'cancelled' THEN o.id END) as order_count,
    COALESCE(SUM(CASE WHEN o.status = 'delivered' THEN oi.total_price ELSE 0 END), 0) as total_revenue
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE p.is_active = 1
    GROUP BY p.id
    HAVING total_sold > 0
    ORDER BY total_sold DESC
    LIMIT 5";
$top_products = $conn->query($sql_top_products);


// 6. Đơn hàng gần đây (Top 5)
// ĐÃ UPDATE LOGIC: Thay JOIN bằng LEFT JOIN phòng trường hợp dữ liệu User bị xóa thì Admin vẫn nhìn thấy Đơn hàng để quản lý
$sql_recent_orders = "SELECT o.*, COALESCE(u.fullname, 'Tài khoản đã xóa') as fullname 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC 
                      LIMIT 5";
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
                <h2>Quản trị hệ thống</h2>
                <p>Chào mừng bạn quay trở lại, hệ thống đang hoạt động ổn định.</p>
            </div>
            <div class="user-profile">
                <span>Xin chào, <strong><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></strong></span>
                <small><?= ucfirst($_SESSION['role'] ?? 'Staff'); ?></small>
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
                    <h3><?= $res_stats['total_orders'] ?? 0; ?></h3>
                    <p>Đơn hàng</p>
                </div>
            </div>

            <div class="card-counter bg-customers">
                <i class="fas fa-user-friends"></i>
                <div class="card-counter__info">
                    <h3><?= $res_users['total_customers'] ?? 0; ?></h3>
                    <p>Khách hàng</p>
                </div>
            </div>

            <div class="card-counter bg-products">
                <i class="fas fa-box-open"></i>
                <div class="card-counter__info">
                    <h3><?= $res_products['total_products'] ?? 0; ?></h3>
                    <p>Sản phẩm</p>
                </div>
            </div>
        </section>

        <!-- Biểu đồ doanh thu theo tháng và tổng đơn hàng -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4 style="margin: 0; color: #2c3e50; font-family: sans-serif;">
                    <i class="fas fa-chart-bar" style="color: #10b981; margin-right: 6px;"></i>
                    Báo cáo phân tích năm <?= $selected_year ?>
                </h4>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-size: 13px; color: #666;">Chọn năm:</label>
                    <select onchange="switchYear(this.value)" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: bold; background: #f8fafc;">
                        <?php foreach ($available_years as $year): ?>
                            <option value="<?= $year ?>" <?= $year === $selected_year ? 'selected' : '' ?>>Năm <?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 20px;">

                <div class="chart-item" style="border: 1px solid #f1f5f9; padding: 15px; border-radius: 6px;">
                    <p style="margin: 0 0 10px 0; font-weight: 600; color: #475569; font-size: 14px;">Doanh thu tháng</p>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="yearlyRevenueChart"></canvas>
                    </div>
                </div>

                <div class="chart-item" style="border: 1px solid #f1f5f9; padding: 15px; border-radius: 6px;">
                    <p style="margin: 0 0 10px 0; font-weight: 600; color: #475569; font-size: 14px;">Số lượng đơn hàng (trừ đơn hủy)</p>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="yearlyOrdersChart"></canvas>
                    </div>
                </div>

            </div>
        </div>

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
    </main>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function switchYear(year) {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('filter_year', year);
        window.location.href = currentUrl.toString();
    }

    // Nạp dữ liệu đồng bộ từ PHP sang JavaScript
    const labelsYearly = <?php echo json_encode($chart_labels); ?>;
    const dataRevenue = <?php echo json_encode($chart_revenue); ?>;
    const dataOrders = <?php echo json_encode($chart_orders); ?>; // Thêm mảng số đơn hàng

    // ==========================================================
    // BIỂU ĐỒ 1: DOANH THU THEO THÁNG (LINE CHART)
    // ==========================================================
    const ctxRevenue = document.getElementById('yearlyRevenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: labelsYearly,
            datasets: [{
                label: 'Doanh thu (₫)',
                data: dataRevenue,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => new Intl.NumberFormat('vi-VN', {
                            notation: 'compact'
                        }).format(value)
                    }
                }
            }
        }
    });

    // ==========================================================
    // BIỂU ĐỒ 2: SỐ ĐƠN HÀNG THEO THÁNG (BAR CHART) - MỚI THÊM
    // ==========================================================
    const ctxOrders = document.getElementById('yearlyOrdersChart').getContext('2d');
    new Chart(ctxOrders, {
        type: 'bar', // Cấu hình dạng cột nhìn rõ ràng độ chênh lệch số lượng đơn
        data: {
            labels: labelsYearly,
            datasets: [{
                label: 'Sản lượng đơn hàng',
                data: dataOrders,
                backgroundColor: '#10b981', // Cột màu xanh lá cây mát mắt
                hoverBackgroundColor: '#059669', // Đổi màu đậm hơn khi hover chuột vào cột
                borderRadius: 4, // Bo tròn nhẹ góc trên đầu cột
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' Số đơn: ' + context.raw + ' đơn hàng';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // Ép các mốc hiển thị trục Y phải là số nguyên (1 đơn, 2 đơn, không hiện 1.5 đơn)
                        precision: 0
                    }
                }
            }
        }
    });
</script>