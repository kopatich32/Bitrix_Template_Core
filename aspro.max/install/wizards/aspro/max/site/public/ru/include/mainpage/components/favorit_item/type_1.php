<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	define("STATISTIC_SKIP_ACTIVITY_CHECK", "true");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}?>
<?$APPLICATION->IncludeComponent(
	"aspro:wrapper.block.max", 
	".default", 
	array(
		"IBLOCK_TYPE" => "#IBLOCK_MAX_CATALOG_TYPE#",
		"IBLOCK_ID" => "#IBLOCK_CATALOG_ID#",
		"FILTER_NAME" => "arRegionLink",
		"COMPONENT_TEMPLATE" => ".default",
		"SECTION_ID" => "",
		"SECTION_CODE" => "",
		"FILTER_PROP_CODE" => "FAVORIT_ITEM",
		"ELEMENT_SORT_FIELD" => "sort",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_SORT_FIELD2" => "id",
		"ELEMENT_SORT_ORDER2" => "desc",
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"ELEMENT_COUNT" => "30",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"DISPLAY_COMPARE" => "Y",
		"SHOW_MEASURE" => "Y",
		"DISPLAY_WISH_BUTTONS" => "Y",
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"SHOW_DISCOUNT_PERCENT_NUMBER" => "Y",
		"SHOW_DISCOUNT_TIME" => "Y",
		"SHOW_OLD_PRICE" => "Y",
		"PROPERTY_CODE" => array("CML2_ARTICLE"),
		"PRICE_CODE" => array(
			0 => "BASE",
			1 => "",
		),
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPERTIES" => array(
			
		),
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"SHOW_RATING" => "Y",
		"STIKERS_PROP" => "HIT",
		"SALE_STIKER" => "SALE_TEXT",
		"CONVERT_CURRENCY" => "N",
		"TITLE_BLOCK" => "Товар дня",
		"STORES" => array(
			0 => "",
			1 => "",
		),
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"SHOW_GALLERY" => "Y",
		"MAX_GALLERY_ITEMS" => "5",
		"ADD_PICT_PROP" => "MORE_PHOTO",
	),
	false
);?>