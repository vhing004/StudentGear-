<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$tab = isset($_GET['tab'])
    ? trim($_GET['tab'])
    : 'orders';

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$status_filter = isset($_GET['status_filter'])
    ? trim($_GET['status_filter'])
    : '';

$target_user_id = isset($_GET['user_id'])
    ? (int)$_GET['user_id']
    : 0;

// =====================================================
// UPDATE ORDER STATUS
// =====================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'update_status'
) {

    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);
    $note = trim($_POST['note'] ?? '');
    $admin_id = $_SESSION['user_id'];

    $stmt_old = $conn->prepare("
        SELECT status
        FROM orders
        WHERE id = ?
    ");

    $stmt_old->bind_param("i", $order_id);
    $stmt_old->execute();

    $old_status = $stmt_old
        ->get_result()
        ->fetch_assoc()['status'];

    $payment_update = '';

    if ($new_status === 'delivered') {
        $payment_update = ",
            payment_status = 'paid',
            delivered_at = NOW()";
    }

    $sql_update = "
        UPDATE orders
        SET status = ?
        $payment_update
        WHERE id = ?
    ";

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_status, $order_id);

    if ($stmt_update->execute()) {

        $stmt_history = $conn->prepare("
            INSERT INTO order_status_history (
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
            $note,
            $admin_id
        );

        $stmt_history->execute();
    }

    header("Location: orders.php?tab=orders&msg=updated");
    exit();
}

