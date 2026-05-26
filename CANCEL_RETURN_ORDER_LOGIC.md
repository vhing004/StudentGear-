# 📋 LOGIC XỬ LÝ HỦY HÀNG (CANCEL) & HOÀN HÀNG (RETURN)

## 🎯 Tổng Quan

### **2 Loại Yêu Cầu**
1. **Hủy Hàng (Cancel Order)** - Trạng thái: pending/confirmed
2. **Hoàn Hàng (Return Order)** - Trạng thái: delivered

### **2 Người Quyết Định**
1. **Khách Hàng** - Gửi yêu cầu
2. **Admin** - Xác nhận hoặc từ chối

---

## 📊 DATABASE SCHEMA - CẦN THÊM

### **Bảng Mới: order_requests (Yêu Cầu Hủy/Hoàn)**

```sql
CREATE TABLE order_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  request_type ENUM('cancel', 'return') NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  
  -- Thông tin khách hàng gửi
  user_id INT NOT NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- Thông tin admin xử lý
  admin_id INT NULL,
  reviewed_at TIMESTAMP NULL,
  rejection_reason TEXT,
  
  -- Hình ảnh chứng minh (cho return)
  evidence_image VARCHAR(255),
  
  -- Refund info
  refund_amount DECIMAL(12, 2),
  refund_status ENUM('pending', 'processing', 'completed') DEFAULT 'pending',
  
  -- Constraints
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  
  -- Chỉ 1 request/loại cho mỗi order
  UNIQUE KEY unique_order_type (order_id, request_type),
  
  INDEX idx_status (status),
  INDEX idx_user (user_id),
  INDEX idx_order (order_id)
);
```

### **Cột Mở Rộng trong Bảng orders**

```sql
-- Thêm vào bảng orders
ALTER TABLE orders ADD COLUMN (
  -- Cho cancel
  can_cancel TINYINT(1) DEFAULT 1,  -- Có thể hủy không
  
  -- Cho return
  can_return TINYINT(1) DEFAULT 0,  -- Có thể hoàn không (sau khi delivered)
  return_deadline DATE,              -- Hạn hoàn hàng (30 ngày sau delivered)
  
  -- Hoàn tiền
  refund_amount DECIMAL(12, 2),     -- Số tiền hoàn
  refund_status ENUM('none', 'pending', 'processing', 'completed') DEFAULT 'none'
);
```

---

## 🔄 WORKFLOW HỦY HÀNG (CANCEL)

### **Timeline**

```
┌─────────────────────────────────────────────────────────────┐
│ Khách hàng → Gửi yêu cầu hủy → Chờ xác nhận (Admin)        │
└──────────────────────┬──────────────────────────────────────┘
                       │
        ┌──────────────┴──────────────┐
        ↓                             ↓
    ✅ APPROVED                  ❌ REJECTED
        │                             │
   (Admin xác nhận)             (Admin từ chối)
        │                             │
    ├─ UPDATE orders              └─ Khách nhận email
    │  status='cancelled'            từ chối
    │                                │
    ├─ Cộng lại stock             └─ Giữ nguyên đơn
    │                                order
    ├─ Hoàn tiền
    │  (nếu đã thanh toán)
    │
    ├─ Gửi email
    │  xác nhận hủy
    │
    └─ DELETE FROM cart
       (nếu chưa checkout)
```

---

## 📋 LOGIC HỦY HÀNG CHI TIẾT

### **Bước 1: Khách Hàng Gửi Yêu Cầu Hủy**

**Điều Kiện:**
- ✅ Order status = "pending" hoặc "confirmed"
- ✅ Trong 24 giờ sau khi tạo đơn
- ✅ Khách đã đăng nhập

