<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	include_once '../../../../ajax/const.php';
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
}
?>
<?
global $arRegion;
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:news.detail", 
	"front_company", 
	array(
		"IBLOCK_TYPE" => "aspro_max_content",
		"IBLOCK_ID" => "#IBLOCK_COMPANY_ID#",
		"FIELD_CODE" => array(
			0 => "NAME",
			1 => "PREVIEW_TEXT",
			2 => "PREVIEW_PICTURE",
			3 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "VIDEO_SOURCE",
			1 => "COMPANY_NAME",
			2 => "URL",
			3 => "VIDEO_SRC",
			4 => "PROPS",
			5 => "SHOW_BUTTON",
			6 => "VIDEO",
			7 => "COMPANY_TEXT",
			8 => "",
		),
		"DETAIL_URL" => "",
		"SECTION_URL" => "",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"BROWSER_TITLE" => "-",
		"DISPLAY_PANEL" => "N",
		"SET_CANONICAL_URL" => "N",
		"SET_TITLE" => "N",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"ADD_ELEMENT_CHAIN" => "N",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "3600000",
		"CACHE_FILTER" => "Y",
		"CACHE_GROUPS" => "N",
		"USE_PERMISSIONS" => "N",
		"GROUP_PERMISSIONS" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"PAGER_TITLE" => "",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_SHOW_ALL" => "Y",
		"CHECK_DATES" => "N",
		"ELEMENT_ID" => "",
		"ELEMENT_CODE" => "front_company_item",
		"IBLOCK_URL" => "",
		"COUNT_BENEFITS" => "3",
		"COMPONENT_TEMPLATE" => "front_company",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"SET_BROWSER_TITLE" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_META_DESCRIPTION" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"STRICT_SECTION_CHECK" => "N",
		"SHOW_ALL_TITLE" => "",
		"MORE_BUTTON_TITLE" => "",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"WIDE_BLOCK" => "Y",
		"REVERCE_IMG_BLOCK" => "N",
		"TYPE_IMG" => "lg",
		"MESSAGE_404" => "",
		"FILTER_NAME" => "arRegionLink",
		"REGION" => $arRegion,
		"TYPE_BLOCK" => "type1",
		"TIZERS_IBLOCK_ID" => "",
		"COUNT_BENEFIT" => "4",
		"BENEFIT_COL" => "2",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO"
	),
	false
);?>