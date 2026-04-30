document.addEventListener("DOMContentLoaded", function () {
  // Cập nhật tổng tiền
  function updateTotal() {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach((sub) => {
      total += parseFloat(
        sub.dataset.raw || sub.textContent.replace(/[^0-9]/g, ""),
      );
    });
    document.getElementById("subtotal").textContent =
      total.toLocaleString("vi-VN") + "₫";
    document.getElementById("total").textContent =
      total.toLocaleString("vi-VN") + "₫";
  }

  // Xử lý nút tăng giảm
  document.querySelectorAll(".qty-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const cartId = this.dataset.id;
      const input = this.parentElement.querySelector(".qty-input");
      let qty = parseInt(input.value);

      if (this.classList.contains("plus")) {
        qty++;
      } else if (this.classList.contains("minus")) {
        qty = Math.max(1, qty - 1);
      }

      input.value = qty;
      updateSubtotal(this.closest("tr"), qty);
    });
  });

  // Cập nhật tạm tính cho từng dòng
  function updateSubtotal(row, qty) {
    const price = parseFloat(
      row.querySelector(".price").textContent.replace(/[^0-9]/g, ""),
    );
    const subtotalEl = row.querySelector(".subtotal");
    const newSubtotal = price * qty;

    subtotalEl.textContent = newSubtotal.toLocaleString("vi-VN") + "₫";
    subtotalEl.dataset.raw = newSubtotal;
    updateTotal();
  }

  // Xóa sản phẩm
  // document.querySelectorAll(".remove-btn").forEach((btn) => {
  //   btn.addEventListener("click", function () {
  //     if (confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) {
  //       const cartId = this.dataset.id;
  //       const row = this.closest("tr");

  //       fetch("<?= BASE_URL ?>handler/remove_from_cart.php", {
  //         method: "POST",
  //         headers: { "Content-Type": "application/x-www-form-urlencoded" },
  //         body: `cart_id=${cartId}`,
  //       })
  //         .then((res) => res.json())
  //         .then((data) => {
  //           if (data.success) {
  //             row.remove();
  //             updateTotal();
  //             if (document.querySelectorAll("tbody tr").length === 0) {
  //               location.reload();
  //             }
  //           } else {
  //             alert(data.message || "Có lỗi xảy ra");
  //           }
  //         });
  //     }
  //   });
  // });
  // Xóa sản phẩm - Dùng Form Submit (redirect)
  // document.querySelectorAll(".remove-btn").forEach((btn) => {
  //   btn.addEventListener("click", function () {
  //     if (!confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?")) return;

  //     const cartId = this.dataset.cartId;

  //     // Tạo form tạm để submit
  //     const form = document.createElement("form");
  //     form.method = "POST";
  //     form.action = "<?= BASE_URL ?>handlers/remove_from_cart.php";

  //     const input = document.createElement("input");
  //     input.type = "hidden";
  //     input.name = "cart_id";
  //     input.value = cartId;

  //     form.appendChild(input);
  //     document.body.appendChild(form);
  //     form.submit();
  //   });
  // });

  // Nút Cập nhật giỏ hàng (có thể gửi tất cả thay đổi nếu cần)
  document
    .getElementById("update-cart-btn")
    .addEventListener("click", function () {
      alert("Giỏ hàng đã được cập nhật!");
      // Sau này có thể gửi toàn bộ dữ liệu qua AJAX nếu muốn
    });
});
