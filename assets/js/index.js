$(document).ready(function () {
  // 1. Khai báo các biến DOM
  const $slider = $("#mainSlider");
  const $wrapper = $("#bannerWrapper");
  const $items = $wrapper.find(".main_banner__item");
  const $tabs = $("#bannerTabs").find(".main_banner__tab-item");
  const $btnPrev = $(".main_banner__btn--prev");
  const $btnNext = $(".main_banner__btn--next");

  const totalSlides = $items.length;
  let currentIndex = 0;
  let autoSlideInterval;

  // 2. KHỞI TẠO: Tính toán chiều rộng cho wrapper
  function initSlider() {
    // Tổng chiều rộng = (số lượng slide) * 100%
    $wrapper.css("width", totalSlides * 100 + "%");
  }

  // 3. HÀM CHUYỂN SLIDE (Core Logic)
  function goToSlide(index) {
    // Đảm bảo index nằm trong khoảng cho phép
    if (index < 0) {
      currentIndex = totalSlides - 1;
    } else if (index >= totalSlides) {
      currentIndex = 0;
    } else {
      currentIndex = index;
    }

    // Tính toán khoảng cách di chuyển (âm để sang phải)
    const translateX = -currentIndex * (100 / totalSlides);

    // Di chuyển wrapper bằng CSS Transform
    $wrapper.css("transform", "translateX(" + translateX + "%)");

    // Cập nhật trạng thái Active cho Tabs
    $tabs.removeClass("main_banner__tab-item--active");
    $tabs.eq(currentIndex).addClass("main_banner__tab-item--active");
  }

  // 4. XỬ LÝ SỰ KIỆN

  // Click nút Next
  $btnNext.click(function () {
    goToSlide(currentIndex + 1);
    resetAutoSlide();
  });

  // Click nút Prev
  $btnPrev.click(function () {
    goToSlide(currentIndex - 1);
    resetAutoSlide();
  });

  // Click vào Tab bên dưới
  $tabs.click(function () {
    const tabIndex = $(this).index(); // Lấy thứ tự của tab được click
    goToSlide(tabIndex);
    resetAutoSlide();
  });

  // 5. TỰ ĐỘNG CHẠY (Auto Slide)
  function startAutoSlide() {
    autoSlideInterval = setInterval(function () {
      goToSlide(currentIndex + 1);
    }, 5000); // 5 giây đổi một lần
  }

  function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
  }

  // Dừng khi rê chuột vào, chạy lại khi rê chuột ra
  $slider.hover(
    function () {
      clearInterval(autoSlideInterval);
    },
    function () {
      startAutoSlide();
    },
  );

  // CHẠY KHỞI TẠO
  if (totalSlides > 0) {
    initSlider();
    startAutoSlide();
  }
});