**Hành Động:**
```php
// File: api/cancel-order.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $reason = $_POST['reason'];  // VD: "Tôi muốn hủy"
    $description = $_POST['description'];  // Chi tiết
    
    // 1. CHECK order tồn tại và thuộc user
    $order = $db->fetchOne(
        "SELECT * FROM orders WHERE id = ? AND user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    
    if (!$order) {
        die(json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']));
    }
    
    // 2. CHECK status có phải pending/confirmed
    if (!in_array($order['status'], ['pending', 'confirmed'])) {
        die(json_encode([
            'success' => false, 
            'message' => 'Chỉ có thể hủy đơn chờ xử lý hoặc đã xác nhận'
        ]));
    }
    
    // 3. CHECK thời hạn (24 giờ)
    $created = strtotime($order['created_at']);
    $now = time();
    if ($now - $created > 86400) {  // 24 giờ
        die(json_encode([
            'success' => false, 
            'message' => 'Quá 24 giờ, không thể hủy. Liên hệ support'
        ]));
    }
    
    // 4. INSERT request
    $db->execute(
        "INSERT INTO order_requests 
         (order_id, request_type, reason, description, user_id)
         VALUES (?, 'cancel', ?, ?, ?)",
        [$order_id, $reason, $description, $_SESSION['user_id']]
    );
    
    // 5. Gửi email thông báo admin
    // sendEmail(admin@..., "Yêu cầu hủy đơn $order_id");
    
    echo json_encode(['success' => true, 'message' => 'Yêu cầu hủy đã được gửi']);
}
```

---

### **Bước 2: Admin Xem & Xác Nhận/Từ Chối**

**Danh Sách Yêu Cầu**

```sql
SELECT 
  r.id,
  r.order_id,
  r.request_type,
  r.reason,
  r.status,
  r.requested_at,
  u.fullname as customer_name,
  u.email as customer_email,
  o.order_code,
  o.status as order_status,
  o.total_price
FROM order_requests r
JOIN orders o ON r.order_id = o.id
JOIN users u ON r.user_id = u.id
WHERE r.request_type = 'cancel' AND r.status = 'pending'
ORDER BY r.requested_at DESC;
```

---

### **Bước 3: Admin Xác Nhận Hủy (APPROVED)**

**File: api/admin/approve-cancel.php**

```php
<?php
require_once '../../config.php';
require_once CLASSES_PATH . 'Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $admin_id = $_SESSION['admin_id'];
    
    // Validate
    $request = $db->fetchOne(
        "SELECT * FROM order_requests WHERE id = ? AND request_type = 'cancel' AND status = 'pending'",
        [$request_id]
    );
    
    if (!$request) {
        die(json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']));
    }
    
    $order_id = $request['order_id'];
    
    // TRANSACTION START
    $db->beginTransaction();
    
    try {
        // 1. UPDATE order status = 'cancelled'
        $db->execute(
            "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
            [$order_id]
        );
        
        // 2. Lấy chi tiết đơn để cộng stock
        $items = $db->fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?",
            [$order_id]
        );
        
        // 3. Cộng lại stock
        foreach ($items as $item) {
            $db->execute(
                "UPDATE products SET stock = stock + ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }
        
        // 4. UPDATE order_requests (approve)
        $db->execute(
            "UPDATE order_requests SET status = 'approved', admin_id = ?, reviewed_at = NOW() WHERE id = ?",
            [$admin_id, $request_id]
        );
        
        // 5. Hoàn tiền (nếu đã thanh toán)
        if ($order['payment_status'] == 'paid') {
            $db->execute(
                "UPDATE orders SET refund_amount = ?, refund_status = 'processing' WHERE id = ?",
                [$order['total_price'] + $order['shipping_fee'], $order_id]
            );
            // Gửi yêu cầu hoàn tiền đến payment gateway
            // processRefund($order);
        }
        
        // 6. INSERT vào order_status_history
        $db->execute(
            "INSERT INTO order_status_history (order_id, old_status, new_status, note, changed_by)
             VALUES (?, ?, 'cancelled', 'Admin phê duyệt hủy đơn', ?)",
            [$order_id, $order['status'], $admin_id]
        );
        
        // 7. Gửi email cho khách
        $customer = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$request['user_id']]);
        // sendCancelApprovedEmail($customer, $order);
        
        // TRANSACTION COMMIT
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Đơn hàng đã được hủy. Hoàn tiền đang xử lý'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
}
```

---

### **Bước 4: Admin Từ Chối Hủy (REJECTED)**

