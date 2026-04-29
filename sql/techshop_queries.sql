-- ============================================================
--  TECHSHOP — TẬP HỢP CÂU TRUY VẤN SQL
--  Dùng với PHP (PDO / mysqli) + XAMPP MySQL
--  Dấu :param  → PDO named parameter
--  Dấu ?       → PDO positional / mysqli bind
-- ============================================================


-- ============================================================
-- [A] TRANG CHỦ & SẢN PHẨM
-- ============================================================

-- A1. Lấy TẤT CẢ sản phẩm đang bán (trang chủ, không lọc)
--     Kèm ảnh đại diện + tên danh mục
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    c.name       AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c        ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1
ORDER BY p.created_at DESC;


-- A2. Lọc sản phẩm THEO DANH MỤC
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    c.name       AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c        ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1
  AND p.category_id = :category_id          -- ← truyền id danh mục
ORDER BY p.created_at DESC;


-- A3. Tìm kiếm sản phẩm THEO TỪ KHÓA (UC3)
--     Dùng FULLTEXT (đã tạo index trong schema)
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    c.name       AS category_name,
    pi.image_path AS primary_image,
    MATCH(p.name, p.description) AGAINST (:keyword IN BOOLEAN MODE) AS relevance
FROM products p
JOIN categories c        ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1
  AND MATCH(p.name, p.description) AGAINST (:keyword IN BOOLEAN MODE)
ORDER BY relevance DESC;


-- A4. Lọc kết hợp: từ khóa + danh mục + khoảng giá (UC3)
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    c.name        AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c        ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1
  AND (:category_id  = 0   OR p.category_id = :category_id)   -- 0 = không lọc danh mục
  AND (:price_min    = 0   OR p.price >= :price_min)
  AND (:price_max    = 0   OR p.price <= :price_max)
  AND (:keyword      = ''  OR p.name LIKE CONCAT('%', :keyword, '%'))
ORDER BY p.created_at DESC;


-- A5. Chi tiết một sản phẩm (UC4)
SELECT
    p.id,
    p.name,
    p.description,
    p.price,
    p.stock_quantity,
    c.id   AS category_id,
    c.name AS category_name
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE p.id = :product_id
  AND p.is_active = 1;


-- A6. Tất cả ảnh của một sản phẩm (UC4 — slider ảnh)
SELECT image_path, is_primary, sort_order
FROM product_images
WHERE product_id = :product_id
ORDER BY is_primary DESC, sort_order ASC;


-- A7. Sản phẩm liên quan (cùng danh mục, trừ sản phẩm hiện tại — UC4)
SELECT
    p.id,
    p.name,
    p.price,
    pi.image_path AS primary_image
FROM products p
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.category_id = :category_id
  AND p.id         != :product_id
  AND p.is_active   = 1
ORDER BY RAND()
LIMIT 4;


-- A8. Danh sách tất cả danh mục (dùng cho menu / sidebar lọc)
SELECT id, name, description FROM categories ORDER BY name ASC;


-- ============================================================
-- [B] ĐĂNG KÝ & ĐĂNG NHẬP (UC1, UC2)
-- ============================================================

-- B1. Kiểm tra email / SĐT đã tồn tại chưa (UC2 validation)
SELECT id FROM users
WHERE email = :email OR phone = :phone
LIMIT 1;


-- B2. Tạo tài khoản mới (UC2)
INSERT INTO users (full_name, email, phone, password_hash, role, status)
VALUES (:full_name, :email, :phone, :password_hash, 'user', 'active');


-- B3. Lấy thông tin đăng nhập (UC1 — kiểm tra email + role + status)
SELECT id, full_name, email, password_hash, role, status
FROM users
WHERE email = :email
LIMIT 1;


-- ============================================================
-- [C] HỒ SƠ CÁ NHÂN (UC9, UC10)
-- ============================================================

-- C1. Xem thông tin hồ sơ hiện tại
SELECT id, full_name, email, phone, address, created_at
FROM users
WHERE id = :user_id;


-- C2. Cập nhật hồ sơ (UC9)
UPDATE users
SET full_name  = :full_name,
    phone      = :phone,
    address    = :address,
    updated_at = NOW()
WHERE id = :user_id;


-- C3. Đổi mật khẩu — lấy hash cũ để verify trước (UC10)
SELECT password_hash FROM users WHERE id = :user_id;

-- C4. Sau khi verify xong → cập nhật mật khẩu mới
UPDATE users
SET password_hash = :new_password_hash,
    updated_at    = NOW()
WHERE id = :user_id;


-- ============================================================
-- [D] GIỎ HÀNG (UC5)
-- ============================================================

