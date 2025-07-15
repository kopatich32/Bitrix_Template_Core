<?php

namespace Aspro\Max\Social;

use Bitrix\Main\Localization\Loc;

class Vk extends Base
{
	const API_VERSION = 5.131;
	const API_URL = 'https://api.vk.com/method/';

	public function __construct($token, $count = 10)
	{
		parent::__construct($token, $count);
	}

	protected function getFormatResult(array $arOptions = []): array
	{
		return $this->getRequest($this->prepareURLString($arOptions).'&count='.htmlspecialcharsbx($this->count_post).'&v='.htmlspecialcharsbx(static::API_VERSION));
	}

	public function getPosts(array $arOptions = [])
	{
		$this->checkApiToken();

		if ($this->error) {
			return [
				'error' => [
					'error_msg' => $this->error
				],
			];
		}

		$arDefaultOptions = [
			'OWNER_ID' => -1,
			'PARAMS' => [],
			'FIELDS' => [],
		];
		$arConfig = array_merge($arDefaultOptions, $arOptions);
		$owner_id = $arConfig['OWNER_ID'];
		$params = $arConfig['PARAMS'];
		$fields = implode(',', $arConfig['FIELDS']);

		$data = $this->getFormatResult([
			'METHOD' => 'wall.get',
			'PARAMS' => array_merge([
				'owner_id' => $owner_id,
				'extended' => 1
			], $params),
			'FIELDS' => $fields,
		]);

		if (isset($data['error'])) {
			return $data;
		}

		if (!count($data['response']['items'] ?? 0)) {
			return [
				'error' => [
					'error_msg' => Loc::getMessage('NO_ITEMS_AVAILABLE'),
				],
			];
		}

		$data['response']['items'] = array_map(function ($item) {
			if (isset($item['copy_history'])) {
				if (strlen($item['copy_history'][0]['text'])) {
					$item['text'] = $item['copy_history'][0]['text'];
				}
				if (isset($item['copy_history'][0]['attachments'])) {
					$item['attachments'] = $item['copy_history'][0]['attachments'];
				}

				$item['copy_history'] = true;
			}

			if (isset($item['attachments'])) {
				$item['attachments'] = array_map(function($attachment) {
					if (
						$attachment['type'] === 'link' &&
						isset($attachment['link']['photo']) &&
						count($attachment['link']['photo'])
					) {
						$attachment['type'] = 'photo';
						$attachment['photo'] = $attachment['link']['photo'];
						unset($attachment['link']);
					}

					return $attachment;
				}, $item['attachments']);
			}

			if (
				isset($item['attachments'])
				&& ($item['attachments'][0]['type'] ?? '') === 'link'
				&& is_array($item['attachments'][0]['link']['photo'] ?? false)
				&& count($item['attachments'][0]['link']['photo'])
			) {
				$item['attachments'][] = [
					'type' => 'photo',
					'photo' => $item['attachments'][0]['link']['photo']
				];
			}

			return $item;
		}, $data['response']['items']);

		$data['response']['items'] = array_filter($data['response']['items'], function ($item) {
			if (strlen(trim($item['text']))) return true;
			if (!isset($item['attachments'])) return false;

			return count(
				array_filter(
					$item['attachments'],
					fn ($attachment) => in_array($attachment['type'], ['video', 'photo'])
				)
			);
		});

		$countItems = count($data['response']['items']);
		if ($countItems < $this->count_post) {
			$offsetData = $this->getPosts($owner_id, ['offset' => $this->count_post]);

			if (isset($offsetData['response']['items'])) {
				$data['response']['items'] = array_merge($data['response']['items'], $offsetData['response']['items']);
			}
		}

		return $data;
	}
}
