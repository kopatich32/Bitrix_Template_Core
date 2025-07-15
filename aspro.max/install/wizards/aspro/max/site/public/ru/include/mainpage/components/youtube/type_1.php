<?
use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	include_once '../../../../ajax/const.php';
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
}

if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
	throw new SystemException('Error include solution constants');
}
?>
<?$APPLICATION->IncludeComponent(
	"aspro:wrapper.block.max", 
	"front_social_video",
	array(
		"COMPONENT_TEMPLATE" => "front_social_video",
		"API_TOKEN_YOUTUBE" => "FROM_THEME",
		"CHANNEL_ID_YOUTUBE" => "FROM_THEME",
		"CHANNEL_ID" => "FROM_THEME",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "86400",
		"CACHE_GROUPS" => "N",
		"TITLE" => "FROM_THEME",
		"SHOW_TITLE" => "FROM_THEME",
		"ITEMS_OFFSET" => "FROM_THEME",
		"TITLE_POSITION" => "FROM_THEME",
		"PLAYLIST_ID_YOUTUBE" => "FROM_THEME",
		"PLAYLIST_ID" => "FROM_THEME",
		"COUNT_VIDEO_YOUTUBE" => "FROM_THEME",
		"COUNT_VIDEO_ON_LINE_YOUTUBE" => "FROM_THEME",
		"ELEMENTS_ROW" => "FROM_THEME",
		"BORDERED" => "FROM_THEME",
		"MOBILE_SCROLLED" => true,
		"MAXWIDTH_WRAP" => true,
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CACHE_FILTER" => "N",
		"SUBTITLE" => "FROM_THEME",
		"RIGHT_LINK" => "FROM_THEME",
		"RIGHT_TITLE" => "FROM_THEME",
		"WIDE" => "FROM_THEME",
		"VIDEO_SOURCE" => "youtube",
		"SORT" => "FROM_THEME",
	),
	false
);?>