-- Migration: Cancel & Return Order Management
-- Date: 2026-05-26

-- 1. Create order_requests table
CREATE TABLE IF NOT EXISTS `order_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `request_type` ENUM('cancel', 'return') NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',

  -- Customer info
  `user_id` INT NOT NULL,
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  -- Admin info
  `admin_id` INT NULL,
  `reviewed_at` TIMESTAMP NULL,
  `rejection_reason` TEXT,

  -- Evidence image (for return)
  `evidence_image` VARCHAR(255),

  -- Refund info
  `refund_amount` DECIMAL(12, 2),
  `refund_status` ENUM('pending', 'processing', 'completed') DEFAULT 'pending',

  -- Constraints
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,

  -- Only 1 request per order type
  UNIQUE KEY unique_order_type (order_id, request_type),

  INDEX idx_status (status),
  INDEX idx_user (user_id),
  INDEX idx_order (order_id),
  INDEX idx_type (request_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add columns to orders table if they don't exist
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `can_cancel` TINYINT(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS `can_return` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `return_deadline` DATE NULL,
ADD COLUMN IF NOT EXISTS `refund_amount` DECIMAL(12, 2) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `refund_status` ENUM('none', 'pending', 'processing', 'completed') DEFAULT 'none';

-- 3. Create order_status_history table if needed
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `old_status` VARCHAR(50),
  `new_status` VARCHAR(50) NOT NULL,
  `note` TEXT,
  `changed_by` INT NULL,
  `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES admin_users(id) ON DELETE SET NULL,

  INDEX idx_order (order_id),
  INDEX idx_status (new_status),
  INDEX idx_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
