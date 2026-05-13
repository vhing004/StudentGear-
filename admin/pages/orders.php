<?php
session_start();
require_once '../../config/db.php';

// Logic bảo mật tương tự categories.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Truy vấn danh sách đơn hàng (Join với users để lấy tên khách hàng)
$sql = "SELECT o.*, u.fullname 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$orders = $conn->query($sql);

// Hàm hỗ trợ hiển thị Badge trạng thái
function getStatusBadge($status)
{
    $class = '';
    $text = '';
    switch ($status) {
        case 'pending':
            $class = 'badge-warning';
            $text = 'Chờ xử lý';
            break;
        case 'confirmed':
            $class = 'badge-info';
            $text = 'Đã xác nhận';
            break;
        case 'shipping':
            $class = 'badge-primary';
            $text = 'Đang giao';
            break;
        case 'delivered':
            $class = 'badge-success';
            $text = 'Đã giao';
            break;
        case 'cancelled':
            $class = 'badge-danger';
            $text = 'Đã hủy';
            break;
        default:
            $class = 'badge-secondary';
            $text = $status;
    }
    return "<span class='status-badge $class'>$text</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng - StudentGear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <?php include_once '../includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="main-content__header">
                <h2>Quản lý đơn hàng</h2>
                <div class="header-actions">
                    <span class="total-orders">Tổng số: <?= $orders->num_rows ?> đơn</span>
                </div>
            </header>

            <section class="table-container">
                <table class="table">
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
                        <?php while ($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?= $row['order_code'] ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <span><?= htmlspecialchars($row['fullname'] ?? $row['shipping_name']) ?></span><br>
                                        <small style="color: #888;"><?= $row['shipping_phone'] ?></small>
                                    </div>
                                </td>
                                <td><b class="text-primary"><?= number_format($row['total_price'], 0, ',', '.') ?>đ</b></td>
                                <td>
                                    <small><?= strtoupper($row['payment_method']) ?></small><br>
                                    <small class="<?= $row['payment_status'] == 'paid' ? 'text-success' : 'text-danger' ?>">
                                        (<?= $row['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa trả' ?>)
                                    </small>
                                </td>
                                <td><?= getStatusBadge($row['status']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <button class="action-link" title="Xem chi tiết"
                                        onclick="viewOrderDetails(<?= $row['id'] ?>, '<?= $row['order_code'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-link" onclick='openEditOrderModal(<?= json_encode($row) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- VIEW DETAIL ORDER -->
    <div id="viewOrderDetailModal" class="modal">
        <div class="modal__content" style="max-width: 1000px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                <h3>Chi tiết đơn hàng #<span id="detail_order_code"></span></h3>
                <button onclick="closeModal('viewOrderDetailModal')" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>

            <div class="table-container" style="margin-top: 20px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>SL</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="order_items_body"></tbody>
                </table>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">

                <div style="padding: 15px; background: #eef2f7; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px; color: #34495e;"><i class="fas fa-user-circle"></i> Người đặt hàng</h4>
                    <div style="font-size: 14px; line-height: 1.6;">
                        <p><strong>Họ tên:</strong> <span id="acc_name"></span></p>
                        <p><strong>Email:</strong> <span id="acc_email"></span></p>
                        <p><strong>Ngày đặt:</strong> <span id="order_date"></span></p>
                    </div>
                </div>

                <div style="padding: 15px; background: #fff3e0; border-radius: 8px; border: 1px solid #ffe0b2;">
                    <h4 style="margin-bottom: 10px; color: #e67e22;"><i class="fas fa-shipping-fast"></i> Địa chỉ giao hàng</h4>
                    <div style="font-size: 14px; line-height: 1.6;">
                        <p><strong>Người nhận:</strong> <span id="ship_name"></span></p>
                        <p><strong>SĐT:</strong> <span id="ship_phone"></span></p>
                        <p><strong>Địa chỉ:</strong> <span id="ship_address"></span></p>
                    </div>
                </div>

            </div>

            <div style="margin-top: 15px; padding: 10px; border-left: 4px solid #3498db; background: #f1f8ff;">
                <strong>Ghi chú đơn hàng:</strong> <span id="ship_note"></span>
            </div>

            <div style="margin-top: 25px;">
                <h4 style="margin-bottom: 15px; color: #2c3e50;"><i class="fas fa-history"></i> Lịch sử xử lý đơn hàng</h4>
                <div id="order_history_timeline" style="max-height: 250px; overflow-y: auto; font-size: 13px;">
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn-secondary" onclick="closeModal('viewOrderDetailModal')">Đóng</button>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL ORDER -->
    <div id="editOrderModal" class="modal">
        <div class="modal__content">
            <h3>Cập nhật đơn hàng #<span id="display_order_code"></span></h3>

            <form action="../handlers/update_order_status.php" method="POST" class="grid-form">
                <input type="hidden" name="id" id="edit_order_id">

                <div class="auth-form__group">
                    <label>Trạng thái đơn hàng</label>
                    <select name="status" id="edit_order_status" class="auth-form__input">
                        <option value="pending">Chờ xử lý</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="shipping">Đang giao hàng</option>
                        <option value="delivered">Đã giao hàng</option>
                        <option value="cancelled">Hủy đơn hàng</option>
                        <option value="returned">Trả hàng</option>
                    </select>
                </div>

                <div class="auth-form__group">
                    <label>Ghi chú nội bộ (Lý do đổi trạng thái)</label>
                    <textarea name="note" class="auth-form__input" placeholder="Ví dụ: Khách hẹn giao lại vào thứ 2..."></textarea>
                </div>

                <div class="modal__footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('editOrderModal')">Hủy</button>
                    <button type="submit" class="btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hàm mở Modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('is-closing');
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        // Hàm đóng Modal kèm hiệu ứng
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);

            // Thêm class closing để kích hoạt animation slideUp và fadeOut
            modal.classList.add('is-closing');

            // Đợi animation chạy xong (0.3s = 300ms) rồi mới ẩn hẳn
            setTimeout(() => {
                modal.classList.remove('is-open');
                modal.classList.remove('is-closing');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        // Cập nhật lại window.onclick để cũng có hiệu ứng khi click ra ngoài
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                const modalId = event.target.id;
                closeModal(modalId);
            }
        }

        // DETAIL MODAL
        function viewOrderDetails(orderId, orderCode) {
            document.getElementById('detail_order_code').innerText = orderCode;
            const tbody = document.getElementById('order_items_body');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Đang tải...</td></tr>';

            openModal('viewOrderDetailModal');

            fetch(`../handlers/get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    const order = data.order;

                    // 1. Điền thông tin Người đặt (Account)
                    document.getElementById('acc_name').innerText = order.account_name || order.shipping_name;
                    document.getElementById('acc_email').innerText = order.account_email || 'Khách vãng lai';
                    document.getElementById('order_date').innerText = new Date(order.created_at).toLocaleString('vi-VN');

                    // 2. Điền thông tin Giao hàng (Shipping)
                    document.getElementById('ship_name').innerText = order.shipping_name;
                    document.getElementById('ship_phone').innerText = order.shipping_phone;
                    document.getElementById('ship_address').innerText = order.shipping_address;
                    document.getElementById('ship_note').innerText = order.note || 'Không có ghi chú';

                    // 3. Render danh sách sản phẩm (vòng lặp data.items như ở bước trước)
                    tbody.innerHTML = '';
                    data.items.forEach(item => {
                        let imgSrc = item.image ? (item.image.startsWith('http') ? item.image : "../../" + item.image) : "../../assets/images/no-image.png";
                        tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td><img src="${imgSrc}" width="40" style="border-radius:4px;"></td>
                        <td><strong>${item.product_name}</strong></td>
                        <td>${new Intl.NumberFormat().format(item.price)}đ</td>
                        <td>${item.quantity}</td>
                        <td><b>${new Intl.NumberFormat().format(item.total_price)}đ</b></td>
                    </tr>
                `);
                    });

                    // Thêm phần này vào bên trong fetch().then(data => { ... })
                    const historyContainer = document.getElementById('order_history_timeline');
                    historyContainer.innerHTML = '';

                    if (data.history.length === 0) {
                        historyContainer.innerHTML = '<p style="color: #888; text-align: center;">Chưa có lịch sử thay đổi.</p>';
                    } else {
                        data.history.forEach(log => {
                            const time = new Date(log.created_at).toLocaleString('vi-VN');
                            const admin = log.admin_name ? log.admin_name : 'Hệ thống/Khách hàng';
                            const note = log.note ? `<br><small style="color: #666;">Ghi chú: ${log.note}</small>` : '';

                            const historyItem = `
            <div style="margin-bottom: 15px; padding-left: 20px; border-left: 2px solid #ddd; position: relative;">
                <span style="position: absolute; left: -7px; top: 0; width: 12px; height: 12px; background: #3498db; border-radius: 50%;"></span>
                <p style="margin: 0;">
                    <strong>${time}</strong>: 
                    <span class="status-badge badge-info" style="min-width: auto; padding: 2px 8px;">${log.old_status || 'Bắt đầu'}</span> 
                    <i class="fas fa-arrow-right" style="font-size: 10px; margin: 0 5px;"></i> 
                    <span class="status-badge badge-success" style="min-width: auto; padding: 2px 8px;">${log.new_status}</span>
                </p>
                <p style="margin: 5px 0 0 0; color: #555;">Thực hiện bởi: <strong>${admin}</strong> ${note}</p>
            </div>
        `;
                            historyContainer.insertAdjacentHTML('beforeend', historyItem);
                        });
                    }
                });
        }

        // EDIT MODAL 
        function openEditOrderModal(order) {
            // Điền dữ liệu vào form
            document.getElementById('edit_order_id').value = order.id;
            document.getElementById('display_order_code').innerText = order.order_code;
            document.getElementById('edit_order_status').value = order.status;

            // Gọi hàm mở modal đã có animation của bạn
            openModal('editOrderModal');
        }
    </script>
</body>

</html>