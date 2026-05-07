<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar__brand">
        <h4>Student<span style="color: #d0021c;">Gear</span></h4>
    </div>
    <nav class="sidebar__nav">
        <a href="<?php echo BASE_URL; ?>admin/index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>admin/pages/categories.php" class="<?= ($current_page == 'categories.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-table-cells-large"></i> Danh mục
        </a>
        <a href="<?php echo BASE_URL; ?>admin/pages/products.php" class="<?= ($current_page == 'products.php') ? 'active' : '' ?>">
            <i class="fas fa-laptop"></i> Sản phẩm
        </a>
        <a href="<?php echo BASE_URL; ?>admin/pages/orders.php" class="<?= ($current_page == 'orders.php') ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> Đơn hàng
        </a>
        <a href="<?php echo BASE_URL; ?>admin/pages/users.php" class="<?= ($current_page == 'users.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Khách hàng
        </a>
        <div class="sidebar__divider"></div>
        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </nav>
</aside>