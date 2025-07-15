<?php

namespace Aspro\Max\Social\Video;

use \Bitrix\Main\Localization\Loc;

class Youtube extends Base
{
	const API_URL = 'https://www.googleapis.com/youtube/v3/';
	protected string $access_token;

	public function __construct(array $options)
	{
		parent::__construct($options);
		$this->access_token = $this->options['api_token'] ?? '';
	}

	public function getVideo(): array
	{
		$this->checkApiToken();

		if ($this->error) {
			return [
				'error' => [
					'error_msg' => $this->error,
				],
			];
		}

		$arNormalizeFormat = [
			'snippet,thumbnails,high,url' => 'IMAGE',
			'snippet,title' => 'TITLE',
			'snippet,publishedAt' => 'DATE',
			'duration' => 'DURATION',
		];

		if ($this->options['playlist_id']) {
			$result = $this->getVideosByPlaylist();
			$arNormalizeFormat['snippet,resourceId,videoId'] = 'ID';
		} else {
			$result = $this->getVideosByChannel();
			$arNormalizeFormat['id,videoId'] = 'ID';
		}

		if (empty($result['items'])) {
			return [
				'error' => [
					'error_msg' => Loc::getMessage('NO_ITEMS_FOUND'),
				],
			];
		}

		$this->items = $result['items'];
		$this->processDurationVideos();

		$this->normalizeResults($arNormalizeFormat);

		$this->modifyItems();

		return $this->items;
	}

	protected function getVideosByPlaylist(): array
	{
		return $this->getFormatResult([
			'method' => 'playlistItems',
			'part' => 'snippet,id',
			'playlist' => $this->options['playlist_id'],
		]);
	}

	protected function getVideosByChannel(): array
	{
		return $this->getFormatResult([
			'method' => 'search',
			'part' => 'snippet',
		]);
	}

	protected function getFormatResult(array $options)
	{
		$config = array_merge([
			'method' => '',
			'part' => '',
			'playlist' => '',
			'addUrlParams' => '',
			'metadata' => '',
			'id' => '',
		], $options);

		$baseUrl = static::API_URL.$config['method'].'?key='.$this->access_token;

		$params = [];

		if ($config['playlist']) {
			$params['playlistId'] = $config['playlist'];
		} elseif ($config['metadata']) {
			$params['id'] = $config['id'];
		} else {
			$params['channelId'] = $this->options['channel_id'];
			$params['order'] = $this->options['sort'];
			$params['type'] = 'video';
		}

		$params['part'] = $config['part'];
		$params['maxResults'] = $this->options['count'];

		if ($config['addUrlParams']) {
			parse_str($config['addUrlParams'], $extraParams);
			$params = array_merge($params, $extraParams);
		}

		$queryString = http_build_query($params);

		$url = $baseUrl.'&'.$queryString;

		return $this->getRequest($url);
	}

	protected function processDurationVideos()
	{

		$durations = $this->getDurationVideos();

		$this->addDurationFieldToVideos($durations);
	}
	
	private function getDurationVideos()
	{
		$arIDs = [];
		foreach ($this->items as $item) {
			$arIDs[] = $this->getVideoId($item);
		}
		return $this->getFormatResult(['method' => 'videos', 'id' => implode(',', $arIDs), 'part' => 'contentDetails', 'metadata' => true]);
	}

	private function getVideoId($video)
	{
		return $video['snippet']['resourceId']['videoId'] ?? $video['id'];
	}
	
	private function addDurationFieldToVideos($durations)
	{
		if (!$durations['items']) return;

		foreach ($this->items as $key => &$item) {
			$item['duration'] = $this->formatDuration($durations['items'][$key]['contentDetails']['duration']);
		}
	}

	protected function formatDuration($duration)
	{
		$obDuration = new \DateInterval($duration);

		return ($obDuration->h > 0 ? sprintf('%02d', $obDuration->h).':' : '').sprintf('%02d', $obDuration->i).':'.sprintf('%02d', $obDuration->s);
	}

	protected function modifyItems()
	{
		if ($this->items) {
			foreach ($this->items as &$item) {
				$item['ORIGIN_SRC'] = 'https://youtube.com/watch?v='.$item['ID'];
				$item['DATE'] = $this->formatDate($item['DATE']);
			}
		}
	}

	public function getRightLinkBase(): string
	{
		return "https://youtube.com/channel/{$this->options['channel_id']}";
	}
}
