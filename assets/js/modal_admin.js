// --- 1. QUẢN LÝ ĐÓNG/MỞ MODAL ---
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "block";
    document.body.style.overflow = "hidden";
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }
}

// Đóng modal khi click ra ngoài vùng xám
window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    closeModal(event.target.id);
  }
};

// --- 2. HÀM PREVIEW ẢNH DÙNG CHUNG (Tái sử dụng cho mọi chỗ) ---
// inputId: ID của thẻ input file
// previewImgId: ID của thẻ <img> để hiển thị
// containerId: ID của div bao quanh (nếu muốn ẩn/hiện)
// oldContainerId: ID của div chứa ảnh cũ (dùng cho lúc Edit)
function handleImagePreview(
  inputId,
  previewImgId,
  containerId = null,
  oldContainerId = null,
) {
  const input = document.getElementById(inputId);
  if (!input) return;

  input.addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const previewImg = document.getElementById(previewImgId);
        if (previewImg) previewImg.src = e.target.result;

        if (containerId)
          document.getElementById(containerId).style.display = "block";
        if (oldContainerId)
          document.getElementById(oldContainerId).style.display = "none";
      };
      reader.readAsDataURL(file);
    }
  });
}

// Hàm xóa preview (Dùng cho nút X)
function clearPreview(inputId, containerId, oldContainerId = null) {
  document.getElementById(inputId).value = "";
  document.getElementById(containerId).style.display = "none";
  if (oldContainerId)
    document.getElementById(oldContainerId).style.display = "block";
}

// --- 3. KHỞI TẠO CÁC SỰ KIỆN PREVIEW ---
// Cho Modal Thêm Sản phẩm
handleImagePreview("product_image_input", "img_preview", "image_preview_box");

// Cho Modal Edit Sản phẩm
handleImagePreview(
  "edit_prod_image_input",
  "edit_prod_new_preview",
  "new_img_preview_container",
  "old_img_container",
);

// Cho Modal Thêm/Sửa Danh mục (Nếu bạn làm trang Categories)
handleImagePreview(
  "add_cat_image_input",
  "add_cat_preview_img",
  "add_cat_preview_container",
);

// --- 4. LOGIC RIÊNG CHO EDIT PRODUCT ---
function openEditProdModal(product) {
  // Điền text/số
  document.getElementById("edit_prod_id").value = product.id;
  document.getElementById("edit_prod_name").value = product.name;
  document.getElementById("edit_prod_price").value = product.price;
  document.getElementById("edit_prod_cost").value = product.cost_price;
  document.getElementById("edit_prod_stock").value = product.stock;
  document.getElementById("edit_prod_discount").value =
    product.discount_percent;
  document.getElementById("edit_prod_desc").value = product.description;
  document.getElementById("edit_prod_cat").value = product.category_id;

  // Checkbox
  document.getElementById("edit_prod_featured").checked =
    parseInt(product.is_featured) === 1;
  document.getElementById("edit_prod_new").checked =
    parseInt(product.is_new) === 1;
  document.getElementById("edit_prod_active").checked =
    parseInt(product.is_active) === 1;

  // Reset ảnh về trạng thái ban đầu (Hiện cũ, ẩn mới)
  document.getElementById("old_img_container").style.display = "block";
  document.getElementById("new_img_preview_container").style.display = "none";
  document.getElementById("edit_prod_image_input").value = "";

  const previewImg = document.getElementById("edit_prod_preview");
  previewImg.src = product.image.startsWith("http")
    ? product.image
    : "../../" + product.image;

  openModal("editProdModal");
}
