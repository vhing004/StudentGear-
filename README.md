# StudentGear - Website Bán Đồ Công Nghệ Giá Rẻ Cho Sinh Viên

## 📋 Giới thiệu

**StudentGear** là website thương mại điện tử chuyên bán các phụ kiện công nghệ giá rẻ, tập trung vào đối tượng sinh viên. Dự án được thực hiện theo quy trình phát triển phần mềm định hướng doanh nghiệp trong môn **Chuyên đề Định hướng Doanh nghiệp**.

Website cung cấp các sản phẩm như: tai nghe, ốp lưng, sạc dự phòng, chuột bàn phím, cáp sạc, hub USB... với mức giá phải chăng và giao diện thân thiện trên thiết bị di động.

## 🎯 Mục tiêu dự án

- Xây dựng một website bán hàng cơ bản hoàn chỉnh trong thời gian 6-8 tuần.
- Áp dụng quy trình phân tích - thiết kế - triển khai giống như doanh nghiệp thật.
- Tập trung vào trải nghiệm người dùng (UX) dành cho sinh viên.
- Có đầy đủ chức năng mua sắm và quản trị backend.

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

- **Sinh viên**: [Tên của bạn]
- **Lớp**: ...
- **Môn học**: Chuyên đề Định hướng Doanh nghiệp

## 📄 Tài liệu dự án

- Tài liệu Phân tích & Thiết kế Hệ thống
- Use Case Specification
- ER Diagram & Database Design
- Báo cáo cuối kỳ

---

**Made with ❤️ for students**
