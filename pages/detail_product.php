<?php
include_once '../config/db.php';
include_once "../includes/header.php";

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// 2. Tăng lượt xem (views) mỗi khi truy cập
$conn->query("UPDATE products SET views = views + 1 WHERE id = $product_id");

if ($product_id <= 0) {
    header("Location: " . BASE_URL);
    exit;
}

// Query lấy chi tiết sản phẩm + tên danh mục
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.is_active = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<h2 class='text-center mt-5'>Sản phẩm không tồn tại hoặc đã bị ẩn.</h2>";
    exit;
}

// Tính giá cũ (giá thị trường)
$current_price = (float)$product['price'];
$discount_percent = (float)$product['discount_percent'];
$old_price = 0;

if ($discount_percent > 0) {
    $old_price = round($current_price / (1 - $discount_percent / 100));
}
?>
<!-- Chi tiết sản phẩm -->
<section class="product-detail">
    <div class="product-detail__container container">

        <!-- Breadcrumb -->
        <nav class="product-detail__breadcrumb">
            <a href="<?= BASE_URL ?>">Trang chủ</a> »
            <a href="<?= BASE_URL ?>tat-ca-san-pham.php">Sản phẩm</a> »
            <?php if (!empty($product['category_name'])): ?>
                <a href="<?= BASE_URL ?>pages/category.php?category_id=<?= htmlspecialchars($product['category_id']) ?>">
                    <?= htmlspecialchars($product['category_name']) ?>
                </a> »
            <?php endif; ?>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="product-detail__content">

            <!-- Phần hình ảnh -->
            <div class="product-detail__media">
                <div class="product-detail__image-main">
                    <img src="<?= htmlspecialchars($product['image'] ?? '/assets/images/no-image.jpg') ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="product-detail__img">

                    <?php if ($discount_percent > 0): ?>
                        <span class="product-detail__badge">Giảm -<?= number_format($discount_percent, 0) ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails (có thể mở rộng sau) -->
                <div class="product-detail__thumbnails">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Main" class="product-detail__thumb active">
                </div>
            </div>

            <!-- Phần thông tin -->
            <div class="product-detail__info">

                <h1 class="product-detail__title">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>

                <div class="product-detail__meta">
                    <span class="product-detail__sku">
                        Mã hàng: <strong><?= htmlspecialchars($product['slug'] ?? 'N/A') ?></strong>
                    </span>
                </div>

                <!-- Đánh giá -->
                <div class="product-detail__rating">
                    <div class="product-detail__stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <span class="product-detail__review-count"> <?= htmlspecialchars($product['views']) ?> lượt xem</span>
                </div>

                <!-- Giá bán -->
                <div class="product-detail__price-box">
                    <p>
                        Giá khuyến mãi: <span class="product-detail__price-current"><?= number_format($current_price, 0, ',', '.') ?>₫</span>
                    </p>
                    <?php if ($old_price > 0): ?>
                        <p>
                            Giá thị trường: <span class="product-detail__price-old"><?= number_format($old_price, 0, ',', '.') ?>₫</span>
                        </p>
                    <?php endif; ?>


                    <?php if ($old_price > 0): ?>
                        <p>
                            Tiết kiệm: <span class="product-detail__discount-info">
                                <?= number_format($old_price - $current_price, 0, ',', '.') ?>₫
                                (<?= number_format($discount_percent, 0) ?>%)
                            </span>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Đặc điểm nổi bật -->
                <div class="product-detail__features">
                    <h3>Mô tả sản phẩm</h3>
                    <ul>
                        <li> <?= htmlspecialchars($product['description']) ?></li>
                    </ul>
                </div>

                <!-- Chọn số lượng và nút mua -->
                <div class="product-detail__actions">

                    <form id="addToCartForm" action="
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        echo BASE_URL . 'handler/add_to_cart.php';
                    } else {
                        echo BASE_URL . 'auth/login.php';
                    }
                    ?>
                    " method="POST" class="product-detail__add-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <div class="product-detail__quantity">
                            <button type="button" class="quantity-btn minus" onclick="changeQuantity(-1)">−</button>
                            <input type="number"
                                id="quantity"
                                name="quantity"
                                value="1"
                                min="1"
                                max="<?= $product['stock'] ?>"
                                class="quantity-input"
                                required>
                            <button type="button" class="quantity-btn plus" onclick="changeQuantity(1)">+</button>
                        </div>

                        <button type="submit"
                            name="add_to_cart"
                            onclick="addToCart()"
                            class="product-detail__btn product-detail__btn--add">
                            <i class="fa-solid fa-cart-plus"></i> THÊM VÀO GIỎ HÀNG
                        </button>
                    </form>
                </div>

                <!-- Nút MUA HÀNG NGAY -->
                <form action="<?= BASE_URL ?>handler/buy_now.php" method="POST" style="margin-top: 12px;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" id="buy_quantity" value="1">

                    <button type="submit"
                        name="buy_now"
                        class="product-detail__btn product-detail__btn--buy">
                        MUA HÀNG NGAY
                    </button>
                </form>

                <!-- Thông báo stock -->
                <?php if ($product['stock'] > 0): ?>
                    <p class="product-detail__stock-info">
                        Còn <strong><?= $product['stock'] ?></strong> sản phẩm sẵn sàng giao
                    </p>
                <?php else: ?>
                    <p class="product-detail__stock-info stock-out">
                        <strong>Hết hàng</strong>
                    </p>
                <?php endif; ?>

                <button class="product-detail__btn product-detail__btn--fast-order">
                    ĐẶT HÀNG NHANH<br>
                    <small>Ship COD toàn quốc - Thanh toán khi nhận hàng</small>
                </button>

                <!-- Dịch vụ -->
                <div class="product-detail__services">
                    <div class="service-item">
                        <i class="fa-solid fa-truck-fast"></i>
                        <div>
                            <strong>SHIP COD TOÀN QUỐC</strong><br>
                            <small>Freeship Hà Nội từ 299K • Toàn quốc từ 500K</small>
                        </div>
                    </div>
                    <div class="service-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        <div>
                            <strong>BẢO HÀNH NHANH CHÓNG</strong><br>
                            <small>Bảo hành 3 tháng • Đổi mới trong 1 tháng</small>
                        </div>
                    </div>
                    <div class="service-item">
                        <i class="fa-solid fa-headset"></i>
                        <div>
                            <strong>HỖ TRỢ 24/7</strong><br>
                            <small>Hotline: 0989 498 757</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
