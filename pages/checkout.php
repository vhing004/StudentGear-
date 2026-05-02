<?php
require_once '../config/db.php';
include '../includes/header.php';

// 1. Kiểm tra đăng nhập và dữ liệu thanh toán
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$checkout_items = $_SESSION['checkout_items'];
$total_temp = 0;
$shipping_fee = 30000;

// 2. Lấy thông tin từ đơn hàng gần nhất của người dùng này
$old_info = null;
$sql_recent = "SELECT shipping_name, shipping_phone, shipping_address 
               FROM orders 
               WHERE user_id = '$user_id' 
               ORDER BY id DESC LIMIT 1"; // Sắp xếp theo ID giảm dần để lấy đơn mới nhất
$res_recent = $conn->query($sql_recent);

if ($res_recent && $res_recent->num_rows > 0) {
    $old_info = $res_recent->fetch_assoc();
}
?>

<main class="checkout-page">
    <div class="container">
        <!-- Form gửi dữ liệu sang process_order.php để lưu DB -->
        <form action="<?= BASE_URL ?>handler/process_order.php" method="POST" class="checkout-container">

            <!-- BÊN TRÁI: THÔNG TIN GIAO HÀNG -->
            <div class="billing-details">
                <section class="checkout-section">
                    <h3 class="section-title">
                        <i class="fa-solid fa-map-location-dot"></i> Thông tin nhận hàng
                    </h3>
                    <p style="font-size: 1.2rem; color: #666; margin-bottom: 15px;">
                        * Thông tin được tự động điền từ đơn hàng trước đó (nếu có). Bạn có thể chỉnh sửa lại.
                    </p>

                    <div class="form-group">
                        <label for="shipping_name">Họ và tên người nhận *</label>
                        <input type="text" id="shipping_name" name="shipping_name"
                            value="<?= isset($old_info['shipping_name']) ? htmlspecialchars($old_info['shipping_name']) : '' ?>"
                            placeholder="Ví dụ: Nguyễn Văn A" required>
                    </div>

                    <div class="form-row" style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="shipping_phone">Số điện thoại *</label>
                            <input type="tel" id="shipping_phone" name="shipping_phone"
                                value="<?= isset($old_info['shipping_phone']) ? htmlspecialchars($old_info['shipping_phone']) : '' ?>"
                                placeholder="09xx xxx xxx" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="shipping_address">Địa chỉ chi tiết *</label>
                        <textarea id="shipping_address" name="shipping_address"
                            placeholder="Số nhà, tên đường, phường/xã, quận/huyện..."
                            rows="3" required><?= isset($old_info['shipping_address']) ? htmlspecialchars($old_info['shipping_address']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="note">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea id="note" name="note" placeholder="Lời nhắn cho shipper hoặc cửa hàng..." rows="2"></textarea>
                    </div>
                </section>

                <section class="checkout-section" style="margin-top: 30px;">
                    <h3 class="section-title">
                        <i class="fa-solid fa-credit-card"></i> Phương thức thanh toán
                    </h3>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="option-content">
                                <span class="option-name">Thanh toán khi nhận hàng (COD)</span>
                                <span class="option-desc">Bạn sẽ thanh toán bằng tiền mặt cho nhân viên giao hàng.</span>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="option-content">
                                <span class="option-name">Chuyển khoản ngân hàng</span>
                                <span class="option-desc">Đơn hàng sẽ được xử lý sau khi chúng tôi nhận được tiền chuyển khoản.</span>
                            </div>
                        </label>
                    </div>
                </section>
            </div>

            <!-- BÊN PHẢI: TÓM TẮT ĐƠN HÀNG (STICKY) -->
            <aside class="order-summary">
                <h3 class="section-title" style="border:none; text-align:center; display:block;">Đơn hàng của bạn</h3>

                <div class="product-list">
                    <?php foreach ($checkout_items as $item):
                        $price_sale = $item['price'] * (1 - ($item['discount_percent'] / 100));
                        $subtotal = $price_sale * $item['buy_qty'];
                        $total_temp += $subtotal;
                    ?>
                        <div class="product-item">
                            <img src="<?= $item['image'] ?>" class="product-img" alt="Product">
                            <div class="product-info">
                                <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="qty-price"><?= $item['buy_qty'] ?> x <?= number_format($price_sale, 0, ',', '.') ?>₫</div>
                            </div>
                            <div class="product-total">
                                <?= number_format($subtotal, 0, ',', '.') ?>₫
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="calc-rows">
                    <div class="row">
                        <span>Tạm tính</span>
                        <span><?= number_format($total_temp, 0, ',', '.') ?>₫</span>
                    </div>
                    <div class="row">
                        <span>Phí vận chuyển</span>
                        <span><?= number_format($shipping_fee, 0, ',', '.') ?>₫</span>
                    </div>
                    <div class="row final-total">
                        <span>TỔNG CỘNG</span>
                        <span><?= number_format($total_temp + $shipping_fee, 0, ',', '.') ?>₫</span>
                    </div>
                </div>

                <!-- Input ẩn để gửi thông tin tiền sang backend -->
                <input type="hidden" name="total_price" value="<?= $total_temp + $shipping_fee ?>">
                <input type="hidden" name="shipping_fee" value="<?= $shipping_fee ?>">

                <button type="submit" name="btn_order" class="btn-submit-order">
                    <i class="fa-solid fa-check"></i> XÁC NHẬN ĐẶT HÀNG
                </button>

                <div style="text-align: center; margin-top: 15px;">
                    <a href="cart.php" style="color: #666; font-size: 1.3rem; text-decoration: none;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại giỏ hàng
                    </a>
                </div>
            </aside>

        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>