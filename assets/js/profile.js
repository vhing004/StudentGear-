// Logic thay đổi nhanh ảnh xem trước (Preview) ngay khi người dùng chọn file
document.getElementById("avatar-input").addEventListener("change", function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("avatar-preview").src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

// XỬ LÝ ẨN THÔNG BÁO TỰ ĐỘNG SAU 3 GIÂY
// document.addEventListener("DOMContentLoaded", function () {
//   const toast = document.getElementById("toast-notification");
//   if (toast) {
//     setTimeout(function () {
//       toast.classList.add("toast-hidden");

//       setTimeout(function () {
//         toast.remove();
//       }, 500);
//     }, 3000);
//   }
// });
