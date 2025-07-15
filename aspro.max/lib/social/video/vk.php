<?php

namespace Aspro\Max\Social\Video;

use \Aspro\Max\Social;

use Bitrix\Main\Localization\Loc;

class VK extends Social\Vk implements Social\Interface\Video
{
	use Social\Traits\Video;
	protected array $options;
	protected string $groupName = '';

	public function __construct(array $options)
	{
		parent::__construct($options['api_token'], $options['count']);
		$this->options = $options;
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

		$result = $this->getFormatResult([
			'METHOD' => 'video.get',
			'PARAMS' => [
				'owner_id' => "-{$this->options['channel_id']}",
				'extended' => '1',
			],
		]);

		if (empty($result['response']['items'])) {
			return [
				'error' => [
					'error_msg' => Loc::getMessage('NO_ITEMS_FOUND'),
				],
			];
		}

		$this->setGroupName($result);

		$this->items = $result['response']['items'];
		$this->normalizeResults([
			'title' => 'TITLE',
			'image,2,url' => 'IMAGE',
			'adding_date' => 'DATE',
			'player' => 'SRC',
			'duration' => 'duration',
			'id' => 'ID',
		]);

		$this->modifyItems();

		return $this->items;
	}

	protected function modifyItems()
	{
		if ($this->items) {
			foreach ($this->items as &$item) {
				$item['ORIGIN_SRC'] = "https://vk.com/video-{$this->options['channel_id']}_{$item['ID']}";

				$item['DURATION'] = $this->formatDuration($item['duration']);
			}
		}
	}

	protected function setGroupName(array $result)
	{
		if (!empty($result['response']['groups'][0]['screen_name'])) {
			$this->groupName = $result['response']['groups'][0]['screen_name'];
		}
	}

	public function getRightLinkBase(): string
	{
		return "https://vk.com/video/@{$this->groupName}";
	}
}
