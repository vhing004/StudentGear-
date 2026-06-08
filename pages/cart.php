<?php
session_start();
require_once '../config/db.php';
include_once "../includes/header.php"; // Chứa hệ thống mã Global Toast

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy dữ liệu giỏ hàng
$sql = "SELECT c.id as cart_id, c.quantity, 
               p.id as product_id, p.name, p.price, p.image, p.stock, p.discount_percent
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.id DESC"; // Sắp xếp theo ID giỏ hàng mới nhất

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $old_price = (float)$row['price'];
    $discount = (float)$row['discount_percent'];
    $current_price = ($discount > 0) ? $old_price * (1 - ($discount / 100)) : $old_price;

    $row['current_price'] = $current_price;
    $row['subtotal'] = $current_price * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}
?>

<section class="cart-page-section">
    <div class="container">
        <h2 class="page-title">Giỏ Hàng Của Bạn</h2>

        <?php if (empty($cart_items)): ?>
            <div class="cart-empty text-center py-5">
                <i class="fa-solid fa-basket-shopping" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <p>Giỏ hàng của bạn đang trống.</p>
                <a href="<?= BASE_URL ?>" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>

            <div class="cart-wrapper">
                <div class="cart-table-container">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tạm tính</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="cart-product-info">
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <span class="product-name"><?= htmlspecialchars($item['name']) ?></span>
                                    </td>
                                    <td class="cart-product-price">
                                        <?= number_format($item['current_price'], 0, ',', '.') ?>₫
                                    </td>

                                    <td class="cart-product-quantity">
                                        <form class="cart-item-form" action="<?= BASE_URL ?>handler/update_cart.php" method="POST">
                                            <div class="quantity-control">
                                                <input type="hidden" name="cart_items[<?= $item['cart_id'] ?>][cart_id]" value="<?= $item['cart_id'] ?>">

                                                <button type="button" class="qty-btn minus" onclick="changeCartQty(this, -1, <?= $item['stock'] ?>)">-</button>
                                                <input type="number"
                                                    name="cart_items[<?= $item['cart_id'] ?>][quantity]"
                                                    value="<?= $item['quantity'] ?>"
                                                    min="1"
                                                    max="<?= $item['stock'] ?>"
                                                    class="qty-input"
                                                    onchange="validateCartQty(this, <?= $item['stock'] ?>)">
                                                <button type="button" class="qty-btn plus" onclick="changeCartQty(this, 1, <?= $item['stock'] ?>)">+</button>
                                            </div>
                                        </form>
                                    </td>

                                    <td class="cart-product-subtotal">
                                        <?= number_format($item['subtotal'], 0, ',', '.') ?>₫
                                    </td>
                                    <td class="cart-product-actions">
                                        <a href="<?= BASE_URL ?>handler/remove_from_cart.php?cart_id=<?= $item['cart_id'] ?>"
                                            class="btn-delete-item"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')">
                                            <i class="fa-solid fa-trash-can"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="cart-actions-row">
                        <a href="<?= BASE_URL ?>" class="btn btn-continue">
                            <i class="fa-solid fa-arrow-left"></i> TIẾP TỤC XEM SẢN PHẨM
                        </a>
                        <button type="button" onclick="submitAllCartForms()" class="btn btn-update">
                            <i class="fa-solid fa-rotate"></i> CẬP NHẬT GIỎ HÀNG
                        </button>
                    </div>
                </div>

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

                    <a href="<?= BASE_URL ?>handler/buy_now.php?from_cart=1" class="btn btn-checkout">
                        TIẾN HÀNH THANH TOÁN
                    </a>

                    <div class="coupon-section">
                        <h4><i class="fa-solid fa-ticket"></i> Phiếu ưu đãi</h4>
                        <div class="coupon-input">
                            <input type="text" id="coupon_code" placeholder="Mã ưu đãi">
                            <button type="button" id="apply-coupon">Áp dụng</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</section>

<script src="../assets/js/cart.js"></script>
<?php
include_once "../includes/footer.php";
?>