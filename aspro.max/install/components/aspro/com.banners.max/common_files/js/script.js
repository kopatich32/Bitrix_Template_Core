function checkNavColor(slider) {
  let curSlide = slider.find(".swiper-slide-active");
  var nav_color_flex = curSlide.data("nav_color"),
    menu_color = curSlide.data("text_color");

  if (nav_color_flex == "dark")
    slider.find(".swiper-pagination").addClass("flex-dark");
  else
    slider.find(".swiper-pagination").removeClass("flex-dark");

  if (typeof checkNavColor.hasLongBanner === "undefined") {
    checkNavColor.hasLongBanner = $(".wrapper1.long_banner").length;
  }

  if (checkNavColor.hasLongBanner) {
    if (menu_color == "light") {
      $(".header_wrap").addClass("light-menu-color");
    } else {
      $(".header_wrap").removeClass("light-menu-color");
    }

    // logo color
    if (typeof window.headerLogo !== 'undefined') {
      window.headerLogo.setColorOfBanner(curSlide[0]);
    }
  }  

  var eventdata = { slider: slider };
  BX.onCustomEvent("onSlide", [eventdata]);
}

readyDOM(function () {
  typeof useCountdown === 'function' && useCountdown();
  $(".main-slider").mouseenter(function () {
    if (!$(this).hasClass("video_visible") && $(this).data("swiper")) {
      if ($(this).data("swiper").params.autoplay.enabled) {
        $(this).data("swiper").autoplay.stop();
      }
    }
  });

  $(".main-slider").mouseleave(function () {
    if (!$(this).hasClass("video_visible") && $(this).data("swiper")) {
      if ($(this).data("swiper").params.autoplay.enabled) {
        $(this).data("swiper").autoplay.start();
      }
    }
  });
});

BX.addCustomEvent("onSetSliderOptions", function (options) {
  if ("type" in options && options.type === "main_banner") {
    if (typeof arMaxOptions["THEME"] != "undefined") {
      const slideshowSpeed = Math.abs(parseInt(arMaxOptions["THEME"]["BIGBANNER_SLIDESSHOWSPEED"]));
      const animationSpeed = Math.abs(parseInt(arMaxOptions["THEME"]["BIGBANNER_ANIMATIONSPEED"]));

      options.autoplay = slideshowSpeed && arMaxOptions["THEME"]["BIGBANNER_ANIMATIONTYPE"].length ? {} : false;
      // options.autoplay.pauseOnMouseEnter = true;
      // options.autoplay.disableOnInteraction = false;
      options.effect = arMaxOptions["THEME"]["BIGBANNER_ANIMATIONTYPE"] === "FADE" ? "fade" : "slide";
      if (animationSpeed >= 0) {
        options.speed = animationSpeed;
      }
      if (slideshowSpeed >= 0) {
        options.autoplay.delay = slideshowSpeed;
      }
      /*if (arMaxOptions["THEME"]["BIGBANNER_ANIMATIONTYPE"] !== "FADE") {
        options.direction =
          arMaxOptions["THEME"]["BIGBANNER_ANIMATIONTYPE"] === "SLIDE_VERTICAL" ? "vertical" : "horizontal";
      }*/
    }

    if ("CURRENT_BANNER_INDEX" in arMaxOptions && arMaxOptions["CURRENT_BANNER_INDEX"]) {
      currentBannerIndex = arMaxOptions["CURRENT_BANNER_INDEX"] - 1;
      if (currentBannerIndex < options.countSlides) {
        options.initialSlide = currentBannerIndex;
        options.autoplay = false;
      }
    }
  }
});

BX.addCustomEvent("onSlideChanges", function (eventdata) {
  if ("slider" in eventdata && eventdata.slider) {
    const slider = eventdata.slider;
    if (slider && slider.params) {
      if ("type" in slider.params && slider.params.type === "main_banner") {
        setTimeout(function () {
          checkNavColor($(slider.el));
        }, 100);
      }
    }
  }
});