-- D1. Xem giỏ hàng của user
SELECT
    c.id        AS cart_id,
    c.quantity,
    p.id        AS product_id,
    p.name      AS product_name,
    p.price,
    p.stock_quantity,
    (c.quantity * p.price) AS subtotal,
    pi.image_path AS primary_image
FROM cart c
JOIN products p ON p.id = c.product_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE c.user_id = :user_id
  AND p.is_active = 1
ORDER BY c.updated_at DESC;


-- D2. Thêm sản phẩm vào giỏ
--     Nếu đã có → tăng quantity; nếu chưa → thêm mới
INSERT INTO cart (user_id, product_id, quantity)
VALUES (:user_id, :product_id, :qty)
ON DUPLICATE KEY UPDATE
    quantity   = quantity + VALUES(quantity),
    updated_at = NOW();


-- D3. Cập nhật số lượng một item trong giỏ
UPDATE cart
SET quantity   = :quantity,
    updated_at = NOW()
WHERE id = :cart_id AND user_id = :user_id;


-- D4. Xóa một sản phẩm khỏi giỏ
DELETE FROM cart
WHERE id = :cart_id AND user_id = :user_id;


-- D5. Xóa toàn bộ giỏ hàng
DELETE FROM cart WHERE user_id = :user_id;


-- D6. Đếm số item trong giỏ (hiển thị badge trên header)
SELECT COALESCE(SUM(quantity), 0) AS total_items
FROM cart
WHERE user_id = :user_id;


-- ============================================================
-- [E] ĐẶT HÀNG (UC6)
-- ============================================================

-- E1. Lấy giỏ hàng để hiển thị trang xác nhận đặt hàng
--     (Giống D1 nhưng thêm kiểm tra tồn kho)
SELECT
    c.id        AS cart_id,
    c.quantity  AS qty_in_cart,
    p.id        AS product_id,
    p.name,
    p.price,
    p.stock_quantity,
    (c.quantity * p.price) AS subtotal,
    pi.image_path
FROM cart c
JOIN products p ON p.id = c.product_id AND p.is_active = 1
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE c.user_id = :user_id;


-- E2. Tạo đơn hàng mới (chạy trong TRANSACTION)
START TRANSACTION;

    -- Bước 1: Insert đơn hàng
    INSERT INTO orders
        (user_id, receiver_name, receiver_phone, receiver_address, total_amount, status)
    VALUES
        (:user_id, :receiver_name, :receiver_phone, :receiver_address, :total_amount, 'pending');

    -- Bước 2: Lấy order_id vừa tạo (trong PHP: $order_id = $pdo->lastInsertId())

    -- Bước 3: Insert từng sản phẩm vào order_items (lặp trong PHP)
    INSERT INTO order_items
        (order_id, product_id, product_name, product_price, quantity, subtotal)
    VALUES
        (:order_id, :product_id, :product_name, :product_price, :quantity, :subtotal);

    -- Bước 4: Trừ tồn kho (lặp trong PHP cho từng sản phẩm)
    UPDATE products
    SET stock_quantity = stock_quantity - :quantity
    WHERE id = :product_id AND stock_quantity >= :quantity;

    -- Bước 5: Xóa giỏ hàng sau khi đặt thành công
    DELETE FROM cart WHERE user_id = :user_id;

COMMIT;
-- Nếu có lỗi ở bất kỳ bước nào → ROLLBACK;


-- ============================================================
-- [F] LỊCH SỬ & CHI TIẾT ĐƠN HÀNG (UC7, UC8)
-- ============================================================

-- F1. Danh sách đơn hàng của user (UC7)
SELECT
    o.id            AS order_id,
    o.total_amount,
    o.status,
    o.created_at,
    COUNT(oi.id)    AS total_items
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.user_id = :user_id
GROUP BY o.id
ORDER BY o.created_at DESC;


-- F2. Lọc đơn hàng theo trạng thái (UC7 alternative flow)
SELECT
    o.id,
    o.total_amount,
    o.status,
    o.created_at,
    COUNT(oi.id) AS total_items
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.user_id = :user_id
  AND o.status  = :status          -- 'pending' | 'shipping' | 'completed' | 'cancelled'
GROUP BY o.id
ORDER BY o.created_at DESC;


-- F3. Chi tiết một đơn hàng — header (UC8)
SELECT
    o.id,
    o.receiver_name,
    o.receiver_phone,
    o.receiver_address,
    o.total_amount,
    o.status,
    o.cancel_reason,
    o.created_at
