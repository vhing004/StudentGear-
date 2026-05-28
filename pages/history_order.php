# File: history_order.php

```php
<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';

// QUERY ĐƠN HÀNG
$sql_orders = "SELECT * FROM orders WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($status_filter === 'shipping') {
    $sql_orders .= " AND (status = 'confirmed' OR status = 'shipping')";
} elseif ($status_filter === 'returned') {
    $sql_orders .= " AND status = 'returned'";
} elseif ($status_filter !== 'all') {
    $sql_orders .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql_orders .= " ORDER BY created_at DESC";

$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param($types, ...$params);
$stmt_orders->execute();
$res_orders = $stmt_orders->get_result();
?>

<main class="order-history-page">
    <div class="container">
        <div class="order-tabs">
            <a href="?status=all" class="<?= $status_filter == 'all' ? 'active' : '' ?>">Tất cả</a>
            <a href="?status=pending" class="<?= $status_filter == 'pending' ? 'active' : '' ?>">Chờ xác nhận</a>
            <a href="?status=shipping" class="<?= $status_filter == 'shipping' ? 'active' : '' ?>">Vận chuyển</a>
            <a href="?status=delivered" class="<?= $status_filter == 'delivered' ? 'active' : '' ?>">Hoàn thành</a>
            <a href="?status=returned" class="<?= $status_filter == 'returned' ? 'active' : '' ?>">Trả hàng / Hoàn tiền</a>
            <a href="?status=cancelled" class="<?= $status_filter == 'cancelled' ? 'active' : '' ?>">Đã hủy</a>
        </div>

        <!-- Thông báo khi gửi yêu cầu  -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancel_requested'): ?>
            <div style="background:#d4edda;color:#155724;padding:12px;border-radius:6px;margin-bottom:15px;">
                Đã gửi yêu cầu hủy đơn thành công!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'return_requested'): ?>
            <div style="background:#d1ecf1;color:#0c5460;padding:12px;border-radius:6px;margin-bottom:15px;">
                Đã gửi yêu cầu trả hàng / hoàn tiền thành công!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'exist_request'): ?>
            <div style="background:#fff3cd;color:#856404;padding:12px;border-radius:6px;margin-bottom:15px;">
                Đơn hàng này đã có yêu cầu đang xử lý.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:6px;margin-bottom:15px;">
                Có lỗi xảy ra. Vui lòng thử lại.
            </div>
        <?php endif; ?>

        <!-- Hiển thị danh sách đơn hàng  -->
        <div class="order-list">
            <?php if ($res_orders->num_rows > 0): ?>
                <?php while ($order = $res_orders->fetch_assoc()): ?>
                    <?php
                    $order_id = $order['id'];
                    // LẤY SẢN PHẨM
                    $stmt_items = $conn->prepare("
                        SELECT
                            oi.*,
                            p.image
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?
                    ");
                    $stmt_items->bind_param("i", $order_id);
                    $stmt_items->execute();
                    $res_items = $stmt_items->get_result();

                    // LẤY YÊU CẦU HỦY / HOÀN
                    $stmt_request = $conn->prepare("
                        SELECT
                            request_type,
                            status,
                            reason,
                            description,
                            rejection_reason,
                            refund_amount,
                            refund_status,
                            evidence_image,
                            requested_at,
                            reviewed_at
                        FROM order_requests
                        WHERE order_id = ?
                        ORDER BY requested_at DESC
                        LIMIT 1
                    ");

                    $stmt_request->bind_param("i", $order_id);
                    $stmt_request->execute();
                    $request_result = $stmt_request->get_result();
                    $has_request = $request_result->fetch_assoc();
                    ?>

                    <div class="order-card">
                        <div class="order-header">
                            <span class="shop-name">
                                <i class="fa-solid fa-store"></i>
                                StudentGear Official
                            </span>
                            <div class="order-status">
                                <i class="fa-solid fa-truck"></i>
                                <?php
                                $status_text = 'ĐANG XỬ LÝ';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_text = 'CHỜ XÁC NHẬN';
                                        break;
                                    case 'confirmed':
                                        $status_text = 'ĐÃ XÁC NHẬN';
                                        break;
                                    case 'shipping':
                                        $status_text = 'ĐANG GIAO';
                                        break;
                                    case 'delivered':
                                        $status_text = 'HOÀN THÀNH';
                                        break;
                                    case 'cancelled':
                                        $status_text = 'ĐÃ HỦY';
                                        break;
                                    case 'returned':
                                        $status_text = 'ĐÃ TRẢ HÀNG';
                                        break;
                                }
                                echo htmlspecialchars($status_text);
                                ?>
                            </div>
                        </div>
                        <?php while ($item = $res_items->fetch_assoc()): ?>
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="product">
                                <div class="details">
                                    <p class="name">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </p>
                                    <p class="qty">
                                        x<?= (int)$item['quantity'] ?>
                                    </p>
                                </div>
                                <div class="price">
                                    <?= number_format($item['price'], 0, ',', '.') ?>₫
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <div class="order-footer">
                            <div class="total-section">
                                Thành tiền:
                                <span class="total-amount">
                                    <?= number_format($order['total_price'], 0, ',', '.') ?>₫
                                </span>
                            </div>

                            <div class="actions" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                                <?php if ($has_request): ?>
                                    <?php if ($has_request['status'] === 'pending'): ?>
                                        <div>
                                            <span class="request-status pending">
                                                <i class="fas fa-clock"></i>
                                                Đang chờ duyệt yêu cầu
                                                <?= $has_request['request_type'] === 'cancel' ? 'hủy đơn' : 'trả hàng' ?>
                                            </span>
                                        </div>

                                    <?php elseif ($has_request['status'] === 'approved'): ?>
                                        <div>
                                            <span class="request-status approved">
                                                <i class="fas fa-check-circle"></i>
                                                Yêu cầu đã được duyệt
                                            </span>
                                            <?php if (!empty($has_request['refund_status'])): ?>
                                                <div class="refund-status <?= htmlspecialchars($has_request['refund_status']) ?>">
                                                    <?php
                                                    if ($has_request['refund_status'] === 'pending') {
                                                        echo 'Đang xử lý hoàn tiền';
                                                    } elseif ($has_request['refund_status'] === 'refunded') {
                                                        echo 'Đã hoàn tiền';
                                                    } elseif ($has_request['refund_status'] === 'failed') {
                                                        echo 'Hoàn tiền thất bại';
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    <?php elseif ($has_request['status'] === 'rejected'): ?>
                                        <div>
                                            <span class="request-status rejected">
                                                <i class="fas fa-times-circle"></i>
                                                Yêu cầu đã bị từ chối
                                            </span>
                                            <?php if (!empty($has_request['rejection_reason'])): ?>
                                                <div class="rejection-box">
                                                    <?= htmlspecialchars($has_request['rejection_reason']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button
                                                onclick="openReasonModal(<?= $order['id'] ?>, 'cancel', 'Yêu cầu hủy đơn hàng', <?= $order['total_price'] ?>)"
                                                class="btn"
                                                style="background:#dc3545;color:#fff;border:none;cursor:pointer;">
                                                Gửi Lại Yêu Cầu Hủy
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($order['status'] === 'delivered'): ?>
                                            <button
                                                onclick="openReasonModal(<?= $order['id'] ?>, 'return', 'Yêu cầu trả hàng / hoàn tiền', <?= $order['total_price'] ?>)"
                                                class="btn"
                                                style="background:#ffc107;color:#212529;border:none;cursor:pointer;">
                                                Gửi Lại Yêu Cầu
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button
                                            onclick="openReasonModal(<?= $order['id'] ?>, 'cancel', 'Yêu cầu hủy đơn hàng', <?= $order['total_price'] ?>)"
                                            class="btn"
                                            style="background:#dc3545;color:#fff;border:none;cursor:pointer;">
                                            Hủy Đơn
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <button
                                            onclick="openReasonModal(<?= $order['id'] ?>, 'return', 'Yêu cầu Trả hàng / Hoàn tiền', <?= $order['total_price'] ?>)"
                                            class="btn"
                                            style="background:#ffc107;color:#212529;border:none;cursor:pointer;">
                                            Trả Hàng / Hoàn Tiền
                                        </button>
                                        <a href="../handler/reorder_process.php?order_id=<?= $order['id'] ?>"
                                            class="btn btn-primary"
                                            style="text-decoration:none;">
                                            Mua Lại
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="../pages/order_detail.php?id=<?= $order['id'] ?>"
                                    class="btn btn-outline"
                                    style="text-decoration:none;">
                                    Xem Chi Tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <img src="../assets/images/empty-order.png" alt="empty-order">
                    <p>Chưa có đơn hàng nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- MODAL -->
<div id="userReasonModal" class="reason-modal">
    <div class="reason-modal__content">
        <div class="reason-modal__header">
            <h3 id="modal_action_title">Nhập lý do</h3>
            <button onclick="closeReasonModal()"
                class="reason-modal__close">
                &times;
            </button>
        </div>
        <form action="../handler/update_order_action.php"
            method="POST"
            enctype="multipart/form-data"
            id="reasonActionForm">
            <input type="hidden" name="order_id" id="action_order_id">
            <input type="hidden" name="action_type" id="action_type">
            <input type="hidden" name="refund_amount" id="action_refund_amount">
            <div class="reason-form-group">
                <label id="modal_label_text">
                    Vui lòng cung cấp lý do cụ thể:
                </label>
                <select name="reason"
                    id="user_reason_select"
                    required>
                </select>
            </div>
            <div class="reason-form-group">
                <label>Mô tả chi tiết thêm</label>
                <textarea name="description"
                    id="user_description_input"
                    rows="4"
                    placeholder="Nhập mô tả chi tiết..."></textarea>
            </div>
            <div class="reason-form-group"
                id="evidence_upload_box"
                style="display:none;">
                <label>Ảnh minh chứng</label>
                <input type="file"
                    name="evidence_image"
                    accept="image/*">
            </div>
            <div class="reason-modal__footer">
                <button type="button"
                    class="btn"
                    style="background:#e2e8f0;border:none;cursor:pointer;"
                    onclick="closeReasonModal()">
                    Đóng
                </button>
                <button type="submit"
                    class="btn btn-primary"
                    style="border:none;cursor:pointer;">
                    Gửi Yêu Cầu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('userReasonModal');
    const orderIdInput = document.getElementById('action_order_id');
    const actionTypeInput = document.getElementById('action_type');
    const refundAmountInput = document.getElementById('action_refund_amount');
    const modalTitle = document.getElementById('modal_action_title');
    const modalLabel = document.getElementById('modal_label_text');
    const reasonSelect = document.getElementById('user_reason_select');
    const descInput = document.getElementById('user_description_input');
    const evidenceBox = document.getElementById('evidence_upload_box');

    const cancelReasons = [
        'Tôi muốn thay đổi địa chỉ nhận hàng',
        'Tôi muốn đổi màu sắc/kích cỡ sản phẩm khác',
        'Tìm thấy giá tốt hơn ở nơi khác',
        'Đặt trùng đơn hàng',
        'Tôi không còn nhu cầu mua nữa'
    ];

    const returnReasons = [
        'Sản phẩm bị bóp méo hoặc hư hỏng',
        'Giao sai sản phẩm',
        'Sản phẩm bị lỗi kỹ thuật',
        'Sản phẩm không đúng mô tả',
        'Thiếu phụ kiện đi kèm'
    ];

    function openReasonModal(orderId, action, titleText, totalPrice) {
        orderIdInput.value = orderId;
        actionTypeInput.value = action;
        refundAmountInput.value = totalPrice;
        modalTitle.innerText = titleText;
        reasonSelect.innerHTML = '';
        descInput.value = '';

        let reasons = [];

        if (action === 'cancel') {
            modalLabel.innerText = 'Lý do hủy đơn hàng của bạn là gì?';
            reasons = cancelReasons;
            evidenceBox.style.display = 'none';
        } else {
            modalLabel.innerText = 'Lý do bạn muốn trả hàng / hoàn tiền?';
            reasons = returnReasons;
            evidenceBox.style.display = 'block';
        }

        reasons.forEach(reason => {
            const option = document.createElement('option');
            option.value = reason;
            option.textContent = reason;
            reasonSelect.appendChild(option);
        });
        modal.style.display = 'flex';
    }

    function closeReasonModal() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            closeReasonModal();
        }
    }
</script>

<?php include '../includes/footer.php'; ?>