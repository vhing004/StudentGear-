# 🛍️ StudentGear - Nền Tảng Thương Mại Điện Tử Phụ Kiện Công Nghệ Giá Rẻ

<div align="center">

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat&logo=bootstrap)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-Educational-green?style=flat)](#license)
[![Status](https://img.shields.io/badge/Status-Active%20Development-brightgreen?style=flat)](#)

**Một nền tảng thương mại điện tử hiện đại dành riêng cho sinh viên, cung cấp phụ kiện công nghệ chất lượng cao với giá cả phải chăng.**

[📋 Tính Năng](#-tính-năng-chính) • [🚀 Cài Đặt](#-hướng-dẫn-cài-đặt) • [📁 Cấu Trúc](#-cấu-trúc-dự-án) • [💻 Công Nghệ](#-công-nghệ-sử-dụng) • [📖 Tài Liệu](#-tài-liệu-api)

</div>

---

## 📋 Giới Thiệu

**StudentGear** là một nền tảng thương mại điện tử toàn diện được xây dựng đặc biệt cho sinh viên, với mục tiêu cung cấp các phụ kiện công nghệ chất lượng cao với giá cả phải chăng. Dự án được phát triển theo quy trình phân tích - thiết kế - triển khai chuyên nghiệp, áp dụng các best practices trong ngành công nghiệp phần mềm.

### 🎯 Tầm Nhìn & Sứ Mệnh

**Tầm nhìn:** Trở thành nền tảng mua sắm phụ kiện công nghệ hàng đầu cho sinh viên Việt Nam, nơi công nghệ không phải lúc nào cũng đắt tiền.

**Sứ mệnh:**

- Cung cấp sản phẩm công nghệ chất lượng cao với giá cạnh tranh
- Xây dựng trải nghiệm mua sắm thân thiện và dễ sử dụng
- Hỗ trợ tối đa cho khách hàng sinh viên
- Ứng dụng công nghệ hiện đại trong doanh nghiệp bán hàng

---

## ✨ Tính Năng Chính

### 👤 Tính Năng Người Dùng (User)

#### Tài Khoản & Xác Thực

- ✅ **Đăng ký tài khoản** - Form đăng ký với xác thực email
- ✅ **Đăng nhập an toàn** - Hỗ trợ phiên làm việc (Session)
- ✅ **Quên mật khẩu** - Khôi phục mật khẩu qua email
- ✅ **Cập nhật profil** - Chỉnh sửa thông tin cá nhân, avatar
- ✅ **Đổi mật khẩu** - Thay đổi mật khẩu với xác nhận cũ

#### Mua Sắm & Quản Lý Sản Phẩm

- ✅ **Duyệt danh sách sản phẩm** - Giao diện hiển thị sản phẩm responsiveness
- ✅ **Tìm kiếm nâng cao** - Tìm kiếm theo tên, keyword
- ✅ **Lọc sản phẩm** - Lọc theo danh mục, khoảng giá, đánh giá
- ✅ **Xem chi tiết sản phẩm** - Hiển thị thông tin, hình ảnh, giá, tồn kho
- ✅ **Đánh giá & bình luận** - Người dùng có thể đánh giá sản phẩm
- ✅ **Yêu thích** - Lưu sản phẩm yêu thích

#### Giỏ Hàng & Thanh Toán

- ✅ **Quản lý giỏ hàng** - Thêm/xóa/cập nhật số lượng sản phẩm
- ✅ **Tính giá tự động** - Tính tổng tiền, thuế (nếu có)
- ✅ **Đặt hàng (COD)** - Thanh toán khi nhận hàng
- ✅ **Lưu hóa đơn** - Lưu thông tin đơn hàng
- ⏳ **Thanh toán online** - Tích hợp VNPay, MoMo (sắp tới)

#### Lịch Sử & Theo Dõi

- ✅ **Xem lịch sử đơn hàng** - Danh sách tất cả đơn hàng đã mua
- ✅ **Chi tiết đơn hàng** - Xem thông tin chi tiết từng đơn
- ✅ **Theo dõi trạng thái** - Kiểm tra trạng thái (Chờ xác nhận, Đang giao, Đã giao, Hủy)
- ✅ **Tái đặt hàng** - Đặt lại những sản phẩm đã mua

### 🔐 Tính Năng Quản Trị (Admin)

#### Quản Lý Sản Phẩm

- ✅ **Quản lý danh mục** - Thêm/Sửa/Xóa danh mục sản phẩm
- ✅ **Quản lý sản phẩm** - Thêm/Sửa/Xóa sản phẩm chi tiết
- ✅ **Upload hình ảnh** - Tải lên nhiều hình ảnh sản phẩm
- ✅ **Quản lý kho** - Cập nhật tồn kho, SKU, mã barcode
- ✅ **Định giá sản phẩm** - Đặt giá gốc, giá bán, chiết khấu

#### Quản Lý Đơn Hàng

- ✅ **Xem tất cả đơn hàng** - Danh sách đơn với bộ lọc
- ✅ **Cập nhật trạng thái** - Thay đổi trạng thái đơn hàng
- ✅ **In hóa đơn** - Xuất PDF hóa đơn
- ✅ **Quản lý vận chuyển** - Theo dõi các hãng giao hàng
- ✅ **Xử lý hoàn/hủy** - Quản lý yêu cầu hoàn tiền

#### Quản Lý Người Dùng

- ✅ **Danh sách người dùng** - Xem tất cả tài khoản khách hàng
- ✅ **Khóa/Mở khóa tài khoản** - Quản lý quyền truy cập
- ✅ **Xem lịch sử mua hàng** - Kiểm tra hành vi mua sắm
- ✅ **Gửi thông báo** - Gửi tin nhắn tới khách hàng

#### Phân Tích & Báo Cáo

- ✅ **Dashboard** - Tổng quan doanh số, khách hàng, đơn hàng
- ✅ **Thống kê doanh số** - Báo cáo doanh thu theo ngày/tháng/năm
- ✅ **Sản phẩm bán chạy** - Danh sách sản phẩm top bán
- ✅ **Khách hàng VIP** - Thống kê khách hàng thân thiết
- ✅ **Báo cáo chi tiết** - Xuất báo cáo theo định dạng CSV/Excel

---

## 🛍️ Các Danh Mục Sản Phẩm

StudentGear cung cấp hơn **1000+ sản phẩm** bao gồm:

### 1. 🎧 **Tai Nghe & Loa**

- Tai nghe True Wireless Bluetooth
- Tai nghe over-ear chuyên nghiệp
- Loa Bluetooth di động
- Tai nghe gaming với mic
- **Khoảng giá:** 50,000đ - 1,500,000đ

### 2. 📱 **Ốp Lưng & Bảo Vệ**

- Ốp lưng chống sốc (iPhone, Samsung, Xiaomi)
- Bao da bảo vệ 360°
- Dán kính cường lực chống vỡ
- Miếng dán camera bảo vệ
- **Khoảng giá:** 25,000đ - 300,000đ

### 3. 🔋 **Sạc & Năng Lượng**

- Pin sạc dự phòng (10,000 - 50,000 mAh)
- Sạc nhanh 65W - 120W
- Cáp sạc Type-C, Lightning, Micro USB
- Sạc không dây QI
- **Khoảng giá:** 30,000đ - 800,000đ

### 4. 🖱️ **Chuột & Bàn Phím**

- Bàn phím cơ RGB chuyên gaming
- Chuột không dây chính xác cao
- Bàn phím mềm ergonomic
- Combo bàn phím + chuột giá tốt
- **Khoảng giá:** 80,000đ - 2,000,000đ

### 5. 🔌 **Cáp & Chuyển Đổi**

- Cáp HDMI, VGA, USB
- Đầu chuyển USB Hub 4-7 cổng
- Chuyển Type-C sang USB 3.0
- Bộ chuyển đổi đa năng
- **Khoảng giá:** 20,000đ - 500,000đ

### 6. 🎁 **Phụ Kiện Khác**

- Giá đỡ điện thoại & iPad
- Bàn di chuột (Mouse Pad) chuyên gaming
- Túi chống sốc cho laptop
- Gương selfie LED, đèn chiếu sáng
- **Khoảng giá:** 15,000đ - 400,000đ

---

## 💻 Công Nghệ Sử Dụng

### Backend

| Công Nghệ      | Phiên Bản | Mục Đích                                 |
| ---------------- | ----------- | ------------------------------------------- |
| **PHP**    | 8.0+        | Xử lý logic server-side                   |
| **MySQL**  | 8.0+        | Lưu trữ dữ liệu                         |
| **MySQLi** | Latest      | Kết nối database với prepared statements |

### Frontend

| Công Nghệ                    | Phiên Bản | Mục Đích               |
| ------------------------------ | ----------- | ------------------------- |
| **HTML5**                | Latest      | Cấu trúc trang web      |
| **SCSS/SASS**            | 1.77.0+     | Quản lý CSS nâng cao   |
| **JavaScript (Vanilla)** | ES6+        | Tương tác phía client |
| **Bootstrap**            | 5           | Framework UI responsive   |
| **Font Awesome**         | 6+          | Icon library              |

### Build Tools & Dev Tools

| Công Nghệ          | Mục Đích                   |
| -------------------- | ----------------------------- |
| **Vite**       | Module bundler, build tool    |
| **NPM**        | Package manager               |
| **XAMPP**      | Local development environment |
| **phpMyAdmin** | Quản lý database GUI        |

### Architecture & Pattern

- **MVC (Model-View-Controller)** - Kiến trúc ứng dụng
- **Session-based** - Quản lý phiên người dùng
- **RESTful API** - Xử lý request/response
- **Object-Oriented PHP** - Lập trình hướng đối tượng

---

## 📁 Cấu Trúc Dự Án

```
StudentGear/
│
├── 📂 admin/                      # Trang quản trị admin
│   ├── handlers/                  # Xử lý các hành động (CRUD)
│   │   ├── add_category.php
│   │   ├── edit_category.php
│   │   ├── delete_category.php
│   │   ├── add_product.php
│   │   ├── edit_product.php
│   │   ├── delete_product.php
│   │   ├── update_order_status.php
│   │   ├── toggle_user.php
│   │   └── ...
│   ├── includes/
│   │   └── sidebar.php            # Sidebar navigation
│   ├── pages/                     # Các trang admin
│   │   ├── dashboard.php          # Dashboard tổng quan
│   │   ├── products.php           # Quản lý sản phẩm
│   │   ├── categories.php         # Quản lý danh mục
│   │   ├── orders.php             # Quản lý đơn hàng
│   │   ├── users.php              # Quản lý người dùng
│   │   └── ...
│   └── index.php                  # Admin entry point
│
├── 📂 auth/                       # Xác thực người dùng
│   ├── login.php                  # Form đăng nhập
│   ├── reg.php                    # Form đăng ký
│   └── logout.php                 # Xử lý đăng xuất
│
├── 📂 config/                     # Cấu hình ứng dụng
│   └── db.php                     # Kết nối database
│
├── 📂 handler/                    # Xử lý các hành động chính
│   ├── add_to_cart.php            # Thêm vào giỏ hàng
│   ├── remove_from_cart.php       # Xóa khỏi giỏ hàng
│   ├── update_cart.php            # Cập nhật giỏ hàng
│   ├── process_order.php          # Xử lý đặt hàng
│   ├── buy_now.php                # Mua ngay
│   ├── update_profile.php         # Cập nhật profil
│   ├── update_order_action.php    # Cập nhật trạng thái đơn
│   └── reorder_process.php        # Đặt lại hàng
│
├── 📂 includes/                   # Các file include chung
│   ├── header.php                 # Header/Navigation
│   ├── footer.php                 # Footer
│   └── config.php                 # Database config (tham chiếu)
│
├── 📂 pages/                      # Các trang người dùng
│   ├── home.php                   # Trang chủ
│   ├── category.php               # Trang danh mục
│   ├── detail_product.php         # Chi tiết sản phẩm
│   ├── cart.php                   # Giỏ hàng
│   ├── checkout.php               # Trang thanh toán
│   ├── order_detail.php           # Chi tiết đơn hàng
│   ├── history_order.php          # Lịch sử đơn hàng
│   ├── profile.php                # Profil người dùng
│   └── ...
│
├── 📂 assets/                     # Tài nguyên tĩnh
│   ├── css/                       # Stylesheet
│   │   ├── main.css               # CSS chính
│   │   └── ...
│   ├── scss/                      # SCSS source
│   │   ├── main.scss
│   │   ├── pages/
│   │   ├── components/
│   │   └── ...
│   ├── js/                        # JavaScript files
│   │   ├── index.js               # JS chính
│   │   ├── cart.js                # Giỏ hàng logic
│   │   ├── profile.js             # Profil logic
│   │   ├── modal_admin.js         # Admin modals
│   │   ├── category_admin.js      # Category management
│   │   ├── product_admin.js       # Product management
│   │   ├── order_admin.js         # Order management
│   │   └── ...
│   └── images/                    # Hình ảnh tĩnh
│       ├── logos/
│       ├── icons/
│       ├── banners/
│       ├── avatars/               # Avatar người dùng
│       └── ...
│
├── 📂 uploads/                    # Upload files từ người dùng
│   └── products/                  # Hình ảnh sản phẩm
│
├── 📂 sql/                        # Database files
│   ├── studentgear.sql            # Schema chính
│   └── studentgear_csdl.sql       # Dữ liệu mẫu
│
├── index.php                      # Entry point chính
├── package.json                   # NPM dependencies
├── package-lock.json              # Lock file
├── .gitignore                     # Git ignore rules
└── README.md                      # Tài liệu này
```

---

## 🚀 Hướng Dẫn Cài Đặt

### 📋 Yêu Cầu Hệ Thống

- **PHP**: 8.0 hoặc cao hơn
- **MySQL**: 8.0 hoặc cao hơn
- **Server**: Apache/Nginx
- **Memory**: 512MB tối thiểu
- **Disk Space**: 500MB tối thiểu
- **Browser**: Chrome, Firefox, Safari, Edge (phiên bản mới)

### 💾 Bước 1: Chuẩn Bị Môi Trường

#### Windows (sử dụng XAMPP)

```bash
# Tải XAMPP từ https://www.apachefriends.org/
# Cài đặt vào C:\xampp (hoặc nơi khác tùy chọn)

# Khởi động XAMPP Control Panel
# Bật Apache và MySQL
```

#### macOS (sử dụng Homebrew)

```bash
brew install php mysql
brew services start mysql
```

#### Linux (Ubuntu/Debian)

```bash
sudo apt-get update
sudo apt-get install php php-mysql mysql-server apache2
sudo systemctl start apache2 mysql
```

### 📥 Bước 2: Clone & Thiết Lập Dự Án

```bash
# Clone repository
git clone https://github.com/huuvinh/studentgear.git
cd StudentGear

# Hoặc: Giải nén vào thư mục htdocs
# Ví dụ: C:\xampp\htdocs\studentgear

# Cài đặt dependencies
npm install

# Build CSS từ SCSS
npm run sass

# Hoặc chạy dev server
npm run dev
```

### 🗄️ Bước 3: Cấu Hình Database

#### 3a. Tạo Database

```sql
-- Mở phpMyAdmin: http://localhost/phpmyadmin/
-- Tạo database mới tên "studentgear"

CREATE DATABASE studentgear CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3b. Import Database

```bash
# Mở phpMyAdmin
# - Chọn database "studentgear"
# - Click "Import" tab
# - Chọn file: sql/studentgear.sql
# - Click "Go"

# Hoặc qua command line:
mysql -u root -p studentgear < sql/studentgear.sql
```

### ⚙️ Bước 4: Cấu Hình Ứng Dụng

**File:** `config/db.php`

```php
<?php
$localhost = 'localhost';
$username = 'root';              // MySQL username (mặc định: root)
$password = '';                  // MySQL password (mặc định: rỗng)
$dbname = 'studentgear';         // Database name

$conn = new mysqli($localhost, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

define('BASE_URL', 'http://localhost/studentgear/');
?>
```

> **Lưu ý:** Nếu dùng mật khẩu MySQL, thay `$password = '';` thành `$password = 'your_password';`

### 🌐 Bước 5: Khởi Chạy Ứng Dụng

```bash
# Truy cập application
# Trang chủ: http://localhost/studentgear/
# Admin: http://localhost/studentgear/admin/
```

---

## 🔑 Tài Khoản Mặc Định

### Tài Khoản Admin

```
Email: admin@studentgear.com
Mật khẩu: admin123
```

**Quyền hạn:**

- Quản lý sản phẩm & danh mục
- Quản lý đơn hàng
- Quản lý người dùng
- Xem thống kê & báo cáo

### Tài Khoản Khách Hàng Mẫu

```
Email: user@example.com
Mật khẩu: User@123
```

> **Lưu ý Bảo Mật:** Thay đổi mật khẩu mặc định ngay sau khi cài đặt trong môi trường production!

---

## 📊 Cấu Trúc Database

### Bảng Chính

| Bảng              | Mục Đích              | Số Bản Ghi |
| ------------------ | ------------------------ | ------------ |
| `users`          | Thông tin người dùng | ~100         |
| `admin_users`    | Tài khoản quản trị   | ~5           |
| `categories`     | Danh mục sản phẩm     | ~6           |
| `products`       | Sản phẩm               | ~1000+       |
| `product_images` | Hình ảnh sản phẩm    | ~3000+       |
| `cart`           | Giỏ hàng người dùng | Dynamic      |
| `orders`         | Đơn hàng              | ~500+        |
| `order_items`    | Chi tiết đơn hàng    | ~1500+       |
| `banners`        | Banner quảng cáo       | ~5           |

### Sơ Đồ Quan Hệ (ER Diagram)

```
users (1) ──→ (N) orders
users (1) ──→ (N) cart
categories (1) ──→ (N) products
products (1) ──→ (N) product_images
products (1) ──→ (N) cart
products (1) ──→ (N) order_items
orders (1) ──→ (N) order_items
admin_users (1) ──→ (N) order_status_history
```

---

## 🎨 Hướng Dẫn Sử Dụng

### Cho Khách Hàng

#### 1️⃣ Đăng Ký Tài Khoản

```
1. Truy cập trang chủ: http://localhost/studentgear/
2. Click "Đăng Ký" ở góc phải trên
3. Nhập email, mật khẩu, họ tên
4. Click "Đăng Ký"
5. Xác nhận email (nếu có)
```

#### 2️⃣ Mua Sắm

```
1. Duyệt danh mục hoặc tìm kiếm sản phẩm
2. Xem chi tiết sản phẩm (hình ảnh, giá, mô tả)
3. Click "Thêm vào giỏ" hoặc "Mua ngay"
4. Cập nhật số lượng nếu cần
```

#### 3️⃣ Thanh Toán

```
1. Truy cập Giỏ Hàng
2. Xem lại sản phẩm và số lượng
3. Click "Thanh Toán"
4. Nhập thông tin giao hàng
5. Chọn phương thức thanh toán (COD)
6. Xác nhận đơn hàng
```

#### 4️⃣ Theo Dõi Đơn Hàng

```
1. Click Tài Khoản → "Lịch Sử Đơn Hàng"
2. Xem danh sách đơn hàng
3. Click vào đơn để xem chi tiết
4. Kiểm tra trạng thái: Chờ xác nhận → Đã xác nhận → Đang giao → Đã giao
```

### Cho Quản Trị Viên

#### 1️⃣ Đăng Nhập Admin

```
1. Truy cập: http://localhost/studentgear/admin/
2. Nhập email: admin@studentgear.com
3. Nhập mật khẩu: admin123
4. Click "Đăng Nhập"
```

#### 2️⃣ Quản Lý Sản Phẩm

```
1. Sidebar → "Sản Phẩm"
2. Thêm sản phẩm mới:
   - Click "Thêm Sản Phẩm"
   - Nhập tên, giá, mô tả
   - Upload hình ảnh
   - Click "Lưu"
3. Chỉnh sửa sản phẩm:
   - Tìm sản phẩm trong danh sách
   - Click "Sửa" (biểu tượng bút chì)
   - Thay đổi thông tin
   - Click "Cập Nhật"
4. Xóa sản phẩm:
   - Click "Xóa" (biểu tượng thùng rác)
   - Xác nhận xóa
```

#### 3️⃣ Quản Lý Đơn Hàng

```
1. Sidebar → "Đơn Hàng"
2. Xem danh sách đơn hàng (lọc theo trạng thái)
3. Cập nhật trạng thái:
   - Click vào đơn hàng
   - Thay đổi trạng thái
   - Click "Cập Nhật"
4. Xem chi tiết đơn hàng (sản phẩm, giá, địa chỉ giao)
5. Liên hệ khách hàng nếu cần
```

#### 4️⃣ Xem Thống Kê

```
1. Sidebar → "Dashboard"
2. Xem các chỉ số chính:
   - Doanh số hôm nay
   - Số đơn hàng mới
   - Số khách hàng mới
   - Sản phẩm bán chạy
```

---

## 🔒 Bảo Mật

### Các Biện Pháp Bảo Mật Hiện Tại

✅ **Session-based Authentication**

- Sử dụng PHP Session để quản lý phiên người dùng
- Session timeout sau 30 phút không hoạt động

✅ **Input Validation**

- Kiểm tra dữ liệu đầu vào trước xử lý
- Sử dụng Regular Expression cho email, phone

✅ **MySQLi Prepared Statements**

- Sử dụng prepared statements để phòng chống SQL Injection
- Binding parameters thay vì concatenate

✅ **Password Hashing**

- Mật khẩu được hash bằng `password_hash()` (bcrypt)
- Xác thực mật khẩu qua `password_verify()`

✅ **CSRF Protection** (Partial)

- Tokens cho các form nhạy cảm

### Khuyến Nghị Bảo Mật Production

⚠️ **Cần Cải Thiện:**

1. Sử dụng HTTPS/SSL certificate
2. Implement CSRF tokens đầy đủ
3. Rate limiting cho login/API endpoints
4. Logging & Monitoring hành động admin
5. 2-Factor Authentication (2FA)
6. Role-Based Access Control (RBAC)
7. Input sanitization đầy đủ (htmlspecialchars, filter_var)
8. Regular security audits & updates

---

## 📖 Tài Liệu API

### 📌 Authentication Endpoints

#### POST /auth/login.php

Đăng nhập người dùng

**Request:**

```php
POST /auth/login.php
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=password123
```

**Response:**

```php
// Success (Redirect to home or store session)
// Error (Display error message)
```

#### POST /auth/reg.php

Đăng ký tài khoản mới

**Request:**

```php
POST /auth/reg.php
Content-Type: application/x-www-form-urlencoded

fullname=Nguyen Van A&email=user@example.com&password=password123&confirm_password=password123
```

#### GET /auth/logout.php

Đăng xuất

---

### 🛒 Cart Endpoints

#### POST /handler/add_to_cart.php

Thêm sản phẩm vào giỏ hàng

**Request:**

```php
POST /handler/add_to_cart.php
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

#### POST /handler/update_cart.php

Cập nhật số lượng sản phẩm trong giỏ

**Request:**

```php
POST /handler/update_cart.php

product_id=1&quantity=5
```

#### POST /handler/remove_from_cart.php

Xóa sản phẩm khỏi giỏ hàng

---

### 📦 Order Endpoints

#### POST /handler/process_order.php

Đặt hàng / Tạo đơn hàng

**Request:**

```php
POST /handler/process_order.php

recipient_name=Tran B&phone=0987654321&address=123 Nguyen Hue, TP HCM
```

#### GET /handler/update_order_action.php

Cập nhật trạng thái đơn hàng (Admin)

---

### 👤 Profile Endpoints

#### POST /handler/update_profile.php

Cập nhật thông tin profil

**Request:**

```php
POST /handler/update_profile.php

fullname=New Name&phone=0987654321&address=New Address
```

---

## 🧪 Testing & Troubleshooting

### Issues Thường Gặp

#### ❌ "Không thể kết nối tới database"

**Giải pháp:**

- Kiểm tra MySQL có đang chạy
- Xác minh thông tin kết nối trong `config/db.php`
- Kiểm tra user/password MySQL

```bash
# Test kết nối MySQL
mysql -u root -p

# Hoặc qua command line
mysql -u root --password="" -e "SELECT 1"
```

#### ❌ "Lỗi 404 - Trang không tìm thấy"

**Giải pháp:**

- Kiểm tra URL: `http://localhost/studentgear/`
- Đảm bảo thư mục được đặt trong `htdocs`
- Restart Apache

#### ❌ "Không upload được hình ảnh"

**Giải pháp:**

- Kiểm tra quyền thư mục `uploads/products/` (755 hoặc 777)
- Giới hạn dung lượng file trong `php.ini`
- Kiểm tra định dạng file (jpg, png, gif, webp)

```bash
# Linux/Mac: Cấp quyền
chmod 755 uploads/products/

# Hoặc
chmod 777 uploads/products/
```

#### ❌ "SCSS không tự động compile"

**Giải pháp:**

```bash
# Chạy Sass watcher
npm run sass

# Hoặc build manual
npm run build
```

---

## 🤝 Đóng Góp & Phát Triển

### Quy Trình Phát Triển

```
1. Fork dự án
2. Tạo branch tính năng: git checkout -b feature/amazing-feature
3. Commit changes: git commit -m 'Add amazing feature'
4. Push to branch: git push origin feature/amazing-feature
5. Open Pull Request
```

### Code Style Guidelines

**PHP:**

```php
// Sử dụng camelCase cho biến & hàm
$userName = "John";
function getUserData() { }

// Sử dụng PascalCase cho class
class UserManager { }

// Comment cho logic phức tạp
if ($user->isActive) {
    // Process active user
}
```

**JavaScript:**

```javascript
// Sử dụng const/let (không var)
const userName = "John";
let counter = 0;

// Arrow functions
const getData = async () => { }

// Template literals
const message = `Hello ${name}`;
```

**SCSS:**

```scss
// Nest selectors
.product {
  &-title { }
  &:hover { }
}

// Variables
$primary-color: #007bff;
$border-radius: 4px;
```

---

## 📝 Danh Sách Chức Năng

### ✅ Đã Hoàn Thành

- [X] Hệ thống xác thực (Đăng ký/Đăng nhập/Đăng xuất)
- [X] Trang chủ với banner quảng cáo
- [X] Danh sách & chi tiết sản phẩm
- [X] Tìm kiếm và lọc sản phẩm
- [X] Giỏ hàng (Thêm/Sửa/Xóa)
- [X] Thanh toán COD
- [X] Lịch sử đơn hàng
- [X] Chi tiết đơn hàng
- [X] Profil người dùng
- [X] Admin Dashboard
- [X] Quản lý sản phẩm (Admin)
- [X] Quản lý danh mục (Admin)
- [X] Quản lý đơn hàng (Admin)
- [X] Quản lý người dùng (Admin)
- [X] Upload hình ảnh sản phẩm
- [X] Responsive design (Mobile-first)
- [X] SCSS + Vite build pipeline

### ⏳ Planned Features (Tương Lai)

- [ ] Thanh toán online (VNPay, Momo)
- [ ] Hệ thống đánh giá & bình luận
- [ ] Wishlist / Sản phẩm yêu thích
- [ ] Email notifications
- [ ] Mã giảm giá & Voucher
- [ ] Tracking đơn hàng real-time
- [ ] Mobile app (React Native)
- [ ] AI recommendations
- [ ] Chat support
- [ ] Multi-language support

---

## 📄 Tài Liệu Dự Án

Các tài liệu kỹ thuật đi kèm:

- 📋 **Tài liệu Phân tích & Thiết kế Hệ thống** - Yêu cầu, use case, diagram
- 📊 **ER Diagram & Database Design** - Schema chi tiết
- 🎨 **UI/UX Mockup** - Wireframe & design prototype
- 📈 **Báo cáo Dự Án** - Tóm tắt thực hiện & kết quả
- 🧪 **Test Cases** - Kịch bản kiểm thử

---

## 👥 Tác Giả & Cộng Tác

### Tác Giả Chính

**Huu Vinh**

- 👨‍💻 Full-Stack Developer
- 📧 Email: nhv8386@gmail.com
- 🔗 GitHub: *[https://github.com/vhing004](https://github.com/vhing004)*

### Nhóm Thực Hiện

| Vai Trò                     | Người Thực Hiện | Trách Nhiệm                      |
| ---------------------------- | ------------------- | ---------------------------------- |
| **Project Manager**    | Huu Vinh            | Quản lý dự án, lập kế hoạch |
| **Backend Developer**  | Huu Vinh            | PHP, MySQL, API logic              |
| **Frontend Developer** | Huu Vinh            | HTML, CSS, JavaScript, UI/UX       |
| **UI/UX Designer**     | Huu Vinh            | Design, Mockup, Wireframe          |
| **QA/Testing**         | Huu Vinh            | Testing, Bug reporting             |

### Hỗ Trợ Từ

- 🏫 **Trường:** Đại học Mỏ Địa Chất
- 📚 **Môn học:** Chuyên đề Định hướng Doanh nghiệp
- 👨‍🏫 **Giảng viên hướng dẫn:** Ngô Ngọc Anh
- 💼 **Lớp:** Công nghệ phần mềm

---

## 📞 Liên Lạc & Hỗ Trợ

### 💬 Gửi Phản Hồi

Nếu bạn gặp vấn đề hoặc có đề xuất:

1. **GitHub Issues**: [Report bug / Feature request](https://github.com/huuvinh/studentgear/issues)
2. **Email**: [huvinh@student.edu.vn](mailto:huvinh@student.edu.vn)
3. **Discussion**: Tham gia [GitHub Discussions](https://github.com/huuvinh/studentgear/discussions)

### 🆘 Hỗ Trợ Kỹ Thuật

**FAQ & Troubleshooting**: Xem mục [Testing &amp; Troubleshooting](#-testing--troubleshooting) ở trên

**Common Issues**:

- Installation problems
- Database connection errors
- Image upload issues
- Performance optimization

---

## 📜 License

Dự án này được cấp phép dưới **Educational License**

```
Copyright © 2025-2026 Huu Vinh

Dự án được phát triển cho mục đích giáo dục trong khuôn khổ môn học.
Sử dụng, sửa đổi và phân phối chỉ dành cho mục đích học tập.

Prohibitions:
- Không sử dụng cho mục đích thương mại mà không được phép
- Không xóa hoặc chỉnh sửa thông tin bản quyền
- Không sử dụng tên dự án để quảng bá sản phẩm không được phép
```

---

## 🙏 Cảm Ơn & Lời Cảm Ơn

Cảm ơn những người đã hỗ trợ dự án này:

- 🎓 Đội ngũ giảng viên & mentor
- 👥 Cộng đồng developer
- 📚 Open source community
- 💙 Tất cả những người sử dụng & đóng góp ý kiến

---

## 🌟 Một Số Highlights

> **StudentGear** không chỉ là một dự án học tập, mà còn là một nền tảng thực tế demonstrating professional software development practices:

✨ **Full-Stack Development** - Từ Database design đến Frontend UI
🏗️ **Clean Architecture** - MVC pattern, modular code
🔒 **Security Focus** - Authentication, input validation, prepared statements
📱 **Responsive Design** - Mobile-first approach
⚡ **Performance Optimized** - Asset compilation, caching strategies
📊 **Scalable** - Database normalization, code organization

---

<div align="center">

### 🚀 Ready to Get Started?

[📥 Clone Repository](#-hướng-dẫn-cài-đặt) | [📖 Read Docs](#-tài-liệu-api) | [🤝 Contribute](#-đóng-góp--phát-triển)

---

**Made with ❤️ for students | Developed by [Huu Vinh](https://github.com/huuvinh)**

**Last Updated:** June 8, 2025 | **Version:** 1.0.0 | **Status:** Active Development 🚀

![GitHub stars](https://img.shields.io/github/stars/huuvinh/studentgear?style=social)
![GitHub followers](https://img.shields.io/github/followers/huuvinh?style=social)

</div>
