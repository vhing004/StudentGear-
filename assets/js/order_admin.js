// Hàm bổ trợ: Chuyển đổi nhãn trạng thái sang Tiếng Việt và trả về định dạng HTML + Class màu đồng bộ hệ thống
function formatHistoryStatus(status) {
  let text = "";
  let badgeClass = "";

  switch (status) {
    case "pending":
      text = "Chờ xử lý";
      badgeClass = "badge-warning"; // Màu vàng nền ấm
      break;
    case "confirmed":
      text = "Đã xác nhận";
      badgeClass = "badge-info"; // Màu xanh ngọc
      break;
    case "shipping":
      text = "Đang giao";
      badgeClass = "badge-primary"; // Màu xanh dương
      break;
    case "delivered":
      text = "Đã giao";
      badgeClass = "badge-success"; // Màu xanh lá cây
      break;
    case "cancelled":
      text = "Đã hủy";
      badgeClass = "badge-danger"; // Màu đỏ
      break;
    case "returned":
      text = "Trả hàng / Hoàn tiền";
      badgeClass = "badge-secondary"; // Màu xám hệ thống
      break;
    default:
      text = status || "Bắt đầu";
      badgeClass = "badge-secondary";
  }

  return `<span class="status-badge ${badgeClass}" style="min-width: auto; padding: 2px 8px;">${text}</span>`;
}

// DETAIL MODAL - Xem chi tiết đơn hàng và lịch sử cập nhật trạng thái tiếng Việt
function viewOrderDetails(orderId, orderCode) {
  document.getElementById("detail_order_code").innerText = orderCode;
  const tbody = document.getElementById("order_items_body");
  tbody.innerHTML =
    '<tr><td colspan="5" style="text-align:center;">Đang tải...</td></tr>';

  openModal("viewOrderDetailModal");

  fetch(`../handlers/get_order_details.php?id=${orderId}`)
    .then((response) => response.json())
    .then((data) => {
      const order = data.order;

      // 1. Điền thông tin Người đặt (Account)
      document.getElementById("acc_name").innerText =
        order.account_name || order.shipping_name;
      document.getElementById("acc_email").innerText =
        order.account_email || "Khách vãng lai";
      document.getElementById("order_date").innerText = new Date(
        order.created_at,
      ).toLocaleString("vi-VN");

      // 2. Điền thông tin Giao hàng (Shipping)
      document.getElementById("ship_name").innerText = order.shipping_name;
      document.getElementById("ship_phone").innerText = order.shipping_phone;
      document.getElementById("ship_address").innerText =
        order.shipping_address;
      document.getElementById("ship_note").innerText =
        order.note || "Không có ghi chú";

      // 3. Render danh sách sản phẩm
      tbody.innerHTML = "";
      data.items.forEach((item) => {
        let imgSrc = item.image
          ? item.image.startsWith("http")
            ? item.image
            : "../../" + item.image
          : "../../assets/images/no-image.png";
        tbody.insertAdjacentHTML(
          "beforeend",
          `
                    <tr>
                        <td><img src="${imgSrc}" width="40" style="border-radius:4px;"></td>
                        <td><strong>${item.product_name}</strong></td>
                        <td>${new Intl.NumberFormat().format(item.price)}đ</td>
                        <td>${item.quantity}</td>
                        <td><b>${new Intl.NumberFormat().format(item.total_price)}đ</b></td>
                    </tr>
                `,
        );
      });

      // 4. Render lịch sử xử lý đơn hàng (Đồng bộ ngôn ngữ & Màu sắc hệ thống)
      const historyContainer = document.getElementById(
        "order_history_timeline",
      );
      historyContainer.innerHTML = "";

      if (data.history.length === 0) {
        historyContainer.innerHTML =
          '<p style="color: #888; text-align: center; font-style: italic; padding: 10px;">Chưa có lịch sử thay đổi.</p>';
      } else {
        data.history.forEach((log) => {
          const time = new Date(log.created_at).toLocaleString("vi-VN");
          const admin = log.admin_name ? log.admin_name : "Hệ thống/Khách hàng";
          const note = log.note
            ? `<br><small style="color: #40c057; font-weight: 500;"><i class="fas fa-comment-dots"></i> Lý do/Ghi chú: ${log.note}</small>`
            : "";

          // Biến đổi các trạng thái thô sang Tiếng Việt bằng hàm bổ trợ
          const oldStatusBadge = formatHistoryStatus(log.old_status);
          const newStatusBadge = formatHistoryStatus(log.new_status);

          // Lấy màu sắc nút tròn Timeline dựa trên trạng thái mới nhất để tạo điểm nhấn
          let dotColor = "#94a3b8"; // Mặc định xám
          if (log.new_status === "delivered") dotColor = "#10b981"; // Xanh lá thành công
          if (log.new_status === "cancelled") dotColor = "#ef4444"; // Đỏ nếu hủy đơn
          if (log.new_status === "shipping") dotColor = "#3b82f6"; // Xanh dương nếu đi giao

          const historyItem = `
                        <div style="margin-bottom: 20px; padding-left: 20px; border-left: 2px dashed #cbd5e1; position: relative;">
                            <span style="position: absolute; left: -7px; top: 3px; width: 12px; height: 12px; background: ${dotColor}; border-radius: 50%; box-shadow: 0 0 0 3px #fff;"></span>
                            <p style="margin: 0; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <strong style="color: #475569;">${time}</strong>: 
                                ${oldStatusBadge} 
                                <i class="fas fa-long-arrow-alt-right" style="font-size: 13px; color: #94a3b8; margin: 0 2px;"></i> 
                                ${newStatusBadge}
                            </p>
                            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 13px;">
                                <i class="fas fa-user-edit" style="font-size: 11px; margin-right: 3px;"></i> Thực hiện bởi: <strong style="color: #334155;">${admin}</strong> ${note}
                            </p>
                        </div>
                    `;
          historyContainer.insertAdjacentHTML("beforeend", historyItem);
        });
      }
    });
}