**File: api/admin/reject-cancel.php**

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $rejection_reason = $_POST['rejection_reason'];
    $admin_id = $_SESSION['admin_id'];
    
    // UPDATE request
    $db->execute(
        "UPDATE order_requests SET status = 'rejected', admin_id = ?, 
         reviewed_at = NOW(), rejection_reason = ? WHERE id = ?",
        [$admin_id, $rejection_reason, $request_id]
    );
    
    // Gửi email từ chối
    $request = $db->fetchOne("SELECT * FROM order_requests WHERE id = ?", [$request_id]);
    $customer = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$request['user_id']]);
    // sendCancelRejectedEmail($customer, $rejection_reason);
    
    echo json_encode(['success' => true, 'message' => 'Đã từ chối yêu cầu hủy']);
}
```

---

## 🔄 WORKFLOW HOÀN HÀNG (RETURN)

### **Timeline**

```
┌────────────────────────────────────────────────────────┐
│ Đơn hàng được giao (delivered)                         │
└─────────────────┬──────────────────────────────────────┘
                  │
          (Trong 30 ngày)
                  │
          ┌──────▼──────┐
          │ Khách gửi   │
          │ yêu cầu     │
          │ hoàn hàng   │
          └──────┬──────┘
                 │
    ┌────────────┴────────────┐
    ↓                         ↓
✅ APPROVED                ❌ REJECTED
    │                         │
├─ Khách gửi lại        └─ Email từ chối
│  hàng
│
├─ Admin nhận hàng
│  & kiểm tra
│
├─ UPDATE status
│  'returned'
│
├─ Cộng lại stock
│
├─ Hoàn tiền
│
└─ Đóng request
```

---

## 📋 LOGIC HOÀN HÀNG CHI TIẾT

### **Bước 1: Khách Hàng Gửi Yêu Cầu Hoàn**

**Điều Kiện:**
- ✅ Order status = "delivered"
- ✅ Trong 30 ngày sau khi giao
- ✅ Có lý do hoàn hàng

**Hành Động:**

```php
// File: api/return-order.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $reason = $_POST['reason'];  // VD: "Sản phẩm lỗi", "Không như mô tả"
    $description = $_POST['description'];
    $evidence_image = $_FILES['evidence'];  // Ảnh chứng minh
    
    // 1. CHECK order
    $order = $db->fetchOne(
        "SELECT * FROM orders WHERE id = ? AND user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    
    if ($order['status'] !== 'delivered') {
        die(json_encode([
            'success' => false, 
            'message' => 'Chỉ có thể hoàn hàng khi đã giao thành công'
        ]));
    }
    
    // 2. CHECK thời hạn (30 ngày)
    $delivered = strtotime($order['delivered_at']);
    $now = time();
    if ($now - $delivered > 2592000) {  // 30 ngày
        die(json_encode([
            'success' => false, 
            'message' => 'Quá 30 ngày, không thể hoàn hàng'
        ]));
    }
    
    // 3. Upload hình ảnh
    $evidence_path = null;
    if ($evidence_image['error'] === 0) {
        $evidence_path = uploadFile($evidence_image, 'public/return-evidence/');
    }
    
    // 4. INSERT request
    $db->execute(
        "INSERT INTO order_requests 
         (order_id, request_type, reason, description, user_id, evidence_image)
         VALUES (?, 'return', ?, ?, ?, ?)",
        [$order_id, $reason, $description, $_SESSION['user_id'], $evidence_path]
    );
    
    echo json_encode(['success' => true, 'message' => 'Yêu cầu hoàn hàng đã được gửi']);
}
```

---

### **Bước 2: Admin Xem Yêu Cầu Hoàn**

**Danh Sách**

```sql
SELECT 
  r.id,
  r.order_id,
  r.reason,
  r.description,
  r.evidence_image,
  r.status,
  r.requested_at,
  u.fullname as customer_name,
  u.email,
  u.phone,
  u.address,
  o.order_code,
  o.total_price,
  o.shipping_fee,
  GROUP_CONCAT(
    CONCAT(p.name, ' x', oi.quantity) 
    SEPARATOR ', '
  ) as products
