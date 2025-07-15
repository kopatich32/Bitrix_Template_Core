readyDOM(() => {
	BX.bindDelegate(document, 'click', { class: '_rutube-video' }, function () {
		const src = this.dataset.url;
		if (!src) return;

		const nodeIframe = BX.create('iframe', {
			attrs: {
				height: '100%',
				width: '100%',
				allow: 'clipboard-write; autoplay',
				frameborder: "0",
				webkitAllowFullScreen: true,
				mozallowfullscreen: true,
				allowFullScreen: true,
				src,
			},
		});

		nodeIframe.addEventListener('load', function() {
			this.contentWindow.postMessage(
				JSON.stringify({
					type: "player:play",
					data: {},
				}),
				"*"
			)
		});

		const nodeParent = this.parentNode;

		nodeParent.innerHTML = '';
		nodeParent.appendChild(nodeIframe);
	});
})
