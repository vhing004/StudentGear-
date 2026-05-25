-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 25, 2026 at 07:01 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studentgear`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','moderator','staff') COLLATE utf8mb4_general_ci DEFAULT 'staff',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `fullname`, `role`, `is_active`, `created_at`, `last_login`) VALUES
(2, 'admin', 'admin123', 'admin@studentgear.com', 'Quản trị viên', 'admin', 1, '2026-05-04 06:13:23', '2026-05-08 05:55:25');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `position` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `image`, `link`, `start_date`, `end_date`, `position`, `is_active`, `created_at`) VALUES
(1, 'Siêu phẩm giá tai nghe Iphone', 'https://phukienngonbore.com/wp-content/uploads/2021/12/banner-1.png', '/products?discount=50', '2024-01-01', '2024-01-31', 2, 1, '2026-04-17 08:54:59'),
(2, 'Tai nghe Airpod ', 'https://phukienngonbore.com/wp-content/uploads/2021/12/banner-2.png', '/products?category=new', '2024-01-01', '2024-12-31', 3, 1, '2026-04-17 08:54:59'),
(3, 'Sạc nhanh Iphone', 'https://phukienngonbore.com/wp-content/uploads/2021/12/banner-3.png', '/products', '2024-02-01', '2024-03-31', 4, 1, '2026-04-17 08:54:59'),
(4, 'Hàng chất - Giá rẻ - Bảo hành nhanh', 'https://phukienngonbore.com/wp-content/uploads/2021/03/phu-kien-dien-thoai-gia-re.png', '/products?discount=50', '2024-01-01', '2024-01-31', 5, 1, '2026-04-17 08:54:59'),
(5, 'Đổi mới trong 3 tháng', 'https://phukienngonbore.com/wp-content/uploads/2022/08/doi-moi-1536x575.png', '/products?new3month', '2024-01-01', '2024-01-31', 1, 1, '2026-04-17 08:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Laptop', 'Máy tính xách tay, laptop chính hãng', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_789.png', 'laptop', 1, '2026-04-17 08:54:59', '2026-05-07 09:59:45'),
(2, 'Điện thoại và Củ Sạc', 'Điện thoại di động, smartphone', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/s/a/samsung-galaxy-s26-ultra-1.jpg', 'dien-thoai', 1, '2026-04-17 08:54:59', '2026-05-07 09:06:52'),
(3, 'Tai nghe', 'Tai nghe, headphones, earbuds', 'https://phukienngonbore.com/wp-content/uploads/2021/12/airpod3-ho-van-1562m-300x300.png', 'tai-nghe', 1, '2026-04-17 08:54:59', '2026-05-07 09:07:55'),
(4, 'Chuột và Bàn phím', 'Chuột máy tính, bàn phím cơ', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/a/gaming_8_-_2025-05-30t092833.530.png', 'chuot-ban-phim', 1, '2026-04-17 08:54:59', '2026-05-07 09:08:29'),
(5, 'Phụ kiện', 'Sạc, dây cáp, ốp lưng', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/2/c270-hd-webcam-refresh.png', 'phu-kien', 1, '2026-04-17 08:54:59', '2026-05-07 09:09:15'),
(6, 'Màn hình', 'Màn hình máy tính, monitor', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_635_30.png', 'man-hinh', 1, '2026-04-17 08:54:59', '2026-05-07 09:09:44'),
(7, 'Ghế làm việc', 'Bàn gaming, bàn làm việc', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/h/ghe-cong-thai-hoc-hyperwork-airy-1.png', 'ban-lam-viec', 1, '2026-04-17 08:54:59', '2026-05-07 09:10:35'),
(8, 'Đèn LED', 'Đèn bàn, đèn thông minh', 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-led-de-ban-taotronics-tt-dl01-tt-dl02-1_3.jpg', 'den-led', 1, '2026-04-17 08:54:59', '2026-05-15 05:56:42'),
(9, 'PC Gaming ver 2', 'PC gaming tất cả các thể loại.', '../../assets/images/categories/1779085043_DEALHUNTER365.jpg', 'pc-gaming-ver-2', 0, '2026-05-16 08:13:00', '2026-05-21 09:45:21'),
(10, 'Chair', 'Ghế ngồi cao cấp ', '../../assets/images/categories/1779085020_avatar.jpg', 'chair', 0, '2026-05-17 04:08:45', '2026-05-18 06:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `shipping_address` text COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','confirmed','shipping','delivered','cancelled','returned') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `payment_method` enum('cod','bank_transfer','online') COLLATE utf8mb4_general_ci DEFAULT 'cod',
  `payment_status` enum('unpaid','paid','refund') COLLATE utf8mb4_general_ci DEFAULT 'unpaid',
  `note` text COLLATE utf8mb4_general_ci,
  `cancel_return_reason` text COLLATE utf8mb4_general_ci,
  `tracking_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cancelled_reason` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_price`, `shipping_fee`, `discount_amount`, `shipping_address`, `shipping_phone`, `shipping_name`, `status`, `payment_method`, `payment_status`, `note`, `cancel_return_reason`, `tracking_number`, `cancelled_reason`, `created_at`, `updated_at`, `confirmed_at`, `shipped_at`, `delivered_at`) VALUES
