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

// function getStatusClass($status)
// {
//     switch (strtolower($status)) {
//         case 'pending':
//             return 'status-pending';   // Chờ xử lý - Cam
//         case 'confirmed':
//             return 'status-confirmed'; // Đã xác nhận - Xanh biển nhạt
//         case 'shipping':
//             return 'status-shipping';  // Đang giao - Xanh biển đậm
//         case 'delivered':
//             return 'status-delivered'; // Đã giao - Xanh lá
//         case 'cancelled':
//             return 'status-cancelled'; // Đã hủy - Đỏ
//         default:
//             return 'status-default';
//     }
// }

// function getStatusText($status)
// {
//     $text = [
//         'pending'   => 'CHỜ XỬ LÝ',
//         'confirmed' => 'ĐÃ XÁC NHẬN',
//         'shipping'  => 'ĐANG GIAO HÀNG',
//         'delivered' => 'ĐÃ GIAO THÀNH CÔNG',
//         'cancelled' => 'ĐÃ HỦY'
//     ];
//     return $text[strtolower($status)] ?? strtoupper($status);
// }

// function getStatusInfo($status)
// {
//     $map = [
//         'pending'   => ['text' => 'Chờ xử lý', 'class' => 'badge-warning', 'icon' => 'fa-clock'],
//         'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'badge-info', 'icon' => 'fa-check-circle'],
//         'shipping'  => ['text' => 'Đang giao hàng', 'class' => 'badge-primary', 'icon' => 'fa-truck'],
//         'delivered' => ['text' => 'Đã giao thành công', 'class' => 'badge-success', 'icon' => 'fa-box-open'],
//         'cancelled' => ['text' => 'Đã hủy', 'class' => 'badge-danger', 'icon' => 'fa-times-circle'],
//     ];
//     return $map[$status] ?? ['text' => $status, 'class' => 'badge-secondary', 'icon' => 'fa-info-circle'];
// }

// function getStatusTimelineConfig($status)
// {
//     switch ($status) {
//         case 'pending':
//             return ['color' => '#ff9800', 'bg' => '#fff4e5', 'icon' => 'fa-clock', 'text' => 'Chờ xử lý'];
//         case 'confirmed':
//             return ['color' => '#0288d1', 'bg' => '#e3f2fd', 'icon' => 'fa-check-circle', 'text' => 'Đã xác nhận'];
//         case 'shipping':
//             return ['color' => '#3f51b5', 'bg' => '#e8eaf6', 'icon' => 'fa-truck', 'text' => 'Đang giao hàng'];
//         case 'delivered':
//             return ['color' => '#2e7d32', 'bg' => '#edf7ed', 'icon' => 'fa-box-open', 'text' => 'Đã giao thành công'];
//         case 'cancelled':
//             return ['color' => '#d32f2f', 'bg' => '#fdeded', 'icon' => 'fa-times-circle', 'text' => 'Đã hủy'];
//         default:
//             return ['color' => '#757575', 'bg' => '#f5f5f5', 'icon' => 'fa-info-circle', 'text' => $status];
//     }
// }


function getOrderStatusMaster($status)
{
    $status = strtolower($status);
    $configs = [
        'pending' => [
            'text'   => 'Chờ xử lý',
            'class'  => 'status-pending', // Class cho text highlight
            'badge'  => 'badge-warning',  // Class cho badge admin
            'color'  => '#ff9800',        // Màu cam
            'bg'     => '#fff4e5',        // Nền cam nhạt
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
            'text'   => 'Trả hàng',
            'class'  => 'status-default',
            'badge'  => 'badge-secondary',
            'color'  => '#757575',
            'bg'     => '#f5f5f5',
            'icon'   => 'fa-undo'
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
                <strong class="payment-badge <?= $order['payment_status'] == 'paid' ? 'status-delivered' : 'status-cancelled' ?>">
                    <i class="fa-solid <?= $order['payment_status'] == 'paid' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <?= $order['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                </strong>
            </div>
            <h3><i class="fas fa-history"></i> Lịch sử đơn hàng</h3>
            <div class="timeline-v2">
                <?php if ($res_history && $res_history->num_rows > 0): ?>
                    <?php while ($h = $res_history->fetch_assoc()):
                        // Gọi hàm Master để lấy toàn bộ cấu hình màu sắc, icon
                        $config = getOrderStatusMaster($h['new_status']);
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
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
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