FROM order_requests r
JOIN orders o ON r.order_id = o.id
JOIN users u ON r.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
WHERE r.request_type = 'return' AND r.status = 'pending'
GROUP BY r.id
ORDER BY r.requested_at DESC;
```

---

### **Bước 3: Admin Phê Duyệt Hoàn (APPROVED)**

**File: api/admin/approve-return.php**

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $admin_id = $_SESSION['admin_id'];
    
    // Validate
    $request = $db->fetchOne(
        "SELECT * FROM order_requests WHERE id = ? AND request_type = 'return'",
        [$request_id]
    );
    
    if (!$request || $request['status'] !== 'pending') {
        die(json_encode(['success' => false, 'message' => 'Invalid request']));
    }
    
    // TRANSACTION START
    $db->beginTransaction();
    
    try {
        $order_id = $request['order_id'];
        $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$order_id]);
        
        // 1. UPDATE orders status = 'returned'
        $db->execute(
            "UPDATE orders SET status = 'returned', updated_at = NOW() WHERE id = ?",
            [$order_id]
        );
        
        // 2. Cộng lại stock
        $items = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order_id]);
        foreach ($items as $item) {
            $db->execute(
                "UPDATE products SET stock = stock + ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }
        
        // 3. UPDATE order_requests
        $db->execute(
            "UPDATE order_requests SET status = 'approved', admin_id = ?, 
             reviewed_at = NOW(), refund_status = 'processing' WHERE id = ?",
            [$admin_id, $request_id]
        );
        
        // 4. Xử lý hoàn tiền
        $refund_amount = $order['total_price'] + $order['shipping_fee'];
        $db->execute(
            "UPDATE orders SET refund_amount = ?, refund_status = 'processing' WHERE id = ?",
            [$refund_amount, $order_id]
        );
        
        // Gọi payment gateway để hoàn tiền
        // processRefund($order, $refund_amount);
        
        // 5. INSERT vào order_status_history
        $db->execute(
            "INSERT INTO order_status_history (order_id, old_status, new_status, note, changed_by)
             VALUES (?, 'delivered', 'returned', 'Admin phê duyệt hoàn hàng', ?)",
            [$order_id, $admin_id]
        );
        
        // 6. Gửi email
        $customer = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$order['user_id']]);
        // sendReturnApprovedEmail($customer, $order);
        
        // COMMIT
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Phê duyệt hoàn hàng. Hãy yêu cầu khách gửi hàng về'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
```

---

### **Bước 4: Admin Từ Chối Hoàn (REJECTED)**

**File: api/admin/reject-return.php**

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $rejection_reason = $_POST['rejection_reason'];
    $admin_id = $_SESSION['admin_id'];
    
    // UPDATE
    $db->execute(
        "UPDATE order_requests SET status = 'rejected', admin_id = ?, 
         reviewed_at = NOW(), rejection_reason = ? WHERE id = ?",
        [$admin_id, $rejection_reason, $request_id]
    );
    
    // Gửi email từ chối
    $request = $db->fetchOne("SELECT * FROM order_requests WHERE id = ?", [$request_id]);
    // sendReturnRejectedEmail($customer, $rejection_reason);
    
    echo json_encode(['success' => true, 'message' => 'Đã từ chối yêu cầu hoàn hàng']);
}
```

---

## 🎨 HTML UI - KHÁCH HÀNG

### **File: pages/user/cancel-order.php**

```html
<?php
$order = $db->fetchOne("SELECT * FROM orders WHERE id = ? AND user_id = ?", 
    [$_GET['id'], $_SESSION['user_id']]);

if (!$order || !in_array($order['status'], ['pending', 'confirmed'])) {
    die('❌ Không thể hủy đơn này');
}

// Check thời hạn 24h
$created = strtotime($order['created_at']);
$hours_passed = (time() - $created) / 3600;
if ($hours_passed > 24) {
    die('❌ Quá 24 giờ, không thể hủy. Liên hệ support: ' . SUPPORT_EMAIL);
}
?>