FROM orders o
WHERE o.id      = :order_id
  AND o.user_id = :user_id;       -- đảm bảo user chỉ xem đơn của chính mình


-- F4. Danh sách sản phẩm trong đơn hàng (UC8)
SELECT
    oi.product_id,
    oi.product_name,
    oi.product_price,
    oi.quantity,
    oi.subtotal,
    pi.image_path AS primary_image
FROM order_items oi
LEFT JOIN product_images pi
    ON pi.product_id = oi.product_id AND pi.is_primary = 1
WHERE oi.order_id = :order_id;


-- ============================================================
-- [G] ADMIN — QUẢN LÝ NGƯỜI DÙNG (UC11)
-- ============================================================

-- G1. Danh sách tất cả người dùng (chỉ role = 'user')
SELECT
    id,
    full_name,
    email,
    phone,
    address,
    status,
    created_at
FROM users
WHERE role = 'user'
ORDER BY created_at DESC;


-- G2. Tìm kiếm người dùng theo tên hoặc email
SELECT id, full_name, email, phone, status, created_at
FROM users
WHERE role  = 'user'
  AND (full_name LIKE CONCAT('%', :keyword, '%')
       OR email  LIKE CONCAT('%', :keyword, '%'))
ORDER BY created_at DESC;


-- G3. Lọc người dùng theo trạng thái
SELECT id, full_name, email, phone, status, created_at
FROM users
WHERE role   = 'user'
  AND status = :status               -- 'active' | 'blocked'
ORDER BY created_at DESC;


-- G4. Xem chi tiết một người dùng
SELECT id, full_name, email, phone, address, status, created_at
FROM users
WHERE id = :user_id AND role = 'user';


-- G5. Xem lịch sử đơn hàng của một người dùng (UC11)
SELECT
    o.id            AS order_id,
    o.total_amount,
    o.status,
    o.created_at,
    COUNT(oi.id)    AS total_items
FROM orders o
JOIN order_items oi ON oi.order_id = o.id
WHERE o.user_id = :user_id
GROUP BY o.id
ORDER BY o.created_at DESC;


-- G6. Khóa / mở khóa tài khoản người dùng (UC11)
UPDATE users
SET status     = :status,           -- 'active' | 'blocked'
    updated_at = NOW()
WHERE id = :user_id AND role = 'user';


-- ============================================================
-- [H] ADMIN — QUẢN LÝ DANH MỤC (UC12)
-- ============================================================

-- H1. Lấy danh sách danh mục kèm số lượng sản phẩm
SELECT
    c.id,
    c.name,
    c.description,
    c.created_at,
    COUNT(p.id) AS product_count
FROM categories c
LEFT JOIN products p
    ON p.category_id = c.id AND p.is_active = 1
GROUP BY c.id
ORDER BY c.name ASC;


-- H2. Thêm danh mục mới
INSERT INTO categories (name, description)
VALUES (:name, :description);


-- H3. Sửa danh mục
UPDATE categories
SET name        = :name,
    description = :description,
    updated_at  = NOW()
WHERE id = :category_id;


-- H4. Kiểm tra danh mục có sản phẩm không trước khi xóa
SELECT COUNT(*) AS product_count
FROM products
WHERE category_id = :category_id AND is_active = 1;

-- H5. Xóa danh mục (chỉ xóa khi product_count = 0)
DELETE FROM categories WHERE id = :category_id;


-- ============================================================
-- [I] ADMIN — QUẢN LÝ SẢN PHẨM (UC13)
-- ============================================================

-- I1. Danh sách tất cả sản phẩm (bảng admin)
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    p.is_active,
    p.created_at,
    c.name        AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
ORDER BY p.created_at DESC;


-- I2. Tìm kiếm sản phẩm (admin)
SELECT
    p.id, p.name, p.price, p.stock_quantity, p.is_active,
    c.name AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c ON c.id = p.category_id
LEFT JOIN product_images pi
    ON pi.product_id = p.id AND pi.is_primary = 1
WHERE (:category_id = 0 OR p.category_id = :category_id)
  AND (:keyword = '' OR p.name LIKE CONCAT('%', :keyword, '%'))
ORDER BY p.created_at DESC;


-- I3. Thêm sản phẩm mới
INSERT INTO products (category_id, name, description, price, stock_quantity, is_active)
VALUES (:category_id, :name, :description, :price, :stock_quantity, 1);
-- Sau đó lấy lastInsertId() để insert ảnh

-- I4. Thêm ảnh cho sản phẩm vừa tạo (lặp trong PHP)
INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
VALUES (:product_id, :image_path, :is_primary, :sort_order);


