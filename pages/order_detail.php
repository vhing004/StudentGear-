<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: order_history.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// 1. Lấy thông tin chung đơn hàng (Phải khớp với user_id để bảo mật)
$sql_order = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
$res_order = $conn->query($sql_order);
$order = $res_order->fetch_assoc();

if (!$order) {
    die("Đơn hàng không tồn tại hoặc bạn không có quyền xem.");
}

// 2. Lấy danh sách sản phẩm kèm ảnh từ bảng products
$sql_items = "SELECT oi.*, p.image 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = $order_id";
$res_items = $conn->query($sql_items);

// 3. Lấy lịch sử trạng thái đơn hàng
$sql_history = "SELECT * FROM order_status_history 
                WHERE order_id = $order_id 
                ORDER BY created_at DESC";
$res_history = $conn->query($sql_history);
?>
<main class="order-detail-page">
    <div class="container">
        <div class="detail-header">
            <a href="order_history.php" class="back-link"><i class="fa-solid fa-chevron-left"></i> QUAY LẠI</a>
            <div class="order-meta">
                <span>MÃ ĐƠN HÀNG: <?= $order['order_code'] ?></span> |
                <span class="status-highlight"><?= strtoupper($order['status']) ?></span>
            </div>
        </div>

        <!-- Timeline Trạng Thái -->
        <section class="detail-section timeline-box">
            <h3 class="section-title">Lịch sử đơn hàng</h3>
            <ul class="timeline">
                <?php while ($h = $res_history->fetch_assoc()): ?>
                    <li class="timeline-item">
                        <div class="time"><?= date('H:i d/m/Y', strtotime($h['created_at'])) ?></div>
                        <div class="content">
                            <span class="status-name"><?= $h['new_status'] ?></span>
                            <p class="note"><?= htmlspecialchars($h['note']) ?></p>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        </section>

        <div class="detail-grid">
            <!-- Địa chỉ & Thông tin thanh toán -->
            <div class="info-column">
                <section class="detail-section">
                    <h3 class="section-title"><i class="fa-solid fa-location-dot"></i> Địa chỉ nhận hàng</h3>
                    <div class="address-card">
                        <strong><?= htmlspecialchars($order['shipping_name']) ?></strong>
                        <p><?= $order['shipping_phone'] ?></p>
                        <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <?php if ($order['note']): ?>
                            <p class="order-note">Ghi chú: <?= htmlspecialchars($order['note']) ?></p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="detail-section">
                    <h3 class="section-title"><i class="fa-solid fa-credit-card"></i> Phương thức thanh toán</h3>
                    <p><?= $order['payment_method'] == 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng' ?></p>
                </section>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="products-column">
                <section class="detail-section">
                    <h3 class="section-title">Sản phẩm</h3>
                    <div class="product-list">
                        <?php while ($item = $res_items->fetch_assoc()): ?>
                            <div class="product-item">
                                <img src="<?= $item['image'] ?>" alt="product">
                                <div class="info">
                                    <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="qty">x<?= $item['quantity'] ?></p>
                                </div>
                                <div class="price"><?= number_format($item['price'], 0, ',', '.') ?>₫</div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="total-calculation">
                        <div class="calc-row"><span>Tạm tính:</span><span><?= number_format($order['total_price'] - $order['shipping_fee'], 0, ',', '.') ?>₫</span></div>
                        <div class="calc-row"><span>Phí vận chuyển:</span><span><?= number_format($order['shipping_fee'], 0, ',', '.') ?>₫</span></div>
                        <div class="calc-row grand-total">
                            <span>Tổng cộng:</span>
                            <span><?= number_format($order['total_price'], 0, ',', '.') ?>₫</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>