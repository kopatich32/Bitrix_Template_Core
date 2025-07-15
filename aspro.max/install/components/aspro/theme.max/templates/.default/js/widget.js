var restoreThematics, selectThematic, selectPreset, setConfiguration;

function saveFrontParameter(params) {
  paramsDefault = {
    VALUE: "",
    NAME: "",
    OPTIONS: {},
    RELOAD: false,
  };
  params = Object.assign({}, paramsDefault, params);

  $(".sharepreset-part--export").removeClass("sharepreset-part--exported2Link");

  //save option
  $.post(
    arAsproOptions["SITE_DIR"] + "ajax/options_save_mainpage.php",
    {
      VALUE: params.VALUE,
      NAME: params.NAME,
    }
  );
  if (params.RELOAD) {
    submitTimer = setTimeout(function () {
      $("form[name=style-switcher]").submit();
    }, timeoutSubmit);
  }
}

$(document).ready(function () {
  // selected thematic is current value by default
  var selectedThematic;
  if (arMaxOptions.THEMATICS && typeof arMaxOptions.THEMATICS === 'object') {
    selectedThematic = arMaxOptions.THEMATICS.VALUE;
  }

  // select current thematic value
  restoreThematics = function () {
    selectedThematic = arMaxOptions.THEMATICS.VALUE;
    selectThematic(selectedThematic, false);
  };

  // select thematic
  selectThematic = function (thematic, bShowPresets) {
    var $thematic = ($thematic = $(".presets .thematik .item[data-code=" + thematic + "]"));
    if ($thematic.length) {
      if (typeof arMaxOptions.THEMATICS.LIST[thematic] === "object") {
        // thematic found

        // save selected value
        selectedThematic = thematic;

        // mark as current
        $thematic.addClass("active").siblings().removeClass("active");

        // set "-" on presets`s subtab
        $(".presets .presets_subtabs .presets_subtab .desc").html("&mdash;");

        // set thematic title on thematics`s subtab
        $(".presets .presets_subtabs .presets_subtab:first .desc").text(arMaxOptions.THEMATICS.LIST[thematic].TITLE);

        // hide all presets
        $(".presets .presets_block .conf .item").addClass("hidden");

        // show add new preset in preset editor
        $(".presets .presets_block .conf .item .js-addpreset").closest(".item").removeClass("hidden"); //

        if (typeof BX.admin !== "object") {
          // if user is not admin, than hide APPLY buttons of all presets
          $(".presets .presets_block .conf .apply_conf_block").addClass("hidden");
        }

        // unmark current preset
        $(".presets .presets_block .conf .item .preset-block.current").removeClass("current");

        for (var i in arMaxOptions.THEMATICS.LIST[thematic].PRESETS.LIST) {
          //each thematic`s preset

          var preset = arMaxOptions.THEMATICS.LIST[thematic].PRESETS.LIST[i];
          var $presetBlock = $(".presets .presets_block .conf .item .preset-block[data-id=" + preset + "]");

          if ($presetBlock.length) {
            if (typeof arMaxOptions.PRESETS.LIST[preset] === "object") {
              // show preset
              $presetBlock.closest(".item").removeClass("hidden");

              // if selected thematic is without URL, than hide APPLY buttons of it`s presets
              if (arMaxOptions.THEMATICS.LIST[thematic].URL.length) {
                // show APPLY button
                $presetBlock.find(".apply_conf_block").removeClass("hidden");
              }

              if (arMaxOptions.THEMATICS.VALUE == thematic) {
                // selected current thematic

                // show APPLY button
                $presetBlock.find(".apply_conf_block").removeClass("hidden");

                if (arMaxOptions.PRESETS.VALUE == preset) {
                  // current preset

                  // mark as current preset
                  $presetBlock.addClass("current");

                  // set preset title on presets`s subtab
                  $(".presets .presets_subtabs .presets_subtab:last .desc").text(arMaxOptions.PRESETS.LIST[preset].TITLE);
                }
              }
            }
          }
        }

        if (typeof bShowPresets !== "undefined" && bShowPresets) {
          // open presets list
          $(".presets .presets_subtabs .presets_subtab").last().trigger("click");
        }

        return;
      }
    }
  };

  // select preset
  selectPreset = function (preset) {
    var $preset = $(".style-switcher .presets .preset-block[data-id=" + preset + "]");

    if ($preset.length) {
      if (
        // selected preset is current already or editing
        $preset.hasClass("current") ||
        $preset.hasClass("editing")
      ) {
        return;
      }

      if (typeof arMaxOptions.PRESETS.LIST[preset] === "object") {
        // is dev
        var bDev = location.hostname.indexOf("dev.aspro.ru") !== -1;

        // is demo
        var bDemo =
          !bDev &&
          typeof arMaxOptions.THEMATICS.LIST[selectedThematic] === "object" &&
          arMaxOptions.THEMATICS.LIST[selectedThematic].URL.length &&
          arMaxOptions.USE_DEMO_LINK === "Y";

        // is selected thematic not current
        var bPrepareWizard = !bDev && !bDemo && selectedThematic != arMaxOptions.THEMATICS.VALUE;

        if (bPrepareWizard) {
          // install new thematic
          prepareWizard(selectedThematic, preset);
        } else {
          // unmark current preset
          $(".style-switcher .presets .preset-block.current").removeClass("current");

          // mark as current
          $preset.addClass("current");

          // set preset title on presets`s subtab
          $preset.closest(".presets").find(".presets_subtab.active .desc").text($preset.find(".info .title").text());

          // apply preset configuration
          setConfiguration(selectedThematic, preset);
        }
      }
    }
  };

  setConfiguration = function (thematic, preset) {
    if (typeof arMaxOptions.THEMATICS.LIST[thematic] === "object") {
      if (typeof arMaxOptions.PRESETS.LIST[preset] === "object") {
        // is dev
        var bDev = location.hostname.indexOf("dev.aspro.ru") !== -1;

        // is demo
        var bDemo = !bDev && arMaxOptions.THEMATICS.LIST[thematic].URL.length;

        if (bDemo && thematic !== arMaxOptions.THEMATICS.VALUE) {
          location.href = arMaxOptions.THEMATICS.LIST[thematic].URL + "?aspro_preset=" + preset;
        } else {
          // order of main page blocks
          var order = [];

          // list of options to send
          var options = {
            backurl: arMaxOptions["SITE_DIR"],
            THEMATIC: thematic,
          };

          var serialize = $("form[name=style-switcher]").serializeArray();
          for (j in serialize) {
            // add each form option value
            options[serialize[j].name] = serialize[j].value;
          }

          if (typeof arMaxOptions.PRESETS.LIST[preset]["OPTIONS"] === "object") {
            for (j in arMaxOptions.PRESETS.LIST[preset]["OPTIONS"]) {
              // change value of each preset option in options list

              var val = arMaxOptions.PRESETS.LIST[preset]["OPTIONS"][j];
              if (typeof val !== "object") {
                options[j] = val;
              } else {
                if (typeof val.VALUE !== "undefined") {
                  options[j] = val.VALUE;

                  if (typeof val.ADDITIONAL_OPTIONS === "object") {
                    for (z in val.ADDITIONAL_OPTIONS) {
                      var addoption = val.ADDITIONAL_OPTIONS[z];
                      if (typeof addoption === "object") {
                        for (addoptioncode in addoption) {
                          if (typeof addoption[addoptioncode] !== "undefined") {
                            options[addoptioncode + "_" + z] = addoption[addoptioncode];
                          }
                        }
                      }
                    }
                  }

                  if (typeof val.SUB_PARAMS === "object") {
                    for (z in val.SUB_PARAMS) {
                      var subval = val.SUB_PARAMS[z];
                      if (typeof subval !== "object") {
                        options[val.VALUE + "_" + z] = subval;
                      } else {
                        if (typeof subval.VALUE !== "undefined") {
                          options[val.VALUE + "_" + z] = subval.VALUE;

                          if (typeof subval.TEMPLATE !== "undefined") {
                            options[val.VALUE + "_" + z + "_TEMPLATE"] = subval.TEMPLATE;

                            if (typeof subval.ADDITIONAL_OPTIONS !== "undefined") {
                              for (addoptioncode in subval.ADDITIONAL_OPTIONS) {
                                options[val.VALUE + "_" + z + "_" + addoptioncode + "_" + subval.TEMPLATE] =
                                  subval.ADDITIONAL_OPTIONS[addoptioncode];
                              }
                            }
                          }

                          if (typeof subval.FON !== "undefined") {
                            options["fon" + val.VALUE + z] = subval.FON;
                          }
                        }
                      }
                    }
                  }

                  if (typeof val.DEPENDENT_PARAMS === "object") {
                    for (z in val.DEPENDENT_PARAMS) {
                      var depval = val.DEPENDENT_PARAMS[z];
                      if (typeof depval !== "object") {
                        options[z] = depval;
                      }
                    }
                  }

                  if (typeof val.ORDER === "string") {
                    order.push({
                      NAME: "SORT_ORDER_" + j + "_" + val.VALUE,
                      VALUE: val.ORDER,
                    });

                    options["SORT_ORDER_" + j + "_" + val.VALUE] = val.ORDER;
                  }
                }
              }
            }
          }

          if (typeof arMaxOptions.THEMATICS.LIST[thematic]["OPTIONS"] === "object") {
            for (j in arMaxOptions.THEMATICS.LIST[thematic]["OPTIONS"]) {
              // change value of each thematic option in options list

              var val = arMaxOptions.THEMATICS.LIST[thematic]["OPTIONS"][j];
              if (typeof val !== "object") {
                options[j] = val;
              } else {
                if (typeof val.VALUE !== "undefined") {
                  options[j] = val.VALUE;

                  if (typeof val.ADDITIONAL_OPTIONS === "object") {
                    for (z in val.ADDITIONAL_OPTIONS) {
                      var addoption = val.ADDITIONAL_OPTIONS[z];
                      if (typeof addoption === "object") {
                        for (addoptioncode in addoption) {
                          if (typeof addoption[addoptioncode] !== "undefined") {
                            options[addoptioncode + "_" + z] = addoption[addoptioncode];
                          }
                        }
                      }
                    }
                  }

                  if (typeof val.SUB_PARAMS === "object") {
                    for (z in val.SUB_PARAMS) {
                      var subval = val.SUB_PARAMS[z];
                      if (typeof subval !== "object") {
                        options[val.VALUE + "_" + z] = subval;
                      } else {
                        if (typeof subval.VALUE !== "undefined") {
                          options[val.VALUE + "_" + z] = subval.VALUE;

                          if (typeof subval.TEMPLATE !== "undefined") {
                            options[val.VALUE + "_" + z + "_TEMPLATE"] = subval.TEMPLATE;

                            if (typeof subval.ADDITIONAL_OPTIONS !== "undefined") {
                              for (addoptioncode in subval.ADDITIONAL_OPTIONS) {
                                options[val.VALUE + "_" + z + "_" + addoptioncode + "_" + subval.TEMPLATE] =
                                  subval.ADDITIONAL_OPTIONS[addoptioncode];
                              }
                            }
                          }

                          if (typeof subval.FON !== "undefined") {
                            options["fon" + val.VALUE + z] = subval.FON;
                          }
                        }
                      }
                    }
                  }

                  if (typeof val.DEPENDENT_PARAMS === "object") {
                    for (z in val.DEPENDENT_PARAMS) {
                      var depval = val.DEPENDENT_PARAMS[z];
                      if (typeof depval !== "object") {
                        options[z] = depval;
                      }
                    }
                  }

                  if (typeof val.ORDER === "string") {
                    order.push({
                      NAME: "SORT_ORDER_" + j + "_" + val.VALUE,
                      VALUE: val.ORDER,
                    });

                    options["SORT_ORDER_" + j + "_" + val.VALUE] = val.ORDER;
                  }
                }
              }
            }
          }

          function _sendOptions() {
            $.ajax({
              type: "POST",
              data: options,
              success: function () {
                // close switcher
                $(".style-switcher .presets_action").trigger("click");

                // go to main page
                location.href = arMaxOptions["SITE_DIR"];
              },
            });
          }

          function _sendOrder() {
            if (order.length) {
              var sort = order.pop();
              $.ajax({
                url: arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php",
                type: "POST",
                data: sort,
                success: function () {
                  _sendOrder();
                },
              });
            } else {
              _sendOptions();
            }
          }

          // send each order array and than send options array
          _sendOrder();
        }
      }
    }
  };

  // show prepare wizard page, can redefine
  if (typeof prepareWizard === "undefined") {
    prepareWizard = function (thematic, preset) {
      if (typeof arMaxOptions.THEMATICS.LIST[thematic] === "object") {
        if (typeof arMaxOptions.PRESETS.LIST[preset] === "object") {
          $.ajax({
            url: $(".style-switcher .contents.wizard").data("script"),
            type: "POST",
            data: {
              action: "getform",
              thematic: thematic,
              preset: preset,
            },
            success: function (response) {
              // put response to content
              $(".style-switcher .contents.wizard").html(response);

              // show prepare wizard page
              $(".style-switcher .contents.wizard").addClass("active");
            },
          });
        }
      }
    };
  }
  //sort order for main page
  $(".refresh-block.sup-params .values .inner-wrapper").each(function () {
    var _th = $(this),
      sort_block = _th[0];
    Sortable.create(sort_block, {
      handle: ".drag",
      animation: 150,
      forceFallback: true,
      filter: ".no_drag",
      // Element dragging started
      onStart: function (/**Event*/ evt) {
        evt.oldIndex; // element index within parent
        window.getSelection().removeAllRanges();

        $(evt.item).find(".template_block").addClass("hidden");
      },
      // Element dragging ended
      onEnd: function (/**Event*/ evt) {
        $(evt.item).find(".template_block").removeClass("hidden");
      },
      onMove: function (evt) {
        return evt.related.className.indexOf("no_drag") === -1;
      },
      // Changed sorting within list
      onUpdate: function (/**Event*/ evt) {
        var itemEl = evt.item; // dragged HTMLElement
        var order = [],
          current_type = _th.data("key"),
          name = "SORT_ORDER_INDEX_TYPE_" + current_type;
        $(itemEl).find(".template_block").removeClass("hidden");

        _th.find(".option-wrapper").each(function () {
          order.push(
            $(this)
              .find('.blocks input[type="checkbox"]')
              .attr("name")
              .replace(current_type + "_", "")
          );
          let orderIndex = $(this).index() + 1;
          let dragBlock = $(
            'div[data-class="' +
              $(this)
                .find('.blocks input[type="checkbox"]')
                .attr("name")
                .toLowerCase()
                .replace(current_type + "_", "") +
              '_drag"]'
          );
          dragBlock.attr("data-order", orderIndex);
          dragBlock.css('--order-index', orderIndex);
        });

        $("input[name=" + name + "]").val(order.join(","));

        $(".sharepreset-part--export").removeClass("sharepreset-part--exported2Link");

        //save option
        $.post(arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php", {
          VALUE: order.join(","),
          NAME: name,
        });

        var eventdata = { action: "jsLoadBlock" };
        BX.onCustomEvent("onCompleteAction", [eventdata]);
      },
    });
  });

  if ($(".base_color_custom input[type=hidden]").length) {
    $(".base_color_custom input[type=hidden]").each(function () {
      var _this = $(this),
        parent = $(this).closest(".base_color_custom");
      _this.spectrum({
        preferredFormat: "hex",
        showButtons: true,
        showInput: true,
        showPalette: false,
        appendTo: parent,
        chooseText: BX.message("CUSTOM_COLOR_CHOOSE"),
        cancelText: BX.message("CUSTOM_COLOR_CANCEL"),
        containerClassName: "custom_picker_container",
        replacerClassName: "custom_picker_replacer",
        clickoutFiresChange: false,
        move: function (color) {
          var colorCode = color.toHexString();
          /*parent.find('span span.vals').text(colorCode);
					parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);
					*/
          parent.find("span span.bg").attr("style", "background:" + colorCode);
        },
        hide: function (color) {
          var colorCode = color.toHexString();
          /*parent.find('span span.vals').text(colorCode);
					parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);
					*/
          parent.find("span span.bg").attr("style", "background:" + colorCode);
        },
        change: function (color) {
          var colorCode = color.toHexString();
          parent.addClass("current").siblings().removeClass("current");

          parent.find("span span.vals").text(colorCode);
          parent.find("span.animation-all").attr("style", "border-color:" + colorCode);

          $("form[name=style-switcher] input[name=" + parent.find(".click_block").data("option-id") + "]").val(
            parent.find(".click_block").data("option-value")
          );
          $("form[name=style-switcher]").submit();
        },
      });
    });
  }

  $(".base_color_custom").click(function (e) {
    e.preventDefault();
    $("input[name=" + $(this).data("name") + "]").spectrum("toggle");
    return false;
  });

  if ($(".base_color.current").length) {
    $(".base_color.current").each(function () {
      var color_block = $(this).closest(".options").find(".base_color_custom"),
        curcolor = $(this).data("color");
      if (curcolor != undefined && curcolor.length) {
        $("input[name=" + color_block.data("name") + "]").spectrum("set", curcolor);
        color_block.find("span span").attr("style", "background:" + curcolor);
      }
    });
  }

  if (!funcDefined("showToggles")) {
    showToggles = function () {
      new DG.OnOffSwitchAuto({
        cls: ".block-item.active .custom-switch",
        textOn: "",
        height: 33,
        heightTrack: 16,
        textOff: "",
        trackColorOff: "f5f5f5",
        listener: function (name, checked) {
          if (window.array.indexOf(name) == -1) {
            window.array.push(name);
            setTimeout(function () {
              window.array.splice(window.array.indexOf(name), 1);
            }, 500);
            var bNested =
              $("input[name=" + name + "]").closest(".values").length &&
              !$("input[name=" + name + "]").closest(".subs").length;
            if (checked) $("input[name=" + name + "]").val("Y");
            else $("input[name=" + name + "]").val("N");

            if (bNested) {
              var ajax_btn = $('<div class="btn-ajax-block animation-opacity"></div>'),
                option_wrapper = $("input[name=" + name + "]").closest(".option-wrapper"),
                pos = BX.pos(option_wrapper[0], true),
                current_index = $("input[name=" + name + "]")
                  .closest(".inner-wrapper")
                  .data("key"),
                div_class = name.replace(current_index + "_", ""),
                top = 0;

              ajax_btn.html($(".values > .apply-block").html());
              option_wrapper.toggleClass("disabled");
              top = pos.top + $(".style-switcher .header").actual("outerHeight");
              ajax_btn.css("top", top);
              if ($(".btn-ajax-block").length) $(".btn-ajax-block").remove();
              ajax_btn.appendTo($(".style-switcher"));
              ajax_btn.addClass("opacity1");

              if (checked) {
                if (div_class == "WITH_LEFT_BLOCK") {
                  $(".wrapper_inner.front").removeClass("wide_page");
                  $(".wrapper1.front_page").addClass("with_left_block");
                  $(".wrapper_inner.front .container_inner > .right_block").removeClass("wide_Y").addClass("wide_N");
                  $(".wrapper_inner.front .container_inner > .left_block").removeClass("hidden");

                  if (typeof window["stickySidebar"] !== "undefined") {
                    window["stickySidebar"].updateSticky();
                  }
                }
                $(".drag-block[data-class=" + div_class.toLowerCase() + "_drag]").removeClass("hidden");
                $(".templates_block .item." + name + "").removeClass("hidden");

                InitFlexSlider();
                $(window).resize();

                if (div_class == "BIG_BANNER_INDEX") {
                  $(".wrapper1").addClass("long_banner");
                  $(window).resize();
                }
                if (div_class == "MAPS" && typeof map !== "undefined") {
                  setTimeout(function () {
                    map.setBounds(clusterer.getBounds(), {
                      zoomMargin: 40,
                      // checkZoomRange: true
                    });
                  }, 200);
                }
              } else {
                $(".drag-block[data-class=" + div_class.toLowerCase() + "_drag]").addClass("hidden");
                $(".templates_block .item." + name + "").addClass("hidden");

                if (div_class == "WITH_LEFT_BLOCK") {
                  $(".wrapper_inner.front").addClass("wide_page");
                  $(".wrapper1.front_page").removeClass("with_left_block");
                  $(".wrapper_inner.front .container_inner > .right_block")
                    .removeClass("wide_N wide_")
                    .addClass("wide_Y");
                  $(".wrapper_inner.front .container_inner > .left_block").addClass("hidden");

                  $(window).resize();
                }

                if (div_class == "BIG_BANNER_INDEX") {
                  $(".wrapper1").removeClass("long_banner");
                }
              }

              var eventdata = { action: "jsLoadBlock" };
              BX.onCustomEvent("onCompleteAction", [eventdata]);

              //save option
              $.post(arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php", {
                VALUE: $("input[name=" + name + "]").val(),
                NAME: name,
              });
            }

            setTimeout(function () {
              if (!bNested) $("form[name=style-switcher]").submit();
            }, 200);
          } else {
            return false;
          }
        },
      });
    };
  }

  //showToggles(); //replace checkbox in custom toggle

  $(".style-switcher .on-off-switch").on("click", function () {
    var $checkbox = $(this).prev();
    if ($checkbox.is("input[type=checkbox]")) {
      var name = $checkbox.attr("name");

      var bChecked = !$checkbox.prop("checked");
      $checkbox.prop("checked", bChecked).trigger("change");
      
      if (submitTimer) {
        clearTimeout(submitTimer)
      }
      
      submitTimer = setTimeout(function () {
        if (window.array.indexOf(name) == -1) {
          window.array.push(name);
          setTimeout(function () {
            window.array.splice(window.array.indexOf(name), 1);
          }, 500);
          var bNested =
            $("input[name=" + name + "]").closest(".values").length &&
            !$("input[name=" + name + "]").closest(".subs").length;
          if (bChecked) $("input[name=" + name + "]").val("Y");
          else $("input[name=" + name + "]").val("N");

          if (bNested) {
            var ajax_btn = $('<div class="btn-ajax-block animation-opacity"></div>'),
              option_wrapper = $("input[name=" + name + "]").closest(".option-wrapper"),
              pos = BX.pos(option_wrapper[0], true),
              current_index = $("input[name=" + name + "]")
                .closest(".inner-wrapper")
                .data("key"),
              div_class = name.replace(current_index + "_", ""),
              top = 0;

            ajax_btn.html($(".values > .apply-block").html());
            option_wrapper.toggleClass("disabled");
            top = pos.top + $(".style-switcher .header").actual("outerHeight");
            ajax_btn.css("top", top);
            if ($(".btn-ajax-block").length) $(".btn-ajax-block").remove();
            ajax_btn.appendTo($(".style-switcher"));
            ajax_btn.addClass("opacity1");

            if (bChecked) {
              if (div_class == "WITH_LEFT_BLOCK") {
                const $leftBlockOnFront = document.querySelector(".wrapper_inner.front .container_inner .left_block");

                if (!$leftBlockOnFront) {
                  BX.ajax({
                    url: arAsproOptions["SITE_DIR"] + "ajax/left_block.php",
                    processData: false,
                    onsuccess: function (html) {
                      isOnceInited = false;
                      const ob = BX.processHTML(html);

                      document
                        .querySelector(".wrapper_inner.front .container_inner")
                        .insertAdjacentHTML("beforeend", ob.HTML);

                      BX.ajax.processScripts(ob.SCRIPT);
                      if (ob.STYLE.length > 0) {
                        BX.loadCSS(ob.STYLE);
                      }

                      if (typeof window["stickySidebar"] !== "undefined") {
                        window["stickySidebar"].updateSticky();
                      }
                    },
                    onfailure: function(err) {
                      console.log(err);
                    }
                  });
                }
                $(".wrapper_inner.front").removeClass("wide_page");
                $(".wrapper1.front_page").addClass("with_left_block");
                $(".wrapper_inner.front .container_inner > .right_block").removeClass("wide_Y").addClass("wide_N");
                $(".wrapper_inner.front .container_inner > .left_block").removeClass("hidden");

                if (typeof window["stickySidebar"] !== "undefined") {
                  window["stickySidebar"].updateSticky();
                }
              }
              $(".drag-block[data-class=" + div_class.toLowerCase() + "_drag]").removeClass("hidden");
              $(".templates_block .item." + name + "").removeClass("hidden");

              $(window).resize();

              if (div_class == "BIG_BANNER_INDEX") {
                $(".wrapper1").addClass("long_banner");
                $(window).resize();
              }
              if (div_class == "MAPS" && typeof map !== "undefined") {
                setTimeout(function () {
                  map.setBounds(clusterer.getBounds(), {
                    zoomMargin: 40,
                    // checkZoomRange: true
                  });
                }, 200);
              }
            } else {
              $(".drag-block[data-class=" + div_class.toLowerCase() + "_drag]").addClass("hidden");
              $(".templates_block .item." + name + "").addClass("hidden");

              if (div_class == "WITH_LEFT_BLOCK") {
                $(".wrapper_inner.front").addClass("wide_page");
                $(".wrapper1.front_page").removeClass("with_left_block");
                $(".wrapper_inner.front .container_inner > .right_block")
                  .removeClass("wide_N wide_")
                  .addClass("wide_Y");
                $(".wrapper_inner.front .container_inner > .left_block").addClass("hidden");

                $(window).resize();
              }

              if (div_class == "BIG_BANNER_INDEX") {
                $(".wrapper1").removeClass("long_banner");
              }
            }

            var eventdata = { action: "jsLoadBlock" };
            BX.onCustomEvent("onCompleteAction", [eventdata]);

            //save option
            $.post(arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php", {
              VALUE: $("input[name=" + name + "]").val(),
              NAME: name,
            });
          }
          // setTimeout(function () {
            if (!bNested) $("form[name=style-switcher]").submit();
          // }, 400);
        } else {
          return false;
        }
      }, timeoutSubmit);
    }
  });

  const onScrollHandler = throttle(function (e) {
    var topPositionRightBlock = e.target.scrollTop;
    $.cookie("STYLE_SWITCHER_SCROLL_PARAMETERS", topPositionRightBlock, { path: arMaxOptions["SITE_DIR"] });
  }, 500);

  $(".style-switcher .contents.parametrs .right-block").on("scroll", onScrollHandler);

  if ($.cookie("STYLE_SWITCHER_SCROLL_PARAMETERS")) {
    document.querySelector(".right-block.scrollblock").scrollTop = $.cookie("STYLE_SWITCHER_SCROLL_PARAMETERS");
  }

  $(".style-switcher .item input[type=checkbox]").on("change", function () {
    $(".sharepreset-part--export").removeClass("sharepreset-part--exported2Link");

    var _this = $(this);
    if (_this.is(":checked")) _this.val("Y");
    else _this.val("N");
    if (typeof _this.data("dynamic") === undefined) {
      $("form[name=style-switcher]").submit();
    } else {
      $("." + _this.data("index_block")).toggleClass("grey_block");
      //save option
      $.post(arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php", {
        VALUE: _this.val(),
        NAME: _this.attr("name"),
      });
    }
  });

  $(".sup-params .values .subtitle").click(function () {
    var _this = $(this),
      wrapper = _this.closest(".option-wrapper");
    if (wrapper.find(".template_block > .item").is(":visible"))
      $.removeCookie("STYLE_SWITCHER_TEMPLATE" + wrapper.index(), { path: arMaxOptions["SITE_DIR"] });
    else $.cookie("STYLE_SWITCHER_TEMPLATE" + wrapper.index(), "Y", { path: arMaxOptions["SITE_DIR"] });

    wrapper.find(".template_block .item").slideToggle();
  });

  $(".presets .presets_subtabs .presets_subtab").on("click", function () {
    var _this = $(this);
    _this.siblings().removeClass("active");
    _this.addClass("active");

    $(".presets .presets_block .options").removeClass("active");
    _this
      .closest(".presets")
      .find(".options:eq(" + _this.index() + ")")
      .addClass("active");

    // $('.dynamic_left_side .cl').click();
    if (_this.index() == 0) {
      restoreThematics();
    }

    $.cookie("STYLE_SWITCHER_CONFIG_BLOCK", _this.index(), { path: arMaxOptions["SITE_DIR"] });
  });

  $(".style-switcher").on("click", ".can_save .save_btn", function () {
    var _this = $(this);

    if (timerHide) {
      clearTimeout(timerHide);
      timerHide = false;
    }

    $.ajax({
      type: "POST",
      url: arMaxOptions["SITE_DIR"] + "ajax/options_save.php",
      data: { SAVE_OPTIONS: "Y" },
      dataType: "json",
      success: function (response) {
        if ("STATUS" in response) {
          if (!$(".save_config_status").length)
            $('<div class="save_config_status"><span></span></div>').appendTo(_this.parent());
          if (response.STATUS === "OK") $(".save_config_status").addClass("success");
          else $(".save_config_status").addClass("error");

          $(".save_config_status span").text(BX.message(response.MESSAGE));

          $(".save_config_status").slideDown(200);
          timerHide = setTimeout(function () {
            // here delayed functions in event
            $(".save_config_status").slideUp(200, function () {
              $(this).remove();
              $(".style-switcher .parametrs .action_block").removeClass('can_save');
              $(".style-switcher .right-block .inner-content").removeClass("with-action-block");
            });
          }, 1000);
        }
      },
    });
  });

  $('.item.groups-tab a[data-toggle="tab"].linked').on("shown.bs.tab", function (e) {
    var _this = $(this);

    $.cookie("styleSwitcherTabs" + _this.closest(".tabs").data("parent"), _this.parent().index(), { path: "/" });
  });

  $(".style-switcher .section-block").on("click", function () {
    var $tab = $(this);

    $tab.addClass("active").siblings().removeClass("active");

    $(".style-switcher .right-block .contents." + $tab.data("type"))
      .addClass("active")
      .siblings()
      .removeClass("active");

    $.cookie("styleSwitcherType", $tab.data("type"), { path: "/" });

    // save switcher as open
    $.cookie("styleSwitcher", "open", { path: "/" });

    if ($tab.hasClass("share_tab") || $tab.hasClass("demos_tab") || $tab.hasClass("updates_tab")) {
      $.removeCookie("styleSwitcherType", { path: "/" });
      $.removeCookie("styleSwitcher", { path: "/" });
      if ($tab.is(".updates_tab.loading_state")) {
        getExternalNews();
      }
      else {
        if ($tab.is(".share_tab.loading_state")) {
          if ($(".style-switcher .contents.share").length) {
            $.ajax({
              url: $(".style-switcher .contents.share").data("script"),
              type: "POST",
              data: {
                siteId: arAsproOptions["SITE_ID"],
                siteDir: arAsproOptions["SITE_DIR"],
                lang: BX.message.LANGUAGE_ID,
              },
              beforeSend: function () {
                $tab.addClass("loading_state");
                $(".style-switcher .contents.share").addClass("form sending");
              },
              success: function (response) {
                // put response to content
                $(".style-switcher .contents.share").html(response);
              },
              error: function (jqXhr) {
                console.log(jqXhr);
              },
              complete: function () {
                $tab.removeClass("loading_state");
                $(".style-switcher .contents.share").removeClass("form sending");
              },
            });
          }
        }
      }
    }
  });

  $(".style-switcher .subsection-block").on("click", function () {
    $(this).siblings().removeClass("active");
    $(this).addClass("active");

    $(".style-switcher .right-block .contents .content-body .block-item").removeClass("active");
    $(".style-switcher .right-block .contents .content-body .block-item:eq(" + $(this).index() + ")").addClass(
      "active"
    );

    $.cookie("styleSwitcherSubType", $(this).index(), { path: "/" });
  });

  $(".style-switcher .reset").click(function (e) {
    $("form[name=style-switcher]").append('<input type="hidden" name="THEME" value="default" />');
    $("form[name=style-switcher]").submit();

    $.removeCookie("styleSwitcherTabsCatalog", { path: "/" });
  });

  $(".style-switcher .ext_hint_title").click(function () {
    var _this = $(this);

    if ($(".dynamic_left_side").length) $(".dynamic_left_side").remove();

    $('<div class="dynamic_left_side scrollblock"><div class="items_inner"></div></div>').appendTo(
      _this.closest(".contents.parametrs > .right-block")
    );
    $(
      '<div class="cl" title="' + BX.message("FANCY_CLOSE") + '">' + $(".close_block .closes").html() + "</div>"
    ).appendTo($(".dynamic_left_side"));

    $(".ext_hint_desc").find("iframe").attr("src", $(".ext_hint_desc").find("iframe").data("src"));

    $(".dynamic_left_side .items_inner").html(_this.siblings(".ext_hint_desc").html());

    if (timerDynamicLeftSide) {
      clearTimeout(timerDynamicLeftSide);
      timerDynamicLeftSide = false;
    }
    timerDynamicLeftSide = setTimeout(function () {
      $(".dynamic_left_side").addClass("active");
    }, 100);
  });

  $(".style-switcher .sup-params.options .block-title").click(function () {
    $(this).next().slideToggle();
  });

  $(
    ".style-switcher .options > .link-item,.style-switcher .options > div:not(.base_color_custom) .link-item,.style-switcher .options > div:not(.base_color_custom) .click_block"
  ).click(function (e) {
    var _this = $(this);
    var bMulti = _this.data("type") == "multi";
    var bCurrent = _this.hasClass("current");

    if (e && e.target && !bCurrent && $(e.target).closest(".switcher-select").length) {
      return;
    }

    if (
      !bCurrent ||
      (e &&
        e.target &&
        ($(e.target).closest(".subs").length))
    ) {
      // set cookie for scroll block
      if (typeof $(this).data("option-type") != "undefined") $.cookie("scroll_block", $(this).data("option-type"));

      // set action form for redirect
      if (typeof $(this).data("option-url") != "undefined")
        $("form[name=style-switcher]").prepend(
          '<input type="hidden" name="backurl" value=' + $(this).data("option-url") + " />"
        );
    }

    if (!bMulti && bCurrent) return;

    if (bMulti) {
      _this.toggleClass("current");
    } else {
      if (!_this.closest(".subs").length) _this.closest(".options").find(".link-item").removeClass("current");

      _this.siblings().removeClass("current");
      _this.addClass("current");
    }

    if (bMulti) {
      var input = $("form[name=style-switcher] input[name=" + _this.data("option-id") + "]");
      var inputVal = input.val();

      if (!inputVal) {
        input.val(_this.data("option-value"));
      } else {
        inputVal = inputVal.split(",");
        if (bCurrent) {
          inputVal.splice(inputVal.indexOf(_this.data("option-value")), 1);
        } else {
          inputVal.push(_this.data("option-value"));
        }
        inputVal = inputVal.join();
        input.val(inputVal);
      }
    } else {
      $("form[name=style-switcher] input[name=" + _this.data("option-id") + "]").val(_this.data("option-value"));
    }

    if (_this.closest(".sup-params").length) $.removeCookie("styleSwitcher", { path: "/" });

    if (_this.closest(".options").data("ajax") === "Y") {
      const value = _this.data("option-value").toLowerCase();

      if (_this.data("option-id") === "THEME_VIEW_COLOR") {
        $("body").removeClass("theme-default theme-dark theme-light");
        $("body").addClass("theme-" + value);

        $(".jqmOverlay").trigger("click");
      }

      var saveParams = {
        VALUE: _this.data("option-value"),
        NAME: _this.data("option-id"),
      };
      // saveFrontParameter(saveParams);

      //save option
      $.post(arMaxOptions["SITE_DIR"] + "ajax/options_save_mainpage.php", saveParams);

      // trigger theme color changed event only after saving options
      if (_this.data("option-id") === "THEME_VIEW_COLOR") {
        BX.onCustomEvent("onChangeThemeColor", [{ value: value }]);
      }
    } else {
      if (_this.closest(".options").hasClass("refresh-block")) {
        if (!_this.closest(".options").hasClass("sup-params")) var index = _this.index() - 1;
        _this.closest(".item").find(".sup-params.options").removeClass("active");
        _this
          .closest(".item")
          .find(".sup-params.options.s_" + _this.data("option-value") + "")
          .addClass("active");
        $("form[name=style-switcher]").submit();
      } else {
        $("form[name=style-switcher]").submit();
      }
    }
  });

  $(".tooltip-link").on("shown.bs.tooltip", function (e) {
    var tooltip_block = $(this).next(),
      wihdow_height = $(window).height(),
      scroll = $(this).closest("form").scrollTop(),
      pos = BX.pos($(this)[0], true),
      pos_tooltip = BX.pos(tooltip_block[0], true),
      pos_item_wrapper = BX.pos($(this).closest(".item")[0], true);

    if (!$(this).closest(".item").next().length && pos_tooltip.bottom > pos_item_wrapper.bottom) {
      tooltip_block.removeClass("bottom").addClass("top");
      tooltip_block.css({ top: pos.top - tooltip_block.actual("outerHeight") });
    }
  });

  function closeActiveSwitcherPopups() {
    var activePopup = $(".switcher-select__popup.active");
    if (activePopup.length) {
      activePopup.removeClass("active");
    }
  }

  $(document).on("click", ".switcher-select__current", function () {
    var _this = $(this);
    var popup = _this.siblings(".switcher-select__popup");
    var bActive = popup.hasClass("active");

    closeActiveSwitcherPopups();

    if (popup.length) {
      bCanSubmit = false;
      
      if (submitTimer) {
        clearTimeout(submitTimer);
        submitTimer = false;
      }

      if (bActive) {
        popup.removeClass("active");
      } else {
        popup.addClass("active");
      }
    }
  });

  $(document).on("click", function (e) {
    var _target = $(e.target);
    if (!_target.hasClass("switcher-select__popup-item") && !_target.closest(".switcher-select__current").length) {
      closeActiveSwitcherPopups();
    }
  });

  $(document).on("click", ".switcher-select__popup-item", function () {
    var _this = $(this);
    var value = _this.data("value");
    var title = _this.data("title");
    var parent = _this.closest(".switcher-select");
    var current = _this.hasClass("switcher-select__popup-item--current");

    if (!current && parent.length) {
      var input = parent.find(".switcher-select__input");
      if (input.length) {
        input.val(value);

        if (_this.closest(".link-item").length) {
          if (_this.closest(".link-item.current").length) {
            var saveParams = {
              VALUE: value,
              NAME: input.attr("name"),
              RELOAD: true,
            };
            saveFrontParameter(saveParams);
          } else {
            _this.closest(".link-item").trigger("click");
          }
        } else {
          var saveParams = {
            VALUE: value,
            NAME: input.attr("name"),
            RELOAD: true,
          };
          saveFrontParameter(saveParams);
        }
      }

      var current = parent.find(".switcher-select__current");
      if (current.length) {
        current.text(title);
      }

      parent.find(".switcher-select__popup-item--current").removeClass("switcher-select__popup-item--current");
      _this.addClass("switcher-select__popup-item--current");
    }

    var popup = _this.closest(".switcher-select__popup");
    if (popup.length) {
      popup.toggleClass("active");
    }
  });
});
