-- Database: studentgear

-- Tạo database
CREATE DATABASE IF NOT EXISTS studentgear;
USE studentgear;

-- Bảng: users (Người dùng khách hàng)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  fullname VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  avatar VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_username (username)
);

-- Bảng: admin_users (Người dùng quản trị)
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  fullname VARCHAR(100),
  role ENUM('admin', 'moderator', 'staff') DEFAULT 'staff',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  INDEX idx_username (username)
);

-- Bảng: categories (Danh mục sản phẩm)
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  image VARCHAR(255),
  slug VARCHAR(100) UNIQUE,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (name)
);

-- Bảng: products (Sản phẩm)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description LONGTEXT,
  price DECIMAL(10, 2) NOT NULL,
  cost_price DECIMAL(10, 2),
  stock INT NOT NULL DEFAULT 0,
  category_id INT NOT NULL,
  image VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  is_featured TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  discount_percent DECIMAL(5, 2) DEFAULT 0,
  views INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  INDEX idx_category (category_id),
  INDEX idx_featured (is_featured),
  INDEX idx_new (is_new),
  FULLTEXT INDEX ft_name_desc (name, description)
);

-- Bảng: product_images (Hình ảnh sản phẩm)
CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_product (product_id)
);

-- Bảng: cart (Giỏ hàng)
CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_product (user_id, product_id),
  INDEX idx_user (user_id)
);

-- Bảng: orders (Đơn hàng)
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_code VARCHAR(50) UNIQUE NOT NULL,
  total_price DECIMAL(12, 2) NOT NULL,
  shipping_fee DECIMAL(10, 2) DEFAULT 0,
  discount_amount DECIMAL(10, 2) DEFAULT 0,
  shipping_address TEXT NOT NULL,
  shipping_phone VARCHAR(20) NOT NULL,
  shipping_name VARCHAR(100),
  status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
  payment_method ENUM('cod', 'bank_transfer', 'online') DEFAULT 'cod',
  payment_status ENUM('unpaid', 'paid', 'refund') DEFAULT 'unpaid',
  note TEXT,
  tracking_number VARCHAR(50),
  cancelled_reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  confirmed_at TIMESTAMP NULL,
  shipped_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_order_code (order_code),
  INDEX idx_status (status),
  INDEX idx_created (created_at)
);

-- Bảng: order_items (Chi tiết đơn hàng)
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  discount_percent DECIMAL(5, 2) DEFAULT 0,
  total_price DECIMAL(12, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX idx_order (order_id)
);

-- Bảng: order_status_history (Lịch sử trạng thái đơn hàng)
CREATE TABLE IF NOT EXISTS order_status_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  old_status VARCHAR(50),
  new_status VARCHAR(50) NOT NULL,
  note TEXT,
  changed_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  INDEX idx_order (order_id)
);

-- Bảng: reviews (Đánh giá sản phẩm - tùy chọn)
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT,
  is_verified_purchase TINYINT(1) DEFAULT 0,
  is_approved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_product (product_id),
  INDEX idx_user (user_id)
);

-- Bảng: banners (Banner khuyến mãi)
CREATE TABLE IF NOT EXISTS banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  image VARCHAR(255) NOT NULL,
  link VARCHAR(255),
  start_date DATE,
  end_date DATE,
  position INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active (is_active)
);

-- Bảng: statistics (Thống kê)
CREATE TABLE IF NOT EXISTS statistics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE,
  total_orders INT DEFAULT 0,
  total_revenue DECIMAL(12, 2) DEFAULT 0,
  total_customers INT DEFAULT 0,
  new_customers INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_date (date),
  INDEX idx_date (date)
);

-- Thêm indexes cho tối ưu hóa
CREATE INDEX idx_product_price ON products(price);
CREATE INDEX idx_product_discount ON products(discount_percent);
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_order_payment ON orders(payment_status);
