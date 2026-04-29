-- =============================================================
--  TECHSHOP - MySQL Database Schema
--  Phiên bản đầy đủ: discount, rating, lượt mua, banner, ...
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

-- -------------------------------------------------------------
-- 1. USERS
-- -------------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,                   -- bcrypt hash
    phone       VARCHAR(20)     DEFAULT NULL,
    address     TEXT            DEFAULT NULL,
    avatar      VARCHAR(255)    DEFAULT NULL,
    role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. CATEGORIES
-- -------------------------------------------------------------
CREATE TABLE categories (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    slug        VARCHAR(110)    NOT NULL UNIQUE,
    icon        VARCHAR(255)    DEFAULT NULL,               -- icon class hoặc đường dẫn SVG
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. PRODUCTS
-- -------------------------------------------------------------
CREATE TABLE products (
    id                  INT UNSIGNED        AUTO_INCREMENT PRIMARY KEY,
    category_id         INT UNSIGNED        NOT NULL,
    name                VARCHAR(200)        NOT NULL,
    slug                VARCHAR(220)        NOT NULL UNIQUE,
    sku                 VARCHAR(60)         NOT NULL UNIQUE,        -- mã sản phẩm

    -- Giá & giảm giá
    price               DECIMAL(15,2)       NOT NULL,              -- giá gốc
    discount_percent    TINYINT UNSIGNED    NOT NULL DEFAULT 0,    -- % giảm (0-100)
    -- Giá bán thực tế = price * (1 - discount_percent/100)
    -- Tính bằng GENERATED COLUMN để query nhanh
    sale_price          DECIMAL(15,2)       GENERATED ALWAYS AS
                            (ROUND(price * (1 - discount_percent / 100), 0)) STORED,

    -- Mô tả
    short_desc          VARCHAR(500)        DEFAULT NULL,          -- mô tả ngắn (card sản phẩm)
    description         LONGTEXT            DEFAULT NULL,          -- mô tả chi tiết (HTML)
    specifications      JSON                DEFAULT NULL,          -- thông số kỹ thuật dạng JSON

    -- Stock
    stock               INT UNSIGNED        NOT NULL DEFAULT 0,

    -- Thống kê
    total_sold          INT UNSIGNED        NOT NULL DEFAULT 0,    -- lượt mua / đã bán
    avg_rating          DECIMAL(3,2)        NOT NULL DEFAULT 0.00, -- trung bình sao (0.00-5.00)
    review_count        INT UNSIGNED        NOT NULL DEFAULT 0,    -- số lượt đánh giá

    -- Gắn nhãn nổi bật
    is_hot              TINYINT(1)          NOT NULL DEFAULT 0,    -- sản phẩm HOT
    is_featured         TINYINT(1)          NOT NULL DEFAULT 0,    -- sản phẩm nổi bật (hero section)
    is_new              TINYINT(1)          NOT NULL DEFAULT 1,    -- sản phẩm mới

    is_active           TINYINT(1)          NOT NULL DEFAULT 1,    -- soft delete
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_category  (category_id),
    INDEX idx_sale_price (sale_price),
    INDEX idx_total_sold (total_sold DESC),
    INDEX idx_hot        (is_hot),
    INDEX idx_featured   (is_featured),
    INDEX idx_is_new     (is_new),
    FULLTEXT INDEX ft_product_search (name, short_desc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. PRODUCT IMAGES  (slide ảnh trang chi tiết)
-- -------------------------------------------------------------
CREATE TABLE product_images (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED    NOT NULL,
    image_url   VARCHAR(255)    NOT NULL,
    alt_text    VARCHAR(150)    DEFAULT NULL,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,       -- thứ tự slide; 0 = ảnh đại diện
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_img_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_pi_product (product_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. PRODUCT REVIEWS  (rating & nhận xét)
-- -------------------------------------------------------------
CREATE TABLE product_reviews (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED    NOT NULL,
    user_id     INT UNSIGNED    NOT NULL,
    order_id    INT UNSIGNED    DEFAULT NULL,              -- đánh giá sau khi mua (tùy chọn)
    rating      TINYINT UNSIGNED NOT NULL,                -- 1-5 sao
    comment     TEXT            DEFAULT NULL,
    is_approved TINYINT(1)      NOT NULL DEFAULT 1,       -- admin duyệt (0 = ẩn)
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_user_product_review (user_id, product_id),
    CONSTRAINT fk_rev_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. BANNERS  (slide banner trang chủ)
-- -------------------------------------------------------------
CREATE TABLE banners (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)    NOT NULL,
    subtitle    VARCHAR(250)    DEFAULT NULL,
    image_url   VARCHAR(255)    NOT NULL,
    link_url    VARCHAR(255)    DEFAULT NULL,              -- link khi click banner
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    starts_at   DATETIME        DEFAULT NULL,             -- thời gian bắt đầu hiển thị
    ends_at     DATETIME        DEFAULT NULL,             -- thời gian kết thúc
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 7. CART
-- -------------------------------------------------------------
CREATE TABLE cart (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    product_id  INT UNSIGNED    NOT NULL,
    quantity    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    added_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_cart_item (user_id, product_id),      -- upsert an toàn
    CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 8. ORDERS
-- -------------------------------------------------------------
CREATE TABLE orders (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED    NOT NULL,

    -- Snapshot thông tin giao hàng tại thời điểm đặt
    shipping_name       VARCHAR(100)    NOT NULL,
    shipping_phone      VARCHAR(20)     NOT NULL,
    shipping_address    TEXT            NOT NULL,

    -- Tổng tiền snapshot
    subtotal            DECIMAL(15,2)   NOT NULL,         -- tổng trước phí ship
    shipping_fee        DECIMAL(15,2)   NOT NULL DEFAULT 0,
    total_amount        DECIMAL(15,2)   NOT NULL,         -- subtotal + shipping_fee

    -- Trạng thái
    status              ENUM(
                            'pending',      -- chờ xác nhận
                            'confirmed',    -- đã xác nhận
                            'shipping',     -- đang giao
                            'delivered',    -- đã giao
                            'cancelled'     -- đã huỷ
                        ) NOT NULL DEFAULT 'pending',

    payment_method      ENUM('cod','bank_transfer','momo') NOT NULL DEFAULT 'cod',
    payment_status      ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',

    note                TEXT            DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_order_user   (user_id),
    INDEX idx_order_status (status),
    INDEX idx_order_date   (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 9. ORDER ITEMS
-- -------------------------------------------------------------
CREATE TABLE order_items (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    order_id            INT UNSIGNED    NOT NULL,
    product_id          INT UNSIGNED    NOT NULL,

    -- Snapshot sản phẩm tại thời điểm đặt (bảo toàn lịch sử)
    product_name        VARCHAR(200)    NOT NULL,
    product_sku         VARCHAR(60)     NOT NULL,
    product_image       VARCHAR(255)    DEFAULT NULL,
    unit_price          DECIMAL(15,2)   NOT NULL,         -- giá bán thực tế lúc mua
    discount_percent    TINYINT UNSIGNED NOT NULL DEFAULT 0,

    quantity            SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    line_total          DECIMAL(15,2)   NOT NULL,         -- unit_price * quantity

    CONSTRAINT fk_oi_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_oi_order   (order_id),
    INDEX idx_oi_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  TRIGGERS: tự động cập nhật avg_rating & review_count
-- =============================================================

DELIMITER $$

-- Sau khi thêm review
CREATE TRIGGER trg_review_insert
AFTER INSERT ON product_reviews FOR EACH ROW
BEGIN
    UPDATE products
    SET
        avg_rating   = (SELECT ROUND(AVG(rating), 2) FROM product_reviews
                        WHERE product_id = NEW.product_id AND is_approved = 1),
        review_count = (SELECT COUNT(*) FROM product_reviews
                        WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END$$

-- Sau khi cập nhật review
CREATE TRIGGER trg_review_update
AFTER UPDATE ON product_reviews FOR EACH ROW
BEGIN
    UPDATE products
    SET
        avg_rating   = (SELECT ROUND(AVG(rating), 2) FROM product_reviews
                        WHERE product_id = NEW.product_id AND is_approved = 1),
        review_count = (SELECT COUNT(*) FROM product_reviews
                        WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END$$

-- Sau khi xóa review
CREATE TRIGGER trg_review_delete
AFTER DELETE ON product_reviews FOR EACH ROW
BEGIN
    UPDATE products
    SET
        avg_rating   = IFNULL((SELECT ROUND(AVG(rating), 2) FROM product_reviews
                               WHERE product_id = OLD.product_id AND is_approved = 1), 0),
        review_count = (SELECT COUNT(*) FROM product_reviews
                        WHERE product_id = OLD.product_id AND is_approved = 1)
    WHERE id = OLD.product_id;
END$$

-- Sau khi đặt hàng thành công → cộng total_sold
CREATE TRIGGER trg_order_delivered
AFTER UPDATE ON orders FOR EACH ROW
BEGIN
    IF NEW.status = 'delivered' AND OLD.status != 'delivered' THEN
        UPDATE products p
        JOIN order_items oi ON oi.product_id = p.id
        SET p.total_sold = p.total_sold + oi.quantity
        WHERE oi.order_id = NEW.id;
    END IF;
END$$

DELIMITER ;

-- =============================================================
--  STORED PROCEDURE: đặt hàng (transaction 4 bước)
-- =============================================================

DELIMITER $$

CREATE PROCEDURE sp_place_order(
    IN  p_user_id       INT UNSIGNED,
    IN  p_ship_name     VARCHAR(100),
    IN  p_ship_phone    VARCHAR(20),
    IN  p_ship_address  TEXT,
    IN  p_ship_fee      DECIMAL(15,2),
    IN  p_payment       VARCHAR(20),
    IN  p_note          TEXT,
    OUT p_order_id      INT UNSIGNED,
    OUT p_error_msg     VARCHAR(200)
)
BEGIN
    DECLARE v_subtotal  DECIMAL(15,2) DEFAULT 0;
    DECLARE v_total     DECIMAL(15,2) DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_order_id  = 0;
        SET p_error_msg = 'Lỗi hệ thống khi đặt hàng';
    END;

    -- Bước 1: Kiểm tra giỏ hàng không rỗng
    IF (SELECT COUNT(*) FROM cart WHERE user_id = p_user_id) = 0 THEN
        SET p_order_id  = 0;
        SET p_error_msg = 'Giỏ hàng trống';
        LEAVE sp_place_order;
    END IF;

    START TRANSACTION;

    -- Bước 2: Kiểm tra tồn kho
    IF EXISTS (
        SELECT 1 FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = p_user_id
          AND p.stock < c.quantity
    ) THEN
        ROLLBACK;
        SET p_order_id  = 0;
        SET p_error_msg = 'Một số sản phẩm không đủ tồn kho';
        LEAVE sp_place_order;
    END IF;

    -- Bước 3: Tạo đơn hàng
    SELECT SUM(p.sale_price * c.quantity)
    INTO v_subtotal
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = p_user_id;

    SET v_total = v_subtotal + p_ship_fee;

    INSERT INTO orders (user_id, shipping_name, shipping_phone, shipping_address,
                        subtotal, shipping_fee, total_amount, payment_method, note)
    VALUES (p_user_id, p_ship_name, p_ship_phone, p_ship_address,
            v_subtotal, p_ship_fee, v_total, p_payment, p_note);

    SET p_order_id = LAST_INSERT_ID();

    -- Bước 4: Copy cart → order_items & trừ stock
    INSERT INTO order_items (order_id, product_id, product_name, product_sku,
                              product_image, unit_price, discount_percent, quantity, line_total)
    SELECT
        p_order_id,
        p.id,
        p.name,
        p.sku,
        (SELECT image_url FROM product_images
         WHERE product_id = p.id ORDER BY sort_order LIMIT 1),
        p.sale_price,
        p.discount_percent,
        c.quantity,
        p.sale_price * c.quantity
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = p_user_id;

    UPDATE products p
    JOIN cart c ON c.product_id = p.id
    SET p.stock = p.stock - c.quantity
    WHERE c.user_id = p_user_id;

    DELETE FROM cart WHERE user_id = p_user_id;

    COMMIT;
    SET p_error_msg = '';
END$$

DELIMITER ;

-- =============================================================
--  DỮ LIỆU MẪU (Seed Data)
-- =============================================================

-- Admin account (password: admin123 → bcrypt placeholder)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin TechShop', 'admin@techshop.vn', '$2y$10$placeholder_hash_admin', 'admin');

-- Danh mục
INSERT INTO categories (name, slug, sort_order) VALUES
('Chuột',           'chuot',          1),
('Bàn phím',        'ban-phim',       2),
('Tai nghe',        'tai-nghe',       3),
('Màn hình',        'man-hinh',       4),
('Webcam',          'webcam',         5),
('Loa',             'loa',            6),
('Phụ kiện khác',   'phu-kien-khac',  7);

-- Banner trang chủ
INSERT INTO banners (title, subtitle, image_url, link_url, sort_order) VALUES
('Siêu Sale Tháng 5',    'Giảm đến 40% toàn bộ phụ kiện',       'banners/banner1.jpg', '/products', 1),
('Bàn phím cơ hot nhất', 'Keychron K2 - Gõ mượt, giá tốt',     'banners/banner2.jpg', '/products/ban-phim', 2),
('Tai nghe gaming',       'Âm thanh vòm 7.1 - Trải nghiệm đỉnh', 'banners/banner3.jpg', '/products/tai-nghe', 3);

-- Sản phẩm mẫu
INSERT INTO products (category_id, name, slug, sku, price, discount_percent,
                      short_desc, is_hot, is_featured, is_new, stock) VALUES
(1, 'Chuột Logitech G502 X Plus',    'chuot-logitech-g502-x-plus',    'LG-G502XP',  2490000, 15,
 'Chuột gaming không dây cao cấp, sensor HERO 25K', 1, 1, 1, 50),
(2, 'Bàn phím Keychron K2 V2',       'ban-phim-keychron-k2-v2',       'KC-K2V2',    1990000, 10,
 'Bàn phím cơ compact 75%, kết nối Bluetooth & USB', 1, 1, 0, 30),
(3, 'Tai nghe Razer BlackShark V2',  'tai-nghe-razer-blackshark-v2',  'RZ-BSV2',    1750000, 20,
 'Tai nghe gaming 7.1, driver 50mm THX Spatial Audio', 0, 1, 1, 40),
(4, 'Màn hình LG UltraGear 27GP850', 'man-hinh-lg-ultragear-27gp850', 'LG-27GP850', 8990000, 5,
 '27 inch QHD 165Hz, 1ms GtG, IPS Nano', 1, 0, 1, 20),
(1, 'Chuột SteelSeries Rival 3',     'chuot-steelseries-rival-3',     'SS-RVL3',     490000, 0,
 'Chuột gaming phổ thông, 8500 CPI, nhẹ 77g', 0, 0, 0, 100);
