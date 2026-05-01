<?php
session_start();
require_once '../config/db.php';
include_once "../includes/header.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy dữ liệu giỏ hàng
$sql = "SELECT c.id as cart_id, c.quantity, 
               p.id as product_id, p.name, p.price, p.image, p.stock,
               (p.price * c.quantity) AS subtotal
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['subtotal'];
}
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-title">GIỎ HÀNG</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Giỏ hàng của bạn đang trống!</p>
                <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>

            <div class="cart-content">
                <!-- Danh sách sản phẩm -->
                <div class="cart-items">
                    <form id="cart-form">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>SẢN PHẨM</th>
                                    <th>GIÁ</th>
                                    <th>SỐ LƯỢNG</th>
                                    <th>TẠM TÍNH</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr data-cart-id="<?= $item['cart_id'] ?>">
                                        <td class="product-info">
                                            <img src="<?= htmlspecialchars($item['image'] ?? '/assets/images/no-image.jpg') ?>"
                                                alt="<?= htmlspecialchars($item['name']) ?>"
                                                class="product-thumb">
                                            <div>
                                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                            </div>
                                        </td>
                                        <td class="price" data-price="<?= $item['price'] ?>">
                                            <?= number_format($item['price'], 0, ',', '.') ?>₫
                                        </td>
                                        <td class="quantity">
                                            <div class="quantity-control">
                                                <button type="button" class="qty-btn minus" data-cart-id="<?= $item['cart_id'] ?>">-</button>
                                                <input type="number"
                                                    class="qty-input"
                                                    value="<?= $item['quantity'] ?>"
                                                    min="1"
                                                    max="<?= $item['stock'] ?>"
                                                    data-cart-id="<?= $item['cart_id'] ?>">
                                                <button type="button" class="qty-btn plus" data-cart-id="<?= $item['cart_id'] ?>">+</button>
                                            </div>
                                        </td>
                                        <td class="subtotal" data-subtotal="<?= $item['subtotal'] ?>">
                                            <?= number_format($item['subtotal'], 0, ',', '.') ?>₫
                                        </td>
                                        <td class="remove">
                                            <button type="button" class="remove-btn" data-cart-id="<?= $item['cart_id'] ?>">
                                                <a href="<?php echo BASE_URL; ?>handler/remove_from_cart.php?cart_id=<?= $item['cart_id'] ?>"> <i class="fa-solid fa-trash"></i></a>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>

                    <div class="cart-actions">
                        <a href="<?= BASE_URL ?>index.php" class="btn btn-outline">
                            ← TIẾP TỤC XEM SẢN PHẨM
                        </a>
                        <button id="update-cart-btn" class="btn btn-secondary">
                            <a href="<?php echo BASE_URL; ?>handler/update_cart.php?cart_id=<?= $item['cart_id'] ?>"> CẬP NHẬT GIỎ HÀNG</a>
                        </button>
                    </div>
                </div>

                <!-- Cột tổng tiền -->
                <div class="cart-summary">
                    <h3>CỘNG GIỎ HÀNG</h3>
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span id="subtotal"><?= number_format($total, 0, ',', '.') ?>₫</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng</span>
                        <span id="total"><?= number_format($total, 0, ',', '.') ?>₫</span>
                    </div>

                    <a href="<?= BASE_URL ?>handler/buy_now.php?from_cart=1"
                        class="btn btn-checkout">
                        TIẾN HÀNH THANH TOÁN
                    </a>

                    <div class="coupon-section">
                        <h4><i class="fa-solid fa-ticket"></i> Phiếu ưu đãi</h4>
                        <div class="coupon-input">
                            <input type="text" id="coupon_code" placeholder="Mã ưu đãi">
                            <button id="apply-coupon">Áp dụng</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php
include_once  "../includes/footer.php";
?>

<script src="<?= BASE_URL ?>assets/js/cart.js"></script>