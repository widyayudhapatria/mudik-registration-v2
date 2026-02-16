/*  Theme Name: Linexon - Responsive Bootstrap 4 Landing page template
    Author: Themesdesign
    Version: 1.0.0
    File Description:Main JS file of the template
*/
(function ($) {
  "use strict";

  function initNavbarStickey() {
    $(window).scroll(function () {
      var scroll = $(window).scrollTop();
      if (scroll >= 50) {
        $(".sticky").addClass("darkheader");
      } else {
        $(".sticky").removeClass("darkheader");
      }
    });
  }

  function initSmoothLink() {
    $(".navigation-menu a").on("click", function (event) {
      var $anchor = $(this);
      $("html, body")
        .stop()
        .animate(
          {
            scrollTop: $($anchor.attr("href")).offset().top - 0,
          },
          1500,
          "easeInOutExpo",
        );
      event.preventDefault();
    });
  }

  function initNavbarToggler() {
    var scroll = $(window).scrollTop();

    $(".navbar-toggle").on("click", function (event) {
      $(this).toggleClass("open");
      $("#navigation").slideToggle(400);
    });

    $(".navigation-menu>li").slice(-2).addClass("last-elements");
  }

  function initScrollspy() {
    $("#navigation").scrollspy({ offset: 250 });
  }

  function init() {
    initNavbarStickey();
    initSmoothLink();
    initNavbarToggler();
    initScrollspy();
  }
  init();
})(jQuery);