// =====================================================
// APPROVE REQUEST
// =====================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'approve_request'
) {

    $request_id = (int)$_POST['request_id'];
    $admin_id = $_SESSION['user_id'];

    $stmt_request = $conn->prepare("
        SELECT *
        FROM order_requests
        WHERE id = ?
        LIMIT 1
    ");

    $stmt_request->bind_param("i", $request_id);
    $stmt_request->execute();

    $request = $stmt_request
        ->get_result()
        ->fetch_assoc();

    if ($request) {

        $stmt_approve = $conn->prepare("
            UPDATE order_requests
            SET
                status = 'approved',
                admin_id = ?,
                reviewed_at = NOW()
            WHERE id = ?
        ");

        $stmt_approve->bind_param(
            "ii",
            $admin_id,
            $request_id
        );

        $stmt_approve->execute();

        $new_order_status =
            $request['request_type'] === 'cancel'
            ? 'cancelled'
            : 'returned';

        $stmt_order = $conn->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        $stmt_order->bind_param(
            "si",
            $new_order_status,
            $request['order_id']
        );

        $stmt_order->execute();

        // history
        $stmt_history = $conn->prepare("
            INSERT INTO order_status_history (
                order_id,
                old_status,
                new_status,
                note,
                changed_by
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $note = $request['request_type'] === 'cancel'
            ? 'Admin đã duyệt yêu cầu hủy đơn'
            : 'Admin đã duyệt yêu cầu trả hàng';

        $stmt_history->bind_param(
            "isssi",
            $request['order_id'],
            $request['order_type'],
            $new_order_status,
            $note,
            $admin_id
        );

        $stmt_history->execute();
    }

    header("Location: orders.php?tab=requests&msg=approved");
    exit();
}

// =====================================================
// REJECT REQUEST
// =====================================================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'reject_request'
) {

    $request_id = (int)$_POST['request_id'];
    $reject_reason = trim($_POST['reject_reason']);
    $admin_id = $_SESSION['user_id'];

    $stmt_reject = $conn->prepare("
        UPDATE order_requests
        SET
            status = 'rejected',
            rejection_reason = ?,
            admin_id = ?,
            reviewed_at = NOW()
        WHERE id = ?
    ");

    $stmt_reject->bind_param(
        "sii",
        $reject_reason,
        $admin_id,
        $request_id
    );

    $stmt_reject->execute();

    header("Location: orders.php?tab=requests&msg=rejected");
    exit();
}

// =====================================================
// QUERY TAB ORDERS
// =====================================================
if ($tab === 'orders') {

    $sql = "
        SELECT
            o.*,
            u.fullname
        FROM orders o
        LEFT JOIN users u
            ON o.user_id = u.id
        WHERE o.status IN (
            'pending',
            'confirmed',
            'shipping',
            'delivered'
        )
    ";

    if ($target_user_id > 0) {
        $sql .= " AND o.user_id = $target_user_id";
    }

    if (!empty($search)) {
        $sql .= "
            AND (
                o.order_code LIKE '%$search%'
                OR o.shipping_phone LIKE '%$search%'
                OR o.shipping_name LIKE '%$search%'
                OR u.fullname LIKE '%$search%'
            )
        ";
    }

    if (!empty($status_filter)) {
        $sql .= " AND o.status = '$status_filter'";
    }

    $sql .= " ORDER BY o.created_at DESC";

    $orders = $conn->query($sql);
} else {

    // =====================================================
    // QUERY TAB REQUESTS
    // =====================================================
    $sql = "
        SELECT
            r.*,
            o.order_code,
            o.status AS order_status,
            o.total_price,
            u.fullname,
            u.email
        FROM order_requests r
        JOIN orders o
            ON r.order_id = o.id
        JOIN users u
            ON r.user_id = u.id
        WHERE 1=1
    ";

    if (!empty($search)) {
        $sql .= "
            AND (
                o.order_code LIKE '%$search%'
                OR u.fullname LIKE '%$search%'
                OR u.email LIKE '%$search%'
            )
        ";
    }

    if (!empty($status_filter)) {
        $sql .= " AND r.status = '$status_filter'";
    }

    $sql .= " ORDER BY r.requested_at DESC";

    $orders = $conn->query($sql);
}

// =====================================================
// BADGE STATUS
// =====================================================
function getStatusBadge($status)
{
    switch ($status) {

        case 'pending':
            return "<span class='status-badge badge-warning'>Chờ xử lý</span>";

        case 'confirmed':
            return "<span class='status-badge badge-info'>Đã xác nhận</span>";

        case 'shipping':
            return "<span class='status-badge badge-primary'>Đang giao</span>";

        case 'delivered':
            return "<span class='status-badge badge-success'>Đã giao</span>";

        case 'cancelled':
            return "<span class='status-badge badge-danger'>Đã hủy</span>";

        case 'returned':
            return "<span class='status-badge badge-danger'>Trả hàng</span>";

        case 'approved':
            return "<span class='status-badge badge-success'>Đã duyệt</span>";

        case 'rejected':
            return "<span class='status-badge badge-danger'>Từ chối</span>";

        default:
            return "<span class='status-badge badge-secondary'>$status</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet"
        href="../../assets/css/admin.css">

    <style>
        .admin-tabs {
            display: flex;
            gap: 12px;
            margin: 20px 0;
        }

        .admin-tabs a {
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            background: #f1f1f1;
            color: #333;
            font-weight: 600;
        }

        .admin-tabs a.active {
            background: #2563eb;
            color: #fff;
        }

        .request-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .request-reason {
            max-width: 220px;
            line-height: 1.5;
        }

        .request-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-success {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body class="admin-body">

    <div class="admin-wrapper">

        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">

            <header class="main-content__header">

                <h2>
                    <?= $tab === 'orders'
                        ? 'Quản lý đơn hàng'
                        : 'Yêu cầu hoàn / hủy hàng' ?>
                </h2>

                <div class="header-actions">

                    <form method="GET"
                        class="search-form"
                        style="display:flex;gap:10px;">

                        <input type="hidden"
                            name="tab"
                            value="<?= $tab ?>">

                        <div class="auth-form__group"
                            style="margin-bottom:0;">

                            <input type="text"
                                name="search"
                                class="auth-form__input"
                                placeholder="Tìm kiếm..."
                                value="<?= htmlspecialchars($search) ?>">
                        </div>

                        <div class="auth-form__group"
                            style="margin-bottom:0;">

                            <select name="status_filter"
                                class="auth-form__input"
                                onchange="this.form.submit()">

                                <option value="">
                                    Tất cả trạng thái
                                </option>

                                <?php if ($tab === 'orders'): ?>

                                    <option value="pending">
                                        Chờ xử lý
                                    </option>

                                    <option value="confirmed">
                                        Đã xác nhận
                                    </option>

                                    <option value="shipping">
                                        Đang giao
                                    </option>

                                    <option value="delivered">
                                        Đã giao
                                    </option>

                                <?php else: ?>

                                    <option value="pending">
                                        Chờ duyệt
                                    </option>

                                    <option value="approved">
                                        Đã duyệt
                                    </option>

                                    <option value="rejected">
                                        Đã từ chối
                                    </option>

                                <?php endif; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </header>

            <div class="admin-tabs">

                <a href="?tab=orders"
                    class="<?= $tab === 'orders' ? 'active' : '' ?>">

                    <i class="fas fa-box"></i>
                    Quản lý đơn hàng
                </a>

                <a href="?tab=requests"
                    class="<?= $tab === 'requests' ? 'active' : '' ?>">

                    <i class="fas fa-undo"></i>
                    Yêu cầu hoàn / hủy
                </a>
            </div>

            <section class="table-container">

                <table class="table">

                    <?php if ($tab === 'orders'): ?>

                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($orders->num_rows > 0): ?>

                                <?php while ($row = $orders->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            <strong>
                                                #<?= $row['order_code'] ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $row['fullname']
                                                    ?? $row['shipping_name']
                                            ) ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= number_format(
                                                    $row['total_price'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ) ?>đ
                                            </strong>
                                        </td>

                                        <td>
                                            <?= strtoupper(
                                                $row['payment_method']
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= getStatusBadge($row['status']) ?>
                                        </td>

                                        <td>
                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime($row['created_at'])
                                            ) ?>
                                        </td>

                                        <td>

                                            <button
                                                class="action-link"
                                                onclick='openEditOrderModal(<?= json_encode($row) ?>)'>

                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php endif; ?>
                        </tbody>

                    <?php else: ?>

                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Loại yêu cầu</th>
                                <th>Lý do</th>
                                <th>Ảnh</th>
                                <th>Hoàn tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($orders->num_rows > 0): ?>

                                <?php while ($row = $orders->fetch_assoc()): ?>

                                    <tr>

                                        <td>
                                            #<?= $row['order_code'] ?>
                                        </td>

                                        <td>
                                            <div>
                                                <strong>
                                                    <?= htmlspecialchars($row['fullname']) ?>
                                                </strong>

                                                <br>

                                                <small>
                                                    <?= htmlspecialchars($row['email']) ?>
                                                </small>
                                            </div>
                                        </td>

                                        <td>
                                            <?= $row['request_type'] === 'cancel'
                                                ? 'Hủy đơn'
                                                : 'Trả hàng' ?>
                                        </td>

                                        <td>
                                            <div class="request-reason">

                                                <strong>
                                                    <?= htmlspecialchars($row['reason']) ?>
                                                </strong>

                                                <br><br>

                                                <small>
                                                    <?= htmlspecialchars(
                                                        $row['description']
                                                    ) ?>
                                                </small>
                                            </div>
                                        </td>

                                        <td>

                                            <?php if (!empty($row['evidence_image'])): ?>

                                                <a href="<?= htmlspecialchars($row['evidence_image']) ?>"
                                                    target="_blank">

                                                    <img src="<?= htmlspecialchars($row['evidence_image']) ?>"
                                                        class="request-image">
                                                </a>

                                            <?php else: ?>

                                                Không có

                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= number_format(
                                                $row['refund_amount'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>đ
                                        </td>

                                        <td>
                                            <?= getStatusBadge($row['status']) ?>
                                        </td>

                                        <td>
                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime($row['requested_at'])
                                            ) ?>
                                        </td>

                                        <td>

                                            <?php if ($row['status'] === 'pending'): ?>

                                                <div class="request-actions">

                                                    <form method="POST">

                                                        <input type="hidden"
                                                            name="action"
                                                            value="approve_request">

                                                        <input type="hidden"
                                                            name="request_id"
                                                            value="<?= $row['id'] ?>">

                                                        <button type="submit"
                                                            class="btn-success">
                                                            Duyệt
                                                        </button>
                                                    </form>

                                                    <button
                                                        type="button"
                                                        class="btn-danger"
                                                        onclick="openRejectModal(<?= $row['id'] ?>)">
                                                        Từ chối
                                                    </button>
                                                </div>

                                            <?php else: ?>

                                                Đã xử lý

                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php endif; ?>
                        </tbody>

                    <?php endif; ?>
                </table>
            </section>
        </main>
    </div>

    <!-- EDIT ORDER MODAL -->
    <div id="editOrderModal" class="modal">

        <div class="modal__content">

            <h3>
                Cập nhật đơn hàng
            </h3>

            <form method="POST">

                <input type="hidden"
                    name="action"
                    value="update_status">

                <input type="hidden"
                    name="order_id"
                    id="edit_order_id">

                <div class="auth-form__group">

                    <label>
                        Trạng thái mới
                    </label>

                    <select name="status"
                        id="edit_order_status"
                        class="auth-form__input">

                        <option value="pending">
                            Chờ xử lý
                        </option>

                        <option value="confirmed">
                            Đã xác nhận
                        </option>

                        <option value="shipping">
                            Đang giao hàng
                        </option>

                        <option value="delivered">
                            Đã giao hàng
                        </option>
                    </select>
                </div>

                <div class="auth-form__group">

                    <label>
                        Ghi chú
                    </label>

                    <textarea name="note"
                        class="auth-form__input"
                        rows="4"></textarea>
                </div>

                <div class="modal__footer">

                    <button type="button"
                        class="btn-secondary"
                        onclick="closeModal('editOrderModal')">
                        Hủy
                    </button>

                    <button type="submit"
                        class="btn-primary">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- REJECT MODAL -->
    <div id="rejectModal" class="modal">

        <div class="modal__content">

            <h3>
                Từ chối yêu cầu
            </h3>

            <form method="POST">

                <input type="hidden"
                    name="action"
                    value="reject_request">

                <input type="hidden"
                    name="request_id"
                    id="reject_request_id">

                <div class="auth-form__group">

                    <label>
                        Lý do từ chối
                    </label>

                    <textarea name="reject_reason"
                        class="auth-form__input"
                        rows="4"
                        required></textarea>
                </div>

                <div class="modal__footer">

                    <button type="button"
                        class="btn-secondary"
                        onclick="closeModal('rejectModal')">
                        Đóng
                    </button>

                    <button type="submit"
                        class="btn-danger">
                        Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/modal_admin.js"></script>

    <script>
        function openEditOrderModal(order) {

            document.getElementById('edit_order_id').value = order.id;

            document.getElementById('edit_order_status').value = order.status;

            document.getElementById('editOrderModal').style.display = 'flex';
        }

        function openRejectModal(requestId) {

            document.getElementById('reject_request_id').value = requestId;

            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeModal(modalId) {

            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {

            const editModal = document.getElementById('editOrderModal');
            const rejectModal = document.getElementById('rejectModal');

            if (event.target === editModal) {
                editModal.style.display = 'none';
            }

            if (event.target === rejectModal) {
                rejectModal.style.display = 'none';
            }
        }
    </script>

</body>

</html>