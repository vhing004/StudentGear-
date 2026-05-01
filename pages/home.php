<?php
$sql = "SELECT title, image FROM banners";
$result = $conn->query($sql);
?>

<head>
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
</head>

<main class="main">
    <div class="container">
        <!-- BANNER -->
        <section class="banner">
            <div class="banner__container">
                <div class="banner__slider">
                    <?php
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <div class="item" data-title="<?php echo $row['title']; ?>">
                            <img src="<?php echo $row['image']; ?>" alt="banner">
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="banner__sidebar">
                    <div class="banner__side-item">
                        <img src="<?php echo BASE_URL; ?>assets/images/banner1.jpg" alt="Sub 1" class="banner__side-img">
                    </div>
                    <div class="banner__side-item">
                        <img src="<?php echo BASE_URL; ?>assets/images/banner2.jpg" alt="Sub 2" class="banner__side-img">
                    </div>
                </div>
            </div>

            <nav class="banner__tabs" id="bannerTabs">
            </nav>
        </section>

        <!-- Product hot -->
        <?php
        require_once 'config/db.php';

        // Truy vấn lấy sản phẩm Nổi bật hoặc có lượt xem cao (Hot)
        // Sắp xếp theo is_featured (1 lên trước) và views (nhiều lên trước)
        $sql = "SELECT * FROM products 
        WHERE is_active = 1 
        ORDER BY is_featured DESC, views DESC 
        LIMIT 10";

        $result = $conn->query($sql);
        ?>

        <section class="hot">
            <div class="hot_head">
                <h4 class="hot_title">Sản phẩm bán chạy</h4>
                <a href="<?php BASE_URL ?>pages/category.php" class="hot_all">Xem tất cả</a>
            </div>
            <div class="hot_list">
                <?php
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        // Tính toán giá hiển thị
                        $current_price = $row['price'];
                        $discount = (int)$row['discount_percent'];

                        // Giả định: Nếu có discount, tính giá cũ (gạch ngang)
                        // Hoặc nếu bạn có cột giá cũ riêng thì thay vào đây
                        $old_price = ($discount > 0) ? $current_price / (1 - ($discount / 100)) : 0;
                ?>
                        <article class="hot_list__item">
                            <div class="hot_list__media">
                                <a href="<?php echo BASE_URL ?>pages/detail_product.php?product_id=<?php echo $row['id']; ?>">
                                    <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>" class="hot_list__img">
                                </a>
                                <?php if ($discount > 0): ?>
                                    <span class="hot_list__badge">Giảm -<?= $discount ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="hot_list__info">
                                <h3 class="hot_list__title">
                                    <a href="<?php echo BASE_URL ?>pages/detail_product.php?product_id=<?php echo $row['id']; ?>"><?= $row['name'] ?></a>
                                </h3>
                                <div class="hot_list__price-box">
                                    <?php if ($old_price > 0): ?>
                                        <span class="hot_list__price-old"><?= number_format($old_price, 0, ',', '.') ?>₫</span>
                                    <?php endif; ?>
                                    <span class="hot_list__price-current"><?= number_format($current_price, 0, ',', '.') ?>₫</span>
                                </div>

                                <div class="hot_list__rating">
                                    <div class="hot_list__stars">
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <div class="hot_list__count"><?= $row['views'] ?> lượt xem</div>
                                </div>
                            </div>
                        </article>
                <?php
                    endwhile;
                else:
                    echo "<p>Không có sản phẩm nào.</p>";
                endif;
                ?>
            </div>
        </section>

        <?php
        require_once 'config/db.php';

        // 1. Lấy tất cả danh mục đang hoạt động
        $sql_categories = "SELECT * FROM categories WHERE is_active = 1 ORDER BY id ASC";
        $res_categories = $conn->query($sql_categories);

        if ($res_categories->num_rows > 0):
            while ($cat = $res_categories->fetch_assoc()):
                $cat_id = $cat['id'];
                $cat_name = $cat['name'];
                $cat_slug = $cat['slug'];

                // 2. Với mỗi danh mục, lấy ra 5 sản phẩm mới nhất hoặc nổi bật
                $sql_products = "SELECT * FROM products 
                         WHERE category_id = $cat_id AND is_active = 1 
                         ORDER BY is_featured DESC, created_at DESC 
                         LIMIT 5";
                $res_products = $conn->query($sql_products);

                // Chỉ hiển thị danh mục nếu có ít nhất 1 sản phẩm
                if ($res_products->num_rows > 0):
        ?>

                    <section class="hot">
                        <div class="hot_head">
                            <h4 class="hot_title"><?php echo htmlspecialchars($cat_name); ?></h4>
                            <a href="<?php BASE_URL ?>pages/category.php?id=<?= $cat_id ?>" class="hot_all">Xem tất cả</a>
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
            endwhile;
        endif;
        ?>
    </div>
</main>

<script
    type="text/javascript"
    src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
<script
    type="text/javascript"
    src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script
    type="text/javascript"
    src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="./js/app.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/index.js"></script>