// EDIT MODAL
// UPDATE: Hàm mở modal chỉnh sửa đơn hàng (Tự động cập nhật trạng thái và gán ghi chú tương ứng)
function openEditOrderModal(order) {
  // 1. Điền các dữ liệu cơ bản của đơn hàng vào form
  document.getElementById("edit_order_id").value = order.id;
  document.getElementById("display_order_code").innerText = order.order_code;

  const statusSelect = document.getElementById("edit_order_status");
  const noteTextarea = document.getElementById("edit_order_note");

  if (statusSelect) {
    statusSelect.value = order.status; // Cập nhật trạng thái hiện tại của đơn hàng vào thẻ select
  }

  if (noteTextarea && statusSelect) {
    // Khai báo bản đồ ghi chú mẫu tương ứng với từng trạng thái bằng tiếng Việt
    const statusNotes = {
      pending: "Đơn hàng đang chờ hệ thống kiểm tra và xác nhận thông tin.",
      confirmed:
        "Đơn hàng đã được xác nhận thành công. Shop đang chuẩn bị đóng gói hàng hóa.",
      shipping:
        "Đơn hàng đã được bàn giao cho đơn vị vận chuyển. Vui lòng chú ý điện thoại từ shipper!",
      delivered:
        "Đơn hàng của bạn đã giao thành công. Cảm ơn bạn đã mua sắm tại StudentGear!",
      cancelled: "Đơn hàng đã bị hủy. Hệ thống rất tiếc vì sự bất tiện này.",
      returned:
        "Hệ thống đã nhận lại hàng hoàn và tiến hành thủ tục hoàn tiền/đổi trả.",
    };

    // Điền ghi chú mẫu tương ứng với trạng thái hiện hành của đơn hàng khi vừa mở modal
    const currentStatus = statusSelect.value;
    noteTextarea.value = statusNotes[currentStatus] || "";
  }

  // 2. Gọi hàm mở modal đã có animation của bạn
  openModal("editOrderModal");
}
function openRejectModal(requestId) {
  document.getElementById("reject_request_id").value = requestId;
  openModal("rejectModal");
}

document.addEventListener("DOMContentLoaded", function () {
  const statusSelect = document.getElementById("edit_order_status");
  const noteTextarea = document.getElementById("edit_order_note");

  const statusNotes = {
    pending: "Đơn hàng đang chờ hệ thống kiểm tra thông tin và chuẩn bị.",
    confirmed:
      "Đơn hàng đã được StudentGear xác nhận thành công và chuẩn bị đóng gói.",
    shipping:
      "Đơn hàng đã đóng gói hoàn tất và bàn giao cho đơn vị vận chuyển.",
    delivered: "Đơn hàng đã được giao thành công đến tay khách hàng.",
    cancelled: "Đơn hàng đã bị hủy bỏ bởi ban quản trị hệ thống.",
    returned:
      "Hệ thống đã nhận lại hàng hoàn trả và xử lý thủ tục cho quý khách.",
  };

  if (statusSelect && noteTextarea) {
    statusSelect.addEventListener("change", function () {
      const selectedStatus = this.value;
      if (statusNotes[selectedStatus] !== undefined) {
        noteTextarea.value = statusNotes[selectedStatus];
      } else {
        noteTextarea.value = "";
      }
    });
  }

  const originalOpenEditModal = window.openEditOrderModal;
  window.openEditOrderModal = function (orderData) {
    if (typeof originalOpenEditModal === "function") {
      originalOpenEditModal(orderData);
    }

    const currentStatus = orderData.status;
    const statusWeights = {
      pending: 1,
      confirmed: 2,
      shipping: 3,
      delivered: 4,
      cancelled: 5,
      returned: 6,
    };
    const currentWeight = statusWeights[currentStatus] || 0;

    Array.from(statusSelect.options).forEach((option) => {
      const optionValue = option.value;
      const optionWeight = statusWeights[optionValue] || 0;

      option.disabled = false;
      option.style.display = "block";

      if (optionWeight <= currentWeight) {
        option.disabled = true;
        option.style.display = "none";
      }
      if (currentStatus === "pending" && optionValue === "delivered") {
        option.disabled = true;
        option.style.display = "none";
      }
    });

    for (let i = 0; i < statusSelect.options.length; i++) {
      if (!statusSelect.options[i].disabled) {
        statusSelect.value = statusSelect.options[i].value;
        break;
      }
    }

    if (statusSelect) {
      statusSelect.dispatchEvent(new Event("change"));
    }
  };
});
