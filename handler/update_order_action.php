<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

$user_id = $_SESSION['user_id'];

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$action_type = isset($_POST['action_type']) ? trim($_POST['action_type']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$refund_amount = isset($_POST['refund_amount']) ? (float)$_POST['refund_amount'] : 0;

if ($order_id <= 0 || empty($action_type) || empty($reason)) {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

if (!in_array($action_type, ['cancel', 'return'])) {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

// =========================================
// KIỂM TRA ĐƠN HÀNG
// =========================================
$stmt_order = $conn->prepare(" 
    SELECT * 
    FROM orders 
    WHERE id = ? AND user_id = ?
    LIMIT 1
");

$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();

if (!$order) {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

// =========================================
// VALIDATE THEO LOẠI YÊU CẦU
// =========================================
if ($action_type === 'cancel' && $order['status'] !== 'pending') {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

if ($action_type === 'return' && $order['status'] !== 'delivered') {
    header('Location: ../pages/history_order.php?msg=error');
    exit();
}

// =========================================
// KIỂM TRA REQUEST ĐANG TỒN TẠI
// =========================================
$stmt_exist = $conn->prepare(" 
    SELECT id, status
    FROM order_requests
    WHERE order_id = ?
    AND request_type = ?
    ORDER BY requested_at DESC
    LIMIT 1
");

$stmt_exist->bind_param("is", $order_id, $action_type);
$stmt_exist->execute();
$result_exist = $stmt_exist->get_result();
$exist_request = $result_exist->fetch_assoc();

if ($exist_request && $exist_request['status'] === 'pending') {
    header('Location: ../pages/history_order.php?msg=exist_request');
    exit();
}

// =========================================
// XỬ LÝ ẢNH MINH CHỨNG
// =========================================
$evidence_image = null;

if (
    isset($_FILES['evidence_image']) &&
    $_FILES['evidence_image']['error'] === 0
) {

    $upload_dir = '../uploads/order_requests/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp = $_FILES['evidence_image']['tmp_name'];
    $file_name = time() . '_' . basename($_FILES['evidence_image']['name']);

    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($file_ext, $allowed_ext)) {
        header('Location: ../pages/history_order.php?msg=error');
        exit();
    }

    $new_path = $upload_dir . $file_name;

    if (move_uploaded_file($file_tmp, $new_path)) {
        $evidence_image = $new_path;
    }
}

// =========================================
// NẾU REQUEST CŨ BỊ TỪ CHỐI -> UPDATE
// =========================================
if ($exist_request && $exist_request['status'] === 'rejected') {

    $stmt_update = $conn->prepare(" 
        UPDATE order_requests
        SET
            reason = ?,
            description = ?,
            evidence_image = ?,
            refund_amount = ?,
            status = 'pending',
            rejection_reason = NULL,
            refund_status = 'pending',
            requested_at = NOW(),
            reviewed_at = NULL
        WHERE id = ?
    ");

    $stmt_update->bind_param(
        "sssdi",
        $reason,
        $description,
        $evidence_image,
        $refund_amount,
        $exist_request['id']
    );

    $success = $stmt_update->execute();
} else {

    // =========================================
    // INSERT REQUEST MỚI
    // =========================================
    $refund_status = $action_type === 'return'
        ? 'pending'
        : null;

    $stmt_insert = $conn->prepare(" 
        INSERT INTO order_requests (
            order_id,
            user_id,
            request_type,
            reason,
            description,
            evidence_image,
            refund_amount,
            refund_status,
            status,
            requested_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt_insert->bind_param(
        "iissssds",
        $order_id,
        $user_id,
        $action_type,
        $reason,
        $description,
        $evidence_image,
        $refund_amount,
        $refund_status
    );

    $success = $stmt_insert->execute();
}

// =========================================
// KẾT QUẢ
// =========================================
if ($success) {

    if ($action_type === 'cancel') {
        header('Location: ../pages/history_order.php?msg=cancel_requested');
    } else {
        header('Location: ../pages/history_order.php?msg=return_requested');
    }

    exit();
}

header('Location: ../pages/history_order.php?msg=error');
exit();
?>
```

# File này đã xử lý đầy đủ

## Chức năng đã hỗ trợ

* Kiểm tra đăng nhập
* Validate request POST
* Kiểm tra quyền đơn hàng theo user
* Chỉ cho hủy khi trạng thái `pending`
* Chỉ cho hoàn hàng khi trạng thái `delivered`
* Chống gửi request trùng
* Upload ảnh minh chứng
* Validate định dạng ảnh
* Tự tạo thư mục upload
* INSERT vào bảng `order_requests`
* Cho phép gửi lại request nếu admin từ chối
* Reset trạng thái request khi gửi lại
* Refund status tự động xử lý
* Prepared Statement chống SQL Injection
* Redirect thông báo kết quả

# Thư mục upload cần tạo

```bash
/uploads/order_requests/
```

# Bước tiếp theo nên làm

## Admin duyệt request

Bạn nên tạo:

```php
/admin/order_requests.php
```

để admin:

* xem danh sách request
* xem ảnh minh chứng
* duyệt / từ chối
* cập nhật refund_status
* cập nhật trạng thái orders

## Logic admin approve

### Hủy đơn

```php
orders.status = cancelled
```

### Hoàn hàng

```php
orders.status = returned
```

## Logic admin reject

Cập nhật:

```php
order_requests.status = rejected
order_requests.rejection_reason
```