<div class="cancel-form">
    <h2>🛑 Yêu Cầu Hủy Đơn Hàng</h2>
    
    <div class="order-info">
        <p><strong>Mã đơn:</strong> <?php echo $order['order_code']; ?></p>
        <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_price'] + $order['shipping_fee']); ?> ₫</p>
        <p><strong>Trạng thái:</strong> <?php echo $order['status']; ?></p>
        <p style="color: #ff6b6b;">⏰ Thời hạn hủy còn: 
            <?php echo round(24 - $hours_passed, 1); ?> giờ
        </p>
    </div>
    
    <form method="POST" action="../../api/cancel-order.php">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
        
        <div class="form-group">
            <label>Lý do hủy *</label>
            <select name="reason" required>
                <option value="">-- Chọn lý do --</option>
                <option value="Tôi không muốn mua nữa">Tôi không muốn mua nữa</option>
                <option value="Đơn hàng tạo nhầm">Đơn hàng tạo nhầm</option>
                <option value="Tìm được sản phẩm rẻ hơn">Tìm được sản phẩm rẻ hơn</option>
                <option value="Khác">Khác</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Chi tiết (tùy chọn)</label>
            <textarea name="description" rows="3" placeholder="Mô tả thêm lý do của bạn"></textarea>
        </div>
        
        <div class="alert alert-info">
            <strong>ℹ️ Lưu ý:</strong>
            <ul>
                <li>✅ Tiền sẽ được hoàn nếu đã thanh toán</li>
                <li>✅ Stock sẽ được cộng lại</li>
                <li>⏳ Admin sẽ xác nhận trong vòng 24 giờ</li>
                <li>📧 Bạn sẽ nhận email thông báo kết quả</li>
            </ul>
        </div>
        
        <button type="submit" class="btn btn-danger">🛑 Xác Nhận Hủy Đơn</button>
        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">Quay Lại</a>
    </form>
</div>
```

---

### **File: pages/user/return-order.php**

```html
<?php
$order = $db->fetchOne("SELECT * FROM orders WHERE id = ? AND user_id = ?", 
    [$_GET['id'], $_SESSION['user_id']]);

if ($order['status'] !== 'delivered') {
    die('❌ Chỉ có thể hoàn hàng khi đã giao thành công');
}

// Check 30 days
$delivered = strtotime($order['delivered_at']);
$days_passed = (time() - $delivered) / 86400;
if ($days_passed > 30) {
    die('❌ Quá 30 ngày, không thể hoàn hàng');
}
?>

<div class="return-form">
    <h2>↩️ Yêu Cầu Hoàn Hàng</h2>
    
    <div class="order-info">
        <p><strong>Mã đơn:</strong> <?php echo $order['order_code']; ?></p>
        <p><strong>Tổi tiền hoàn:</strong> <?php echo number_format($order['total_price'] + $order['shipping_fee']); ?> ₫</p>
        <p><strong>Ngày giao:</strong> <?php echo date('d/m/Y', strtotime($order['delivered_at'])); ?></p>
        <p style="color: #ff6b6b;">⏰ Hạn hoàn còn: 
            <?php echo round(30 - $days_passed, 1); ?> ngày
        </p>
    </div>
    
    <!-- Sản phẩm trong đơn -->
    <table class="products-table">
        <thead>
            <tr>
                <th>Sản Phẩm</th>
                <th>SL</th>
                <th>Giá</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $items = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
            foreach ($items as $item):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price']); ?> ₫</td>
                    <td><?php echo number_format($item['total_price']); ?> ₫</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <form method="POST" action="../../api/return-order.php" enctype="multipart/form-data">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
        
        <div class="form-group">
            <label>Lý do hoàn hàng *</label>
            <select name="reason" required>
                <option value="">-- Chọn lý do --</option>
                <option value="Sản phẩm lỗi">Sản phẩm lỗi / Hỏng</option>
                <option value="Không như mô tả">Không như mô tả</option>
                <option value="Kích thước/Màu sai">Kích thước / Màu sai</option>
                <option value="Giao nhầm sản phẩm">Giao nhầm sản phẩm</option>
                <option value="Khác">Khác</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Chi tiết vấn đề *</label>
            <textarea name="description" rows="4" required 
                      placeholder="Mô tả chi tiết vấn đề với sản phẩm..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Hình ảnh chứng minh *</label>
            <input type="file" name="evidence" accept="image/*" required>
            <small>📸 Tải lên hình ảnh sản phẩm lỗi/không đúng (jpg, png, max 5MB)</small>
        </div>
        
        <div class="alert alert-info">
            <strong>ℹ️ Quy Trình Hoàn Hàng:</strong>
            <ol>
                <li>Bạn gửi yêu cầu + hình ảnh chứng minh</li>
                <li>Admin kiểm tra và phê duyệt (24-48 giờ)</li>
                <li>Bạn gửi hàng lại (đơn bưu điện nặng lạng)</li>
                <li>Admin nhận hàng và kiểm tra</li>
                <li>Tiền được hoàn vào tài khoản (3-5 ngày làm việc)</li>
            </ol>
        </div>
        
        <div class="alert alert-warning">
            <strong>⚠️ Lưu Ý:</strong>
            <ul>
                <li>✅ Hoàn tiền toàn bộ: Sản phẩm + Phí ship</li>
                <li>📮 Chi phí gửi hàng lại: Khách tự chịu</li>
                <li>📷 Ảnh chứng minh bắt buộc</li>
                <li>📦 Hàng phải còn nguyên, chưa qua sử dụng</li>
            </ul>
        </div>
        
        <button type="submit" class="btn btn-warning">↩️ Gửi Yêu Cầu Hoàn Hàng</button>
        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">Quay Lại</a>
    </form>
