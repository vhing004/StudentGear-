// FUNNTION OPEN MODAL
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove("is-closing");
  modal.classList.add("is-open");
  document.body.style.overflow = "hidden";
}

//  FUNNTION CLOSE MODAL WITH ANIMATION
function closeModal(modalId) {
  const modal = document.getElementById(modalId);

  // Thêm class closing để kích hoạt animation slideUp và fadeOut
  modal.classList.add("is-closing");

  // Đợi animation chạy xong (0.3s = 300ms) rồi mới ẩn hẳn
  setTimeout(() => {
    modal.classList.remove("is-open");
    modal.classList.remove("is-closing");
    document.body.style.overflow = "auto";
  }, 300);
}

// CLICK OUT
window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    const modalId = event.target.id;
    closeModal(modalId);
  }
};

// PREVIEW IMAGE CHOOSE FILE
function previewImage(input) {
  const container = document.getElementById("image_preview_container");
  const preview = document.getElementById("add_preview");

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      container.style.display = "block";
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document
  .getElementById("product_image_input")
  .addEventListener("change", function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        document
          .getElementById("img_preview")
          .setAttribute("src", e.target.result);
        document.getElementById("image_preview_box").style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  });

function removePreview() {
  document.getElementById("product_image_input").value = "";
  document.getElementById("image_preview_box").style.display = "none";
}