// require_once 'config/db.php';

// 1. Lấy tất cả danh mục của sản phẩm trên
$sql_categories = "SELECT * FROM categories WHERE id = " . (int)$product['category_id'] . " AND is_active = 1 ORDER BY id ASC";
$res_categories = $conn->query($sql_categories);

if ($res_categories->num_rows > 0):
    // while ($cat = $res_categories->fetch_assoc()):
    $cat = $res_categories->fetch_assoc();
    $cat_id = $cat['id'];
    $cat_name = $cat['name'];
    $cat_slug = $cat['slug'];

    // 2. Với mỗi danh mục, lấy ra 5 sản phẩm mới nhất hoặc nổi bật
    $sql_products = "SELECT * FROM products 
        WHERE category_id = $cat_id AND is_active = 1 
        ORDER BY is_featured DESC, created_at DESC 
        LIMIT 10";
    $res_products = $conn->query($sql_products);

    // Chỉ hiển thị danh mục nếu có ít nhất 1 sản phẩm
    if ($res_products->num_rows > 0):
?>

        <section class="hot container">
            <div class="hot_head">
                <h4 class="hot_title">Sản phẩm tương tự</h4>
                <a href="<?php BASE_URL ?>category.php?id=<?= $cat_id ?>" class="hot_all">Xem tất cả</a>
            </div>

            <div class="hot_list">
                <?php
                while ($row = $res_products->fetch_assoc()):
                    $current_price = $row['price'];
                    $discount = (int)$row['discount_percent'];
                    // Tính giá cũ để hiển thị gạch ngang (nếu có giảm giá)
                    $old_price = ($discount > 0) ? $current_price / (1 - ($discount / 100)) : 0;
                ?>
                    <article class="hot_list__item">
                        <div class="hot_list__media">
                            <a href="<?php echo BASE_URL ?>pages/detail_product.php?product_id=<?php echo $row['id']; ?>">
                                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="hot_list__img">
                            </a>
                            <?php if ($discount > 0): ?>
                                <span class="hot_list__badge">Giảm -<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </div>

                        <div class="hot_list__info">
                            <h3 class="hot_list__title">
                                <a href="<?php echo BASE_URL ?>pages/detail_product.php?product_id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                            </h3>

                            <div class="hot_list__price-box">
                                <?php if ($old_price > 0): ?>
                                    <span class="hot_list__price-old"><?php echo number_format($old_price, 0, ',', '.'); ?>₫</span>
                                <?php endif; ?>
                                <span class="hot_list__price-current"><?php echo number_format($current_price, 0, ',', '.'); ?>₫</span>
                            </div>

                            <div class="hot_list__rating">
                                <div class="hot_list__stars">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div class="hot_list__count"><?php echo $row['views']; ?> đánh giá</div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </section>

<?php
    endif; // Kết thúc kiểm tra có sản phẩm hay không
// endwhile;
endif;
?>

<script>
    // Xử lý tăng giảm số lượng với giới hạn stock
    const maxStock = <?= $product['stock'] ?>;

    function changeQuantity(change) {
        let qtyInput = document.getElementById('quantity');
        let currentQty = parseInt(qtyInput.value) || 1;

        let newQty = currentQty + change;

        // Giới hạn số lượng
        if (newQty < 1) newQty = 1;
        if (newQty > maxStock) newQty = maxStock;

        qtyInput.value = newQty;

        // Cập nhật số lượng cho form "Mua ngay"
        document.getElementById('buy_quantity').value = newQty;
    }

    // Đồng bộ số lượng khi người dùng tự nhập vào input
    document.getElementById('quantity').addEventListener('input', function() {
        let val = parseInt(this.value) || 1;
        if (val < 1) val = 1;
        if (val > maxStock) val = maxStock;
        this.value = val;
        document.getElementById('buy_quantity').value = val;
    });

    // Hàm xử lý khi bấm nút THÊM VÀO GIỎ
    function addToCart() {
        const quantity = document.getElementById('quantity').value;
        const productId = <?= $product['id'] ?>;

        // Hiển thị alert ngay lập tức
        alert("Đã thêm " + quantity + " sản phẩm vào giỏ hàng!");

        // Submit form để thực sự thêm vào database
        const form = document.getElementById('addToCartForm');
        form.submit();
    }
</script>
<?php
include_once "../includes/footer.php";
?>