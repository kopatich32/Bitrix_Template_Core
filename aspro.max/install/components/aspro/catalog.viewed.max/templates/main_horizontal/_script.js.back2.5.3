/*BX.addCustomEvent('onSlideInit', function(eventdata) {
	try{
		ignoreResize.push(true);
		if(eventdata){
			var slider = eventdata.slider;
			if(slider){
				$('.viewed_block .rows_block .item .item-title').sliceHeight({'autoslicecount': false, 'slice': 5});
				$('.viewed_block .rows_block .item').sliceHeight({'autoslicecount': false, 'slice': 5});
			}
		}
	}
	catch(e){}
	finally{
		ignoreResize.pop();
	}
});*/

showViewedItems = function (lastViewedTime, bShowMeasure, $viewedSlider) {
  if ($viewedSlider.length) {
    // save $.cookie option
    var bCookieJson = $.cookie.json;
    $.cookie.json = true;

    var arViewedLocal = {};
    var arViewedCookie = {};

    try {
      if (typeof BX.localStorage !== "undefined") {
        var localKey = "MAX_VIEWED_ITEMS_" + BX.message("SITE_ID");
        var cookieParams = { path: "/", expires: 30 };

        arViewedLocal = BX.localStorage.get(localKey) ? BX.localStorage.get(localKey) : {};
        arViewedCookie = $.cookie(localKey) ? $.cookie(localKey) : {};

        var empty =
          '<div class="block-item bordered rounded3"><div class="item_wrap item block-item__wrapper"></div></div>';

        // remove different items
        for (var itemKey in arViewedCookie) {
          if (arViewedLocal[itemKey] == undefined) {
            $viewedSlider
              .find("div[data-id=" + itemKey + "]")
              .closest(".block-item")
              .remove();
            $viewedSlider.closest(".block-items").append(empty);
          }
        }

        for (var PRODUCT_ID in arViewedLocal) {
          var $item = $viewedSlider.find("div[data-id=" + PRODUCT_ID + "]");
          if ($item.length) {
            var item = arViewedLocal[PRODUCT_ID];
            var picture =
              typeof $item.attr("data-picture") !== "undefined"
                ? JSON.parse($item.attr("data-picture"))
                : {
                    ID: false,
                    SRC: BX.message("SITE_TEMPLATE_PATH") + "/images/svg/noimage_product.svg",
                    ALT: item.NAME,
                    TITLE: item.NAME,
                  };
            var bIsOffer = typeof item.IS_OFFER !== "undefined" ? item.IS_OFFER === "Y" : false;
            var bWithOffers = typeof item.WITH_OFFERS !== "undefined" ? item.WITH_OFFERS === "Y" : false;
            var bShowEmptyPrice = $viewedEmptyPriceText !== "N" && (!item.MIN_PRICE || item.MIN_PRICE.VALUE === "0");
            var hidePriceBlock = sMissingGoodsPriceDisplay !== "N" || !item.MIN_PRICE || item.MIN_PRICE.VALUE !== "0";

            $item.html(
              '<div class="block-item__inner flexbox flexbox--row">' +
                '<div class="block-item__image block-item__image--wh90">' +
                '<a href="' +
                item.DETAIL_PAGE_URL +
                '" class="thumb">' +
                '<img border="0" class="img-responsive" src="' +
                picture.SRC +
                '" alt="' +
                (picture.ALT.length ? picture.ALT : item.NAME) +
                '" title="' +
                (picture.TITLE.length ? picture.TITLE : item.NAME) +
                '" />' +
                "</a>" +
                "</div>" +
                '<div class="block-item__info item_info">' +
                '<div class="block-item__title">' +
                '<a class="dark-color font_xs" href="' +
                item.DETAIL_PAGE_URL +
                '"><span>' +
                item.NAME +
                "</span></a>" +
                "</div>" +
                (hidePriceBlock
                  ? '<div class="block-item__cost cost prices clearfix font_sm">' +
                    (!bShowEmptyPrice
                      ? item.MIN_PRICE && item.MIN_PRICE.PRINT_DISCOUNT_VALUE !== undefined
                        ? item.MIN_PRICE.VALUE * 1 > item.MIN_PRICE.DISCOUNT_VALUE * 1
                          ? '<div class="price only_price">' +
                            (bWithOffers ? BX.message("CATALOG_FROM_VIEWED") + " " : "") +
                            item.MIN_PRICE.PRINT_DISCOUNT_VALUE +
                            (bShowMeasure && !bWithOffers && item.CATALOG_MEASURE_NAME.length
                              ? "/" + item.CATALOG_MEASURE_NAME
                              : "") +
                            "</div>"
                          : '<div class="price only_price">' +
                            (bWithOffers ? BX.message("CATALOG_FROM_VIEWED") + " " : "") +
                            item.MIN_PRICE.PRINT_DISCOUNT_VALUE +
                            (bShowMeasure && !bWithOffers && item.CATALOG_MEASURE_NAME.length
                              ? "/" + item.CATALOG_MEASURE_NAME
                              : "") +
                            "</div>"
                        : ""
                      : '<div class="price only_price">' + $viewedEmptyPriceText + "</div>") +
                    "</div>"
                  : "") +
                (bShowBonusViewed === "Y"
                  ? '<div class="bonus-system-block lb_bonus lb_bonus_viewed lb_ajax_' +
                    item.PRODUCT_ID +
                    '" data-item="' +
                    item.PRODUCT_ID +
                    '"></div>'
                  : "") +
                "</div>" +
                "</div>"
            );
          } else {
            // item not finded
            // may be if it`s new item (it`s detail page), than ACTIVE_FROM > last viewed exists item
            // or it`s old died item and quantity limit
            var ACTIVE_FROM =
              typeof arViewedLocal[PRODUCT_ID].ACTIVE_FROM !== "undefined"
                ? arViewedLocal[PRODUCT_ID].ACTIVE_FROM
                : typeof arViewedCookie[PRODUCT_ID] !== "undefined"
                ? arViewedCookie[PRODUCT_ID][0]
                : false;
            if (!ACTIVE_FROM || ACTIVE_FROM < lastViewedTime) {
              // get actual for save
              var _arViewedLocal = BX.localStorage.get(localKey) ? BX.localStorage.get(localKey) : {};
              var _arViewedCookie = $.cookie(localKey) ? $.cookie(localKey) : {};
              delete _arViewedLocal[PRODUCT_ID];
              delete _arViewedCookie[PRODUCT_ID];
              BX.localStorage.set(localKey, _arViewedLocal, 2592000); // 30 days
              $.cookie(localKey, _arViewedCookie, cookieParams);
            }
          }
        }
      }

      //remove some items
      $viewedSlider.find(">.item").each(function () {
        var PRODUCT_ID = typeof $(this).attr("data-id") !== "undefined" ? $(this).attr("data-id") : false;
        if (PRODUCT_ID && typeof arViewedLocal[PRODUCT_ID] === "undefined") {
          $(this).removeClass("has-item").find(">.block-item__inner").remove();
        }
      });

      // if no items than remove block
      if (!$viewedSlider.find(">.item.has-item").length) {
        $viewedSlider.closest(".viewed-wrapper").remove();
      }
    } catch (e) {
      console.error(e);
    } finally {
      // restore $.cookie option
      $.cookie.json = bCookieJson;
    }
  }
};

if (typeof window.frameCacheVars !== "undefined") {
  BX.addCustomEvent("onFrameDataReceived", function (json) {
    InitOwlSlider();
  });
}
