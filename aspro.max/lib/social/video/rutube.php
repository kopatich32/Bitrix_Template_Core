<?php

namespace Aspro\Max\Social\Video;

use Bitrix\Main\Localization\Loc;

class Rutube extends Base
{
	const API_URL = 'https://rutube.ru/api/video/person/';

	public function __construct(array $options)
	{
		parent::__construct($options);

		$this->sort = [
			'date' => '-publication_ts',
			'rating' => '-views',
		];
	}

	public function getVideo(): array 
	{
		$result = $this->getFormatResult([
			'channel_id' => $this->options['channel_id'],
			'params' => [
				'ordering' => $this->sort[$this->options['sort'] ?: 'date'],
			],
		]);

		if (empty($result['results'])) {
			return [
				'error' => [
					'error_msg' => Loc::getMessage('NO_ITEMS_FOUND'),
				],
			];
		}
		
		$this->items = $result['results'];
		if ($this->options['count']) {
			$this->items = array_slice($this->items, 0, $this->options['count']);
		}

		$this->normalizeResults([
			'video_url' => 'ORIGIN_SRC',
			'embed_url' => 'SRC',
			'thumbnail_url' => 'IMAGE',
			'title' => 'TITLE',
			'publication_ts' => 'DATE',
			'duration' => 'duration',
		]);

		$this->modifyItems();

		return $this->items;
	}

	public function getRightLinkBase(): string
	{
		return "https://rutube.ru/channel/{$this->options['channel_id']}";
	}

	protected function getFormatResult(array $options = []): array
	{
		return $this->getRequest($this->prepareUrlString($options));
	}

	protected function prepareUrlString(array $arOptions): string
	{
		$config = array_merge([
			'params' => [
				'ordering' => $this->sort['date'],
			],
		], $arOptions);

		$url = static::API_URL.$config['channel_id'].'/';

		if (!empty($config['params'])) {
			$url .= '?'.http_build_query($config['params']);
		}

		return $url;
	}

	protected function modifyItems()
	{
		foreach ($this->items as &$item) {
			if ($item['IMAGE']) {
				$item['IMAGE'] .= '?size=m';
			}
			$item['DATE'] = $this->formatDate($item['DATE']);
			$item['DURATION'] = $this->formatDuration($item['duration']);
		}
	}
}