-- I5. Sửa thông tin sản phẩm
UPDATE products
SET category_id    = :category_id,
    name           = :name,
    description    = :description,
    price          = :price,
    stock_quantity = :stock_quantity,
    updated_at     = NOW()
WHERE id = :product_id;


-- I6. Cập nhật ảnh chính của sản phẩm
--     Bước 1: bỏ primary của tất cả ảnh cũ
UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id;
--     Bước 2: đặt primary cho ảnh mới
UPDATE product_images
SET is_primary = 1
WHERE id = :image_id AND product_id = :product_id;


-- I7. Xóa ảnh phụ của sản phẩm
DELETE FROM product_images
WHERE id = :image_id AND product_id = :product_id AND is_primary = 0;


-- I8. Ẩn sản phẩm (xóa mềm — is_active = 0)
UPDATE products
SET is_active  = 0,
    updated_at = NOW()
WHERE id = :product_id;


-- I9. Kiểm tra sản phẩm có tồn tại trong đơn hàng chưa (trước khi xóa cứng)
SELECT COUNT(*) AS order_count
FROM order_items
WHERE product_id = :product_id;

-- I10. Xóa cứng sản phẩm (chỉ dùng khi order_count = 0)
DELETE FROM products WHERE id = :product_id;


-- ============================================================
-- [J] ADMIN — QUẢN LÝ ĐƠN HÀNG (UC14)
-- ============================================================

-- J1. Danh sách tất cả đơn hàng
SELECT
    o.id            AS order_id,
    o.receiver_name,
    o.receiver_phone,
    o.total_amount,
    o.status,
    o.created_at,
    u.id            AS user_id,
    u.full_name     AS buyer_name,
    u.email         AS buyer_email,
    COUNT(oi.id)    AS total_items
FROM orders o
JOIN users u        ON u.id = o.user_id
JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id
ORDER BY o.created_at DESC;


-- J2. Lọc đơn hàng theo trạng thái
SELECT
    o.id, o.receiver_name, o.total_amount, o.status, o.created_at,
    u.full_name AS buyer_name,
    COUNT(oi.id) AS total_items
FROM orders o
JOIN users u        ON u.id = o.user_id
JOIN order_items oi ON oi.order_id = o.id
WHERE o.status = :status
GROUP BY o.id
ORDER BY o.created_at DESC;


-- J3. Tìm kiếm đơn hàng theo mã
SELECT
    o.id, o.receiver_name, o.receiver_phone, o.total_amount,
    o.status, o.created_at, u.full_name AS buyer_name
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE o.id = :order_id;


-- J4. Chi tiết đơn hàng — dùng cho trang admin
SELECT
    o.id, o.receiver_name, o.receiver_phone, o.receiver_address,
    o.total_amount, o.status, o.cancel_reason, o.created_at,
    u.id AS user_id, u.full_name AS buyer_name, u.email AS buyer_email
FROM orders o
JOIN users u ON u.id = o.user_id
WHERE o.id = :order_id;


-- J5. Xác nhận đơn hàng: pending → shipping
UPDATE orders
SET status     = 'shipping',
    updated_at = NOW()
WHERE id     = :order_id
  AND status = 'pending';          -- chỉ cho phép khi đang pending


-- J6. Hoàn thành đơn hàng: shipping → completed
UPDATE orders
SET status     = 'completed',
    updated_at = NOW()
WHERE id     = :order_id
  AND status = 'shipping';


-- J7. Hủy đơn hàng (chỉ pending mới được hủy)
UPDATE orders
SET status        = 'cancelled',
    cancel_reason = :cancel_reason,
    updated_at    = NOW()
WHERE id     = :order_id
  AND status = 'pending';

-- J8. Hoàn lại tồn kho khi hủy (lặp trong PHP cho từng order_item)
UPDATE products p
JOIN order_items oi ON oi.product_id = p.id
SET p.stock_quantity = p.stock_quantity + oi.quantity
WHERE oi.order_id = :order_id;


-- ============================================================
-- [K] THỐNG KÊ BÁO CÁO (UC15)
-- ============================================================

