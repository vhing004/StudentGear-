-- ============================================================
--  TECHSHOP DATABASE - Website Bán Đồ Công Nghệ Cho Sinh Viên
--  Stack: PHP + MySQL (XAMPP)
--  Tác giả: Generated from Use Case Specification
-- ============================================================

CREATE DATABASE IF NOT EXISTS techshop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE techshop;

-- ============================================================
-- BẢNG 1: users
-- Lưu tài khoản người dùng và admin (dùng chung 1 bảng, phân biệt qua role)
-- Use case liên quan: UC1 (Đăng nhập), UC2 (Đăng ký),
--                     UC9 (Cập nhật hồ sơ), UC10 (Đổi mật khẩu),
--                     UC11 (Quản lý người dùng - Admin)
-- ============================================================
CREATE TABLE users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(100)     NOT NULL                   COMMENT 'Họ và tên đầy đủ',
    email         VARCHAR(150)     NOT NULL                   COMMENT 'Email đăng nhập',
    phone         VARCHAR(15)      DEFAULT NULL               COMMENT 'Số điện thoại',
    password_hash VARCHAR(255)     NOT NULL                   COMMENT 'Mật khẩu đã được hash (password_hash PHP)',
    address       TEXT             DEFAULT NULL               COMMENT 'Địa chỉ giao hàng mặc định',
    role          ENUM('user','admin') NOT NULL DEFAULT 'user' COMMENT 'Phân quyền: user hoặc admin',
    status        ENUM('active','blocked') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái tài khoản (UC1 exception: bị khóa)',
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    INDEX idx_users_role   (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB COMMENT='Tài khoản người dùng và quản trị viên';


-- ============================================================
-- BẢNG 2: categories
-- Danh mục sản phẩm (Tai nghe, Ốp lưng, Sạc dự phòng, ...)
-- Use case liên quan: UC3 (Lọc sản phẩm theo danh mục),
--                     UC12 (Quản lý danh mục - Admin)
-- ============================================================
CREATE TABLE categories (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)  NOT NULL                   COMMENT 'Tên danh mục (VD: Tai nghe, Ốp lưng)',
    description TEXT          DEFAULT NULL               COMMENT 'Mô tả ngắn về danh mục',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB COMMENT='Danh mục sản phẩm';


-- ============================================================
-- BẢNG 3: products
-- Thông tin sản phẩm công nghệ
-- Use case liên quan: UC3 (Tìm kiếm/lọc), UC4 (Xem chi tiết),
--                     UC5 (Giỏ hàng), UC13 (Quản lý sản phẩm - Admin)
-- ============================================================
CREATE TABLE products (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    category_id    INT UNSIGNED     NOT NULL               COMMENT 'FK → categories.id',
    name           VARCHAR(200)     NOT NULL               COMMENT 'Tên sản phẩm',
    description    TEXT             DEFAULT NULL           COMMENT 'Thông số kỹ thuật / mô tả chi tiết (UC4)',
    price          DECIMAL(12,2)    NOT NULL               COMMENT 'Giá bán (VNĐ)',
    stock_quantity INT UNSIGNED     NOT NULL DEFAULT 0     COMMENT 'Số lượng tồn kho (UC4, UC5 exception)',
    is_active      TINYINT(1)       NOT NULL DEFAULT 1     COMMENT '1 = đang bán, 0 = ẩn/xóa mềm (UC13)',
    created_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_products_category  (category_id),
    INDEX idx_products_price     (price),
    INDEX idx_products_stock     (stock_quantity),
    INDEX idx_products_active    (is_active),
    FULLTEXT INDEX ft_products_search (name, description)   -- Hỗ trợ UC3: tìm kiếm full-text
) ENGINE=InnoDB COMMENT='Sản phẩm công nghệ (tai nghe, ốp lưng, sạc, ...)';


-- ============================================================
-- BẢNG 4: product_images
-- Hình ảnh sản phẩm (1 sản phẩm có nhiều ảnh)
-- Use case liên quan: UC4 (Xem chi tiết - ảnh chính + ảnh phụ)
-- ============================================================
CREATE TABLE product_images (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    product_id  INT UNSIGNED  NOT NULL               COMMENT 'FK → products.id',
    image_path  VARCHAR(255)  NOT NULL               COMMENT 'Đường dẫn file ảnh (VD: uploads/products/abc.jpg)',
    is_primary  TINYINT(1)    NOT NULL DEFAULT 0     COMMENT '1 = ảnh đại diện chính, 0 = ảnh phụ',
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0  COMMENT 'Thứ tự hiển thị ảnh',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_product_images_product   (product_id),
    INDEX idx_product_images_primary   (product_id, is_primary)
) ENGINE=InnoDB COMMENT='Hình ảnh của sản phẩm (hỗ trợ nhiều ảnh)';


-- ============================================================
-- BẢNG 5: cart
-- Giỏ hàng của người dùng đã đăng nhập
-- Use case liên quan: UC5 (Quản lý giỏ hàng)
-- Ghi chú: Mỗi (user_id, product_id) là duy nhất — tăng quantity khi thêm lại
-- ============================================================
CREATE TABLE cart (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED  NOT NULL               COMMENT 'FK → users.id',
    product_id  INT UNSIGNED  NOT NULL               COMMENT 'FK → products.id',
    quantity    INT UNSIGNED  NOT NULL DEFAULT 1     COMMENT 'Số lượng sản phẩm trong giỏ',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_cart_user_product (user_id, product_id),  -- Không trùng sản phẩm cùng user

    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE CASCADE,

    INDEX idx_cart_user (user_id)
) ENGINE=InnoDB COMMENT='Giỏ hàng - lưu sản phẩm chưa đặt của người dùng';


-- ============================================================
-- BẢNG 6: orders
-- Đơn hàng đã được tạo
-- Use case liên quan: UC6 (Đặt hàng), UC7 (Lịch sử mua hàng),
--                     UC8 (Chi tiết đơn hàng), UC14 (Quản lý đơn hàng - Admin),
--                     UC15 (Thống kê báo cáo)
-- ============================================================
CREATE TABLE orders (
    id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED      NOT NULL               COMMENT 'FK → users.id (người đặt)',

    -- Snapshot thông tin nhận hàng tại thời điểm đặt (không phụ thuộc profile user sau này)
    receiver_name   VARCHAR(100)      NOT NULL               COMMENT 'Họ tên người nhận',
    receiver_phone  VARCHAR(15)       NOT NULL               COMMENT 'SĐT người nhận',
    receiver_address TEXT             NOT NULL               COMMENT 'Địa chỉ giao hàng',

    total_amount    DECIMAL(12,2)     NOT NULL               COMMENT 'Tổng tiền đơn hàng (VNĐ)',

    -- Trạng thái đơn hàng: tuân theo quy trình UC14
    -- pending → shipping → completed  |  pending → cancelled
    status          ENUM('pending','shipping','completed','cancelled')
                                      NOT NULL DEFAULT 'pending'
                                      COMMENT 'Trạng thái: pending=Chờ xác nhận, shipping=Đang giao, completed=Hoàn thành, cancelled=Đã hủy',

    cancel_reason   TEXT              DEFAULT NULL           COMMENT 'Lý do hủy đơn (UC14 - Admin nhập khi hủy)',
    created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày đặt hàng',
    updated_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    INDEX idx_orders_user       (user_id),
    INDEX idx_orders_status     (status),
    INDEX idx_orders_created_at (created_at)   -- Phục vụ thống kê theo ngày/tháng/năm (UC15)
) ENGINE=InnoDB COMMENT='Đơn hàng của người dùng';


-- ============================================================
-- BẢNG 7: order_items
-- Chi tiết sản phẩm trong từng đơn hàng
-- Use case liên quan: UC6, UC7, UC8, UC14, UC15
-- Ghi chú: Lưu snapshot tên + giá tại thời điểm mua (tránh thay đổi khi sản phẩm bị sửa)
-- ============================================================
CREATE TABLE order_items (
    id               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    order_id         INT UNSIGNED   NOT NULL               COMMENT 'FK → orders.id',
    product_id       INT UNSIGNED   DEFAULT NULL           COMMENT 'FK → products.id (NULL nếu SP đã bị xóa)',

    -- Snapshot dữ liệu tại thời điểm đặt hàng
    product_name     VARCHAR(200)   NOT NULL               COMMENT 'Tên SP lúc mua (snapshot)',
    product_price    DECIMAL(12,2)  NOT NULL               COMMENT 'Đơn giá lúc mua (snapshot)',

    quantity         INT UNSIGNED   NOT NULL               COMMENT 'Số lượng mua',
    subtotal         DECIMAL(12,2)  NOT NULL               COMMENT 'Thành tiền = product_price × quantity',

    PRIMARY KEY (id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE ON DELETE SET NULL,

    INDEX idx_order_items_order   (order_id),
    INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB COMMENT='Chi tiết sản phẩm trong đơn hàng (snapshot giá tại thời điểm mua)';


-- ============================================================
-- DỮ LIỆU MẪU (SAMPLE DATA)
-- ============================================================

-- Admin mặc định (mật khẩu: Admin@123 — đã hash bằng password_hash PHP)
INSERT INTO users (full_name, email, phone, password_hash, role, status) VALUES
('Quản Trị Viên', 'admin@techshop.vn', '0900000000',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Tài khoản user mẫu (mật khẩu: User@123)
INSERT INTO users (full_name, email, phone, password_hash, address, role, status) VALUES
('Nguyễn Văn An',   'an.nguyen@student.edu.vn',  '0912345678',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '144 Xuân Thủy, Cầu Giấy, Hà Nội', 'user', 'active'),
('Trần Thị Bình',   'binh.tran@student.edu.vn',  '0987654321',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '268 Lý Thường Kiệt, Q.10, TP.HCM', 'user', 'active'),
('Lê Minh Khoa',    'khoa.le@student.edu.vn',    '0977123456',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 '54 Nguyễn Lương Bằng, Đà Nẵng', 'user', 'blocked');

-- Danh mục sản phẩm
INSERT INTO categories (name, description) VALUES
('Tai nghe',         'Tai nghe có dây, không dây, gaming, ANC cho sinh viên'),
('Ốp lưng',          'Ốp lưng điện thoại các loại, chống sốc, thời trang'),
('Sạc dự phòng',     'Pin sạc dự phòng dung lượng cao, nhỏ gọn tiện mang'),
('Chuột & Bàn phím', 'Chuột máy tính, bàn phím cơ/membrane cho sinh viên'),
('Phụ kiện Laptop',  'Hub USB, túi chống sốc, đế tản nhiệt, dây cáp'),
('Cáp & Sạc',        'Cáp sạc Type-C, Lightning, Micro-USB, củ sạc nhanh');

-- Sản phẩm mẫu
INSERT INTO products (category_id, name, description, price, stock_quantity) VALUES
(1, 'Tai nghe Bluetooth JBL Tune 510BT',
 'Âm bass mạnh, thời lượng pin 40 giờ, kết nối Bluetooth 5.0, có thể gập gọn dễ mang theo.',
 890000, 50),
(1, 'Tai nghe có dây Gaming HyperX Cloud Stinger',
 'Jack 3.5mm, micro chống ồn, đệm tai nhung mềm, phù hợp học online và gaming.',
 650000, 30),
(2, 'Ốp lưng iPhone 15 chống sốc Spigen Tough Armor',
 'Chất liệu TPU + PC, tiêu chuẩn MIL-STD-810G, bảo vệ 4 góc, hỗ trợ sạc MagSafe.',
 320000, 120),
(2, 'Ốp lưng Samsung Galaxy A54 trong suốt chống ố vàng',
 'Chất liệu silicon dẻo, trong suốt, không ố vàng theo thời gian, vừa vặn, nhẹ tay.',
 85000, 200),
(3, 'Pin sạc dự phòng Anker PowerCore 10000mAh',
 '10000mAh, hỗ trợ PowerIQ 2.0, đầu ra USB-A + USB-C, nhỏ gọn bỏ túi quần được.',
 490000, 75),
(3, 'Pin sạc dự phòng Xiaomi 33W 20000mAh',
 '20000mAh, sạc nhanh 33W, 2 cổng USB-A, 1 USB-C, màn hình hiển thị phần trăm pin.',
 750000, 40),
(4, 'Chuột không dây Logitech M185',
 'Kết nối USB nano receiver, pin AA dùng 12 tháng, thiết kế tay phải ergonomic.',
 290000, 60),
(4, 'Bàn phím cơ AKKO 3068B Plus',
 'Switch AKKO CS Jelly Purple, 68 phím, kết nối Bluetooth 5.0 + 2.4G + USB-C có dây.',
 1350000, 25),
(5, 'Hub USB-C 7-in-1 Ugreen',
 'HDMI 4K, USB-A 3.0 x3, SD/MicroSD, PD 100W, hỗ trợ MacBook và laptop Thunderbolt.',
 520000, 45),
(6, 'Cáp sạc nhanh Type-C to Type-C 100W Anker',
 'Hỗ trợ USB PD 100W, dài 1.8m, vỏ bọc nylon chịu uốn gập 20.000 lần.',
 180000, 150);

-- Hình ảnh sản phẩm mẫu (ảnh chính)
INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1,  'uploads/products/jbl-tune510bt-main.jpg',       1, 0),
(1,  'uploads/products/jbl-tune510bt-side.jpg',       0, 1),
(2,  'uploads/products/hyperx-stinger-main.jpg',      1, 0),
(3,  'uploads/products/spigen-iphone15-main.jpg',     1, 0),
(4,  'uploads/products/samsung-a54-clear-main.jpg',   1, 0),
(5,  'uploads/products/anker-10000-main.jpg',         1, 0),
(5,  'uploads/products/anker-10000-size.jpg',         0, 1),
(6,  'uploads/products/xiaomi-20000-main.jpg',        1, 0),
(7,  'uploads/products/logitech-m185-main.jpg',       1, 0),
(8,  'uploads/products/akko-3068b-main.jpg',          1, 0),
(8,  'uploads/products/akko-3068b-switch.jpg',        0, 1),
(9,  'uploads/products/ugreen-hub-main.jpg',          1, 0),
(10, 'uploads/products/anker-cable-main.jpg',         1, 0);

-- Đơn hàng mẫu
INSERT INTO orders (user_id, receiver_name, receiver_phone, receiver_address, total_amount, status) VALUES
(2, 'Nguyễn Văn An', '0912345678', '144 Xuân Thủy, Cầu Giấy, Hà Nội',  1380000, 'completed'),
(2, 'Nguyễn Văn An', '0912345678', '144 Xuân Thủy, Cầu Giấy, Hà Nội',   890000, 'shipping'),
(3, 'Trần Thị Bình', '0987654321', '268 Lý Thường Kiệt, Q.10, TP.HCM',  490000, 'pending');

-- Chi tiết đơn hàng mẫu
INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES
(1, 7, 'Chuột không dây Logitech M185',            290000, 1,  290000),
(1, 5, 'Pin sạc dự phòng Anker PowerCore 10000mAh',490000, 1,  490000),
(1, 3, 'Ốp lưng iPhone 15 chống sốc Spigen Tough Armor', 320000, 1, 320000),
(1,10, 'Cáp sạc nhanh Type-C to Type-C 100W Anker',180000, 1,  180000),

(2, 1, 'Tai nghe Bluetooth JBL Tune 510BT',        890000, 1,  890000),

(3, 5, 'Pin sạc dự phòng Anker PowerCore 10000mAh',490000, 1,  490000);