(13, 7, 'ORD-69FAD9D1F2C20', 29048120.00, 30000.00, 0.00, 'Quất Lâm, Giao Thủy, Nam Định', '0377844665', 'Test Case', 'shipping', 'cod', 'paid', 'Ship nhanh, cẩn thận ', NULL, NULL, NULL, '2026-05-06 06:04:01', '2026-05-21 08:37:43', NULL, NULL, '2026-05-14 08:41:59'),
(14, 7, 'ORD-69FAD9FAC922E', 525000.00, 30000.00, 0.00, 'Quất Lâm, Giao Thủy, Nam Định', '0377844665', 'Test Case', 'shipping', 'cod', 'paid', '', NULL, NULL, NULL, '2026-05-06 06:04:42', '2026-05-21 08:37:38', NULL, NULL, '2026-05-21 08:12:01'),
(15, 7, 'ORD-69FAF6994D9BD', 9617500.00, 30000.00, 0.00, 'Quất Lâm, Giao Thủy, Nam Định', '0377844665', 'Test Case', 'pending', 'bank_transfer', 'unpaid', '', NULL, NULL, NULL, '2026-05-06 08:06:49', '2026-05-06 08:06:49', NULL, NULL, NULL),
(16, 7, 'ORD-69FB4CB074161', 13368600.00, 30000.00, 0.00, 'Quất Lâm, Giao Thủy, Nam Định', '0377844665', 'Test Case', 'cancelled', 'cod', 'unpaid', '', NULL, NULL, NULL, '2026-05-06 14:14:08', '2026-05-14 09:14:59', NULL, NULL, NULL),
(17, 7, 'ORD-69FB4E07AB614', 180000.00, 30000.00, 0.00, 'Quất Lâm, Giao Thủy, Nam Định', '0377844665', 'Test Case', 'shipping', 'cod', 'unpaid', '', NULL, NULL, NULL, '2026-05-06 14:19:51', '2026-05-21 09:40:10', NULL, NULL, NULL),
(18, 8, 'ORD-6A05856432B6F', 705000.00, 30000.00, 0.00, 'Định Cương QL17, Gia Bình, Bắc Ninh', '0979071420', 'Nguyễn Hữu Vinh', 'returned', 'cod', 'paid', 'Ship cẩn thận, tốt anh bo', NULL, NULL, 'qqqqqqq', '2026-05-14 08:18:44', '2026-05-22 09:34:22', NULL, NULL, '2026-05-21 08:38:15'),
(19, 8, 'ORD-6A05858F87C61', 78835000.00, 30000.00, 0.00, 'Định Cương QL17, Gia Bình, Bắc Ninh', '0979071420', 'Nguyễn Hữu Vinh', 'shipping', 'cod', 'unpaid', 'ok', NULL, NULL, NULL, '2026-05-14 08:19:27', '2026-05-14 09:30:02', '2026-05-14 08:20:15', NULL, NULL),
(20, 8, 'ORD-6A06B46C24848', 46670000.00, 30000.00, 0.00, 'Định Cương QL17, Gia Bình, Bắc Ninh', '0979071420', 'Nguyễn Hữu Vinh', 'pending', 'cod', 'unpaid', 'ship safe ', NULL, NULL, NULL, '2026-05-15 05:51:40', '2026-05-15 05:51:40', NULL, NULL, NULL),
(21, 8, 'ORD-6A0ABB4988FC9', 1290000.00, 30000.00, 0.00, 'Định Cương QL17, Gia Bình, Bắc Ninh', '0979071420', 'Nguyễn Hữu Vinh', 'delivered', 'cod', 'paid', 'Ngoan anh bo ', NULL, NULL, NULL, '2026-05-18 07:10:01', '2026-05-23 14:14:51', NULL, NULL, '2026-05-23 14:14:51'),
(22, 9, 'ORD-6A0EB9953C4A1', 792500.00, 30000.00, 0.00, 'Trực Ninh, Nam Định', '0977893466', 'Vũ Minh Hào', 'delivered', 'cod', 'paid', 'Ship nhanh đi, đang thất tình ', NULL, NULL, NULL, '2026-05-21 07:51:49', '2026-05-21 08:37:59', NULL, NULL, '2026-05-21 08:37:59'),
(23, 9, 'ORD-6A0EB9B520233', 296000.00, 30000.00, 0.00, 'Trực Ninh, Nam Định', '0977893466', 'Vũ Minh Hào', 'pending', 'cod', 'unpaid', 'nhanh anh ơi ', NULL, NULL, NULL, '2026-05-21 07:52:21', '2026-05-21 07:52:21', NULL, NULL, NULL),
(24, 10, 'ORD-6A0EBA6CB5F46', 10275000.00, 30000.00, 0.00, 'Lê Văn Hiến, Cổ Nhuế 2, Bắc Từ Liêm', '0988872621', 'Ngô Hoàng Long', 'delivered', 'cod', 'paid', 'ship ship gấp ', NULL, NULL, NULL, '2026-05-21 07:55:24', '2026-05-21 08:37:50', NULL, NULL, '2026-05-21 08:37:50'),
(25, 10, 'ORD-6A0EBA877110B', 780000.00, 30000.00, 0.00, 'Lê Văn Hiến, Cổ Nhuế 2, Bắc Từ Liêm', '0988872621', 'Ngô Hoàng Long', 'shipping', 'cod', 'unpaid', 'come on', NULL, NULL, NULL, '2026-05-21 07:55:51', '2026-05-21 07:56:56', NULL, NULL, NULL),
(26, 8, 'ORD-6A10143060744', 525000.00, 30000.00, 0.00, 'Định Cương QL17, Gia Bình, Bắc Ninh', '0979071420', 'Nguyễn Hữu Vinh', 'cancelled', 'cod', 'unpaid', 'Thằng shipper mày làm gì mẹ tao ', NULL, NULL, 'qqqqqqq', '2026-05-22 08:30:40', '2026-05-22 09:33:23', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT '0.00',
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `discount_percent`, `total_price`, `created_at`) VALUES
(25, 13, 14, 'Laptop HP Omnibook 5 AI 16-AF1048TU BZ7Q9PA', 2, 306560.00, 0.00, 613120.00, '2026-05-06 06:04:01'),
(26, 13, 20, 'iPhone 15 Pro Max', 1, 28405000.00, 0.00, 28405000.00, '2026-05-06 06:04:01'),
(27, 14, 13, 'Sạc Dự Phòng MagSafe 10000mAh', 1, 495000.00, 0.00, 495000.00, '2026-05-06 06:04:42'),
(28, 15, 19, 'Lenovo Ideapad 3', 1, 9350000.00, 0.00, 9350000.00, '2026-05-06 08:06:49'),
(29, 15, 114, 'Đèn LED Thông Minh Philips Hue Go', 1, 237500.00, 0.00, 237500.00, '2026-05-06 08:06:49'),
(30, 16, 10, 'Macbook NEO 13 inch A18 PRO', 2, 6669300.00, 0.00, 13338600.00, '2026-05-06 14:14:08'),
(31, 17, 116, 'Dây Đèn LED RGB 5050 (5m)', 1, 150000.00, 0.00, 150000.00, '2026-05-06 14:19:51'),
(32, 18, 50, 'Đèn LED Treo Màn Hình Baseus', 2, 337500.00, 0.00, 675000.00, '2026-05-14 08:18:44'),
(33, 19, 15, 'Macbook Air M2 2023', 2, 24210000.00, 0.00, 48420000.00, '2026-05-14 08:19:27'),
(34, 19, 20, 'iPhone 15 Pro Max', 1, 28405000.00, 0.00, 28405000.00, '2026-05-14 08:19:27'),
(35, 19, 30, 'Bàn phím cơ AKKO 3068', 1, 1125000.00, 0.00, 1125000.00, '2026-05-14 08:19:27'),
(36, 19, 106, 'Bàn Làm Việc Đứng Flexispot ET223 - E7', 1, 855000.00, 0.00, 855000.00, '2026-05-14 08:19:27'),
(37, 20, 21, 'Samsung Galaxy S24 Ultra', 2, 23320000.00, 0.00, 46640000.00, '2026-05-15 05:51:40'),
(38, 21, 31, 'Chuột Logitech G502 Hero', 1, 760000.00, 0.00, 760000.00, '2026-05-18 07:10:01'),
(39, 21, 62, 'Giá đỡ Laptop N3 Aluminum', 2, 250000.00, 0.00, 500000.00, '2026-05-18 07:10:01'),
(40, 22, 28, 'Airpods 2 Hổ Vằn', 2, 212500.00, 0.00, 425000.00, '2026-05-21 07:51:49'),
(41, 22, 50, 'Đèn LED Treo Màn Hình Baseus', 1, 337500.00, 0.00, 337500.00, '2026-05-21 07:51:49'),
(42, 23, 33, 'Chuột không dây Logitech M331', 1, 266000.00, 0.00, 266000.00, '2026-05-21 07:52:21'),
(43, 24, 5, 'Sony WH-1000XM5', 1, 8990000.00, 0.00, 8990000.00, '2026-05-21 07:55:24'),
(44, 24, 13, 'Sạc Dự Phòng MagSafe 10000mAh', 1, 495000.00, 0.00, 495000.00, '2026-05-21 07:55:24'),
(45, 24, 31, 'Chuột Logitech G502 Hero', 1, 760000.00, 0.00, 760000.00, '2026-05-21 07:55:24'),
(46, 25, 62, 'Giá đỡ Laptop N3 Aluminum', 3, 250000.00, 0.00, 750000.00, '2026-05-21 07:55:51'),
(47, 26, 13, 'Sạc Dự Phòng MagSafe 10000mAh', 1, 495000.00, 0.00, 495000.00, '2026-05-22 08:30:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `note` text COLLATE utf8mb4_general_ci,
  `changed_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status`, `new_status`, `note`, `changed_by`, `created_at`) VALUES
