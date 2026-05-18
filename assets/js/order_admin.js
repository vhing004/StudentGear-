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
