
-- ===================================
-- Dữ liệu mẫu
-- ===================================

-- Thêm admin user
INSERT INTO admin_users (username, password, email, fullname, role) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'admin@studentgear.com', 'Admin User', 'admin');

-- Thêm danh mục
INSERT INTO categories (name, description, slug) VALUES
('Laptop', 'Máy tính xách tay, laptop chính hãng', 'laptop'),
('Điện thoại', 'Điện thoại di động, smartphone', 'dien-thoai'),
('Tai nghe', 'Tai nghe, headphones, earbuds', 'tai-nghe'),
('Chuột và Bàn phím', 'Chuột máy tính, bàn phím cơ', 'chuot-ban-phim'),
('Phụ kiện', 'Sạc, dây cáp, ốp lưng', 'phu-kien'),
('Màn hình', 'Màn hình máy tính, monitor', 'man-hinh'),
('Bàn làm việc', 'Bàn gaming, bàn làm việc', 'ban-lam-viec'),
('Đèn LED', 'Đèn bàn, đèn thông minh', 'den-led');

-- Thêm sản phẩm mẫu
INSERT INTO products (name, description, price, cost_price, stock, category_id, is_featured, is_new, discount_percent) VALUES
('Laptop Dell XPS 13', 'Laptop Dell XPS 13 inch FHD, Intel Core i5, 8GB RAM, 512GB SSD', 25999000, 20000000, 15, 1, 1, 1, 10),
('MacBook Air M1', 'Laptop Apple MacBook Air M1, 8GB RAM, 256GB SSD', 29990000, 25000000, 8, 1, 1, 0, 5),
('iPhone 14 Pro', 'iPhone 14 Pro 128GB, màn hình AMOLED, camera 48MP', 29990000, 24000000, 20, 2, 1, 1, 0),
('Samsung Galaxy S23', 'Samsung Galaxy S23 Ultra, Snapdragon 8 Gen 2, 256GB', 23990000, 18000000, 25, 2, 0, 1, 15),
('Sony WH-1000XM5', 'Tai nghe Sony WH-1000XM5 ANC, Bluetooth 5.3', 8990000, 7000000, 30, 3, 1, 0, 0),
('Logitech MX Master 3', 'Chuột Logitech MX Master 3, Bluetooth, USB-C', 2190000, 1500000, 50, 4, 0, 0, 10),
('Razer DeathAdder V2', 'Chuột gaming Razer DeathAdder V2, 20000 DPI', 1490000, 1000000, 40, 4, 0, 0, 0),
('LG 27UP550', 'Màn hình LG 27 inch 4K, 60Hz, IPS Panel', 8990000, 6500000, 12, 6, 0, 0, 20),
('Logitech K840', 'Bàn phím cơ Logitech K840, RGB LED', 2990000, 2000000, 25, 4, 0, 0, 5);

-- Thêm người dùng mẫu
INSERT INTO users (username, email, password, fullname, phone, address) VALUES
('nguyenvana', 'nguyenvana@email.com', '$2y$10$YourHashedPasswordHere', 'Nguyễn Văn A', '0912345678', 'Hà Nội'),
('tranvantb', 'tranvantb@email.com', '$2y$10$YourHashedPasswordHere', 'Trần Văn B', '0987654321', 'Thành phố Hồ Chí Minh'),
('phamvanc', 'phamvanc@email.com', '$2y$10$YourHashedPasswordHere', 'Phạm Văn C', '0909090909', 'Đà Nẵng');

-- Thêm order mẫu
INSERT INTO orders (user_id, order_code, total_price, shipping_fee, shipping_address, shipping_phone, shipping_name, status) VALUES
(1, 'ORD001', 30000000, 30000, '123 Đường Láng, Hà Nội', '0912345678', 'Nguyễn Văn A', 'confirmed'),
(2, 'ORD002', 15000000, 30000, '456 Nguyễn Huệ, TP.HCM', '0987654321', 'Trần Văn B', 'shipping'),
(3, 'ORD003', 8990000, 20000, '789 Hàng Dương, Đà Nẵng', '0909090909', 'Phạm Văn C', 'pending');

-- Thêm order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total_price) VALUES
(1, 1, 'Laptop Dell XPS 13', 1, 25999000, 25999000),
(1, 4, 'Tai nghe Sony WH-1000XM5', 1, 8990000, 8990000),
(2, 3, 'iPhone 14 Pro', 1, 29990000, 29990000),
(3, 5, 'Sony WH-1000XM5', 1, 8990000, 8990000);

-- Thêm banner mẫu
INSERT INTO banners (title, image, link, start_date, end_date, position, is_active) VALUES
('Flash Sale 50%', '/assets/images/banner/flash-sale.png', '/products?discount=50', '2024-01-01', '2024-01-31', 1, 1),
('Công Nghệ Mới 2024', '/assets/images/banner/tech-2024.png', '/products?category=new', '2024-01-01', '2024-12-31', 2, 1),
('Khuyến Mãi Mùa Xuân', '/assets/images/banner/spring.png', '/products', '2024-02-01', '2024-03-31', 3, 1);