<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

class CAsproSocialVideo extends CBitrixComponent 
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['SHOW_TITLE'] = $arParams['SHOW_TITLE'] === 'Y';

		if (!isset($arParams['CACHE_TIME'])) {
			$arParams['CACHE_TIME'] = 86400;
		}

		if (!isset($arParams['SORT']) || $arParams['SORT'] === 'FROM_THEME') {
			$arParams['SORT'] = TSolution::getFrontParametrValue('SORT_'.strtoupper($arParams['VIDEO_SOURCE']));
		}

		if ($arParams['TITLE'] === 'FROM_THEME') {
			$arParams['TITLE'] = TSolution::getFrontParametrValue("TITLE_VIDEO_".strtoupper($arParams['VIDEO_SOURCE']));
		}
		
		if ($arParams['RIGHT_TITLE'] === 'FROM_THEME') {
			$arParams['RIGHT_TITLE'] = TSolution::getFrontParametrValue("RIGHT_TITLE_".strtoupper($arParams['VIDEO_SOURCE']));
		}

		if (!$arParams['CHANNEL_ID'] || $arParams['CHANNEL_ID'] === 'FROM_THEME') {
			$paramName = match($arParams['VIDEO_SOURCE']) {
				'vk' => 'GROUP_ID',
				default => 'CHANNEL_ID',
			};
			$arParams['CHANNEL_ID'] = TSolution::getFrontParametrValue($paramName.'_'.strtoupper($arParams['VIDEO_SOURCE']));
		}

		if (in_array($arParams['VIDEO_SOURCE'], ['youtube', 'vk'])) {
			if (!$arParams['API_TOKEN'] || $arParams['API_TOKEN'] === 'FROM_THEME') {
				$arParams['API_TOKEN'] = TSolution::getFrontParametrValue('API_TOKEN_'.strtoupper($arParams['VIDEO_SOURCE']));
			}
		}

		if ($arParams['VIDEO_SOURCE'] === 'youtube') {
			if (!$arParams['PLAYLIST_ID'] || $arParams['PLAYLIST_ID'] === 'FROM_THEME') {
				$arParams['PLAYLIST_ID'] = TSolution::getFrontParametrValue('PLAYLIST_ID_'.strtoupper($arParams['VIDEO_SOURCE']));
			}
		}

		return $arParams;
	}

	public function executeComponent()
	{
		if (
			$this->startResultCache(
				$this->arParams['CACHE_TIME']
			)
		) {
			$this->arResult = $this->arResultSet();
			$this->includeComponentTemplate();
		}
		return $this->arResult;
	}

	protected function arResultSet(): array
	{
		$obVideo = TSolution\Social\Video\Factory::create($this->arParams['VIDEO_SOURCE'], $this->prepareVideoOptions());
		if (is_object($obVideo) && method_exists($obVideo, 'getVideo')) {
			$result = $obVideo->getVideo();
			
		} else {
			$result = [
				'error' => [
					'error_msg' => Loc::getMessage('NO_ITEMS_FOUND'),
				],
			];
		}

		if ($result['error']) {
			$this->AbortResultCache();
			return $result;
		}

		$this->setRightLink($obVideo);

		$arResult = [
			'ITEMS' => $result,
		];

		return $arResult;
	}

	protected function prepareVideoOptions(): array
	{
		$options = [
			'channel_id' => $this->arParams['CHANNEL_ID'],
			'count' => ($this->arParams['COUNT_ELEMENTS'] ?: $this->arParams['ELEMENTS_ROW']),
		];
		if ($this->arParams['API_TOKEN']) {
			$options['api_token'] = $this->arParams['API_TOKEN'];
		}
		if ($this->arParams['PLAYLIST_ID']) {
			$options['playlist_id'] = $this->arParams['PLAYLIST_ID'];
		}
		if ($this->arParams['SORT']) {
			$options['sort'] = $this->arParams['SORT'];
		}

		return $options;
	}

	protected function setRightLink($obVideo)
	{
		if (
			!$this->arParams['RIGHT_LINK'] 
			|| $this->arParams['RIGHT_LINK'] !== 'FROM_THEME'
		) {
			return;
		}

		$this->arParams['RIGHT_LINK'] = $obVideo->getRightLinkBase();
		$this->arParams['RIGHT_LINK_EXTERNAL'] = true;
	}
}