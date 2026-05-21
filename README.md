# StudentGear - Website Bán Đồ Công Nghệ Giá Rẻ Cho Sinh Viên

## 📋 Giới thiệu

**StudentGear** là website thương mại điện tử chuyên bán các phụ kiện công nghệ giá rẻ, tập trung vào đối tượng sinh viên. Dự án được thực hiện theo quy trình phát triển phần mềm định hướng doanh nghiệp trong môn **Chuyên đề Định hướng Doanh nghiệp**.

Với phương châm "Công nghệ không phải lúc nào cũng đắt", StudentGear mang đến các sản phẩm chất lượng với giá cả phải chăng, phù hợp với ngân sách hạn hẹp của sinh viên. Website cung cấp giao diện thân thiện, dễ sử dụng trên mọi thiết bị di động và máy tính.

## 🎯 Mục tiêu dự án

- Xây dựng một website bán hàng cơ bản hoàn chỉnh trong thời gian 6-8 tuần.
- Áp dụng quy trình phân tích - thiết kế - triển khai giống như doanh nghiệp thật.
- Tập trung vào trải nghiệm người dùng (UX) dành cho sinh viên với giao diện thân thiện, dễ sử dụng.
- Có đầy đủ chức năng mua sắm và quản trị backend cho nhân viên quản lý.
- Giảm thiểu chi phí cho sinh viên khi mua các phụ kiện công nghệ cần thiết.

## ✨ Tính năng chính

### Phía Người dùng (User)

- Đăng ký, Đăng nhập
- Xem sản phẩm, tìm kiếm, lọc theo danh mục và giá
- Xem chi tiết sản phẩm
- Quản lý giỏ hàng
- Đặt hàng (COD)
- Xem lịch sử đơn hàng và chi tiết đơn hàng
- Cập nhật thông tin cá nhân, đổi mật khẩu

### Phía Quản trị (Admin)

- Quản lý danh mục sản phẩm
- Quản lý sản phẩm (Thêm/Sửa/Xóa + upload nhiều ảnh)
- Quản lý đơn hàng (cập nhật trạng thái)
- Xem thống kê cơ bản

## 🛍 Danh sách sản phẩm chi tiết

StudentGear cung cấp các danh mục sản phẩm đa dạng:

### 1. **Tai nghe & Loa**

- Tai nghe có dây & không dây
- Tai nghe nhét (Earbuds)
- Loa Bluetooth di động
- Giá: từ 50,000đ - 1,500,000đ

### 2. **Ốp lưng & Bao da**

- Ốp lưng chống sốc cho iPhone, Samsung, Xiaomi
- Bao da bảo vệ
- Dán kính cường lực
- Giá: từ 25,000đ - 300,000đ

### 3. **Sạc & Pin dự phòng**

- Pin sạc dự phòng (Power Bank) 10,000 - 50,000 mAh
- Cáp sạc nhanh USB-C, Lightning, Micro USB
- Sạc nhanh 65W - 120W
- Cáp dài và bộ sạc du lịch
- Giá: từ 30,000đ - 800,000đ

### 4. **Chuột & Bàn phím**

- Chuột không dây với khoảng cách 10m
- Chuột gaming có dây độ chính xác cao
- Bàn phím cơ và phím mềm
- Combo chuột + bàn phím giá tốt
- Giá: từ 80,000đ - 2,000,000đ

### 5. **Cáp & Đầu chuyển đổi**

- Cáp HDMI, VGA cho máy chiếu
- Đầu chuyển USB Hub 4-7 cổng
- Bộ chuyển đổi Type-C sang USB 3.0
- Dây tín hiệu 3.5mm chất lượng cao
- Giá: từ 20,000đ - 500,000đ

### 6. **Phụ kiện khác**

- Giá đỡ điện thoại & iPad
- Bàn di chuột (Mouse pad)
- Túi đựng máy tính, tai nghe
- Gương selfie, đèn LED chiếu sáng
- Giá: từ 15,000đ - 400,000đ

## 🛠 Công nghệ sử dụng

- **Backend**: PHP 8 + MySQL
- **Frontend**: HTML5, SCSS, JavaScript
- **Database**: MySQL (XAMPP)
- **Công cụ khác**:
  - Bootstrap 5
  - Font Awesome
  - Vite + Sass (build CSS)

## 📁 Cấu trúc thư mục

StudentGear/
├── assets/ # CSS, JS, images
├── admin/ # Trang quản trị
├── includes/ # Header, Footer, Config, Functions
├── pages/ # Các trang người dùng
├── auth/ # Đăng nhập, đăng ký
├── uploads/products/ # Lưu ảnh sản phẩm
├── index.php
├── package.json
└── README.md

## 🚀 Hướng dẫn cài đặt và chạy

1. **Clone dự án** hoặc giải nén vào thư mục `htdocs` của XAMPP
2. Import file database:
   - Tên database: `StudentGear`
   - File: `StudentGear_db.sql`
3. Cấu hình kết nối database trong file `includes/config.php`
4. Khởi động **Apache** và **MySQL** trong XAMPP
5. Truy cập: `http://localhost/StudentGear`

**Tài khoản mặc định:**

- **Admin**: `admin@StudentGear.vn` / `Admin@123`
- **User**: `an.nguyen@student.edu.vn` / `User@123`

## 📋 Danh sách chức năng đã hoàn thành

- [x] Hệ thống đăng ký / đăng nhập
- [x] Trang chủ
- [x] Danh sách & chi tiết sản phẩm
- [x] Tìm kiếm và lọc sản phẩm
- [x] Giỏ hàng
- [x] Đặt hàng
- [x] Lịch sử đơn hàng
- [x] Trang quản trị (Admin)
- [ ] Thanh toán online (tương lai)

## 👥 Nhóm thực hiện

- **Sinh viên**: Huu Vinh
- **Lớp**: Lớp Thương Mại Điện Tử
- **Môn học**: Chuyên đề Định hướng Doanh nghiệp
- **Vai trò**: Phát triển Full-Stack (Backend PHP + Frontend HTML/CSS/JS)

## 📄 Tài liệu dự án

- Tài liệu Phân tích & Thiết kế Hệ thống
- Use Case Specification
- ER Diagram & Database Design
- Báo cáo cuối kỳ

## 💡 Tác giả & Liên lạc

**Dự án được phát triển bởi Huu Vinh**

StudentGear được xây dựng với mục đích giáo dục, nhằm áp dụng các kiến thức lập trình và phân tích hệ thống vào một dự án thực tế hoàn chỉnh. Dự án này thể hiện kỹ năng:

- Lập trình Backend với PHP 8 & MySQL
- Thiết kế Frontend với HTML5, SCSS, JavaScript
- Quản lý cơ sở dữ liệu
- Xây dựng giao diện người dùng thân thiện
- Áp dụng quy trình phát triển phần mềm chuyên nghiệp

---

**Made with ❤️ for students | Developed by Huu Vinh**
