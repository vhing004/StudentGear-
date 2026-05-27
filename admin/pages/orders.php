<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra quyền hạn bảo mật hệ thống
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'orders';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';
$target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// =====================================================
// HOẠT ĐỘNG 1: CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG THỦ CÔNG (TAB ORDERS)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status']);
    $note = trim($_POST['note'] ?? '');

    // 1. Lấy trạng thái hiện tại từ DB để kiểm tra tính hợp lệ của luồng đi
    $stmt_old = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt_old->bind_param("i", $order_id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result()->fetch_assoc();

    if ($res_old) {
        $old_status = $res_old['status'];

        // Cấu hình trọng số tiến trình trạng thái để ngăn chặn hạ cấp dữ liệu ngược về quá khứ
        $status_weights = [
            'pending'   => 1,
            'confirmed' => 2,
            'shipping'  => 3,
            'delivered' => 4,
            'cancelled' => 5,
            'returned'  => 5
        ];

        // Nếu cố tình chuyển trạng thái có trọng số thấp hơn trạng thái hiện tại => Chặn đứng ngay
        if ($status_weights[$new_status] < $status_weights[$old_status]) {
            header("Location: orders.php?tab=orders&msg=error_rollback");
            exit();
        }

        // Thực thi cập nhật trạng thái mới hợp lệ (Đồng thời cập nhật trường cập nhật thời gian)
        $payment_update = '';
        if ($new_status === 'delivered') {
            $payment_update = ", payment_status = 'paid', delivered_at = NOW()";
        }

        $sql_update = "UPDATE orders SET status = ?, updated_at = NOW() $payment_update WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_status, $order_id);

        if ($stmt_update->execute()) {
            // Lưu lịch sử biến động trạng thái
            $stmt_history = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, note, changed_by) VALUES (?, ?, ?, ?, ?)");
            $stmt_history->bind_param("isssi", $order_id, $old_status, $new_status, $note, $admin_id);
            $stmt_history->execute();
        }
    }

    header("Location: orders.php?tab=orders&msg=success");
    exit();
}

// =====================================================
// HOẠT ĐỘNG 2: DUYỆT YÊU CẦU HOÀN HỦY TỪ KHÁCH HÀNG (TAB REQUESTS)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve_request') {
    $request_id = (int)$_POST['request_id'];
    $order_id = (int)$_POST['order_id'];
    $request_type = trim($_POST['request_type']);

    $conn->begin_transaction();
    try {
        // Lấy trạng thái hiện hành của đơn hàng làm căn cứ ghi lịch sử log
        $stmt_current = $conn->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt_current->bind_param("i", $order_id);
        $stmt_current->execute();
        $old_order_status = $stmt_current->get_result()->fetch_assoc()['status'] ?? 'pending';

        // Xác định trạng thái cập nhật tiếp theo dựa trên loại yêu cầu khiếu nại
        $target_order_status = ($request_type === 'cancel') ? 'cancelled' : 'returned';
        $note_history = ($request_type === 'cancel') ? 'Admin đã duyệt yêu cầu hủy đơn hàng.' : 'Admin đã duyệt yêu cầu hoàn trả hàng hóa.';

        // Trạng thái hoàn tiền (Chỉ cập nhật processing nếu là hoàn trả hàng, hủy đơn thì chuyển thẳng thành completed hoặc giữ nguyên)
        $refund_status_update = ($request_type === 'return') ? 'processing' : 'completed';

        // Bước A: Cập nhật phiếu yêu cầu sang trạng thái đã duyệt
        $stmt_approve = $conn->prepare("UPDATE order_requests SET status = 'approved', admin_id = ?, reviewed_at = NOW(), refund_status = ? WHERE id = ?");
        $stmt_approve->bind_param("isi", $admin_id, $refund_status_update, $request_id);
        $stmt_approve->execute();

        // Bước B: Chuyển đổi trạng thái đơn hàng gốc đồng bộ theo phiếu duyệt
        $stmt_order = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt_order->bind_param("si", $target_order_status, $order_id);
        $stmt_order->execute();

        // Bước C: Bắn dữ liệu log vào bảng lịch sử đơn hàng
        $stmt_history = $conn->prepare("INSERT INTO order_status_history (order_id, old_status, new_status, note, changed_by) VALUES (?, ?, ?, ?, ?)");
        $stmt_history->bind_param("isssi", $order_id, $old_order_status, $target_order_status, $note_history, $admin_id);
        $stmt_history->execute();

        $conn->commit();
        header("Location: orders.php?tab=requests&msg=approve_success");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: orders.php?tab=requests&msg=error");
        exit();
    }
}