-- K1. Dashboard tổng quan (4 card số liệu)
SELECT
    (SELECT COUNT(*)          FROM users   WHERE role = 'user')                     AS total_users,
    (SELECT COUNT(*)          FROM orders  WHERE status = 'completed')              AS total_orders_done,
    (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed') AS total_revenue,
    (SELECT COALESCE(SUM(oi.quantity), 0)
     FROM order_items oi JOIN orders o ON o.id = oi.order_id
     WHERE o.status = 'completed')                                                  AS total_items_sold;


-- K2. Doanh thu theo NGÀY (trong 1 tháng, chọn tháng + năm)
SELECT
    DATE(o.created_at)        AS order_date,
    COUNT(o.id)               AS orders_count,
    SUM(o.total_amount)       AS revenue
FROM orders o
WHERE o.status       = 'completed'
  AND YEAR(o.created_at)  = :year
  AND MONTH(o.created_at) = :month
GROUP BY DATE(o.created_at)
ORDER BY order_date ASC;


-- K3. Doanh thu theo THÁNG (trong 1 năm)
SELECT
    MONTH(o.created_at)  AS month,
    COUNT(o.id)          AS orders_count,
    SUM(o.total_amount)  AS revenue
FROM orders o
WHERE o.status = 'completed'
  AND YEAR(o.created_at) = :year
GROUP BY MONTH(o.created_at)
ORDER BY month ASC;


-- K4. Doanh thu theo NĂM (tất cả các năm)
SELECT
    YEAR(o.created_at)  AS year,
    COUNT(o.id)         AS orders_count,
    SUM(o.total_amount) AS revenue
FROM orders o
WHERE o.status = 'completed'
GROUP BY YEAR(o.created_at)
ORDER BY year ASC;


-- K5. Top 5 sản phẩm bán chạy nhất
SELECT
    oi.product_name,
    oi.product_id,
    SUM(oi.quantity)    AS total_sold,
    SUM(oi.subtotal)    AS total_revenue,
    pi.image_path       AS primary_image
FROM order_items oi
JOIN orders o ON o.id = oi.order_id AND o.status = 'completed'
LEFT JOIN product_images pi
    ON pi.product_id = oi.product_id AND pi.is_primary = 1
GROUP BY oi.product_id, oi.product_name
ORDER BY total_sold DESC
LIMIT 5;


-- K6. Số đơn hàng theo từng trạng thái (biểu đồ tròn)
SELECT
    status,
    COUNT(*) AS count
FROM orders
GROUP BY status;


-- K7. Người dùng đăng ký mới theo tháng (trong năm hiện tại)
SELECT
    MONTH(created_at) AS month,
    COUNT(*)          AS new_users
FROM users
WHERE role = 'user'
  AND YEAR(created_at) = YEAR(CURDATE())
GROUP BY MONTH(created_at)
ORDER BY month ASC;


-- K8. Sản phẩm sắp hết hàng (tồn kho <= 10)
SELECT
    p.id,
    p.name,
    p.stock_quantity,
    c.name AS category_name
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE p.is_active = 1
  AND p.stock_quantity <= 10
ORDER BY p.stock_quantity ASC;







-- ============================================================
-- VIEW HỖ TRỢ TRUY VẤN PHỔ BIẾN
-- ============================================================

-- View: danh sách sản phẩm kèm ảnh chính và tên danh mục (dùng cho trang chủ / search)
CREATE OR REPLACE VIEW v_product_list AS
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_quantity,
    p.is_active,
    c.id   AS category_id,
    c.name AS category_name,
    pi.image_path AS primary_image
FROM products p
JOIN categories c ON c.id = p.category_id
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
WHERE p.is_active = 1;

-- View: tổng quan đơn hàng kèm tên người đặt (dùng cho admin quản lý đơn hàng)
CREATE OR REPLACE VIEW v_order_summary AS
SELECT
    o.id            AS order_id,
    o.user_id,
    u.full_name     AS buyer_name,
    u.email         AS buyer_email,
    o.receiver_name,
    o.receiver_phone,
    o.receiver_address,
    o.total_amount,
    o.status,
    o.cancel_reason,
    o.created_at
FROM orders o
JOIN users u ON u.id = o.user_id;

-- View: thống kê doanh thu theo ngày (chỉ tính đơn hoàn thành - UC15)
CREATE OR REPLACE VIEW v_revenue_by_day AS
SELECT
    DATE(created_at)          AS order_date,
    COUNT(*)                  AS total_orders,
    SUM(total_amount)         AS revenue,
    SUM((SELECT SUM(oi.quantity)
         FROM order_items oi WHERE oi.order_id = o.id)) AS items_sold
FROM orders o
WHERE status = 'completed'
GROUP BY DATE(created_at);

-- View: thống kê doanh thu theo tháng (UC15)
CREATE OR REPLACE VIEW v_revenue_by_month AS
SELECT
    YEAR(created_at)          AS yr,
    MONTH(created_at)         AS mo,
    COUNT(*)                  AS total_orders,
    SUM(total_amount)         AS revenue
FROM orders
WHERE status = 'completed'
GROUP BY YEAR(created_at), MONTH(created_at);