</div>
```

---

## 👨‍💼 ADMIN PANEL

### **File: pages/admin/cancel-return-requests.php**

```html
<div class="requests-container">
    <h2>📋 Yêu Cầu Hủy & Hoàn Hàng</h2>
    
    <!-- Tabs -->
    <ul class="tabs">
        <li><a href="#pending">⏳ Chờ Xử Lý</a></li>
        <li><a href="#approved">✅ Đã Phê Duyệt</a></li>
        <li><a href="#rejected">❌ Đã Từ Chối</a></li>
    </ul>
    
    <!-- Yêu Cầu Chờ Xử Lý -->
    <div id="pending" class="tab-content">
        <h3>⏳ Chờ Xử Lý (<?php echo count($pending_requests); ?>)</h3>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Loại</th>
                    <th>Khách Hàng</th>
                    <th>Lý Do</th>
                    <th>Tổng Tiền</th>
                    <th>Ngày Gửi</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_requests as $req): ?>
                    <tr>
                        <td><strong><?php echo $req['order_code']; ?></strong></td>
                        <td>
                            <?php if ($req['request_type'] == 'cancel'): ?>
                                <span class="badge badge-danger">🛑 Hủy</span>
                            <?php else: ?>
                                <span class="badge badge-warning">↩️ Hoàn</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($req['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($req['reason']); ?></td>
                        <td><?php echo number_format($req['total_price']); ?> ₫</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($req['requested_at'])); ?></td>
                        <td>
                            <button class="btn-sm btn-success" onclick="approveRequest(<?php echo $req['id']; ?>)">
                                ✅ Phê Duyệt
                            </button>
                            <button class="btn-sm btn-danger" onclick="rejectRequest(<?php echo $req['id']; ?>)">
                                ❌ Từ Chối
                            </button>
                            <button class="btn-sm btn-info" onclick="viewDetails(<?php echo $req['id']; ?>)">
                                👁️ Chi Tiết
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Chi Tiết Request (Modal) -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <h3>📋 Chi Tiết Yêu Cầu</h3>
            
            <p><strong>Mã Đơn:</strong> <span id="detailOrderCode"></span></p>
            <p><strong>Khách:</strong> <span id="detailCustomer"></span></p>
            <p><strong>Loại:</strong> <span id="detailType"></span></p>
            <p><strong>Lý Do:</strong> <span id="detailReason"></span></p>
            <p><strong>Chi Tiết:</strong></p>
            <pre id="detailDescription"></pre>
            
            <!-- Cho hoàn hàng: Hình ảnh chứng minh -->
            <div id="evidenceDiv">
                <p><strong>Hình Ảnh Chứng Minh:</strong></p>
                <img id="detailEvidence" src="" style="max-width: 100%; max-height: 400px;">
            </div>
            
            <!-- Sản phẩm trong đơn -->
            <p><strong>Sản Phẩm:</strong></p>
            <table>
                <thead>
                    <tr><th>Tên</th><th>SL</th><th>Giá</th><th>Tổng</th></tr>
                </thead>
                <tbody id="detailItems">
                    <!-- AJAX load -->
                </tbody>
            </table>
            
            <!-- Form phê duyệt / từ chối -->
            <div class="form-group">
                <label>Lý Do Từ Chối (nếu cần):</label>
                <textarea id="rejectionReason" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
            </div>
            
            <div class="button-group">
                <button onclick="approveRequest()" class="btn btn-success">✅ Phê Duyệt</button>
                <button onclick="rejectRequest()" class="btn btn-danger">❌ Từ Chối</button>
                <button onclick="closeModal()" class="btn btn-secondary">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveRequest(requestId) {
    if (!confirm('Bạn chắc chắn phê duyệt yêu cầu này?')) return;
    
    fetch('../../api/admin/approve-cancel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `request_id=${requestId}`
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}

function rejectRequest(requestId) {
    const reason = prompt('Lý do từ chối:');
    if (!reason) return;
    
    fetch('../../api/admin/reject-cancel.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `request_id=${requestId}&rejection_reason=${encodeURIComponent(reason)}`
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
</script>
```

---

## 📊 DATABASE QUERIES TÓMLƯỢC

```sql
-- Danh sách yêu cầu chờ xử lý
SELECT * FROM order_requests WHERE status = 'pending' ORDER BY requested_at DESC;

-- Danh sách yêu cầu hoàn
SELECT * FROM order_requests WHERE request_type = 'return' AND status = 'approved';

-- Cộng lại stock khi hủy/hoàn
UPDATE products SET stock = stock + 5 WHERE id = 1;

-- Cập nhật trạng thái hoàn tiền
UPDATE orders SET refund_status = 'completed' WHERE id = 1;

-- Kiểm tra yêu cầu chưa xử lý
SELECT COUNT(*) FROM order_requests WHERE status = 'pending';
```

---

## 🎯 EMAIL TEMPLATES

### **Email: Yêu Cầu Hủy Được Phê Duyệt**

```
Xin chào {CUSTOMER_NAME},

Yêu cầu hủy đơn hàng {ORDER_CODE} của bạn đã được phê duyệt ✅

📋 Chi Tiết:
- Mã Đơn: {ORDER_CODE}
- Số Tiền Hoàn: {REFUND_AMOUNT} ₫
- Trạng Thái Hoàn Tiền: Đang Xử Lý

💳 Tiền sẽ được hoàn vào tài khoản của bạn trong vòng 3-5 ngày làm việc.

📞 Nếu có câu hỏi, hãy liên hệ: {SUPPORT_EMAIL}

Cảm ơn bạn đã sử dụng StudentGear!
```

### **Email: Yêu Cầu Hoàn Được Phê Duyệt**

```
Xin chào {CUSTOMER_NAME},

Yêu cầu hoàn hàng {ORDER_CODE} của bạn đã được phê duyệt ✅

📦 Vui lòng gửi hàng lại đến:

StudentGear Logistics
Địa chỉ: {RETURN_ADDRESS}
SĐT: {RETURN_PHONE}

⏰ Mã Hoàn: {RETURN_CODE}
💰 Số Tiền Hoàn: {REFUND_AMOUNT} ₫
📮 Chi Phí Gửi: Bạn tự chịu

📧 Vui lòng gửi ảnh hoá đơn vận chuyển để chúng tôi theo dõi.

Cảm ơn bạn!
```

---

## ✅ CHECKLIST TRIỂN KHAI

- [ ] Tạo bảng `order_requests` trong database
- [ ] Thêm cột vào bảng `orders` (can_cancel, can_return, refund_status)
- [ ] Tạo file `api/cancel-order.php`
- [ ] Tạo file `api/return-order.php`
- [ ] Tạo file admin API (approve/reject)
- [ ] Tạo UI khách hàng (cancel/return form)
- [ ] Tạo admin panel xem yêu cầu
- [ ] Tạo email templates
- [ ] Test logic hủy & hoàn
- [ ] Test refund/cộng stock
- [ ] Deploy lên production

---

**Vậy là bạn đã có logic xử lý hủy hàng và hoàn hàng hoàn chỉnh! 🎉**

