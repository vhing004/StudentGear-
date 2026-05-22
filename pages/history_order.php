<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Khởi tạo câu SQL cơ bản
$sql_orders = "SELECT * FROM orders WHERE user_id = '$user_id'";

// Cập nhật điều kiện lọc
if ($status_filter === 'shipping') {
    $sql_orders .= " AND (status = 'confirmed' OR status = 'shipping')";
} elseif ($status_filter === 'returned') {
    $sql_orders .= " AND status = 'returned'";
} elseif ($status_filter !== 'all') {
    $sql_orders .= " AND status = '$status_filter'";
}

$sql_orders .= " ORDER BY created_at DESC";
$res_orders = $conn->query($sql_orders);
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

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancel_success'): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; margin-bottom: 15px; border-radius: 4px;">Hủy đơn hàng thành công!</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'return_success'): ?>
            <div style="background: #fff3cd; color: #856404; padding: 12px; margin-bottom: 15px; border-radius: 4px;">Gửi yêu cầu hoàn hàng thành công. Vui lòng chờ hệ thống xét duyệt!</div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 15px; border-radius: 4px;">Có lỗi xảy ra hoặc lý do trống, vui lòng thao tác lại.</div>
        <?php endif; ?>

        <div class="order-list">
            <?php if ($res_orders->num_rows > 0): ?>
                <?php while ($order = $res_orders->fetch_assoc()):
                    $order_id = $order['id'];
                    $sql_items = "SELECT oi.*, p.image 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.order_id = '$order_id'";
                    $res_items = $conn->query($sql_items);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="shop-name"><i class="fa-solid fa-store"></i> StudentGear Official</span>
                            <div class="order-status">
                                <i class="fa-solid fa-truck"></i>
                                <?php
                                $status_text = 'ĐANG XỬ LÝ';
                                if ($order['status'] == 'delivered') $status_text = 'HOÀN THÀNH';
                                if ($order['status'] == 'cancelled') $status_text = 'ĐA HỦY';
                                if ($order['status'] == 'returned') $status_text = 'YÊU CẦU TRẢ HÀNG';
                                echo htmlspecialchars(strtoupper($order['status'])) . " | <span class='status-text'>$status_text</span>";
                                ?>
                            </div>
                        </div>

                        <?php while ($item = $res_items->fetch_assoc()): ?>
                            <div class="product-info">
                                <img src="<?= $item['image'] ?>" alt="product">
                                <div class="details">
                                    <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="qty">x<?= $item['quantity'] ?></p>
                                </div>
                                <div class="price">
                                    <?= number_format($item['price'], 0, ',', '.') ?>₫
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <div class="order-footer">
                            <div class="total-section">
                                Thành tiền: <span class="total-amount"><?= number_format($order['total_price'], 0, ',', '.') ?>₫</span>
                            </div>
                            <div class="actions" style="display: flex; gap: 10px;">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button onclick="openReasonModal(<?= $order['id'] ?>, 'cancel', 'Yêu cầu hủy đơn hàng')" class="btn" style="background: #dc3545; color: #fff; border: none; cursor: pointer;">Hủy Đơn</button>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button onclick="openReasonModal(<?= $order['id'] ?>, 'return', 'Yêu cầu Trả hàng / Hoàn tiền')" class="btn" style="background: #ffc107; color: #212529; border: none; cursor: pointer;">Trả Hàng / Hoàn Tiền</button>
                                    <a href="../handler/reorder_process.php?order_id=<?= $order['id'] ?>" class="btn btn-primary" style="text-decoration: none;">Mua Lại</a>
                                <?php endif; ?>

                                <a href="../pages/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline" style="text-decoration: none;">Xem Chi Tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <img src="../assets/images/empty-order.png" alt="no order">
                    <p>Chưa có đơn hàng nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="userReasonModal" class="reason-modal">
    <div class="reason-modal__content">
        <div class="reason-modal__header">
            <h3 id="modal_action_title">Nhập lý do</h3>
            <button onclick="closeReasonModal()" class="reason-modal__close">&times;</button>
        </div>
        <form action="../handler/update_order_action.php" method="POST" id="reasonActionForm">
            <input type="hidden" name="order_id" id="action_order_id">
            <input type="hidden" name="action_type" id="action_type">

            <div class="reason-form-group">
                <label id="modal_label_text">Vui lòng cung cấp lý do cụ thể:</label>
                <textarea name="reason" id="user_reason_input" rows="4" placeholder="Nhập ít nhất 6 ký tự..." required></textarea>
            </div>

            <div class="reason-modal__footer">
                <button type="button" class="btn" style="background: #e2e8f0; border: none; cursor: pointer;" onclick="closeReasonModal()">Đóng</button>
                <button type="submit" class="btn btn-primary" style="border: none; cursor: pointer;">Xác nhận gửi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('userReasonModal');
    const orderIdInput = document.getElementById('action_order_id');
    const actionTypeInput = document.getElementById('action_type');
    const modalTitle = document.getElementById('modal_action_title');
    const modalLabel = document.getElementById('modal_label_text');
    const reasonInput = document.getElementById('user_reason_input');

    function openReasonModal(orderId, action, titleText) {
        orderIdInput.value = orderId;
        actionTypeInput.value = action;
        modalTitle.innerText = titleText;
        reasonInput.value = ""; // Clear dữ liệu cũ

        if (action === 'cancel') {
            modalLabel.innerText = "Lý do hủy đơn hàng của bạn là gì?";
            reasonInput.placeholder = "Ví dụ: Đổi ý không mua nữa, trùng đơn, áp nhầm mã...";
        } else {
            modalLabel.innerText = "Lý do bạn muốn hoàn trả sản phẩm này?";
            reasonInput.placeholder = "Ví dụ: Sản phẩm lỗi, không giống mô tả, vỡ hỏng do vận chuyển...";
        }

        modal.style.display = 'flex';
    }

    function closeReasonModal() {
        modal.style.display = 'none';
    }

    // Ngăn chặn bấm ra ngoài popup làm mất dữ liệu đột ngột
    window.onclick = function(event) {
        if (event.target == modal) {
            closeReasonModal();
        }
    }

    // Ràng buộc kiểm tra không cho gửi lý do trống rỗng hoặc quá ngắn
    document.getElementById('reasonActionForm').addEventListener('submit', function(e) {
        if (reasonInput.value.trim().length < 6) {
            e.preventDefault();
            alert('Vui lòng nhập lý do rõ ràng cụ thể hơn (tối thiểu 6 ký tự)!');
        }
    });
</script>

<?php include '../includes/footer.php'; ?>