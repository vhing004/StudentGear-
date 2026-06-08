document.addEventListener("DOMContentLoaded", function () {
  // 1. Cập nhật tổng tiền (Giữ nguyên logic của bạn nhưng tối ưu hiển thị)
  function updateTotal() {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach((sub) => {
      // Ưu tiên lấy giá trị từ dataset.raw để tránh lỗi định dạng tiền tệ
      total += parseFloat(
        sub.dataset.raw || sub.textContent.replace(/[^0-9]/g, ""),
      );
    });

    const formattedTotal = total.toLocaleString("vi-VN") + "₫";
    document.getElementById("subtotal").textContent = formattedTotal;
    document.getElementById("total").textContent = formattedTotal;
  }

  // 2. Xử lý nút tăng giảm
  document.querySelectorAll(".qty-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      // Trong cart.php của bạn dùng data-cart-id, nên hãy dùng đúng tên đó
      const input = this.parentElement.querySelector(".qty-input");
      let qty = parseInt(input.value);
      const max = parseInt(input.max) || 999;

      if (this.classList.contains("plus")) {
        if (qty < max) qty++; // Kiểm tra giới hạn tồn kho
      } else if (this.classList.contains("minus")) {
        qty = Math.max(1, qty - 1);
      }

      input.value = qty;
      updateSubtotal(this.closest("tr"), qty);
    });
  });

  // 3. Đồng bộ khi người dùng tự nhập số vào ô input
  document.querySelectorAll(".qty-input").forEach((input) => {
    input.addEventListener("change", function () {
      let qty = parseInt(this.value) || 1;
      const max = parseInt(this.max);

      if (qty < 1) qty = 1;
      if (qty > max) qty = max;

      this.value = qty;
      updateSubtotal(this.closest("tr"), qty);
    });
  });

  // 4. Cập nhật tạm tính cho từng dòng
  function updateSubtotal(row, qty) {
    const price = parseFloat(row.querySelector(".price").dataset.price); // Lấy từ dataset để chính xác hơn[cite: 2]
    const subtotalEl = row.querySelector(".subtotal");
    const newSubtotal = price * qty;

    subtotalEl.textContent = newSubtotal.toLocaleString("vi-VN") + "₫";
    subtotalEl.dataset.raw = newSubtotal;
    updateTotal();
  }

  // 5. Nút Cập nhật giỏ hàng: Gửi dữ liệu về Server[cite: 2]
  const updateBtn = document.getElementById("update-cart-btn");
  if (updateBtn) {
    updateBtn.addEventListener("click", function (e) {
      e.preventDefault(); // Chặn thẻ <a> nếu có[cite: 2]

      const cartItems = [];
      document.querySelectorAll(".qty-input").forEach((input) => {
        cartItems.push({
          cart_id: input.dataset.cartId, // Phải khớp với data-cart-id trong HTML[cite: 2]
          quantity: input.value,
        });
      });

      // Gửi dữ liệu bằng AJAX (fetch)[cite: 2]
      fetch("../handler/update_cart.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ cart_items: cartItems }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Giỏ hàng đã được cập nhật thành công!");
            location.reload(); // Tải lại để đồng bộ hoàn toàn[cite: 2]
          } else {
            alert("Lỗi: " + (data.message || "Không thể cập nhật giỏ hàng"));
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Đã xảy ra lỗi khi kết nối với máy chủ.");
        });
    });
  }
});

// Hàm tăng giảm số lượng trực quan
// function changeCartQty(button, change, maxStock) {
//   const input = button.parentElement.querySelector(".qty-input");
//   let currentVal = parseInt(input.value) || 1;
//   let newVal = currentVal + change;

//   if (newVal < 1) newVal = 1;
//   if (newVal > maxStock) newVal = maxStock;

//   input.value = newVal;
// }

// // Kiểm tra số lượng người dùng tự nhập vào ô
// function validateCartQty(input, maxStock) {
//   let val = parseInt(input.value) || 1;
//   if (val < 1) val = 1;
//   if (val > maxStock) val = maxStock;
//   input.value = val;
// }

// Hàm gửi tất cả dữ liệu số lượng lên handler/update_cart.php khi bấm nút cập nhật
function submitAllCartForms() {
  // Tìm form đầu tiên để lấy làm form chính gửi đi
  const mainForm = document.querySelector(".cart-item-form");
  if (!mainForm) return;

  // Thu thập toàn bộ các ô input số lượng từ các dòng sản phẩm khác gộp chung vào form chính
  const allForms = document.querySelectorAll(".cart-item-form");
  allForms.forEach((form) => {
    if (form !== mainForm) {
      const inputs = form.querySelectorAll("input");
      inputs.forEach((input) => {
        // Tạo thẻ sao chép ẩn để gom chung dữ liệu
        const cloneInput = input.cloneNode(true);
        cloneInput.type = "hidden";
        mainForm.appendChild(cloneInput);
      });
    }
  });

  // Tạo thêm biến nhận diện nút hành động "update_cart_action" cho PHP nhận biết
  const actionInput = document.createElement("input");
  actionInput.type = "hidden";
  actionInput.name = "update_cart_action";
  actionInput.value = "1";
  mainForm.appendChild(actionInput);

  // Thực hiện reload và đẩy dữ liệu đồng bộ sang backend
  mainForm.submit();
}
