if (typeof window.JDeveloper === "undefined") {
  window.JDeveloper = function (result) {
    var _private = {
      inited: false,
    };

    Object.defineProperties(this, {
      inited: {
        get: function () {
          return _private.inited;
        },
        set: function (value) {
          if (value) {
            _private.inited = true;
          }
        },
      },
    });

    this.result = function (key) {
      if (typeof result === "object") {
        if (typeof key !== "undefined") {
          if (Object.keys(result).indexOf(key) > -1) {
            return result[key];
          }
        }
      }

      return undefined;
    };

    this.init();
  };

  window.JDeveloper.prototype = {
    node: null,
    handlers: {},

    init: function () {
      if (!this.inited) {
        this.inited = true;

        this.node = BX("developer");
        this.node.developer = this;

        this.bindEvents();

        var prefersColorScheme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

        if (this.result.NEED_AJAX || this.result.color !== prefersColorScheme) {
          this.refresh();
        }
      }
    },

    bindEvents: function () {
      var that = this;

      if (typeof this.handlers.onChangePrefersColorScheme !== "function") {
        this.handlers.onChangePrefersColorScheme = function (event) {
          if (!event) {
            event = window.event;
          }

          var newColorScheme = event.matches ? "dark" : "light";
          that.refresh();
        };

        window
          .matchMedia("(prefers-color-scheme: dark)")
          .addEventListener("change", this.handlers.onChangePrefersColorScheme);
      }

      if (typeof this.handlers.onChangeThemeColor !== "function") {
        this.handlers.onChangeThemeColor = function () {
          that.refresh();
        };

        BX.addCustomEvent("onChangeThemeColor", this.handlers.onChangeThemeColor);
      }
    },

    unbindEvents: function () {
      if (typeof this.handlers.onChangeThemeColor === "function") {
        BX.removeCustomEvent("onChangeThemeColor", this.handlers.onChangeThemeColor);
        delete this.handlers.onChangeThemeColor;
      }
    },

    refresh: function () {
      var componentAction = "getDeveloper";
      var componentName = "aspro:developer.max";
      var sessid = BX.message("bitrix_sessid");
      var lang = BX.message("LANGUAGE_ID");
      var siteId = arAsproOptions.SITE_ID;
      var signedParams = this.result("SIGNED_PARAMS");
      var prefersColorScheme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

      var that = this;
      BX.ajax({
        url:
          "/bitrix/services/main/ajax.php?mode=ajax&c=" +
          encodeURIComponent(componentName) +
          "&action=" +
          componentAction +
          "&sessid=" +
          sessid +
          "&SITE_ID=" +
          siteId +
          "&siteId=" +
          siteId +
          "&lang=" +
          lang +
          "&signedParameters=" +
          encodeURIComponent(signedParams) +
          "&clear_cache=Y&prefersColorScheme=" +
          prefersColorScheme,
        method: "POST",
        async: true,
        processData: true,
        scriptsRunFirst: true,
        emulateOnload: true,
        start: true,
        cache: false,
        onsuccess: function (response) {
          if (response.trim().length) {
            if (that.node) {
              that.unbindEvents();
              that.node.parentElement.insertAdjacentHTML("beforeEnd", response);
              that.node.remove();

              that.node = BX("developer");
              that.node.developer = that;
              that.bindEvents();
            }
          }
        },
        onfailure: function () {},
      });
    },
  };
}