// =====================================================
// HOẠT ĐỘNG 3: TỪ CHỐI YÊU CẦU HOÀN HỦY (ĐÃ SỬA LỖI FATAL)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject_request') {
    $request_id = (int)$_POST['request_id'];
    $reject_reason = trim($_POST['reject_reason']);

    // SỬA LỖI: Sử dụng "sii" thay vì "siii" vì chỉ có 3 biến: $reject_reason (s), $admin_id (i), $request_id (i)
    $stmt_reject = $conn->prepare("UPDATE order_requests SET status = 'rejected', rejection_reason = ?, admin_id = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt_reject->bind_param("sii", $reject_reason, $admin_id, $request_id);
    $stmt_reject->execute();

    header("Location: orders.php?tab=requests&msg=reject_success");
    exit();
}

// =====================================================
// TRUY XUẤT VÀ CHUẨN HÓA DỮ LIỆU ĐỌC ĐỒNG BỘ (SẮP XẾP LÊN ĐẦU KHI THAY ĐỔI)
// =====================================================
$search_param = "%$search%";
if ($tab === 'requests') {
    $sql = "SELECT r.*, o.order_code, o.status AS order_status, o.total_price, u.fullname, u.email 
            FROM order_requests r 
            JOIN orders o ON r.order_id = o.id 
            JOIN users u ON r.user_id = u.id WHERE 1=1";
    if (!empty($search)) {
        $sql .= " AND (o.order_code LIKE ? OR u.fullname LIKE ? OR u.email LIKE ?)";
    }
    if (!empty($status_filter)) {
        $sql .= " AND r.status = ?";
    }

    // Đẩy các yêu cầu vừa được xem xét (reviewed_at) hoặc vừa gửi lên đầu tiên
    $sql .= " ORDER BY COALESCE(r.reviewed_at, r.requested_at) DESC, r.requested_at DESC";

    $stmt_list = $conn->prepare($sql);
    if (!empty($search) && !empty($status_filter)) {
        $stmt_list->bind_param("ssss", $search_param, $search_param, $search_param, $status_filter);
    } elseif (!empty($search)) {
        $stmt_list->bind_param("sss", $search_param, $search_param, $search_param);
    } elseif (!empty($status_filter)) {
        $stmt_list->bind_param("s", $status_filter);
    }
    $stmt_list->execute();
    $orders_result = $stmt_list->get_result();
} else {
    // Tab 'orders' truyền thống
    $sql = "SELECT o.*, u.fullname FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
    if ($target_user_id > 0) {
        $sql .= " AND o.user_id = ?";
    }
    if (!empty($search)) {
        $sql .= " AND (o.order_code LIKE ? OR o.shipping_phone LIKE ? OR o.shipping_name LIKE ? OR u.fullname LIKE ?)";
    }
    if (!empty($status_filter)) {
        $sql .= " AND o.status = ?";
    }

    // Đẩy các đơn hàng vừa được thay đổi trạng thái (updated_at) hoặc đơn hàng mới lên đầu
    $sql .= " ORDER BY COALESCE(o.updated_at, o.created_at) DESC, o.created_at DESC";

    $stmt_list = $conn->prepare($sql);
    if ($target_user_id > 0 && !empty($search) && !empty($status_filter)) {
        $stmt_list->bind_param("isssss", $target_user_id, $search_param, $search_param, $search_param, $search_param, $status_filter);
    } elseif ($target_user_id > 0 && !empty($search)) {
        $stmt_list->bind_param("issss", $target_user_id, $search_param, $search_param, $search_param, $search_param);
    } elseif ($target_user_id > 0 && !empty($status_filter)) {
        $stmt_list->bind_param("is", $target_user_id, $status_filter);
    } elseif (!empty($search) && !empty($status_filter)) {
        $stmt_list->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $status_filter);
    } elseif ($target_user_id > 0) {
        $stmt_list->bind_param("i", $target_user_id);
    } elseif (!empty($search)) {
        $stmt_list->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    } elseif (!empty($status_filter)) {
        $stmt_list->bind_param("s", $status_filter);
    }
    $stmt_list->execute();
    $orders_result = $stmt_list->get_result();
}

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
            return "<span class='status-badge badge-secondary'>Trả hàng</span>";
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
    <title>Quản lý đơn hàng - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="../../assets/images/admin.webp" type="image/x-icon">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .admin-tabs {
            display: flex;
            gap: 12px;
            margin: 20px 0;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .admin-tabs a {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            background: #edf2f7;
            color: #4a5568;
            font-weight: 600;
            transition: all 0.2s;
        }

        .admin-tabs a.active {
            background: #d0021c;
            color: #fff;
        }

        .alert-box {
            padding: 12px 16px;
            margin: 15px 0;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #98e6b1;
        }

        .alert-danger {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }

        .alert-warning {
            background: #feebc8;
            color: #744210;
            border: 1px solid #fbd38d;
        }

        .action-flex-wrap {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .evidence-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #cbd5e0;
        }
    </style>
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2><?= $tab === 'orders' ? 'Quản lý đơn hàng' : 'Yêu cầu hoàn / hủy từ khách hàng' ?></h2>
                <div class="header-actions">
                    <form method="GET" class="search-form" style="display:flex; gap:10px;">
                        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                        <div class="auth-form__group" style="margin-bottom:0;">
                            <input type="text" name="search" class="auth-form__input" placeholder="Tìm kiếm mã đơn, tên..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="auth-form__group" style="margin-bottom:0;">
                            <select name="status_filter" class="auth-form__input" onchange="this.form.submit()">
                                <option value="">Tất cả trạng thái</option>
                                <?php if ($tab === 'orders'): ?>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="shipping" <?= $status_filter === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                    <option value="returned" <?= $status_filter === 'returned' ? 'selected' : '' ?>>Đã trả hàng</option>
                                <?php else: ?>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                    <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Đã chấp nhận</option>
                                    <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </header>

            <div class="admin-tabs">
                <a href="?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>"><i class="fas fa-box"></i> Quản lý đơn hàng</a>
                <a href="?tab=requests" class="<?= $tab === 'requests' ? 'active' : '' ?>"><i class="fas fa-undo-alt"></i> Yêu cầu Hoàn / Hủy</a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'success') echo '<div class="alert-box alert-success">Cập nhật trạng thái thành công!</div>'; ?>
                <?php if ($_GET['msg'] === 'approve_success') echo '<div class="alert-box alert-success">Đã duyệt yêu cầu thành công! Trạng thái đơn hàng gốc đã thay đổi.</div>'; ?>
                <?php if ($_GET['msg'] === 'reject_success') echo '<div class="alert-box alert-warning">Đã từ chối phiếu yêu cầu của khách thành công.</div>'; ?>
                <?php if ($_GET['msg'] === 'error_rollback') echo '<div class="alert-box alert-danger">Lỗi: Không được sửa lùi trạng thái đơn hàng về quá khứ!</div>'; ?>
                <?php if ($_GET['msg'] === 'error') echo '<div class="alert-box alert-danger">Đã xảy ra sự cố dữ liệu hệ thống.</div>'; ?>
            <?php endif; ?>

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
                            <?php if ($orders_result->num_rows > 0): ?>
                                <?php while ($row = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= $row['order_code'] ?></strong></td>
                                        <td><?= htmlspecialchars($row['fullname'] ?? $row['shipping_name']) ?></td>
                                        <td><strong><?= number_format($row['total_price'], 0, ',', '.') ?>đ</strong></td>
                                        <td><?= strtoupper($row['payment_method']) ?> (<?= $row['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa trả' ?>)</td>
                                        <td><?= getStatusBadge($row['status']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <div class="action-flex-wrap">
                                                <button class="action-link" title="Xem chi tiết" onclick="viewOrderDetails(<?= $row['id'] ?>, '<?= $row['order_code'] ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-link" title="Sửa trạng thái" onclick='openEditOrderModal(<?= json_encode($row) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">Không có dữ liệu đơn hàng thỏa mãn.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    <?php else: ?>
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Loại yêu cầu</th>
                                <th>Lý do & Mô tả</th>
                                <th>Bằng chứng</th>
                                <th>Hoàn tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders_result->num_rows > 0): ?>
                                <?php while ($row = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $row['order_code'] ?><br><small>(<?= number_format($row['total_price'], 0, ',', '.') ?>đ)</small></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td>
                                            <?= $row['request_type'] === 'cancel'
                                                ? '<span style="color:#e53e3e; font-weight:600;"><i class="fas fa-times-circle"></i> Hủy đơn</span>'
                                                : '<span style="color:#dd6b20; font-weight:600;"><i class="fas fa-undo"></i> Trả hàng</span>' ?>
                                        </td>
                                        <td>
                                            <div style="max-width:220px; white-space:normal; word-wrap:break-word; font-size:13px;">
                                                <strong><?= htmlspecialchars($row['reason']) ?></strong>
                                                <?php if (!empty($row['description'])): ?>
                                                    <br><span style="color:#718096; font-size:12px;"><?= htmlspecialchars($row['description']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['evidence_image'])): ?>
                                                <img src="../../<?= htmlspecialchars($row['evidence_image']) ?>" class="evidence-img" alt="Bằng chứng" onclick="window.open(this.src)">
                                            <?php else: ?>
                                                <span style="color:#a0aec0; font-size:12px;">Không có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['request_type'] === 'return'): ?>
                                                <small>Số tiền: </small><b style="color:#2b6cb0;"><?= number_format($row['refund_amount'] ?? $row['total_price'], 0, ',', '.') ?>đ</b><br>
                                                <small style="color:#4a5568;">Trạng thái: <?= strtoupper($row['refund_status']) ?></small>
                                            <?php else: ?>
                                                <span style="color:#a0aec0; font-size:13px;">Không áp dụng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($row['status']) ?>
                                            <?php if ($row['status'] === 'rejected'): ?>
                                                <br><small style="color:#e53e3e;">Lý do từ chối: <?= htmlspecialchars($row['rejection_reason']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['requested_at'])) ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <div class="action-flex-wrap">
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận phê duyệt yêu cầu này? Đơn hàng gốc sẽ tự động đổi trạng thái tương ứng.');">
                                                        <input type="hidden" name="action" value="approve_request">
                                                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                                        <input type="hidden" name="request_type" value="<?= $row['request_type'] ?>">
                                                        <button type="submit" style="background:#28a745; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:12px;"><i class="fas fa-check"></i> Duyệt</button>
                                                    </form>
                                                    <button type="button" style="background:#dc3545; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:12px;" onclick="openRejectModal(<?= $row['id'] ?>)"><i class="fas fa-ban"></i> Từ chối</button>
                                                </div>
                                            <?php else: ?>
                                                <span style="color:#a0aec0; font-size:13px;"><i class="fas fa-check-double"></i> Đã xử lý</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align:center;">Không tìm thấy phiếu yêu cầu hoàn/hủy nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    <?php endif; ?>
                </table>
            </section>
        </main>
    </div>

    <div id="viewOrderDetailModal" class="modal">
        <div class="modal__content" style="max-width: 900px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                <h3>Chi tiết đơn hàng #<span id="detail_order_code"></span></h3>
                <button onclick="closeModal('viewOrderDetailModal')" style="background: none; border: none; font-size: 24px; cursor: pointer; color:#a0aec0;">&times;</button>
            </div>
            <div class="table-container" style="margin-top: 15px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ảnh sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="order_items_body"></tbody>
                </table>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 15px;">
                <div style="padding: 12px; background: #f7fafc; border-radius: 6px;">
                    <h4 style="margin-bottom: 6px; color: #2d3748;"><i class="fas fa-user"></i> Thông tin tài khoản đặt mua</h4>
                    <p>Họ tên: <span id="acc_name"></span></p>
                    <p>Email liên hệ: <span id="acc_email"></span></p>
                    <p>Thời gian đặt hệ thống: <span id="order_date"></span></p>
                </div>
                <div style="padding: 12px; background: #fffaf0; border-radius: 6px; border: 1px solid #feebc8;">
                    <h4 style="margin-bottom: 6px; color: #dd6b20;"><i class="fas fa-truck"></i> Địa chỉ nhận hàng thực tế</h4>
                    <p>Người nhận: <span id="ship_name"></span></p>
                    <p>Số điện thoại: <span id="ship_phone"></span></p>
                    <p>Địa chỉ giao: <span id="ship_address"></span></p>
                </div>
            </div>
            <div style="margin-top: 12px; padding: 10px; background: #ebf8ff; border-left: 4px solid #3182ce; border-radius: 0 4px 4px 0;">
                <strong>Khách hàng ghi chú đơn:</strong> <span id="ship_note"></span>
            </div>
            <div style="margin-top: 20px;">
                <h4 style="color:#2d3748; margin-bottom:10px;"><i class="fas fa-history"></i> Lịch sử cập nhật của đơn hàng</h4>
                <div id="order_history_timeline" style="max-height: 200px; overflow-y: auto; padding-right: 5px;"></div>
            </div>
            <div class="modal__footer" style="margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('viewOrderDetailModal')">Đóng cửa sổ</button>
            </div>
        </div>
    </div>

    <div id="editOrderModal" class="modal">
        <div class="modal__content">
            <h3>Cập nhật đơn hàng #<span id="display_order_code"></span></h3>
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="edit_order_id">
                <div class="auth-form__group">
                    <label style="font-weight:600; display:block; margin-bottom:6px;">Lựa chọn trạng thái xử lý mới</label>
                    <select name="status" id="edit_order_status" class="auth-form__input">
                        <option value="pending">Chờ xử lý</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="shipping">Đang giao hàng</option>
                        <option value="delivered">Đã giao hàng thành công</option>
                        <option value="cancelled">Hủy đơn hàng</option>
                        <option value="returned">Trả hàng / Hoàn tiền</option>
                    </select>
                </div>
                <div class="auth-form__group">
                    <label style="font-weight:600; display:block; margin-bottom:6px;">Ghi chú thông báo gửi khách hàng</label>
                    <textarea name="note" id="edit_order_note" class="auth-form__input" rows="3" placeholder="Lý do thay đổi trạng thái đơn hàng..."></textarea>
                </div>
                <div class="modal__footer" style="margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('editOrderModal')">Hủy bỏ</button>
                    <button type="submit" class="btn-primary" style="background:#d0021c; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer;">Xác nhận cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="modal">
        <div class="modal__content">
            <h3>Từ chối phê duyệt yêu cầu của khách hàng</h3>
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="reject_request">
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="auth-form__group">
                    <label style="font-weight:600; color:#e53e3e; display:block; margin-bottom:6px;">Lý do không chấp nhận hoàn/hủy hàng</label>
                    <textarea name="reject_reason" class="auth-form__input" rows="4" placeholder="Ví dụ: Sản phẩm đã quá hạn đổi trả, mất tem bảo hành của StudentGear..." required></textarea>
                </div>
                <div class="modal__footer" style="margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('rejectModal')">Đóng</button>
                    <button type="submit" style="background:#dc3545; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer;">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/modal_admin.js"></script>
    <script src="../../assets/js/order_admin.js"></script>

    <script>
        function openRejectModal(requestId) {
            document.getElementById('reject_request_id').value = requestId;
            openModal('rejectModal');
        }
    </script>
</body>

</html>