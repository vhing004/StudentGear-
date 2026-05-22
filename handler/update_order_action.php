<?php
session_start();
require_once '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate dữ liệu
    if (
        !isset($_POST['order_id']) ||
        !isset($_POST['action_type']) ||
        !isset($_POST['reason'])
    ) {
        header("Location: ../pages/history_order.php?msg=invalid_data");
        exit();
    }

    $order_id = (int) $_POST['order_id'];
    $action_type = trim($_POST['action_type']);
    $reason = trim($_POST['reason']);
    $user_id = $_SESSION['user_id'];

    // Validate action
    $allowed_actions = ['cancel', 'return'];

    if (!in_array($action_type, $allowed_actions)) {
        header("Location: ../pages/history_order.php?msg=invalid_action");
        exit();
    }

    // Validate reason
    if (empty($reason)) {
        header("Location: ../pages/history_order.php?msg=empty_reason");
        exit();
    }

    // Giới hạn độ dài lý do
    if (strlen($reason) > 500) {
        header("Location: ../pages/history_order.php?msg=reason_too_long");
        exit();
    }

    // Kiểm tra đơn hàng thuộc user
    $stmt = $conn->prepare("
        SELECT id, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: ../pages/history_order.php?msg=order_not_found");
        exit();
    }

    $order = $result->fetch_assoc();

    $old_status = $order['status'];

    $new_status = '';
    $history_note = '';
    $success_msg = '';

    // Xử lý hủy đơn
    if ($action_type === 'cancel') {

        // Chỉ được hủy khi pending
        if ($old_status !== 'pending') {
            header("Location: ../pages/history_order.php?msg=cannot_cancel");
            exit();
        }

        $new_status = 'cancelled';

        $history_note = "Khách hàng yêu cầu hủy đơn. Lý do: " . $reason;

        $success_msg = 'cancel_success';
    }

    // Xử lý hoàn hàng
    elseif ($action_type === 'return') {

        // Chỉ được hoàn khi delivered
        if ($old_status !== 'delivered') {
            header("Location: ../pages/history_order.php?msg=cannot_return");
            exit();
        }

        $new_status = 'returned';

        $history_note = "Khách hàng yêu cầu hoàn hàng. Lý do: " . $reason;

        $success_msg = 'return_success';
    }

    // Nếu không có trạng thái mới
    if (empty($new_status)) {
        header("Location: ../pages/history_order.php?msg=invalid_status");
        exit();
    }

    // Transaction
    $conn->begin_transaction();

    try {

        // Cập nhật orders
        $stmt_update = $conn->prepare("
            UPDATE orders
            SET 
                status = ?,
                cancelled_reason = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt_update->bind_param(
            "ssi",
            $new_status,
            $reason,
            $order_id
        );

        if (!$stmt_update->execute()) {
            throw new Exception($stmt_update->error);
        }

        /**
         * changed_by:
         * NULL vì user không thuộc admin_users
         */
        $changed_by = null;

        // Ghi lịch sử trạng thái
        $stmt_history = $conn->prepare("
            INSERT INTO order_status_history
            (
                order_id,
                old_status,
                new_status,
                note,
                changed_by
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt_history->bind_param(
            "isssi",
            $order_id,
            $old_status,
            $new_status,
            $history_note,
            $changed_by
        );

        if (!$stmt_history->execute()) {
            throw new Exception($stmt_history->error);
        }

        // Commit
        $conn->commit();

        header("Location: ../pages/history_order.php?status=all&msg=" . $success_msg);
        exit();
    } catch (Exception $e) {

        // Rollback nếu lỗi
        $conn->rollback();

        // Log lỗi
        error_log("Order Action Error: " . $e->getMessage());

        header("Location: ../pages/history_order.php?msg=system_error");
        exit();
    }
} else {

    header("Location: ../pages/history_order.php?msg=invalid_request");
    exit();
}
