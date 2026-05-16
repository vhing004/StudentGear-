<?php
session_start();
require_once '../../config/db.php';

// Bảo mật: Chỉ Admin/Staff mới được truy cập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// --- CẬP NHẬT LOGIC TÌM KIẾM NGƯỜI DÙNG ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 1. Xây dựng câu SQL cơ bản có kết hợp tính toán đơn hàng
$sql_users = "SELECT 
                u.*,
                COUNT(CASE WHEN o.status != 'cancelled' THEN o.id END) AS total_orders,
                COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total_price END), 0) AS total_spent
              FROM users u
              LEFT JOIN orders o ON u.id = o.user_id
              WHERE 1=1"; // Mẹo nối chuỗi AND

// 2. Thêm điều kiện tìm kiếm nếu Admin gõ từ khóa
if (!empty($search)) {
    $sql_users .= " AND (u.username LIKE '%$search%' 
                    OR u.email LIKE '%$search%' 
                    OR u.fullname LIKE '%$search%' 
                    OR u.phone LIKE '%$search%')";
}

// Gom nhóm theo ID người dùng và sắp xếp
$sql_users .= " GROUP BY u.id ORDER BY u.created_at DESC";

$res_users = $conn->query($sql_users);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Quản lý khách hàng</h2>
                <!-- <section class="search-filter-wrapper"> -->
                <form method="GET" action="" class="search-form user">
                    <input type="text" name="search" class="search-input"
                        placeholder="Nhập tên, email, sđt hoặc tài khoản..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-primary" style="padding: 8px 15px;">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="users.php" class="btn-secondary" style="padding: 8px 15px; text-decoration: none; display: inline-flex; align-items: center; border-radius: 4px;">
                            Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </form>
                <!-- </section> -->
                <span class="badge-count">Tổng khách hàng: <?= $res_users->num_rows ?></span>
            </header>


            <section class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tài khoản</th>
                            <th>Họ & Tên</th>
                            <th>Liên hệ</th>
                            <th class="text-center">Số đơn hàng</th>
                            <th class="text-right">Tổng chi tiêu</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_users->num_rows > 0): ?>
                            <?php while ($row = $res_users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong>@<?= htmlspecialchars($row['username']) ?></strong>
                                        <br><small style="color: #888;"><?= htmlspecialchars($row['email']) ?></small>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['fullname'] ?? 'Chưa cập nhật') ?></strong></td>
                                    <td>
                                        <i class="fa-solid fa-phone" style="font-size: 11px; color:#666;"></i> <?= htmlspecialchars($row['phone'] ?? 'N/A') ?>
                                        <br><small style="color: #666;"><i class="fa-solid fa-location-dot" style="font-size: 11px;"></i> <?= htmlspecialchars($row['address'] ?? 'Chưa có địa chỉ') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-count"><?= $row['total_orders'] ?></span>
                                    </td>
                                    <td class="text-right spent-amount">
                                        <?= number_format($row['total_spent'], 0, ',', '.') ?>₫
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge <?= $row['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                                            <?= $row['is_active'] ? 'Đang hoạt động' : 'Đang khóa' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="orders.php?user_id=<?= $row['id'] ?>" class="action-link" title="Xem danh sách đơn hàng">
                                            <i class="fas fa-shopping-bag" style="color: #3f51b5;"></i> Đơn hàng
                                        </a>
                                        <a href="../handlers/toggle_user.php?id=<?= $row['id'] ?>"
                                            class="action-link <?= $row['is_active'] ? 'text-danger' : 'text-success' ?>"
                                            onclick="return confirm('Bạn chắc chắn muốn thay đổi trạng thái tài khoản này?')"
                                            style="margin-left: 10px;">
                                            <i class="fas <?= $row['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 20px; color: #888;">Không tìm thấy khách hàng nào khớp với từ khóa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>

<?php if (isset($_GET['msg'])): ?>
    <script>
        let msg = "<?= $_GET['msg'] ?>";
        if (msg === 'toggle_success') {
            alert('Cập nhật trạng thái tài khoản khách hàng thành công!');
        } else if (msg === 'user_not_found' || msg === 'invalid_id') {
            alert('Tài khoản khách hàng không tồn tại hoặc dữ liệu lỗi!');
        } else if (msg === 'error') {
            alert('Đã xảy ra lỗi hệ thống, vui lòng thử lại sau!');
        }
    </script>
<?php endif; ?>

</html>