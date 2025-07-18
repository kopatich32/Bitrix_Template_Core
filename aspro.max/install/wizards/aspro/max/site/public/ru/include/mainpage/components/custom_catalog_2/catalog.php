<?
use Bitrix\Main\SystemException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	include_once '../../../../ajax/const.php';
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
}
?>
<?$APPLICATION->IncludeComponent(
	"aspro:tabs.max", 
	"main", 
	array(
		"IBLOCK_TYPE" => "aspro_max_catalog",
		"IBLOCK_ID" => "#IBLOCK_CATALOG_ID#",
		"SECTION_ID" => "",
		"SECTION_CODE" => "#CATALOG_SECTION_CODE_2#",
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"ELEMENT_SORT_FIELD" => "SORT",
		"ELEMENT_SORT_ORDER" => "ASC",
		"ELEMENT_SORT_FIELD2" => "ID",
		"ELEMENT_SORT_ORDER2" => "DESC",
		"FILTER_NAME" => "arrFilterProp",
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"PAGE_ELEMENT_COUNT" => "8",
		"LINE_ELEMENT_COUNT" => "4",
		"PROPERTY_CODE" => array(
			0 => "CML2_ARTICLE",
			1 => "PROP_2089",
			2 => "PROP_2085",
			3 => "PROP_2084",
			4 => "PROP_2091",
			5 => "PROP_2086",
			6 => "PROP_2090",
			7 => "PROP_2092",
			8 => "PROP_2093",
			9 => "PROP_2094",
			10 => "",
		),
		"OFFERS_LIMIT" => "0",
		"SECTION_URL" => "",
		"DETAIL_URL" => "",
		"BASKET_URL" => "/basket/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"ADD_DETAIL_TO_SLIDER" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "N",
		"CACHE_FILTER" => "Y",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"BROWSER_TITLE" => "-",
		"ADD_SECTIONS_CHAIN" => "N",
		"DISPLAY_COMPARE" => "Y",
		"SET_TITLE" => "N",
		"SET_STATUS_404" => "N",
		"PRICE_CODE" => array(
			0 => "BASE",
			1 => "OPT",
		),
		"USE_PRICE_COUNT" => "Y",
		"SHOW_ONE_CLICK_BUY" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRODUCT_PROPERTIES" => array(
		),
		"USE_PRODUCT_QUANTITY" => "N",
		"CONVERT_CURRENCY" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "ajax",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"DISCOUNT_PRICE_CODE" => "",
		"AJAX_OPTION_ADDITIONAL" => "",
		"SHOW_ADD_FAVORITES" => "Y",
		"SHOW_ARTICLE_SKU" => "Y",
		"SECTION_NAME_FILTER" => "",
		"SECTION_SLIDER_FILTER" => "21",
		"COMPONENT_TEMPLATE" => "main",
		"OFFERS_FIELD_CODE" => array(
			0 => "ID",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "ASC",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "DESC",
		"SHOW_MEASURE" => "Y",
		"OFFERS_CART_PROPERTIES" => array(
		),
		"DISPLAY_WISH_BUTTONS" => "Y",
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"SHOW_OLD_PRICE" => "Y",
		"SHOW_RATING" => "Y",
		"MAX_GALLERY_ITEMS" => "5",
		"SHOW_GALLERY" => "Y",
		"ADD_PICT_PROP" => "MORE_PHOTO",
		"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
		"SALE_STIKER" => "SALE_TEXT",
		"FAV_ITEM" => "FAVORIT_ITEM",
		"SHOW_DISCOUNT_TIME" => "Y",
		"STORES" => array(
			0 => "2",
			1 => "",
		),
		"STIKERS_PROP" => "HIT",
		"SHOW_DISCOUNT_PERCENT_NUMBER" => "Y",
		"SHOW_MEASURE_WITH_RATIO" => "Y",
		"SHOW_DISCOUNT_TIME_EACH_SKU" => "Y",
		"TITLE_BLOCK" => "#CATALOG_SECTION_NAME_2#",
		"TITLE_BLOCK_ALL" => "Весь каталог",
		"ALL_URL" => "#CATALOG_SECTION_URL_2#",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"ADD_PICT_PROP_OFFER" => "MORE_PHOTO",
		"AJAX_LOAD" => "N",
		"CUSTOM_FILTER" => "",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"SHOW_TABS" => "N",
		"TABS_FILTER" => "PROPERTY",
		"USER_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"FIELDS" => array(
			0 => "",
			1 => "",
		),
		"HIT_PROP" => "HIT"
	),
	false
);?>