(5, 13, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-06 06:04:01'),
(6, 14, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-06 06:04:42'),
(7, 15, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-06 08:06:49'),
(8, 16, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-06 14:14:08'),
(9, 17, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-06 14:19:51'),
(10, 18, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-14 08:18:44'),
(11, 19, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-14 08:19:27'),
(12, 13, 'delivered', 'delivered', '', 2, '2026-05-14 08:41:59'),
(13, 14, 'pending', 'shipping', '', 2, '2026-05-14 08:42:41'),
(14, 16, 'pending', 'cancelled', 'bạn đã yêu cầu hủy ', 2, '2026-05-14 09:14:59'),
(15, 19, 'confirmed', 'shipping', 'Đơn hàng sẽ đến tay bạn', 2, '2026-05-14 09:30:02'),
(16, 20, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-15 05:51:40'),
(17, 21, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-18 07:10:01'),
(18, 21, 'pending', 'confirmed', '', 2, '2026-05-18 07:11:51'),
(19, 21, 'confirmed', 'shipping', 'Đơn hàng của bạn đang được vận chuyển', 2, '2026-05-18 07:13:19'),
(20, 22, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-21 07:51:49'),
(21, 23, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-21 07:52:21'),
(22, 24, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-21 07:55:24'),
(23, 25, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-21 07:55:51'),
(24, 25, 'pending', 'shipping', 'Đơn hàng của bạn đang được giao.', 2, '2026-05-21 07:56:56'),
(25, 22, 'pending', 'shipping', 'Đơn hàng của bạn đang được giao.', 2, '2026-05-21 07:57:39'),
(26, 14, 'shipping', 'delivered', '', 2, '2026-05-21 08:12:01'),
(27, 14, 'delivered', 'shipping', '', 2, '2026-05-21 08:37:38'),
(28, 13, 'delivered', 'shipping', '', 2, '2026-05-21 08:37:43'),
(29, 24, 'pending', 'delivered', '', 2, '2026-05-21 08:37:50'),
(30, 22, 'shipping', 'delivered', '', 2, '2026-05-21 08:37:59'),
(31, 18, 'pending', 'delivered', '', 2, '2026-05-21 08:38:15'),
(32, 17, 'pending', 'shipping', 'Đơn hàng đang chờ hệ thống kiểm tra và xác nhận thông tin.', 2, '2026-05-21 09:40:10'),
(33, 26, NULL, 'pending', 'Khách hàng đặt hàng thành công', NULL, '2026-05-22 08:30:40'),
(41, 26, 'pending', 'cancelled', 'Khách hàng yêu cầu hủy đơn. Lý do: qqqqqqq', NULL, '2026-05-22 09:33:23'),
(42, 18, 'delivered', 'returned', 'Khách hàng yêu cầu hoàn hàng. Lý do: qqqqqqq', NULL, '2026-05-22 09:34:22'),
(43, 21, 'shipping', 'delivered', 'Đơn hàng đã được bàn giao cho đơn vị vận chuyển. Vui lòng chú ý điện thoại từ shipper!', 2, '2026-05-23 14:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `discount_percent` decimal(5,2) DEFAULT '0.00',
  `views` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `cost_price`, `stock`, `category_id`, `image`, `slug`, `is_featured`, `is_new`, `discount_percent`, `views`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Laptop Dell XPS 13', 'Laptop Dell XPS 13 inch FHD, Intel Core i5, 8GB RAM, 512GB SSD', 25999000.00, 20000000.00, 15, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_789.png', NULL, 1, 1, 10.00, 13, 1, '2026-04-17 08:54:59', '2026-05-04 05:59:07'),
(2, 'MacBook Air M1', 'Laptop Apple MacBook Air M1, 8GB RAM, 256GB SSD', 29990000.00, 25000000.00, 8, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_906_1_.png', NULL, 1, 0, 5.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:32:19'),
(3, 'iPhone 14 Pro', 'iPhone 14 Pro 128GB, màn hình AMOLED, camera 48MP', 29990000.00, 24000000.00, 20, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/s/a/samsung-galaxy-s26-ultra-1.jpg', NULL, 1, 1, 0.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:26:44'),
(4, 'Samsung Galaxy S23', 'Samsung Galaxy S23 Ultra, Snapdragon 8 Gen 2, 256GB', 23990000.00, 18000000.00, 25, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/i/dien-thoai-samsung-galaxy-s25-ultra_3__3.png', NULL, 0, 1, 15.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:26:58'),
(5, 'Sony WH-1000XM5', 'Tai nghe Sony WH-1000XM5 ANC, Bluetooth 5.3', 8990000.00, 7000000.00, 30, 3, 'https://phukienngonbore.com/wp-content/uploads/2021/12/airpod3-ho-van-1562m-300x300.png', NULL, 1, 0, 0.00, 4, 1, '2026-04-17 08:54:59', '2026-05-21 07:54:19'),
(6, 'Logitech MX Master 3', 'Chuột Logitech MX Master 3, Bluetooth, USB-C', 2190000.00, 1500000.00, 50, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/h/chuot-khong-day-logitech-mx-master-4_1_.png', NULL, 0, 0, 10.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:38:15'),
(7, 'Razer DeathAdder V2', 'Chuột gaming Razer DeathAdder V2, 20000 DPI', 1490000.00, 1000000.00, 40, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/a/gaming_8_-_2025-05-30t092833.530.png', NULL, 0, 0, 0.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:40:34'),
(8, 'LG 27UP550', 'Màn hình LG 27 inch 4K, 60Hz, IPS Panel', 8990000.00, 6500000.00, 12, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_180_1_13.png', NULL, 0, 0, 20.00, 0, 1, '2026-04-17 08:54:59', '2026-05-06 06:12:40'),
(9, 'Logitech K840', 'Bàn phím cơ Logitech K840, RGB LED', 2990000.00, 2000000.00, 25, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/h/chuot-khong-day-bluetooth-logitech-pebble-m350s_4.png', NULL, 0, 0, 5.00, 0, 1, '2026-04-17 08:54:59', '2026-05-01 09:40:14'),
(10, 'Macbook NEO 13 inch A18 PRO', 'Chip Louda mới nhất, chống ồn cực tốt', 14190000.00, NULL, 100, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/macbook_13_19.png', 'airpods-pro-anc-2026', 1, 1, 53.00, 1539, 1, '2026-04-29 10:00:24', '2026-05-23 08:21:46'),
(11, 'Củ Sạc Nhanh 35W Dual Port', 'Sạc nhanh 2 cổng tiện lợi cho iPhone', 350000.00, NULL, 50, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/u/cu-sac-anker-zolo-a2698-1c-30w.png', 'cu-sac-nhanh-35w', 1, 0, 20.00, 895, 1, '2026-04-29 10:00:24', '2026-05-01 15:07:39'),
(12, 'Laptop Acer Gaming Aspire 7 A715-59G-57TU', 'Âm thanh cực đỉnh, pin trâu 80h', 18500000.00, NULL, 30, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/e/text_d_i_1__4_8.png', 'marshall-major-iv-rep', 1, 1, 15.00, 1229, 1, '2026-04-29 10:00:24', '2026-05-18 07:08:48'),
(13, 'Sạc Dự Phòng MagSafe 10000mAh', 'Hít nam châm cực chắc cho iPhone 12-15', 550000.00, NULL, 40, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/p/i/pin-sac-du-phong-anker-pirume-a1339-9600mah-65w_1_.png', 'sac-du-phong-magsafe', 1, 0, 10.00, 606, 1, '2026-04-29 10:00:24', '2026-05-22 05:55:47'),
(14, 'Laptop HP Omnibook 5 AI 16-AF1048TU BZ7Q9PA', 'Pin 8h liên tục, âm thanh vòm', 14790000.00, NULL, 80, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_744_1_42.png', 'airpods-3-ho-van', 1, 1, 36.00, 2140, 1, '2026-04-29 10:00:24', '2026-05-16 09:26:55'),
(15, 'Macbook Air M2 2023', 'Chip M2 cực mạnh, màn hình Liquid Retina', 26900000.00, NULL, 20, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/macbook_13.png', 'macbook-air-m2', 1, 0, 10.00, 19, 1, '2026-04-29 10:02:50', '2026-05-23 08:01:00'),
(16, 'Laptop Dell XPS 13', 'Thiết kế sang trọng, mỏng nhẹ', 24500000.00, NULL, 15, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/macbook_13_17.png', 'dell-xps-13', 0, 0, 5.00, 2, 1, '2026-04-29 10:02:50', '2026-05-06 07:35:11'),
(17, 'Laptop ASUS Vivobook', 'Màn hình OLED rực rỡ', 15200000.00, NULL, 30, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_744_1_84.png', 'asus-vivobook-oled', 0, 0, 0.00, 10, 1, '2026-04-29 10:02:50', '2026-05-02 14:30:55'),
(18, 'HP Envy 14 2024', 'Hiệu năng cao cho văn phòng', 19800000.00, NULL, 12, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_874_1__2.png', 'hp-envy-14', 0, 0, 8.00, 3, 1, '2026-04-29 10:02:50', '2026-05-14 08:16:20'),
(19, 'Lenovo Ideapad 3', 'Giá rẻ cho sinh viên', 11000000.00, NULL, 50, 1, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/e/text_d_i_7_57.png', 'lenovo-ideapad-3', 0, 0, 15.00, 7, 1, '2026-04-29 10:02:50', '2026-05-06 07:36:15'),
(20, 'iPhone 15 Pro Max', 'Titan tự nhiên, chip A17 Pro', 29900000.00, NULL, 40, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/i/p/iphone-17-pro-max_3.jpg', 'iphone-15-pro-max', 1, 0, 5.00, 11, 1, '2026-04-29 10:02:50', '2026-05-16 09:24:22'),
(21, 'Samsung Galaxy S24 Ultra', 'Camera 200MP, bút S-Pen', 26500000.00, NULL, 35, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/s/a/samsung-galaxy-z-fold-7.jpg', 'samsung-s24-ultra', 0, 0, 12.00, 6, 1, '2026-04-29 10:02:50', '2026-05-18 06:27:33'),
(22, 'Xiaomi 14 Pro', 'Sạc siêu nhanh 120W', 18900000.00, NULL, 25, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/i/dien-thoai-xiaomi-15-ultra.png', 'xiaomi-14-pro', 0, 0, 0.00, 0, 1, '2026-04-29 10:02:50', '2026-05-01 09:28:23'),
(23, 'Oppo Reno 11', 'Chuyên gia chân dung', 10500000.00, NULL, 45, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/x/9/x9-1_1.jpg', 'oppo-reno-11', 0, 0, 10.00, 2, 1, '2026-04-29 10:02:50', '2026-05-01 09:28:43'),
(24, 'iPhone 13 128GB', 'Lựa chọn quốc dân giá tốt', 13500000.00, NULL, 60, 2, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/i/p/iphone-15-plus-256gb_3.png', 'iphone-13-128gb', 0, 0, 20.00, 3, 1, '2026-04-29 10:02:50', '2026-05-02 08:24:34'),
(25, 'Airpods Pro ANC Louda', 'Chống ồn xuyên âm cực tốt', 419000.00, NULL, 100, 3, 'https://phukienngonbore.com/wp-content/uploads/2021/11/airpod-3-rep-1-1-300x300.jpg', 'airpods-pro-anc', 1, 0, 53.00, 4, 1, '2026-04-29 10:02:50', '2026-05-06 06:57:19'),
(26, 'Tai nghe Sony WH-1000XM5', 'Chống ồn số 1 thế giới', 6500000.00, NULL, 15, 3, 'https://phukienngonbore.com/wp-content/uploads/2022/10/tai-nghe-airpod-pro-2-2022-gia-re-300x300.jpg', 'sony-wh-1000xm5', 0, 0, 10.00, 7, 1, '2026-04-29 10:02:50', '2026-05-16 09:27:37'),
(27, 'Marshall Major IV', 'Pin 80h, âm thanh cổ điển', 3200000.00, NULL, 20, 3, 'https://phukienngonbore.com/wp-content/uploads/2022/10/tai-nghe-ho-van-1562ae-300x300.jpg', 'marshall-major-4', 0, 0, 0.00, 1, 1, '2026-04-29 10:02:50', '2026-05-25 05:54:53'),
(28, 'Airpods 2 Hổ Vằn', 'Check setting, pin bền', 250000.00, NULL, 150, 3, 'https://phukienngonbore.com/wp-content/uploads/2021/03/tai-nghe-bluetooth-AMOI-F9-7-200x200.jpg', 'airpods-2-ho-van', 0, 0, 15.00, 3, 1, '2026-04-29 10:02:50', '2026-05-21 07:50:55'),
(29, 'Samsung Buds 2 Pro', 'Âm thanh 24-bit đỉnh cao', 2800000.00, NULL, 30, 3, 'https://phukienngonbore.com/wp-content/uploads/2022/10/tai-nghe-iphone-14-co-day-chinh-hang-300x300.jpg', 'samsung-buds-2-pro', 0, 0, 30.00, 2, 1, '2026-04-29 10:02:50', '2026-05-02 14:00:32'),
(30, 'Bàn phím cơ AKKO 3068', 'Switch AKKO v3 cực mượt', 1250000.00, NULL, 40, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/b/a/ban-phim-co-rapoo-gk500-den.png', 'akko-3068-v3', 1, 0, 10.00, 6, 1, '2026-04-29 10:02:50', '2026-05-18 05:46:06'),
(31, 'Chuột Logitech G502 Hero', 'Cảm biến 25K DPI cực nhạy', 950000.00, NULL, 55, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/h/chuot-gaming-co-day-asus-tuf-m3-gen-2_1_1.png', 'logitech-g502-hero', 0, 0, 20.00, 15, 1, '2026-04-29 10:02:50', '2026-05-21 07:54:29'),
(32, 'Bàn phím DareU EK87', 'Phím cơ giá rẻ quốc dân', 4500000.00, NULL, 100, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/a/gaming_8_-_2025-08-06t111146.036.png', 'dareu-ek87', 0, 0, 0.00, 0, 1, '2026-04-29 10:02:50', '2026-05-06 06:11:40'),
(33, 'Chuột không dây Logitech M331', 'Click không gây tiếng động', 280000.00, NULL, 80, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/f/r/frame_1_3__3.png', 'logitech-m331', 0, 0, 5.00, 1, 1, '2026-04-29 10:02:50', '2026-05-21 07:52:12'),
(34, 'Bộ phím chuột văn phòng Dell', 'Bền bỉ, gõ êm', 3500000.00, 3299999.00, 120, 4, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/a/gaming_8_33_.png', 'bo-phim-chuot-van-phong-dell', 0, 0, 0.00, 0, 1, '2026-04-29 10:02:50', '2026-05-16 08:10:55'),
(36, 'Cáp sạc Type-C 2m bọc dù', 'Siêu bền, chống đứt', 95000.00, NULL, 300, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/a/cap-sac-nhanh-baseus-cafule-pd-2-0-100w-type-c-to-type-c-20v-5a-2m.1_7_.png', 'cap-sac-du-2m', 0, 0, 10.00, 0, 1, '2026-04-29 10:02:50', '2026-05-06 05:50:43'),
(38, 'Ốp lưng Magsafe trong suốt', 'Hít nam châm chắc chắn', 120000.00, NULL, 500, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/o/p/op-lung-iphone-17-pro-max-apple-techwoven-with-magsafe.png', 'op-magsafe-trong', 0, 0, 20.00, 1, 1, '2026-04-29 10:02:50', '2026-05-06 08:31:45'),
(39, 'Hub chuyển đổi 5 in 1', 'Dành cho Macbook và Type-C', 380000.00, NULL, 40, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/h/thumb-op-lung_1_9.png', 'hub-type-c-5in1', 0, 0, 15.00, 0, 1, '2026-04-29 10:02:50', '2026-05-06 05:54:22'),
(50, 'Đèn LED Treo Màn Hình Baseus', 'Chống mỏi mắt, không gây chói màn hình', 450000.00, NULL, 60, 8, 'https://genk.mediacdn.vn/139269124445442048/2026/4/14/egbqegqeg-1776189527631-1776189528738415833856.jpg', 'den-treo-man-hinh-baseus', 1, 0, 25.00, 1810, 1, '2026-04-29 10:03:02', '2026-05-21 10:01:17'),
(51, 'Đèn Bàn Học Chống Cận Xiaomi', 'Ánh sáng tự nhiên, điều khiển qua App', 650000.00, NULL, 40, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-ban-led-hyperwork-luna-hpw-dl01_1_.png', 'den-ban-hoc-xiaomi-gen2', 0, 0, 10.00, 550, 1, '2026-04-29 10:03:02', '2026-05-06 06:26:59'),
(52, 'Dây LED RGB Dán Cạnh Bàn', 'Đổi màu theo nhạc, điều khiển Remote', 150000.00, NULL, 200, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/a/tapo_l900-5_eu_0_normal_20210806071549v.jpg', 'day-led-rgb-5m', 0, 0, 50.00, 2500, 1, '2026-04-29 10:03:02', '2026-05-06 06:27:23'),
(53, 'Đèn Ngủ Phi Hành Gia', 'Chiếu thiên hà cực đẹp cho phòng ngủ', 350000.00, NULL, 85, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/a/tapo-l920-5_overview_02_normal_20211028063043e.jpg', 'den-chieu-phi-hanh-gia', 0, 0, 0.00, 1300, 1, '2026-04-29 10:03:02', '2026-05-06 06:27:38'),
(54, 'Đèn LED Để Bàn Tích Điện', 'Sử dụng liên tục 10h khi mất điện', 190000.00, NULL, 120, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-led-de-ban-taotronics-tt-dl01-tt-dl02-1_3.jpg', 'den-led-tich-dien-gap-gon', 0, 0, 20.00, 420, 1, '2026-04-29 10:03:02', '2026-05-06 06:27:50'),
(58, 'Lót chuột Corsair MM300', 'Lót chuột kích thước lớn, bề mặt vải dệt chống sờn.', 450000.00, 250000.00, 100, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/p/h/photo_2020-09-19_13-56-51_1.jpg', 'lot-chuot-corsair-mm300', 0, 0, 0.00, 45, 1, '2026-05-05 15:17:43', '2026-05-06 06:05:22'),
(62, 'Giá đỡ Laptop N3 Aluminum', 'Chất liệu hợp kim nhôm, hỗ trợ tản nhiệt, gập gọn.', 250000.00, 120000.00, 60, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/e/text_ng_n_15_123.png', 'gia-do-laptop-n3', 0, 0, 0.00, 94, 1, '2026-05-05 15:17:43', '2026-05-21 07:55:38'),
(63, 'Tay cầm Xbox Series X Controller', 'Kết nối Bluetooth, tương thích PC và Console.', 1590000.00, 1300000.00, 12, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/r/o/rog-xbox-ally-x-1_1.jpg', 'tay-cam-xbox-series-x', 1, 0, 0.00, 181, 1, '2026-05-05 15:17:43', '2026-05-06 13:52:01'),
(64, 'Webcam Logitech C922 Pro', 'Stream Full HD 1080p, tích hợp chân tripod nhỏ.', 2150000.00, 1700000.00, 10, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/2/c270-hd-webcam-refresh.png', 'webcam-logitech-c922', 0, 0, 12.00, 76, 1, '2026-05-05 15:17:43', '2026-05-06 08:31:53'),
(65, 'Cáp sạc iPhone Apple 20W', 'Cáp Type-C sang Lightning chính hãng, sạc nhanh.', 550000.00, 350000.00, 80, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/c/a/cap-chuyen-doi-type-c-to-lightning-momax-silicone-30w-2m-dl55.png', 'cap-sac-iphone-20w', 0, 0, 0.00, 420, 1, '2026-05-05 15:17:43', '2026-05-06 05:55:13'),
(66, 'Ổ cứng di động WD My Passport 1TB', 'Sao lưu dữ liệu tự động, mã hóa mật khẩu 256-bit.', 1650000.00, 1350000.00, 18, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/h/the-nho-micro-sdxc-sandisk-extreme-pro-v30-a2-128gb-200mbs.png', 'o-cung-wd-my-passport-1tb', 1, 0, 8.00, 60, 1, '2026-05-05 15:17:43', '2026-05-15 06:51:44'),
(67, 'Thẻ nhớ MicroSD SanDisk 128GB', 'Tốc độ đọc 120MB/s, chuyên dụng cho điện thoại, camera.', 390000.00, 220000.00, 120, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/t/h/the-nho-sandisk-128gb-class-10-100mbs.png', 'the-nho-sandisk-128gb', 0, 0, 20.00, 140, 1, '2026-05-05 15:17:43', '2026-05-06 06:07:32'),
(69, 'Giá treo tai nghe RGB Onikuma', 'Tích hợp đèn LED đổi màu, cổng USB mở rộng.', 320000.00, 180000.00, 45, 5, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/i/gia-treo-tai-nghe-hyperwork-hs01_1_.png', 'gia-treo-tai-nghe-rgb', 0, 0, 0.00, 28, 1, '2026-05-05 15:17:43', '2026-05-06 06:08:03'),
(73, 'Màn hình ASUS ProArt PA278QV', 'Màn hình chuyên đồ họa 27 inch, 100% sRGB, độ phân giải QHD.', 8700000.00, 7200000.00, 8, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_635_30.png', 'man-hinh-asus-proart-pa278qv', 0, 0, 10.00, 95, 1, '2026-05-05 15:19:43', '2026-05-06 06:15:58'),
(74, 'Màn hình ViewSonic VA2432-H', 'Màn hình văn phòng 24 inch IPS, 75Hz, giá rẻ cho sinh viên.', 2450000.00, 1900000.00, 50, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_635_2.png', 'man-hinh-viewsonic-va2432-h', 0, 0, 0.00, 110, 1, '2026-05-05 15:19:43', '2026-05-06 06:15:02'),
(75, 'Màn hình MSI G241V E2', 'Màn hình Gaming 24 inch, tấm nền IPS, 1ms, 75Hz.', 3150000.00, 2600000.00, 20, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_183_1_6.png', 'man-hinh-msi-g241v-e2', 0, 1, 15.00, 65, 1, '2026-05-05 15:19:43', '2026-05-06 06:14:45'),
(76, 'Màn hình Gigabyte G27F 2', 'Kích thước 27 inch, FHD, 165Hz, IPS, chuyên game FPS.', 4850000.00, 3950000.00, 12, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_926_6.png', 'man-hinh-gigabyte-g27f-2', 0, 0, 0.00, 88, 1, '2026-05-05 15:19:43', '2026-05-06 06:14:33'),
(77, 'Màn hình AOC 24G2', 'Màn hình gaming quốc dân 24 inch, 144Hz, IPS.', 4200000.00, 3400000.00, 18, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_923_1.png', 'man-hinh-aoc-24g2', 0, 0, 5.00, 213, 1, '2026-05-05 15:19:43', '2026-05-18 07:03:55'),
(78, 'Màn hình Lenovo L24i-30', 'Thiết kế siêu mỏng, viền gọn gàng, phù hợp làm việc văn phòng.', 2890000.00, 2300000.00, 30, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_179_6_60.png', 'man-hinh-lenovo-l24i-30', 0, 0, 0.00, 45, 1, '2026-05-05 15:19:43', '2026-05-06 06:14:07'),
(79, 'Màn hình HKC MB24V13', 'Màn hình 24 inch Full HD giá tốt, thiết kế hiện đại.', 2190000.00, 1650000.00, 40, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/man-hinh-dell-u2424h-24-inch-10.png', 'man-hinh-hkc-mb24v13', 0, 0, 0.00, 30, 1, '2026-05-05 15:19:43', '2026-05-06 06:13:44'),
(80, 'Màn hình BenQ EX2510S', 'Dòng Mobiuz, 165Hz, 1ms, HDRi, loa tích hợp TreVolo.', 5350000.00, 4500000.00, 7, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_635_24.png', 'man-hinh-benq-ex2510s', 0, 1, 10.00, 75, 1, '2026-05-05 15:19:43', '2026-05-06 06:13:33'),
(81, 'Màn hình Xiaomi Mi Desktop Monitor 27\"', 'Góc nhìn rộng 178 độ, bảo vệ mắt khỏi ánh sáng xanh.', 3450000.00, 2900000.00, 15, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_921_3_.png', 'man-hinh-xiaomi-27-inch', 0, 0, 0.00, 120, 1, '2026-05-05 15:19:43', '2026-05-06 06:13:19'),
(82, 'Màn hình Acer Nitro VG240Y', 'Tấm nền IPS, 165Hz, thiết kế ZeroFrame hầm hố.', 3990000.00, 3200000.00, 22, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_895_1_.png', 'man-hinh-acer-nitro-vg240y', 0, 0, 12.00, 90, 1, '2026-05-05 15:19:43', '2026-05-06 06:13:08'),
(83, 'Màn hình Philips 241V8', 'Công nghệ SmartImage, chế độ LowBlue giảm mỏi mắt.', 2550000.00, 2100000.00, 35, 6, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/r/group_179_6_41.png', 'man-hinh-philips-241v8', 0, 0, 0.00, 25, 1, '2026-05-05 15:19:43', '2026-05-06 06:12:57'),
(106, 'Bàn Làm Việc Đứng Flexispot ET223 - E7', 'Bàn làm việc đứng Flexispot ET223-E7 là dòng bàn cao cấp được ra mắt vào năm 2020, là mẫu bàn làm việc có thể thay đổi chiều cao của thương hiệu Flexispot. Sản phẩm sở hữu những sự cải tiến so với bàn E4 Premium 3 stage trước đó của hãng bao gồm, tính năng chống va chạm, cho phép nâng cấp và sửa chữa và chân bàn trụ vững, cứng cáp hơn.', 950000.00, 600000.00, 40, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/b/_/b_n_i_u_ch_nh_cao.jpg', 'ban-lam-viec-chan-sat-chu-k', 0, 0, 10.00, 90, 1, '2026-05-05 15:21:55', '2026-05-14 08:19:17'),
(107, 'Bàn làm việc đứng điều chỉnh độ cao Flexispot ET114N-EN1', 'ET114N-EN1 là sản phẩm bàn làm việc đứng Flexispot được thiết kế theo chuẩn Ergonomics tuân theo các tiêu chuẩn của BIFMA.  Bàn sẽ mang đến khả năng hoạt động ổn định hơn và chắc chắn hơn.', 1850000.00, 1300000.00, 15, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/b/a/ban-lam-viec-thay-doi-do-cao-flexispot-e4-dergo__1__23bcf0f776f740bbb112441f0fc818a1.jpg', 'ban-lam-viec-vintage-go-cao-su', 0, 0, 0.00, 70, 1, '2026-05-05 15:21:55', '2026-05-06 08:32:33'),
(108, 'Ghế công thái học Ergonomic Sihoo M102C', 'Ghế công thái học Ergonomic Sihoo M102C có thiết kế gây ấn tượng với phần tựa lưng full lưới 2 mảnh bằng lưới PA+Fiber chất lượng cao. Trang bị phần tựa đầu cao 6cm có thể tùy chỉnh được độ nghiêng, kê tay thiết kế 3D với lớp đệm bọc vải cực thoải mái. Sở hữu trục thuỷ lực đảm bảo đạt tiêu chuẩn BIFMA, sản phẩm ghế công thái học Sihoo sử dụng với tải trọng lớn.', 3800000.00, 2900000.00, 12, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/h/ghe-cong-thai-hoc-sihoo-m102c.png', 'ban-lam-viec-streamer-pro', 0, 1, 20.00, 240, 1, '2026-05-05 15:21:55', '2026-05-06 06:24:26'),
(109, 'Ghế công thái học HyperWork Media Airy', 'Ghế công thái học HyperWork Airy có tính năng điều chỉnh đa dạng với chất lượng ấn tượng từ thiết kế chuẩn ergonomic kèm vật liệu bền bỉ, để ưu tiên sự thoải mái cho người dùng. Không những vậy, sản phẩm ghế công thái học HyperWork còn được đề cao về mặt thẩm mỹ qua kiểu dáng hiện đại thích hợp trong mọi không gian. Đi kèm là vô số tính năng phong phú giúp tối ưu chất lượng sử dụng cho người dùng.', 4500000.00, 3500000.00, 8, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/h/ghe-cong-thai-hoc-hyperwork-airy-1.png', 'ban-lam-viec-doi-teamwork', 0, 0, 0.00, 45, 1, '2026-05-05 15:21:55', '2026-05-06 06:23:44'),
(110, 'Ghế văn phòng công thái học Ergonomic Okamura Contessa II', 'Okamura Contessa II là sự kết hợp giữa phong cách thiết kế Ý đến từ thương hiệu ITALDESIGN và kỹ thuật hiện đại của Okamura. Chiếc ghế Okamura mang đến sự thoải mái tối ưu với người dùng, từ đó nâng cao hiệu suất làm việc.', 1250000.00, 850000.00, 18, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/_/0/_0001_e5654fd5-8516-479a-83c1-2c8ef834_1_.jpg', 'ban-console-office-decor', 0, 0, 5.00, 30, 1, '2026-05-05 15:21:55', '2026-05-06 06:23:05'),
(111, 'Ghế Công Thái Học Ergonomic GTChair I-see M Đen', 'Một chiếc ghế êm ái, thoải mái và thoáng mát là điều mà mọi nhân viên văn phòng đều hướng tới. Đáp ứng được tất cả các yêu cầu trên ghế công thái học Ergonomic GTChair I-see M là sản phẩm tuyệt vời mà bạn đang tìm kiếm. Đặc biệt chúng còn giúp giảm thiểu các bệnh về lưng và cột sống.', 2100000.00, 1600000.00, 14, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/a/n/anh_ghe_1_2.jpg', 'ban-lam-viec-mat-kinh-cuong-luc', 0, 0, 0.00, 73, 1, '2026-05-05 15:21:55', '2026-05-06 06:21:38'),
(112, 'Ghế Gaming Công Thái Học GTChair - Marrit X Đen', 'Phù hợp cho trẻ em, điều chỉnh được độ nghiêng mặt bàn.', 2750000.00, 2000000.00, 22, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/h/ghe_cong_thai_hoc_1.jpg', 'ban-hoc-sinh-chong-gu', 0, 0, 0.00, 110, 1, '2026-05-05 15:21:55', '2026-05-06 06:20:49'),
(113, 'Ghế công thái học E-DRA EEC218', 'Ghế công thái học E-Dra EEC218 với chất liệu lưới cao cấp và khả năng điều chỉnh độ cao tựa lưng và đầu mang lại trải nghiệm vô cùng thoải mái. Trang bị trụ thủy lực Class-3 Bifma và bánh xe PU 50mm Bifma trên ghế đảm bảo khả năng vận hành mượt mà. Ngoài ra, sản phẩm ghế công thái học E-Dra này có khả năng chịu tải của ghế lên đến 100kg, tương thích nhiều người dùng.', 1350000.00, 950000.00, 30, 7, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/g/h/ghe-cong-thai-hoc-e-dra-eec218-1.png', 'ban-lam-viec-muji-style-v2', 0, 0, 0.00, 55, 1, '2026-05-05 15:21:55', '2026-05-06 06:20:20'),
(114, 'Đèn LED Thông Minh Philips Hue Go', 'Đèn LED đổi màu 16 triệu màu, điều khiển qua app, tích hợp pin sạc tiện lợi.', 250000.00, 1800000.00, 20, 8, 'https://static.rangdongstore.vn/product/den-ban/RD-RL-16/4.jpg?fm=webp&w=500', 'den-led-thong-minh-philips-hue-go', 1, 1, 5.00, 462, 1, '2026-05-05 15:22:57', '2026-05-15 06:33:08'),
(115, 'Đèn Bàn Học Chống Cận Taotronics', '5 chế độ màu và 7 mức độ sáng, tích hợp cổng sạc USB cho điện thoại.', 150000.00, 850000.00, 35, 8, 'https://static.rangdongstore.vn/product/den-ban/RD-RL-19/2.RD-RL-19-LED.jpg?fm=webp&w=500', 'den-ban-hoc-chong-can-taotronics', 1, 0, 10.00, 310, 1, '2026-05-05 15:22:57', '2026-05-06 06:40:57'),
(116, 'Dây Đèn LED RGB 5050 (5m)', 'Dây đèn trang trí dán tường, kèm remote điều khiển 24 phím, nhiều hiệu ứng nháy.', 150000.00, 80000.00, 150, 8, 'https://static.rangdongstore.vn/product/den-ban/RD-RL-01.V2/RD-RL-27.V2-1.jpg?fm=webp&w=500', 'day-den-led-rgb-5050-5m', 0, 0, 0.00, 122, 1, '2026-05-05 15:22:57', '2026-05-06 14:19:44'),
(117, 'Đèn LED Panel 600x600 Rạng Đông', 'Đèn âm trần công suất 40W, ánh sáng trắng, phù hợp cho văn phòng.', 550000.00, 420000.00, 40, 8, 'https://static.rangdongstore.vn/240731019520/2024/07/31/RD-RL-21-4.jpg?fm=webp&w=500', 'den-led-panel-600x600-rang-dong', 0, 0, 0.00, 86, 1, '2026-05-05 15:22:57', '2026-05-06 08:30:23'),
(118, 'Đèn LED Năng Lượng Mặt Trời 100W', 'Chống nước IP67, tự động bật khi trời tối, kèm chân đế lắp đặt.', 850000.00, 600000.00, 25, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/p/i/pisenledchargeabledesklampwithusb_1923.png', 'den-led-nang-luong-mat-troi-100w', 0, 1, 15.00, 195, 1, '2026-05-05 15:22:57', '2026-05-06 06:28:31'),
(119, 'Đèn LED Âm Trần Philips 9W', 'Ánh sáng trung tính, thiết kế mỏng nhẹ, độ bền lên đến 20.000 giờ.', 125000.00, 95000.00, 200, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-led-de-ban-taotronics-tt-dl064_1_1.jpg', 'den-led-am-tran-philips-9w', 0, 0, 0.00, 61, 1, '2026-05-05 15:22:57', '2026-05-06 08:30:31'),
(120, 'Đèn LED Rọi Ray 20W KingLED', 'Chuyên dùng cho shop thời trang, xoay 360 độ, độ hoàn màu CRI > 90.', 320000.00, 240000.00, 60, 8, 'https://product.hstatic.net/1000253446/product/den_ban_bao_ve_thi_luc_dien_quang_dq_dkl14_7341df5fa78a4951814f17727d6d0220_large.jpg', 'den-led-roi-ray-20w-kingled', 0, 0, 0.00, 45, 1, '2026-05-05 15:22:57', '2026-05-06 06:35:43'),
(121, 'Đèn LED Búp (Bulb) Trụ 30W', 'Tiết kiệm điện 80%, đuôi xoáy E27 phổ biến, độ sáng cao.', 95000.00, 65000.00, 100, 8, 'https://product.hstatic.net/1000253446/product/den_ban_dien_quang_dq_dkl03___kieu_choa_sat__mau_do_den___4356ff2e7f844ac7a35157737070280e_large.jpg', 'den-led-bup-tru-30w', 0, 0, 0.00, 30, 1, '2026-05-05 15:22:57', '2026-05-06 06:36:10'),
(122, 'Đèn LED Chống Cháy Nổ 50W', 'Thân nhôm đúc áp lực, kính cường lực bảo vệ, dùng cho kho bãi.', 2450000.00, 1950000.00, 10, 8, 'https://product.hstatic.net/1000253446/product/ldl18_81c2c48494c84b6391d84afcd365aa35_large.png', 'den-led-chong-chay-no-50w', 0, 0, 10.00, 25, 1, '2026-05-05 15:22:57', '2026-05-06 06:36:43'),
(123, 'Đèn LED Thanh Cảm Ứng Bếp', 'Cảm biến vẫy tay thông minh, lắp đặt dưới tủ bếp hoặc tủ quần áo.', 280000.00, 180000.00, 45, 8, 'https://product.hstatic.net/1000253446/product/den_ban_bao_ve_thi_luc_dien_quang_dq_dkl17___kieu_con_cho___bong_led___df9a88019712488cbdea3715a1fae9db_large.jpg', 'den-led-thanh-cam-ung-bep', 0, 1, 0.00, 140, 1, '2026-05-05 15:22:57', '2026-05-06 06:36:57'),
(124, 'Đèn LED Livestream 12 inch', 'Có giá đỡ điện thoại, 3 chế độ màu, phù hợp quay Tiktok/Livestream.', 350000.00, 220000.00, 80, 8, 'https://product.hstatic.net/1000253446/product/dkl19_13ec8751fc6142e790b34f176365e4c2_large.png', 'den-led-livestream-12-inch', 0, 0, 20.00, 420, 1, '2026-05-05 15:22:57', '2026-05-06 06:37:41'),
(125, 'Đèn LED Nhà Xưởng HighBay 100W', 'Chip LED Lumileds siêu sáng, tản nhiệt nhôm nguyên khối.', 1650000.00, 1300000.00, 15, 8, 'https://static.rangdongstore.vn/250327029133/2025/03/27/RD-RL-45_6w_X-1.jpg?fm=webp&w=500', 'den-led-nha-xuong-highbay-100w', 0, 0, 0.00, 55, 1, '2026-05-05 15:22:57', '2026-05-06 06:31:07'),
(126, 'Đèn LED Ốp Trần Trang Trí', 'Thiết kế hiện đại, nhiều vòng tròn lồng nhau, điều khiển remote.', 1850000.00, 1450000.00, 12, 8, 'https://product.hstatic.net/1000253446/product/den_ban_dien_quang_dq_dkl05___kieu_xe_hoi__mau_vang_den___8a708c65adbd4ce7a8870d91c5a237a1_large.jpg', 'den-led-op-tran-trang-tri-decor', 0, 0, 0.00, 75, 1, '2026-05-05 15:22:57', '2026-05-06 06:38:07'),
(127, 'Đèn LED Sân Vườn Cắm Cỏ', 'Thân inox chống gỉ, ánh sáng vàng ấm, tạo điểm nhấn cảnh quan.', 195000.00, 135000.00, 55, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-led-de-ban-taotronics-tt-dl22-1_1.jpg', 'den-led-san-vuon-cam-co', 0, 0, 0.00, 38, 1, '2026-05-05 15:22:57', '2026-05-06 06:28:17'),
(128, 'Đèn LED Chiếu Điểm Spotlight 7W', 'Góc chiếu hẹp, tạo hiệu ứng tập trung ánh sáng vào vật thể.', 215000.00, 155000.00, 70, 8, 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/den-ngu-led-di-dong-taotronics-tt-dl23-1_1_1.jpg', 'den-led-chieu-diem-spotlight-7w', 0, 0, 5.00, 92, 1, '2026-05-05 15:22:57', '2026-05-16 09:01:29'),
(129, 'Test Product', '12', 1250000.00, 1200000.00, 12, 9, '../../assets/images/products/1779085063_DEALHUNTER365.jpg', 'test-product', 0, 1, 12.00, 0, 0, '2026-05-16 08:34:36', '2026-05-18 06:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `is_verified_purchase` tinyint(1) DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `id` int NOT NULL,
  `date` date DEFAULT NULL,
  `total_orders` int DEFAULT '0',
  `total_revenue` decimal(12,2) DEFAULT '0.00',
  `total_customers` int DEFAULT '0',
  `new_customers` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`id`, `date`, `total_orders`, `total_revenue`, `total_customers`, `new_customers`, `created_at`) VALUES
(1, '2026-05-06', 4, 39370620.00, 1, 1, '2026-05-15 06:09:24'),
(2, '2026-05-14', 2, 79540000.00, 2, 1, '2026-05-15 06:09:24'),
(3, '2026-05-15', 1, 46670000.00, 2, 0, '2026-05-15 06:09:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `phone`, `address`, `avatar`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 'user', 'user004@gmail.com', '$2y$10$pXNkx8HSlA5M3Q7BiP4ffeJA1h21edjgMAi.KhPCWwgNQuSfR2zeq', 'Nguyễn Văn User', NULL, NULL, NULL, 1, '2026-05-06 05:59:46', '2026-05-06 05:59:46'),
(8, 'vinh', 'vinh123@gmail.com', '$2y$10$zfTVSiYrwi7iV.KyL06cl.G8KoZCEAL/xOQVjVWVRcyN9L1Hi9rNO', 'Hữu Vinh', NULL, NULL, NULL, 1, '2026-05-14 08:17:40', '2026-05-16 09:57:13'),
(9, 'hao', 'hao123@gmail.com', '$2y$10$YmCti9ZYjeqjddzV7BnTt.2bMYFH796S7gSWwXtbVOITpWCJGHjcG', 'Vũ Minh Hào', NULL, NULL, NULL, 1, '2026-05-21 07:50:27', '2026-05-21 07:50:27'),
(10, 'long', 'long123@gmail.com', '$2y$10$N1jrd8EeYkz2AfX.Cqn/NO.lHw0wpmcLRbJ01mDK7d6TrI6DtSecC', 'Ngô Hoàng Long', NULL, NULL, NULL, 1, '2026-05-21 07:53:49', '2026-05-21 07:53:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_payment` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_new` (`is_new`),
  ADD KEY `idx_product_price` (`price`),
  ADD KEY `idx_product_discount` (`discount_percent`);
ALTER TABLE `products` ADD FULLTEXT KEY `ft_name_desc` (`name`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`date`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
