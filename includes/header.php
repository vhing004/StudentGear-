<?php require_once 'config/db.php'; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css">
    <title>Trang chủ</title>
</head>

<body>
    <header class="header">
        <div class="header_container">
            <!-- Navigator -->
            <nav class="header_nav">
                <div class="container">
                    <!-- LOGO -->
                    <a href="<?php echo BASE_URL; ?>index.php" class="header_logo">
                        Phụ kiện ngon bổ rẻ
                    </a>
                    <!-- SEARCH -->
                    <form action="./pages/course_detail.php" class="header_search">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm" />
                        <button class="header_search-btn">Tìm kiếm</button>
                    </form>
                    <!-- MENU -->
                    <div class="header_menu">
                        <a href="" class="header_menu-btn">Đăng nhập
                        </a>
                        <a href="" class="header_menu-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Giỏ hàng</span>
                        </a>
                    </div>
                </div>
            </nav>
            <!-- Category -->
            <div class="header_category">
                <ul class="header_list container">
                    <li class="header_list-item"><a href="" class="header_list-item--link">Tai nghe BlueTooth</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Sạc điẹn thoại</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Cáp sạc</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Loa BLUETOOTH</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Đồng hồ</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Ốp điện thoại</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Liên hệ</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Chính sách</a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Tai nghe </a></li>
                    <li class="header_list-item"><a href="" class="header_list-item--link">Tai nghe </a></li>
                </ul>
            </div>
        </div>
    </header>
</body>

</html>