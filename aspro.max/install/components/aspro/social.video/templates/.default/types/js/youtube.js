readyDOM(() => {
	BX.bindDelegate(document, 'click', { class: '_youtube-video' }, function () {
		const videoId = this?.dataset?.videoId;
		if (!videoId) return;

		if (
			!window.YoutubePlayerScriptLoaded
			&& !window.YoutubeReadyToLoadScript
		) {
			window.YoutubeReadyToLoadScript = true;

			const tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			const firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}

		let timeToAbortScript = 0;
		let interval = setInterval(() => {
			if (window.YoutubePlayerScriptLoaded) {
				var player = new YT.Player(`youtube-player-id-${videoId}`, {
					videoId,
					events: {
						'onReady': onPlayerReady,
					}
				});

				clearInterval(interval);
			}

			if ((timeToAbortScript += 100) > 15000) {
				clearInterval(interval);
				console.error('Youtube services is unavailable');
			}
		}, 100);

		this.classList.add('loaded');
	});
});

function onYouTubeIframeAPIReady() {
	window.YoutubePlayerScriptLoaded = true;
}

function onPlayerReady(event) {
	event.target.playVideo();
}