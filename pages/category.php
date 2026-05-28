<?php
// require_once 'config/db.php';
include '../includes/header.php';

// 1. Lấy tham số bộ lọc từ URL
$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// TỰ ĐỘNG TÍNH MỐC GIÁ CAO NHẤT ĐỘNG THEO TỪNG DANH MỤC
$sql_max = "SELECT MAX(price) as max_p FROM products WHERE is_active = 1";
if ($cat_id > 0) {
    $sql_max .= " AND category_id = $cat_id";
}
$res_max = $conn->query($sql_max)->fetch_assoc();
$db_max = isset($res_max['max_p']) ? (float)$res_max['max_p'] : 10000000; // Mặc định 10 triệu nếu trống

// Làm tròn lên mốc triệu gần nhất để thanh trượt hiển thị đẹp mắt (Ví dụ: 9.5tr thành 10tr)
$ceil_price = ceil($db_max / 1000000) * 1000000;
if ($ceil_price <= 0) {
    $ceil_price = 1000000;
}

// Tiếp nhận giá trị lọc người dùng kéo chọn (Nếu chưa kéo thì mặc định lấy kịch khung Max)
$min_price = 0; // Luôn cố định từ 0đ cho bộ lọc thanh trượt đơn
$max_price = isset($_GET['max']) ? intval($_GET['max']) : $ceil_price;

// 2. Lấy thông tin danh mục hiện tại để làm Breadcrumb và Tiêu đề
$current_cat = null;
if ($cat_id > 0) {
    $res_cat = $conn->query("SELECT * FROM categories WHERE id = $cat_id");
    $current_cat = $res_cat->fetch_assoc();
}

// Lấy thông tin danh mục hiện tại để hiển thị tiêu đề
$current_cat_name = "Tất cả sản phẩm";
if ($cat_id > 0) {
    $res_cat = $conn->query("SELECT name FROM categories WHERE id = $cat_id");
    if ($row_cat = $res_cat->fetch_assoc()) {
        $current_cat_name = $row_cat['name'];
    }
}

// 3. Truy vấn danh sách sản phẩm theo bộ lọc
$sql_products = "SELECT * FROM products WHERE is_active = 1";
if ($cat_id > 0) {
    $sql_products .= " AND category_id = $cat_id";
} elseif (isset($_GET['feature']) && $_GET['feature'] === 'true') {
    $sql_products .= " AND is_featured = 1";
}

// Thêm điều kiện tìm kiếm
if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $sql_products .= " AND (name LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
}

// Áp dụng khoảng giá lọc
$sql_products .= " AND price BETWEEN $min_price AND $max_price";

// Sắp xếp theo lựa chọn
switch ($sort) {
    case 'price_asc':
        $sql_products .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql_products .= " ORDER BY price DESC";
        break;
    default:
        $sql_products .= " ORDER BY created_at DESC";
        break;
}

$res_products = $conn->query($sql_products);

// Tạo chuỗi truy vấn tìm kiếm dùng lại cho các URL
$search_param = !empty($search) ? '&search=' . urlencode($search) : '';
?>

