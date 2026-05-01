<?php
// require_once 'config/db.php';
include '../includes/header.php';

// 1. Lấy ID danh mục từ URL (ví dụ: category.php?id=1)
$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$min_price = isset($_GET['min']) ? intval($_GET['min']) : 0;
$max_price = isset($_GET['max']) ? intval($_GET['max']) : 100000000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// 2. Lấy thông tin danh mục hiện tại để làm Breadcrumb và Tiêu đề[cite: 2]
$current_cat = null;
if ($cat_id > 0) {
    $res_cat = $conn->query("SELECT * FROM categories WHERE id = $cat_id");
    $current_cat = $res_cat->fetch_assoc();
}

// 2. Lấy thông tin danh mục hiện tại để hiển thị tiêu đề[cite: 2]
$current_cat_name = "Tất cả sản phẩm";
if ($cat_id > 0) {
    $res_cat = $conn->query("SELECT name FROM categories WHERE id = $cat_id");
    if ($row_cat = $res_cat->fetch_assoc()) {
        $current_cat_name = $row_cat['name'];
    }
}

// 3. Truy vấn LẤY TẤT CẢ sản phẩm (Không dùng LIMIT)
$sql_products = "SELECT * FROM products WHERE is_active = 1";
if ($cat_id > 0) {
    $sql_products .= " AND category_id = $cat_id";
}
$sql_products .= " AND price BETWEEN $min_price AND $max_price";

// Sắp xếp theo lựa chọn[cite: 1]
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
?>

<main class="container category">
    <div class="category_head">
        <div class="category_breadcrumb">
            <a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a> »
            <a href="#">Sản phẩm</a> »
            <span><?php echo $current_cat['name'] ?? 'Tất cả sản phẩm'; ?></span>
        </div>
        <!-- Cụm hiển thị số lượng và Sắp xếp bên phải -->
        <div class="category-main_filter">
            <span style=" font-size: 1.4rem; color: #666;">
                Hiện đang có <strong><?php echo $res_products->num_rows; ?></strong> sản phẩm
            </span>

            <select onchange="location = this.value;" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                <option value="category.php?id=<?= $cat_id ?>&sort=default" <?= $sort == 'default' ? 'selected' : '' ?>>Thứ tự mặc định</option>
                <option value="category.php?id=<?= $cat_id ?>&sort=price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                <option value="category.php?id=<?= $cat_id ?>&sort=price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
            </select>
        </div>
    </div>
    <div class="category-layout" style="display: flex; gap: 20px;">

        <!-- SIDEBAR: Bộ lọc bên trái (Giống image_4a4f9a.jpg) -->
        <aside class="category-sidebar" style="flex: 1; max-width: 250px;">
            <h3 class="hot_title" style="font-size: 1.6rem; margin-bottom: 15px;">DANH MỤC SẢN PHẨM</h3>
            <ul style="list-style: none; padding: 0;">
                <?php
                $res_menu = $conn->query("SELECT id, name FROM categories WHERE is_active = 1");
                while ($m = $res_menu->fetch_assoc()):
                ?>
                    <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        <a href="category.php?id=<?= $m['id'] ?>" style="text-decoration: none; color: <?= ($cat_id == $m['id']) ? '#d0021c' : '#333' ?>;">
                            <i class="fa-solid fa-chevron-right" style="font-size: 1rem;"></i> <?= htmlspecialchars($m['name']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <h3 class="hot_title" style="font-size: 1.6rem; margin-top: 30px; margin-bottom: 15px;">LỌC THEO GIÁ</h3>
            <form action="category.php" method="GET">
                <input type="hidden" name="id" value="<?= $cat_id ?>">
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <input type="number" name="min" value="<?= $min_price ?>" placeholder="Giá từ..." style="padding: 5px;">
                    <input type="number" name="max" value="<?= $max_price ?>" placeholder="Đến..." style="padding: 5px;">
                    <button type="submit" class="btn-filter" style="background: #d0021c; color: #fff; border: none; padding: 8px; cursor: pointer;">Lọc sản phẩm</button>
                </div>
            </form>
        </aside>

        <!-- CONTENT: Hiển thị danh sách sản phẩm (Tái sử dụng class của bạn) -->
        <section class="category-main" style="flex: 3;">
            <!-- Cụm hiển thị số lượng và Sắp xếp bên phải -->
            <!-- <div class="category-main_filter">
                <span style=" font-size: 1.4rem; color: #666;">
                    Hiện đang có <strong><?php echo $res_products->num_rows; ?></strong> sản phẩm
                </span>

                <select onchange="location = this.value;" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                    <option value="category.php?id=<?= $cat_id ?>&sort=default" <?= $sort == 'default' ? 'selected' : '' ?>>Thứ tự mặc định</option>
                    <option value="category.php?id=<?= $cat_id ?>&sort=price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                    <option value="category.php?id=<?= $cat_id ?>&sort=price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                </select>
            </div> -->

            <div class="hot_list" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <?php if ($res_products->num_rows > 0): ?>
                    <?php while ($row = $res_products->fetch_assoc()):
                        $current_price = $row['price'];
                        $discount = (int)$row['discount_percent'];
                        $old_price = ($discount > 0) ? $current_price / (1 - ($discount / 100)) : 0;
                    ?>
                        <!-- TÁI SỬ DỤNG HOÀN TOÀN ARTICLE CỦA BẠN[cite: 1] -->
                        <article class="hot_list__item" style="border: 1px solid #f1f1f1;">
                            <div class="hot_list__media">
                                <a href="detail_product.php?product_id=<?php echo $row['id']; ?>">
                                    <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="hot_list__img">
                                </a>
                                <?php if ($discount > 0): ?>
                                    <span class="hot_list__badge">Giảm -<?php echo $discount; ?>%</span>
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