<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Json;

// need for solution class and variables
if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
	die(Json::encode(['error' => 'Error include solution constants']));
}

$codeBlock = strtoupper($arParams['VIDEO_SOURCE']);
$codeBlockSuffix = '';
if ($codeBlock === 'VK') {
	$codeBlockSuffix = '_VIDEO';
}

$indexType = TSolution::getFrontParametrValue('INDEX_TYPE');
$blockType = TSolution::getFrontParametrValue($indexType.'_'.$codeBlock.$codeBlockSuffix.'_TEMPLATE');

if ($arParams['WIDE'] === 'FROM_THEME') {
	$arParams['WIDE'] = TSolution::getFrontParametrValue($indexType.'_'.$codeBlock.$codeBlockSuffix.'_WIDE_'.$blockType);
}

if ($arParams['ITEMS_OFFSET'] === 'FROM_THEME') {
	$arParams['ITEMS_OFFSET'] = TSolution::getFrontParametrValue($indexType.'_'.$codeBlock.$codeBlockSuffix.'_ITEMS_OFFSET_'.$blockType);
}

if ($arParams['BORDERED'] === 'FROM_THEME') {
	$arParams['BORDERED'] = TSolution::getFrontParametrValue($indexType.'_'.$codeBlock.$codeBlockSuffix.'_BORDERED_'.$blockType);
}

if ($arParams['ELEMENTS_ROW'] === 'FROM_THEME') {
	$arParams['ELEMENTS_ROW'] = TSolution::getFrontParametrValue($indexType.'_'.$codeBlock.$codeBlockSuffix.'_ELEMENTS_COUNT_'.$blockType);
}

if ($arParams['SHOW_TITLE'] === 'FROM_THEME') {
	$arParams['SHOW_TITLE'] = "Y";
}

if ($arParams['TITLE_POSITION'] === 'FROM_THEME') {
	$arParams['TITLE_POSITION'] = TSolution::getFrontParametrValue('TITLE_POSITION_'.$codeBlock.$codeBlockSuffix.'_'.$indexType);
}
?>
<div class="content_wrapper_block">
	<div class="pblock">
		<?$APPLICATION->IncludeComponent(
			"aspro:social.video",
			"",
			array(
				"VIDEO_SOURCE" => $arParams['VIDEO_SOURCE'],
				"API_TOKEN" => $arParams['API_TOKEN'],
				"CHANNEL_ID" => $arParams['CHANNEL_ID'],
				"SORT" => $arParams['SORT'],
				"PLAYLIST_ID" => $arParams['PLAYLIST_ID'],
				"ELEMENTS_ROW" => $arParams['ELEMENTS_ROW'],
				"TITLE" => $arParams['TITLE'],
				"SHOW_TITLE" => $arParams['SHOW_TITLE'],
				"ITEMS_OFFSET" => $arParams['ITEMS_OFFSET'],
				"TITLE_POSITION" => $arParams['TITLE_POSITION'],
				"RIGHT_LINK" => $arParams['RIGHT_LINK'],
				"RIGHT_TITLE" => $arParams["RIGHT_TITLE"],
				"RIGHT_LINK_EXTERNAL" => $arParams['RIGHT_LINK_EXTERNAL'],
				"BORDERED" => $arParams["BORDERED"],
				"WIDE" => $arParams["WIDE"],
				"MOBILE_SCROLLED" => $arParams["MOBILE_SCROLLED"],
				"MAXWIDTH_WRAP" => $arParams["MAXWIDTH_WRAP"],
				"COMPOSITE_FRAME_MODE" => $arParams['COMPOSITE_FRAME_MODE'],
				"COMPOSITE_FRAME_TYPE" => $arParams['COMPOSITE_FRAME_TYPE'],
				"CACHE_TYPE" => $arParams['CACHE_TYPE'],
				"CACHE_TIME" => $arParams['CACHE_TIME'],
				"CACHE_GROUPS" => $arParams['CACHE_GROUPS'],
			)
		);?>
	</div>
</div>