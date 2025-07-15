if(typeof window.JCBasketShareNew === 'undefined'){
	window.JCBasketShareNew = function(rand, arParams, arResult){
		this.rand = rand;

		this.timerCopy = false;

		this.params = {};
		this.result = {};

		this.$popup = false;
		this.$block = false;
		this.$copy = false;
		this.$inputUrl = false;
		this.$error = false;

		if(typeof arParams === 'object'){
			this.params = arParams;

			this.init();
		}
	};

	window.JCBasketShareNew.prototype = {
		init: function(){
			this.$block = $('#basket-share-new-' + this.rand);

			if(this.$block.length){
				this.$popup = this.$block.closest('.popup');
				this.bPopup = this.$popup.length > 0;

				this.$copy = this.$block.find('.basket-share-new-copy-url');
				this.$inputUrl = this.$block.find('.basket-share-new-url input');
				this.$error = this.$block.find('.basket-share-new-error-text');

				var that = this;

				var waitInterval = setInterval(function(){
					if(that.$block.height()){
						clearInterval(waitInterval);

						if(typeof CheckPopupTop === 'function'){
							CheckPopupTop();
						}

						that.onResizeHandler();
					}
				}, 100);
			}

			this.bindEvents();
		},

		bindEvents: function(){
			var that = this;

			if(this.$block.length){
				if(this.bPopup){
					$(window).resize(function(){
						that.onResizeHandler();
					});
				}
			}

			$(document).ready(function(){
				if(that.$copy.length){
					that.waitClipboardInit(100, function(){
						var url = that.$copy.data('clipboard-text');

						clipboard = new Clipboard(that.$copy[0]);
						clipboard.on('success', function(e){
							let $text = that.$copy.find('span.basket-share-new-url-text');
							if ($text.length) {
								let $iconCopy = that.$copy.find('span.basket-share-new-url-icon i.copy');
								let $iconCopied = that.$copy.find('span.basket-share-new-url-icon i.copied');
								
								$text.text(BX.message('BSN_T_URL_COPIED_HINT'));
								that.$copy.addClass('copied');
								$iconCopy.addClass('hidden');
								$iconCopied.removeClass('hidden');

								if(that.timerCopy){
									clearTimeout(that.timerCopy);
									that.timerCopy = false;
								}

								that.timerCopy = setTimeout(function(){
									$text.text(BX.message('BSN_T_URL_COPY_HINT'));
									that.$copy.removeClass('copied');
									$iconCopy.removeClass('hidden');
									$iconCopied.addClass('hidden');
								}, 2000);
							}

						    e.clearSelection();
						});

						clipboard.on('error', function(e){
						    alert(BX.message('BSN_T_URL_COPY_ERROR_HINT'));
						});
					});
				}
			});
		},

		onResizeHandler: function(){
			if(typeof CheckPopupTop === 'function'){
				CheckPopupTop();
			}

			if(this.bPopup){
				if(window.matchMedia('(max-width:767px)').matches){
					// if(window.matchMedia('(max-width:430px)').matches){
					// 	$('html').css('overflow', 'hidden');
					// }
					// else{
					// 	$('html').css('overflow', '');
					// }

					// this.$popup.removeClass('odd');
					// if(this.$popup.width() % 2 > 0){
					// 	this.$popup.addClass('odd');
					// }
				}
			}
		},

		waitClipboardInit: function(delay, callback){
			var that = this;

			if(
				typeof Clipboard !== 'function' ||
				typeof Clipboard.prototype.on !== 'function'
			){
				setTimeout(function(){
					that.waitClipboardInit(delay, callback);
				}, delay);
			}
			else{
				if(typeof callback === 'function'){
					callback();
				}
			}
		}
	};
}