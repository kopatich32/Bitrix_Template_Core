$(document).on("click", '*[data-event="mp_jqm"]', function (e) {
  e.preventDefault();
  e.stopPropagation();

  var _this = $(this);
  
  if (!$(this).hasClass("clicked")) {
    $(this).addClass("clicked");
    $(this).jqmExPopup();
  }
  
  return false;
});


if (!BX.type.isFunction("onLoadjqmPopup")) {
  var onLoadjqmPopup = function (name, hash) {
    hash.w.attr('data-jqm-loaded', '1');

    if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
      $("body").addClass("jqm-initied");
    }
    
    //marketings
    if (name == "dyn_mp_jqm") { 
      var eventdata = { hash: hash };
      BX.onCustomEvent("onMarketingPopupShow", [eventdata]);
    }

    $.each($(hash.t).get(0).attributes, function (index, attr) {
      if (/^data\-autoload\-(.+)$/.test(attr.nodeName)) {
        var key = attr.nodeName.match(/^data\-autoload\-(.+)$/)[1];
        var el = $('input[data-sid="' + key.toUpperCase() + '"]');
        var value = $(hash.t).data("autoload-" + key);
        value = String(value).replace(/%99/g, "\\"); // replace symbol \
        el.val(BX.util.htmlspecialcharsback(value)).attr("readonly", "readonly");
        el.closest(".form-group").addClass("input-filed");
        el.attr("title", el.val());
      }
    });

    // hash.w.find(">div").addClass("scrollblock");
    hash.w.addClass("scrollblock");
    hash.w.addClass("show").css({
      opacity: 1,
    });

    if (hash.c.noOverlay === undefined || (hash.c.noOverlay !== undefined && !hash.c.noOverlay)) {
      // let scrollbarWidth = window.innerWidth - document.documentElement.clientWidth + 'px';
      // $("body").css({ "padding-right": scrollbarWidth, overflow: "hidden", height: "100vh" });
      $("body").css({ overflow: "hidden", height: "100vh" });
      hash.w.closest("#popup_iframe_wrapper").css({ "z-index": 3000, display: "flex" });
    }

    if (hash.c.noOverlay !== undefined && hash.c.noOverlay) {
      hash.w.closest("#body_iframe_wrapper").css({ "z-index": 2999, display: "flex" });
    }

    $("." + name + "_frame").show();
  };
}

if (!BX.type.isFunction("onHidejqmPopup")) {
  var onHidejqmPopup = function (name, hash) {

    if (name == "dyn_mp_jqm") { 
      var eventdata = { hash: hash };
      BX.onCustomEvent("onMarketingPopupClose", [eventdata]);
    }

    hash.w.animate({ opacity: 0 }, 200, function () {
      hash.w.hide();
      hash.w.empty();
      hash.o.remove();
      hash.w.removeClass("show");

      
      if (!hash.w.closest("#popup_iframe_wrapper").find(".jqmOverlay").length) {
        $("body").css({ "padding-right": "", overflow: "", height: "" });
        hash.w.closest("#popup_iframe_wrapper").css({ "z-index": "", display: "" });
      }

      if (!hash.w.closest("#body_iframe_wrapper").find(".jqmWindow.show").length) {
        hash.w.closest("#body_iframe_wrapper").css({ "z-index": "", display: "" });
      }

      if (!$(".jqmOverlay:not(.mobp)").length || $(".jqmOverlay.waiting").length) {
        $("body").removeClass("jqm-initied");
      }

    });

    window.b24form = false;

    // hide bx calc
    let items = document.querySelectorAll(".bx-calendar");
    if (items) {
      items.forEach(item => {
        item.closest(".popup-window").style.display = "none";
      });
    }
  };
}

$.fn.jqmExPopup = function () {
  var _this = $(this);
  var name = _this.data("name");

  if (name.length && _this.attr("disabled") != "disabled") {
    var extClass = "",
      paramsStr = "",
      trigger = "";

    // call counter
    if (typeof $.fn.jqmEx.counter === "undefined") {
      $.fn.jqmEx.counter = 0;
    } else {
      ++$.fn.jqmEx.counter;
    }

    // trigger attrs and params
    $.each(_this.get(0).attributes, function (index, attr) {
      var attrName = attr.nodeName;
      var attrValue = _this.attr(attrName);
      if (attrName !== "onclick") {
        trigger += "[" + attrName + '="' + attrValue + '"]';
      }
      if (/^data\-param\-(.+)$/.test(attrName)) {
        var key = attrName.match(/^data\-param\-(.+)$/)[1];
        paramsStr += key + "=" + attrValue + "&";
      }
    });   

    // popup url
    var script = arAsproOptions["SITE_DIR"] + "ajax/form.php";
    script += "?" + paramsStr;

    // use overlay?
    var noOverlay = _this.data("noOverlay") == "Y";

    // unique frame to each trigger
    if (noOverlay) {
      var frame = $(
        '<div class="' +
          name +
          "_frame " +
          extClass +
          ' jqmWindow popup" data-popup="' +
          $.fn.jqmEx.counter +
          '"></div>'
      ).appendTo("#body_iframe_wrapper");
    } else {
      var frame = $(
        '<div class="' +
          name +
          "_frame " +
          extClass +
          ' jqmWindow popup" data-popup="' +
          $.fn.jqmEx.counter +
          '"></div>'
      ).appendTo("#popup_iframe_wrapper");
    }

    let overlayClass = 'jqmOverlay';
    if (_this.data("jqm-overlay-class")) {
      overlayClass += " " + _this.data("jqm-overlay-class");
    }

    loadJQM(() => {
      
      if (frame.attr('data-jqm-loaded') === '1') { 
        return;
      }
      
      frame.jqm({
        ajax: script,
        trigger: trigger,
        noOverlay: noOverlay,
        overlayClass: overlayClass,
        onLoad: function (hash) {
          onLoadjqmPopup(name, hash);
        },
        onHide: function (hash) {
          if (hash.w.hasClass('jqm-lock-close')) {
            return false;
          }
          onHidejqmPopup(name, hash);
        },
      });
      
      _this.trigger("click");
    });
  }
};

function loadJQM(cb) {
  if (typeof $.fn.jqm === "function") {
    cb();
  } else {
    BX.loadScript("/bitrix/js/aspro/popup/jqModal.js?v=1", () => cb());
  }
}