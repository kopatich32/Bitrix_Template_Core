var timerHide = false;
var timerDynamicLeftSide = false;
var timerResponse = false;
var hoveredSwitcher = false;
window.array = [];

// when change on-off checkbox or selectbox in front options
var submitTimer = false;
var timeoutSubmit = 800

$(document).ready(function () {
  checkNeedRequest = function () {
    return $(".style-switcher-body.loading_block").length;
  };
  /* get updates for solutions from aspro.ru */
  getExternalNews = function () {
    if (!$(".section-block.updates_tab").hasClass("loading_state")) {
      return;
    }
    $.ajax({
      url: "https://aspro.ru/demo/updates/index.php",
      type: "POST",
      data: { AJAX_FORM: "Y" },
      success: function (html) {
        $(".section-block.updates_tab").removeClass("loading_state");
        $(".right-block .inner-content .contents.updates .body_block").html(html);
      },
      error: function (jqXhr) {
        console.log(jqXhr);
      },
    });
  };
  /**/

  getWidgetHtml = function (callback) {
    if (checkNeedRequest()) {
      if ($(".top_block_switch").hasClass("loading")) {
        if (typeof callback === "function") {
          let t = setInterval(function() {
            if (!$(".top_block_switch").hasClass("loading")) {
              clearInterval(t);
              callback();
            }
          }, 100);

          return;
        } 
        else {
          return;
        }
      }
    }
    else {
      if (typeof callback === "function") {
        callback();
      }
      
      return;
    }
    
    $(".top_block_switch").addClass("loading");

    try {
      BX.ajax({
        url: arMaxOptions["SITE_DIR"] + "?BLOCK=widget",
        method: "POST",
        data: BX.ajax.prepareData({ ajax: "Y" }),
        dataType: "html",
        processData: false,
        start: true,
        headers: [{ name: "X-Requested-With", value: "XMLHttpRequest" }],
        onfailure: function (error) {
          console.error(error);
        },
        onsuccess: function (html) {
          var ob = BX.processHTML(html);

          BX.loadCSS(ob.STYLE);

          setTimeout(function () {
            $(".top_block_switch").removeClass("loading");
            $(".style-switcher-body").removeClass("loading_block").html(ob.HTML);
             BX.ajax.processScripts(ob.SCRIPT);
            
                        
            var eventdata = { action: "widgetLoaded" };
            BX.onCustomEvent("onWidgedLoaded", [eventdata]);
          }, 100);

          if (typeof callback === "function") {
            setTimeout(function () {
              callback();
            }, 150);
          }
        },
      });
    } catch (error) {
      console.error(error);
    }
  };

  HideHintBlock = function (bHideOverlay) {
    if (typeof bHideOverlay === "undefined" || bHideOverlay) {
      HideOverlay();
    }
    $.cookie("clickedSwitcher", "Y", { path: "/" });
    if ($(".hint-theme").length) {
      $(".hint-theme").fadeIn(300, function () {
        $(".hint-theme").remove();
      });
    }
  };

  $(".top_block_switch").mouseenter(function () {
    getWidgetHtml();
    hoveredSwitcher = true;
  });

  $("html, body").on("mousedown", function (e) {
    if (typeof e.target.className == "string" && e.target.className.indexOf("adm") < 0) {
      e.stopPropagation();
      if (!$(e.target).closest(".style-switcher .dynamic_left_side").length) {
        $(".style-switcher .dynamic_left_side").removeClass("active");
      }

      if (!$(e.target).closest(".style-switcher .contents.wizard").length) {
        $(".style-switcher .contents.wizard").removeClass("active");
      }
    }
  });

  $(".dynamic_left_side")
    .find("*")
    .on("mousedown", function (e) {
      e.stopPropagation();
    });

  $(document).on("click", ".presets .thematik .item", function () {
    var thematic = $(this).data("code");
    selectThematic(thematic, true);
  });

  $(document).on("click", ".style-switcher .presets .preset-block .apply_conf_block", function (e) {
    var preset = $(this).closest(".preset-block").data("id");
    selectPreset(preset);
  });

  $(document).on("click", ".style-switcher .switch", function (e) {
    e.preventDefault();

    var _this = $(this);
    var styleswitcher = _this.closest(".style-switcher");
    var bSwitchPresets = _this.hasClass("presets_action");
    
    if (_this.hasClass("loadings")) return;
    _this.addClass("loadings");

    setWidgetHtml(styleswitcher, _this, bSwitchPresets);
  });

  setWidgetHtml = function (styleswitcher, _this, bSwitchPresets) {
    getWidgetHtml(function () {
      var presets = styleswitcher.find(".presets");
      var parametrs = styleswitcher.find(".parametrs");

      styleswitcher.find(".section-block").removeClass("active");
      _this.removeClass("loadings");

      // getExternalNews();

      if (typeof getAjaxForm === "function") {
        //   getAjaxForm();
        getAjaxForm(function () {
          $(".section-block.demos_tab").removeClass("hidden");
        });
      }

      if (styleswitcher.hasClass("active")) {
        if (typeof restoreThematics === "function") {
          restoreThematics();
        }

        // current switch type
        var typeSwitcher = $.cookie("styleSwitcherType");

        // change switcher bgcolor
        styleswitcher.find(".switch").removeClass("active");
        styleswitcher.find(".presets_action").removeClass("active");

        if ((bSwitchPresets && typeSwitcher === "presets") || (!bSwitchPresets && typeSwitcher === "parametrs")) {
          HideHintBlock(true);

          // remove switcher type
          $.removeCookie("styleSwitcherType", { path: "/" });

          // save switcher as hidden
          $.removeCookie("styleSwitcher", { path: "/" });

          // hide switcher with transition
          styleswitcher.addClass("closes");
          setTimeout(function () {
            styleswitcher.removeClass("active");
          }, 300);
        } else {
          HideHintBlock(false);

          // save switcher type
          $.cookie("styleSwitcherType", bSwitchPresets ? "presets" : "parametrs", { path: "/" });

          // hide switcher title
          styleswitcher.find(".header .title").hide();

          // hide any content
          styleswitcher.find(".right-block .inner-content .contents").removeClass("active");

          // set presets visible or hidden with transition and change switcher bgcolor
          if (bSwitchPresets) {
            // styleswitcher.find('.header .title.title-presets').show();
            styleswitcher.find(".section-block.presets_tab").addClass("active");
            presets.addClass("active");
          } else if ($(this).hasClass("demo_action")) {
            styleswitcher.find(".section-block.demos_tab").removeClass("hidden").addClass("active");
            styleswitcher.find(".inner-content .contents.demos").addClass("active");

            $.removeCookie("styleSwitcherType", { path: "/" });
            $.removeCookie("styleSwitcher", { path: "/" });
          } else {
            // styleswitcher.find('.header .title.title-parametrs').show();
            styleswitcher.find(".section-block.parametrs_tab").addClass("active");
            parametrs.addClass("active");
          }

          $(this).addClass("active");
        }
      } else {
        HideHintBlock(true);

        // change switcher bgcolor
        _this.addClass("active");

        // save switcher type
        $.cookie("styleSwitcherType", bSwitchPresets ? "presets" : "parametrs", { path: "/" });

        // save switcher as open
        $.cookie("styleSwitcher", "open", { path: "/" });

        // hide any content
        styleswitcher.find(".right-block .inner-content .contents").removeClass("active");

        // set presets visible or hidden immediately before adding .active to .style-switcher
        if (bSwitchPresets) {
          // styleswitcher.find('.header .title.title-presets').show();
          styleswitcher.find(".section-block.presets_tab").addClass("active");
          presets.addClass("active");
        } else if (_this.hasClass("demo_action")) {
          styleswitcher.find(".section-block.demos_tab").removeClass("hidden").addClass("active");
          styleswitcher.find(".inner-content .contents.demos").addClass("active");

          $.removeCookie("styleSwitcherType", { path: "/" });
          $.removeCookie("styleSwitcher", { path: "/" });
        } else {
          // styleswitcher.find('.header .title.title-parametrs').show();
          styleswitcher.find(".section-block.parametrs_tab").addClass("active");
          parametrs.addClass("active");
        }

        // show overlay
        ShowOverlay();

        // show switcher with transition
        styleswitcher.removeClass("closes").addClass("active");
      }
    });
  };

  $(document).on("click", ".close-overlay", function () {
    HideHintBlock();
  });

  $(".close_block").click(function () {
    $(".jqmOverlay").trigger("click");
  });

  $(document).on("click", ".jqmOverlay", function () {
    var styleswitcher = $(".style-switcher");

    if (!$(".hint-theme").length) {
      HideOverlay();
    }

    styleswitcher.each(function () {
      var _this = $(this);
      _this.addClass("closes");

      setTimeout(function () {
        _this.removeClass("active");
      }, 300);

      $(".form_demo-switcher")
        .animate(
          {
            left: "-" + $(".form_demo-switcher").outerWidth() + "px",
          },
          100
        )
        .removeClass("active abs");
    });

    $(".style-switcher .switch,.style-switcher .presets_action").removeClass("active");

    restoreThematics();

    $.removeCookie("styleSwitcherType", { path: "/" });
    $.removeCookie("styleSwitcher", { path: "/" });
  });

  $(document).on("click", ".sharepreset-trigger-open", function (e) {
    e.preventDefault();
    $(".section-block.share_tab").trigger("click");
  });

  $(document).on("click", ".style-switcher .apply", function () {
    $("form[name=style-switcher]").submit();
  });

  $(document).on("click", ".style-switcher .preview_conf_block .btn", function () {
    var _this = $(this);

    if ($(".dynamic_left_side").length) $(".dynamic_left_side").remove();

    $('<div class="dynamic_left_side"><div class="items_inner"><div class="titles_block"></div></div></div>').appendTo(
      _this.closest(".contents.presets .presets_block")
    );
    $(".dynamic_left_side .titles_block").html(
      '<div class="title">' +
        _this.closest(".preset-block").find(".info .title").text() +
        "</div>" +
        '<div class="blocks_wrapper">' +
        '<div class="cl" title="' +
        BX.message("FANCY_CLOSE") +
        '">' +
        $(".close_block .closes").html() +
        "</div>" +
        (_this.closest(".preset-block").find(".apply_conf_block").hasClass("hidden")
          ? ""
          : '<div class="ch" data-id="' +
            _this.closest(".preset-block").data("id") +
            '">' +
            _this.closest(".preset-block").find(".apply_conf_block").html() +
            "</div>") +
        "</div>"
    );

    $('<div class="desc">' + _this.closest(".preset-block").find(".info .description").text() + "</div>").appendTo(
      $(".dynamic_left_side .items_inner")
    );
    if (_this.closest(".preset-block").find(".info .description").data("img"))
      $(
        '<div class="img"><img src="' +
          _this.closest(".preset-block").find(".info .description").data("img") +
          '" /></div>'
      ).appendTo($(".dynamic_left_side .items_inner"));

    /*$(".dynamic_left_side").mCustomScrollbar({
      mouseWheel: {
        scrollAmount: 150,
        preventDefault: true,
      },
    });*/
    if (timerDynamicLeftSide) {
      clearTimeout(timerDynamicLeftSide);
      timerDynamicLeftSide = false;
    }
    timerDynamicLeftSide = setTimeout(function () {
      $(".dynamic_left_side").addClass("active scrollblock");
    }, 100);
  });

  $(document).on("click", ".dynamic_left_side .ch .btn", function (e) {
    var preset = $(this).parent().data("id");
    selectPreset(preset);
    $(".dynamic_left_side").removeClass("active");
  });

  $(document).on("click", ".dynamic_left_side .cl", function (e) {
    $(".dynamic_left_side").removeClass("active");
  });
});