<main class="container category">
    <div class="category_head">
        <div class="category_breadcrumb">
            <a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a> »
            <a href="#">Sản phẩm</a> »
            <span>
                <?php
                if (!empty($search)) {
                    echo "Kết quả tìm kiếm: <strong>" . htmlspecialchars($search) . "</strong>";
                } else {
                    echo htmlspecialchars($current_cat['name'] ?? 'Sản phẩm nổi bật');
                }
                ?>
            </span>
        </div>

        <div class="category-main_filter">
            <span style="font-size: 1.4rem; color: #666;">
                Hiện đang có <strong><?php echo $res_products->num_rows; ?></strong> sản phẩm
            </span>

            <select onchange="location = this.value;" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                <option value="category.php?id=<?= $cat_id ?>&sort=default&max=<?= $max_price . $search_param ?>" <?= $sort == 'default' ? 'selected' : '' ?>>Thứ tự mặc định</option>
                <option value="category.php?id=<?= $cat_id ?>&sort=price_asc&max=<?= $max_price . $search_param ?>" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                <option value="category.php?id=<?= $cat_id ?>&sort=price_desc&max=<?= $max_price . $search_param ?>" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
            </select>
        </div>
    </div>

    <div class="category-layout" style="display: flex; gap: 20px;">
        <aside class="category-sidebar" style="flex: 1; max-width: 250px;">
            <h3 style="font-size: 1.6rem; margin-bottom: 15px;">DANH MỤC SẢN PHẨM</h3>
            <ul style="list-style: none; padding: 0;">
                <?php
                $res_menu = $conn->query("SELECT id, name FROM categories WHERE is_active = 1");
                while ($m = $res_menu->fetch_assoc()):
                ?>
                    <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        <a href="category.php?id=<?= $m['id'] ?>&sort=<?= $sort ?>&max=<?= $max_price . $search_param ?>" style="text-decoration: none; color: <?= ($cat_id == $m['id']) ? '#d0021c' : '#333' ?>;">
                            <i class="fa-solid fa-chevron-right" style="font-size: 1rem;"></i> <?= htmlspecialchars($m['name']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <h3 style="font-size: 1.6rem; margin-top: 30px; margin-bottom: 15px;">LỌC THEO GIÁ</h3>
            <form action="category.php" method="GET">
                <input type="hidden" name="id" value="<?= $cat_id ?>">
                <input type="hidden" name="min" value="0">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>

                <div class="single-slider-container">
                    <input type="range" id="single_price_range"
                        min="0"
                        max="<?= $ceil_price ?>"
                        value="<?= $max_price ?>"
                        step="50000">

                    <div class="price-display-label">
                        Giá dưới: <span id="max_price_text" style="color: #d0021c; font-size: 16px; font-weight: bold;">0đ</span>
                    </div>

                    <input type="hidden" name="max" id="hidden_input_max" value="<?= $max_price ?>">

                    <button type="submit" class="btn-filter" style="width: 100%; background: #d0021c; color: #fff; border: none; padding: 10px; cursor: pointer; font-weight: bold; border-radius: 4px;">
                        <i class="fas fa-filter" style="font-size: 11px; margin-right: 4px;"></i> Lọc sản phẩm
                    </button>
                </div>
            </form>
        </aside>

        <section class="category-main" style="flex: 3;">
            <div class="hot_list" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <?php if ($res_products->num_rows > 0): ?>
                    <?php while ($row = $res_products->fetch_assoc()):
                        $old_price = (float)$row['price'];
                        $discount_percent = (float)$row['discount_percent'];
                        $current_price = ($discount_percent > 0)
                            ? $old_price * (1 - ($discount_percent / 100))
                            : $old_price;
                    ?>
                        <article class="hot_list__item" style="border: 1px solid #f1f1f1;">
                            <div class="hot_list__media">
                                <a href="detail_product.php?product_id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="hot_list__img">
                                </a>
                                <?php if ($discount_percent > 0): ?>
                                    <span class="hot_list__badge">Giảm -<?php echo $discount_percent; ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="hot_list__info">
                                <h3 class="hot_list__title">
                                    <a href="detail_product.php?product_id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
                                </h3>

                                <div class="hot_list__price-box">
                                    <?php if ($old_price > 0): ?>
                                        <span class="hot_list__price-old"><?php echo number_format($old_price, 0, ',', '.'); ?>₫</span>
                                    <?php endif; ?>
                                    <span class="hot_list__price-current"><?php echo number_format($current_price, 0, ',', '.'); ?>₫</span>
                                </div>

                                <div class="hot_list__rating">
                                    <div class="hot_list__stars">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                                    </div>
                                    <div class="hot_list__count"><?php echo $row['views']; ?> đánh giá</div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: span 4; text-align: center; padding: 50px;">Không có sản phẩm nào phù hợp với bộ lọc.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const priceRange = document.getElementById("single_price_range");
        const maxPriceText = document.getElementById("max_price_text");
        const hiddenMax = document.getElementById("hidden_input_max");

        function formatVNCurrency(value) {
            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
        }

        function updateSingleSliderOutput() {
            const currentVal = parseInt(priceRange.value);
            maxPriceText.innerText = formatVNCurrency(currentVal);
            hiddenMax.value = currentVal;
        }

        if (priceRange) {
            priceRange.addEventListener("input", updateSingleSliderOutput);
            updateSingleSliderOutput();
        }
    });
</script>