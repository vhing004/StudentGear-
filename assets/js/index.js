// BANNER
$(document).ready(function () {
  $(".banner__slider").slick({
    slidesToShow: 1,
    slideToScroll: 1,
    autoplaySpeed: 2000,
    arrows: true,
    prevArrow:
      "<button type='button' class='slick-prev pull-left arrow'><i class='fa-solid fa-chevron-left' aria-hidden='true'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right arrow'><i class='fa-solid fa-chevron-right' aria-hidden='true'></i></button>",
    dots: true,
    customPaging: function (slider, i) {
      var title = $(slider.$slides[i]).data("title");
      return '<a class="dot-link">' + title + "</a>";
    },
  });
});
