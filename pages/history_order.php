<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Lấy trạng thái từ URL để lọc (Tất cả, Chờ xác nhận, Hoàn thành...)
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Truy vấn danh sách đơn hàng
$sql_orders = "SELECT * FROM orders WHERE user_id = '$user_id'";
if ($status_filter !== 'all') {
    $sql_orders .= " AND status = '$status_filter'";
}
$sql_orders .= " ORDER BY created_at DESC";
$res_orders = $conn->query($sql_orders);
?>
<main class="order-history-page">
    <div class="container">
        <!-- Tab điều hướng trạng thái -->
        <div class="order-tabs">
            <a href="?status=all" class="<?= $status_filter == 'all' ? 'active' : '' ?>">Tất cả</a>
            <a href="?status=pending" class="<?= $status_filter == 'pending' ? 'active' : '' ?>">Chờ thanh toán</a>
            <a href="?status=confirmed" class="<?= $status_filter == 'confirmed' ? 'active' : '' ?>">Vận chuyển</a>
            <a href="?status=delivered" class="<?= $status_filter == 'delivered' ? 'active' : '' ?>">Hoàn thành</a>
            <a href="?status=cancelled" class="<?= $status_filter == 'cancelled' ? 'active' : '' ?>">Đã hủy</a>
        </div>

        <div class="order-list">
            <?php if ($res_orders->num_rows > 0): ?>
                <?php while ($order = $res_orders->fetch_assoc()):
                    $order_id = $order['id'];
                    // Lấy các sản phẩm của đơn hàng này
                    $sql_items = "SELECT oi.*, p.image 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.order_id = '$order_id'";
                    $res_items = $conn->query($sql_items);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="shop-name"><i class="fa-solid fa-store"></i> StudentGear Official</span>
                            <div class="order-status">
                                <i class="fa-solid fa-truck"></i>
                                <?= strtoupper($order['status']) ?> |
                                <span class="status-text"><?= $order['status'] == 'delivered' ? 'HOÀN THÀNH' : 'ĐANG XỬ LÝ' ?></span>
                            </div>
                        </div>

                        <?php while ($item = $res_items->fetch_assoc()): ?>
                            <div class="product-info">
                                <img src="<?= $item['image'] ?>" alt="product">
                                <div class="details">
                                    <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="qty">x<?= $item['quantity'] ?></p>
                                </div>
                                <div class="price">
                                    <?= number_format($item['price'], 0, ',', '.') ?>₫
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <div class="order-footer">
                            <div class="total-section">
                                Thành tiền: <span class="total-amount"><?= number_format($order['total_price'], 0, ',', '.') ?>₫</span>
                            </div>
                            <div class="actions">
                                <a href="../handler/reorder_process.php?order_id=<?= $order['id'] ?>" class="btn btn-primary">Mua Lại</a>
                                <a href="../pages/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <img src="../assets/images/empty-order.png" alt="no order">
                    <p>Chưa có đơn hàng nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>