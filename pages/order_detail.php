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

/**
 * CẬP NHẬT: Mở rộng các trạng thái hiển thị log trên Timeline 
 * Giúp nhận diện đẹp mắt cả khi user gửi yêu cầu và khi admin duyệt phản hồi.
 */
function getOrderStatusMaster($status)
{
    $status = strtolower($status);
    $configs = [
        'pending' => [
            'text'   => 'Chờ xử lý',
            'class'  => 'status-pending',
            'badge'  => 'badge-warning',
            'color'  => '#ff9800',
            'bg'     => '#fff4e5',
            'icon'   => 'fa-clock'
        ],
        'confirmed' => [
            'text'   => 'Đã xác nhận',
            'class'  => 'status-confirmed',
            'badge'  => 'badge-info',
            'color'  => '#0288d1',
            'bg'     => '#e3f2fd',
            'icon'   => 'fa-check-circle'
        ],
        'shipping' => [
            'text'   => 'Đang giao hàng',
            'class'  => 'status-shipping',
            'badge'  => 'badge-primary',
            'color'  => '#3f51b5',
            'bg'     => '#e8eaf6',
            'icon'   => 'fa-truck'
        ],
        'delivered' => [
            'text'   => 'Đã giao thành công',
            'class'  => 'status-delivered',
            'badge'  => 'badge-success',
            'color'  => '#2e7d32',
            'bg'     => '#edf7ed',
            'icon'   => 'fa-box-open'
        ],
        'cancelled' => [
            'text'   => 'Đã hủy',
            'class'  => 'status-cancelled',
            'badge'  => 'badge-danger',
            'color'  => '#d32f2f',
            'bg'     => '#fdeded',
            'icon'   => 'fa-times-circle'
        ],
        'returned' => [
            'text'   => 'Đã trả hàng / Hoàn tiền',
            'class'  => 'status-default',
            'badge'  => 'badge-secondary',
            'color'  => '#757575',
            'bg'     => '#f5f5f5',
            'icon'   => 'fa-undo'
        ],
        // ==========================================
        // CÁC TRẠNG THÁI BỔ SUNG DÀNH CHO KHỐI LOG HOÀN / HỦY ĐƠN
        // ==========================================
        'request_pending' => [
            'text'   => 'Yêu cầu hoàn/hủy mới',
            'class'  => 'status-pending',
            'badge'  => 'badge-warning',
            'color'  => '#e67e22',
            'bg'     => '#fdf2e9',
            'icon'   => 'fa-paper-plane'
        ],
        'request_approved' => [
            'text'   => 'Yêu cầu được chấp nhận',
            'class'  => 'status-delivered',
            'badge'  => 'badge-success',
            'color'  => '#27ae60',
            'bg'     => '#e8f8f5',
            'icon'   => 'fa-clipboard-check'
        ],
        'request_rejected' => [
            'text'   => 'Yêu cầu bị từ chối',
            'class'  => 'status-cancelled',
            'badge'  => 'badge-danger',
            'color'  => '#c0392b',
            'bg'     => '#f9ebea',
            'icon'   => 'fa-user-times'
        ]
    ];

    return $configs[$status] ?? [
        'text'   => strtoupper($status),
        'class'  => 'status-default',
        'badge'  => 'badge-secondary',
        'color'  => '#757575',
        'bg'     => '#f5f5f5',
        'icon'   => 'fa-info-circle'
    ];
}
?>
<main class="order-detail-page">
    <div class="container">
        <div class="detail-header">
            <a href="history_order.php" class="back-link"><i class="fa-solid fa-chevron-left"></i> QUAY LẠI</a>
            <div class="order-meta">
                <span>MÃ ĐƠN HÀNG: <?= $order['order_code'] ?></span> |
                <?php $conf = getOrderStatusMaster($order['status']); ?>
                <span style="font-weight: bold;" class="<?= $conf['class'] ?>">
                    <?= strtoupper($conf['text']) ?>
                </span>
            </div>
        </div>

        <!-- Timeline Trạng Thái -->
        <section class="order-history">
            <div class="payment-status">
                <span>Trạng thái thanh toán:</span>
                <?php
                // Kiểm tra xem đơn hàng đã từng được hoàn tiền thành công trong lịch sử chưa
                $is_refunded = false;
                if ($res_history && $res_history->num_rows > 0) {
                    while ($check_log = $res_history->fetch_assoc()) {
                        $lower_note_check = mb_strtolower($check_log['note'], 'UTF-8');
                        if (strpos($lower_note_check, 'hoàn tiền thành công') !== false) {
                            $is_refunded = true;
                            break;
                        }
                    }
                    // Giải phóng và đưa con trỏ dữ liệu về lại vị trí đầu tiên để vòng lặp timeline ở dưới vẫn chạy bình thường
                    $res_history->data_seek(0);
                }
                ?>

                <?php if ($is_refunded): ?>
                    <strong class="payment-badge" style="background-color: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; padding: 4px 8px; border-radius: 4px;">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        Đã hoàn tiền
                    </strong>
                <?php else: ?>
                    <strong class="payment-badge <?= $order['payment_status'] == 'paid' ? 'status-delivered' : 'status-cancelled' ?>">
                        <i class="fa-solid <?= $order['payment_status'] == 'paid' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                        <?= $order['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                    </strong>
                <?php endif; ?>
            </div>
            
            <h3><i class="fas fa-history"></i> Lịch sử đơn hàng</h3>
            <div class="timeline-v2">
                <?php if ($res_history && $res_history->num_rows > 0): ?>
                    <?php while ($h = $res_history->fetch_assoc()):
                        /**
                         * GIẢI PHÁP ĐỌC LOG THÔNG MINH:
                         * Nếu trong nội dung cột `note` chứa từ khóa phân biệt hành vi hoàn/hủy,
                         * hệ thống tự động trỏ về cấu hình giao diện đặc trưng (giao diện ảo) thay vì dùng vòng lặp mặc định.
                         */
                        $status_key = $h['new_status'];
                        if (strpos($h['note'], 'gửi yêu cầu') !== false) {
                            $status_key = 'request_pending';
                        } elseif (strpos($h['note'], 'từ chối yêu cầu') !== false) {
                            $status_key = 'request_rejected';
                        } elseif (strpos($h['note'], 'đã duyệt yêu cầu') !== false) {
                            $status_key = 'request_approved';
                        }

                        $config = getOrderStatusMaster($status_key);
                    ?>
                        <div class="timeline-v2-item">
                            <div class="timeline-v2-icon" style="background: <?= $config['color'] ?>;">
                                <i class="fas <?= $config['icon'] ?>"></i>
                            </div>

                            <div class="timeline-v2-content" style="border-left-color: <?= $config['color'] ?>; background: <?= $config['bg'] ?>;">
                                <div class="timeline-v2-header">
                                    <strong style="color: <?= $config['color'] ?>;">
                                        <?= $config['text'] ?>
                                    </strong>
                                    <small><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
                                </div>

                                <?php if (!empty($h['note'])): ?>
                                    <p class="timeline-v2-note">
                                        <i class="fa-solid fa-pen-nib" style="font-size: 10px; opacity: 0.6;"></i>
                                        <?= htmlspecialchars($h['note']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #888; padding: 20px;">Chưa có lịch sử cập nhật cho đơn hàng này.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="detail-grid">
            <!-- Địa chỉ & Thông tin thanh toán -->
            <div class="info-column">
                <section class="detail-section">
                    <h3 class="section-title"><i class="fa-solid fa-location-dot"></i> Địa chỉ nhận hàng</h3>
                    <div class="address-card">
                        <strong>Họ tên: <?= htmlspecialchars($order['shipping_name']) ?></strong>
                        <p><strong>Sđt: </strong><?= $order['shipping_phone'] ?></p>
                        <p style="text-transform: capitalize;"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                        <?php if ($order['note']): ?>
                            <p class="order-note" style="text-transform: capitalize;"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></p>
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