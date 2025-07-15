window.appAspro = window.appAspro || {}

if (!window.appAspro.marketing) {
	window.appAspro.marketing = {
		inited: false,
		showPopup: function (action = '') {
			let selectorPopups = '.dyn_mp_jqm' + (action ? '[data-js-action="leave"]' : ':not([data-js-action])');
			let popupItems = $(selectorPopups);
			if(popupItems.length)
			{
				var allDelays = [];
				popupItems.each(function(i, el) {
					var jqmBlock = $(el),
						delay = 0;
		
					if(!jqmBlock.hasClass('initied')) // first load
					{
						jqmBlock.addClass('initied');
						
						if(jqmBlock.data('param-delay'))
							delay = jqmBlock.data('param-delay')*1000;
		
						while(allDelays.indexOf(delay) !== -1) {
							delay += 100;
						}
		
						allDelays.push(delay);
		
						if(typeof localStorage !== 'undefined')
						{
							let lsKey = jqmBlock.data('ls');
							var dataLS = localStorage.getItem(lsKey),
								ls = '';
							try{
								ls = JSON.parse(dataLS);
							}
							catch(e){
								ls = dataLS
							}
		
							let bShowOnCurrentDevice = true;
							try {
								let mediaSelector = jqmBlock.data('media-query');
								if (mediaSelector) {
									if (!matchMedia(mediaSelector).matches) { 
										bShowOnCurrentDevice = false;
									}
								}
							} catch (error) {
							}
		
							if(bShowOnCurrentDevice && (!ls || (ls && (ls.SHOW_ALWAYS === 'Y' || (ls.TIMESTAMP < Date.now() && ls.TIMESTAMP)))))
							{
								timeoutID = setTimeout(function(){
									jqmBlock.click();
								}, delay);
							}
							if (ls && (ls.SHOW_ALWAYS === 'N' && (ls.TIMESTAMP < Date.now() && ls.TIMESTAMP))) { 
								ls.SHOW_ALWAYS = 'Y';
								localStorage.setItem(lsKey, JSON.stringify(ls));
							}
						}
						else
						{
							var ls = $.cookie(jqmBlock.data('ls'));
							if(!ls)
							{
								timeoutID = setTimeout(function(){
									jqmBlock.click();
								}, delay);
							}
						}
		
					}
					else // ajax popup
					{
						
					}
		
				});
			}
		},
		
		stopShowAlways: function (lsKey) { 
			if(typeof localStorage !== 'undefined')
			{
				let dataLS = localStorage.getItem(lsKey),
					ls = '';
				try{
					ls = JSON.parse(dataLS);
				}
				catch(e){
					ls = dataLS
				}
				if(ls)
				{
					ls.SHOW_ALWAYS = 'N';
					localStorage.setItem(lsKey, JSON.stringify(ls));
				}
			}
		
		},
		
		checkStopAction: function (lsKey, action) {
			let allStopActions = $('.dyn_mp_jqm[data-ls=' + lsKey + ']').data('stop-actions');
			let bNeedStopShow = false;
		
			if (allStopActions) {
				let arStopActions = allStopActions.split(',');
				bNeedStopShow = arStopActions.indexOf(action) !== -1;
			}
		
			return bNeedStopShow;
		}, 

		addJSEvents: function () {
			// client leave site
			let jsPopupLeave = $('.dyn_mp_jqm[data-js-action="leave"]');
			if (jsPopupLeave.length) {
				_this = this;
				document.addEventListener("mouseleave", function(event){
					_this.showPopup('leave');
				});
			}
		}
	}
}


if (!appAspro.marketing.inited) { 
	appAspro.marketing.inited = true;
	

	$(document).ready(function () {
		BX.addCustomEvent("onMarketingPopupClose", function (eventdata) {
			if (eventdata) {
				let mpFrame = eventdata.hash.w,
					popupWrap = mpFrame.find('.marketing-popup'),
					lsKey = popupWrap.data('ls');
					
				if (lsKey) {
					let bNeedStopShow = appAspro.marketing.checkStopAction(lsKey, 'close');
	
					if (bNeedStopShow) {
						appAspro.marketing.stopShowAlways(lsKey);
					}
				}
			}
		});
	
		BX.addCustomEvent("onMarketingPopupShow", function (eventdata) {
			if (eventdata) {
				let hash = eventdata.hash;
				if (typeof $(hash.t).data("ls") !== " undefined" && $(hash.t).data("ls")) {
					var ls = $(hash.t).data("ls"),
						ls_timeout = 0,
						v = "";
			
					if ($(hash.t).data("ls_timeout")) ls_timeout = $(hash.t).data("ls_timeout");
			
					ls_timeout = ls_timeout ? Date.now() + ls_timeout * 1000 : "";
			
					if (typeof localStorage !== "undefined") {
						var val = localStorage.getItem(ls);
						try {
							v = JSON.parse(val);
						} catch (e) {
							v = val;
						}
						if (v != null) {
							localStorage.removeItem(ls);
						}
						v = {};
						v["VALUE"] = "Y";
						v["TIMESTAMP"] = ls_timeout; // default: seconds for 1 day
						
						if ($(hash.t).data("show_always"))
							v["SHOW_ALWAYS"] = $(hash.t).data("show_always");
			
						localStorage.setItem(ls, JSON.stringify(v));
					} else {
						var val = $.cookie(ls);
						if (!val) $.cookie(ls, "Y", { expires: ls_timeout }); // default: seconds for 1 day
					}
	
					var dopClasses = hash.w.find(".marketing-popup").data("classes");
					if (dopClasses) {
						hash.w.addClass(dopClasses);
					}
				}
			}
		});

		appAspro.marketing.showPopup();
		appAspro.marketing.addJSEvents();
		
		if(typeof window.frameCacheVars !== "undefined"){
			BX.addCustomEvent("onFrameDataReceived", function (json){
				appAspro.marketing.showPopup();
				appAspro.marketing.addJSEvents();
			});
		}
	})

	$(document).on('click', '.dyn_mp_jqm_frame [data-marketing-action]', function (e) {
		let mpFrame = $(this).closest('.dyn_mp_jqm_frame'),
			popupWrap = $(this).closest('.marketing-popup'),
			lsKey = popupWrap.data('ls'),
			bNeedStopShow = false,
			btnAction = $(this).attr('data-marketing-action'),
			bLockClose = mpFrame.hasClass('jqm-lock-close');
			
		bNeedStopShow = appAspro.marketing.checkStopAction(lsKey, btnAction);

		if (btnAction === 'btn1') {
			if (bLockClose) {
				mpFrame.removeClass('jqm-lock-close');
				bNeedStopShow = true;
			}
		} else {
			let disagreePopup = mpFrame.find('.marketing-popup-disagree');

			if (bLockClose) {
				if (popupWrap.length) { 
					popupWrap.hide();
				}
				if (disagreePopup.length) { 
					disagreePopup.removeClass('hidden');
				}
				bNeedStopShow = false;
			}
		}

		if (bNeedStopShow) { 
			appAspro.marketing.stopShowAlways(lsKey);
		}

		mpFrame.jqmHide();
	});

	$(document).on('click', '.coupon-block', function (e) {
		let couponText = this.querySelector('.coupon-block__text').textContent.trim();

		if(navigator.clipboard){
			navigator.clipboard.writeText(couponText).then(() => {
				// console.log('ok');
			}).catch(() => {
				console.log('error copy');
			});
		}
	});
	
}