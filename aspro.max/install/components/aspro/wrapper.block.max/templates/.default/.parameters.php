<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$boolCatalog = CModule::IncludeModule("catalog");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$arProperty_LNS = $arProperty_N = $arProperty_X = $arProperty_S = $arProperty_E = array();
if (0 < intval($arCurrentValues['IBLOCK_ID']))
{
	$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"], "ACTIVE"=>"Y"));
	while ($arr=$rsProp->Fetch())
	{
		if($arr["PROPERTY_TYPE"] != "F")
			$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		elseif($arr['PROPERTY_TYPE'] === 'F')
			$arPropertyGallery[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

		if($arr["PROPERTY_TYPE"]=="N")
			$arProperty_N[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		if($arr["PROPERTY_TYPE"]=="S")
			$arProperty_S[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

		if($arr["PROPERTY_TYPE"]!="F")
		{
			if($arr["PROPERTY_TYPE"] == "L")
				$arProperty_X[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
			elseif($arr["PROPERTY_TYPE"] == "E" && $arr["LINK_IBLOCK_ID"] > 0)
				$arProperty_E[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
		}
	}
}

$arProperty_UF = array();
$arSProperty_LNS = array();
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arCurrentValues["IBLOCK_ID"]."_SECTION");
foreach($arUserFields as $FIELD_NAME=>$arUserField)
{
	$arProperty_UF[$FIELD_NAME] = $arUserField["LIST_COLUMN_LABEL"]? $arUserField["LIST_COLUMN_LABEL"]: $FIELD_NAME;
	if($arUserField["USER_TYPE"]["BASE_TYPE"]=="string")
		$arSProperty_LNS[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
}

$arOffers = CIBlockPriceTools::GetOffersIBlock($arCurrentValues["IBLOCK_ID"]);
$OFFERS_IBLOCK_ID = is_array($arOffers)? $arOffers["OFFERS_IBLOCK_ID"]: 0;
$arProperty_Offers = array();
if($OFFERS_IBLOCK_ID)
{
	$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$OFFERS_IBLOCK_ID, "ACTIVE"=>"Y"));
	while($arr=$rsProp->Fetch())
	{
		if($arr["PROPERTY_TYPE"] != "F")
			$arProperty_Offers[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$arSort = CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
	array('KEY_LOWERCASE' => 'Y')
);

$arPrice = array();
if ($boolCatalog)
{
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
	$rsPrice=CCatalogGroup::GetList($v1="sort", $v2="asc");
	while($arr=$rsPrice->Fetch()) $arPrice[$arr["NAME"]] = "[".$arr["NAME"]."] ".$arr["NAME_LANG"];
}
else
{
	$arPrice = $arProperty_N;
}

$arAscDesc = array(
	"asc" => GetMessage("IBLOCK_SORT_ASC"),
	"desc" => GetMessage("IBLOCK_SORT_DESC"),
);

$arTemplateParameters= Array(
	"IBLOCK_TYPE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockType,
		"REFRESH" => "Y",
	),
	"IBLOCK_ID" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_IBLOCK"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => $arIBlock,
		"REFRESH" => "Y",
	),
	"SECTION_ID" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_SECTION_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => '',
	),
	"SECTION_CODE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_SECTION_CODE"),
		"TYPE" => "STRING",
		"DEFAULT" => '',
	),
	"ELEMENT_SORT_FIELD" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
		"TYPE" => "LIST",
		"VALUES" => $arSort,
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "sort",
	),
	"ELEMENT_SORT_ORDER" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
		"TYPE" => "LIST",
		"VALUES" => $arAscDesc,
		"DEFAULT" => "asc",
		"ADDITIONAL_VALUES" => "Y",
	),
	"ELEMENT_SORT_FIELD2" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD2"),
		"TYPE" => "LIST",
		"VALUES" => $arSort,
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "id",
	),
	"ELEMENT_SORT_ORDER2" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER2"),
		"TYPE" => "LIST",
		"VALUES" => $arAscDesc,
		"DEFAULT" => "desc",
		"ADDITIONAL_VALUES" => "Y",
	),
	"FILTER_NAME" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("IBLOCK_FILTER_NAME_IN"),
		"TYPE" => "STRING",
		"DEFAULT" => "arrFilter",
	),
	"TITLE_BLOCK" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("T_TITLE_BLOCK"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	),
	"INCLUDE_SUBSECTIONS" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("CP_BCS_INCLUDE_SUBSECTIONS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"Y" => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ALL'),
			"A" => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ACTIVE'),
			"N" => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_NO'),
		),
		"DEFAULT" => "Y",
	),
	"SHOW_ALL_WO_SECTION" => array(
		"PARENT" => "DATA_SOURCE",
		"NAME" => GetMessage("CP_BCS_SHOW_ALL_WO_SECTION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	/*"DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
		"DETAIL",
		"DETAIL_URL",
		GetMessage("IBLOCK_DETAIL_URL"),
		"",
		"URL_TEMPLATES"
	),*/
	"DISPLAY_COMPARE" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_DESC_DISPLAY_COMPARE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"SHOW_MEASURE" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_MEASURE_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	"DISPLAY_WISH_BUTTONS" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("DISPLAY_WISH_BUTTONS_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"SHOW_DISCOUNT_PERCENT" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_DISCOUNT_PERCENT_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"SHOW_DISCOUNT_PERCENT_NUMBER" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_DISCOUNT_PERCENT_NUMBER_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	"SHOW_DISCOUNT_TIME" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_DISCOUNT_TIME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"SHOW_OLD_PRICE" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_OLD_PRICE_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"SHOW_ONE_CLICK" => Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SHOW_ONE_CLICK_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"ELEMENT_COUNT" => array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("IBLOCK_PAGE_ELEMENT_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "30",
	),
	/*"LINE_ELEMENT_COUNT" => array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("IBLOCK_LINE_ELEMENT_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "3",
	),*/
	"PRICE_CODE" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arPrice,
	),
	"ADD_PROPERTIES_TO_BASKET" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CP_BC_ADD_PROPERTIES_TO_BASKET"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y"
	),
	"PRODUCT_PROPERTIES" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CP_BCS_PRODUCT_PROPERTIES"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => array_merge($arProperty_X, $arProperty_E),
		"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
	),
	"PARTIAL_PRODUCT_PROPERTIES" => array(
		"PARENT" => "PRICES",
		"NAME" => GetMessage("CP_BC_PARTIAL_PRODUCT_PROPERTIES"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
	),
	"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
	"CACHE_FILTER" => array(
		"PARENT" => "CACHE_SETTINGS",
		"NAME" => GetMessage("IBLOCK_CACHE_FILTER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	"CACHE_GROUPS" => array(
		"PARENT" => "CACHE_SETTINGS",
		"NAME" => GetMessage("CP_BCS_CACHE_GROUPS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"FILTER_PROP_CODE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("FILTER_PROP_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty_X,
		"ADDITIONAL_VALUES" => "Y",
	),
	"SHOW_RATING" => Array(
		"NAME" => GetMessage("SHOW_RATING"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	),
	"STIKERS_PROP" => array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("STIKERS_PROP_TITLE"),
		"TYPE" => "LIST",
		"DEFAULT" => "HIT",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => array_merge(Array("-"=>" "), $arProperty_X),
	),
	"SALE_STIKER" =>array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("SALE_STIKER"),
		"TYPE" => "LIST",
		"DEFAULT" => "SALE_TEXT",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => array_merge(Array("-"=>" "), $arProperty_S),
	),
	"TITLE_BLOCK_ALL" => Array(
		"NAME" => GetMessage("TITLE_BLOCK_ALL_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BLOCK_ALL_NAME"),
	),
	"ALL_URL" => Array(
		"NAME" => GetMessage("ALL_URL_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "catalog/",
	),
	"SHOW_GALLERY" => Array(
		"NAME" => GetMessage("SHOW_GALLERY_NAME"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
	),
	"MAX_GALLERY_ITEMS" => Array(
		"NAME" => GetMessage("MAX_GALLERY_ITEMS_NAME"),
		"TYPE" => "SELECTBOX",
		"VALUES" => array(2=>2, 3=>3, 4=>4, 5=>5),
		"DEFAULT" => "5",
		"HIDDEN" => ($arCurrentValues["SHOW_GALLERY"] == "Y" ? "N" : "Y"),
	),
	"ADD_PICT_PROP" => Array(
		"NAME" => GetMessage("ADD_PICT_PROP_NAME"),
		"TYPE" => "LIST",
		"VALUES" => $arPropertyGallery,
		"DEFAULT" => "MORE_PHOTO",
		"HIDDEN" => ($arCurrentValues["SHOW_GALLERY"] == "Y" ? "N" : "Y"),
	),
);

if ($boolCatalog)
{
	global $USER_FIELD_MANAGER;
	$arStore = array();
	$storeIterator = CCatalogStore::GetList(
		array(),
		array('ISSUING_CENTER' => 'Y'),
		false,
		false,
		array('ID', 'TITLE')
	);
	while ($store = $storeIterator->GetNext())
		$arStore[$store['ID']] = "[".$store['ID']."] ".$store['TITLE'];

	$userFields = $USER_FIELD_MANAGER->GetUserFields("CAT_STORE", 0, LANGUAGE_ID);
	$propertyUF = array();

	foreach($userFields as $fieldName => $userField)
		$propertyUF[$fieldName] = $userField["LIST_COLUMN_LABEL"] ? $userField["LIST_COLUMN_LABEL"] : $fieldName;

	$arTemplateParameters['USER_FIELDS'] = array(
			"PARENT" => "STORE_SETTINGS",
			"NAME" => GetMessage("STORE_USER_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $propertyUF,
		);
	$arTemplateParameters['FIELDS'] = array(
		'NAME' => GetMessage("STORE_FIELDS"),
		'PARENT' => 'STORE_SETTINGS',
		'TYPE'  => 'LIST',
		'MULTIPLE' => 'Y',
		'ADDITIONAL_VALUES' => 'Y',
		'VALUES' => array(
			'TITLE'  => GetMessage("STORE_TITLE"),
			'ADDRESS'  => GetMessage("ADDRESS"),
			// 'DESCRIPTION'  => GetMessage('DESCRIPTION'),
			// 'PHONE'  => GetMessage('PHONE'),
			// 'SCHEDULE'  => GetMessage('SCHEDULE'),
			// 'EMAIL'  => GetMessage('EMAIL'),
			// 'IMAGE_ID'  => GetMessage('IMAGE_ID'),
			// 'COORDINATES'  => GetMessage('COORDINATES'),
		)
	);
	$arTemplateParameters['HIDE_NOT_AVAILABLE'] = array(
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('CP_BCS_HIDE_NOT_AVAILABLE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
	if (CModule::IncludeModule('currency'))
	{
		$arTemplateParameters['CONVERT_CURRENCY'] = array(
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCS_CONVERT_CURRENCY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		);

		if (isset($arCurrentValues['CONVERT_CURRENCY']) && 'Y' == $arCurrentValues['CONVERT_CURRENCY'])
		{
			$arCurrencyList = array();
			$rsCurrencies = CCurrency::GetList(($by = 'SORT'), ($order = 'ASC'));
			while ($arCurrency = $rsCurrencies->Fetch())
			{
				$arCurrencyList[$arCurrency['CURRENCY']] = $arCurrency['CURRENCY'];
			}
			$arTemplateParameters['CURRENCY_ID'] = array(
				'PARENT' => 'PRICES',
				'NAME' => GetMessage('CP_BCS_CURRENCY_ID'),
				'TYPE' => 'LIST',
				'VALUES' => $arCurrencyList,
				'DEFAULT' => CCurrency::GetBaseCurrency(),
				"ADDITIONAL_VALUES" => "Y",
			);
		}
	}
}

if (isset($arCurrentValues["USE_PRODUCT_QUANTITY"]) && 'Y' == $arCurrentValues["USE_PRODUCT_QUANTITY"])
{
	$arTemplateParameters['QUANTITY_FLOAT'] = array(
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCS_QUANTITY_FLOAT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	);
}
if(\Bitrix\Main\Loader::includeModule('catalog'))
{
	$arStore = array();
	global $USER_FIELD_MANAGER;
	$storeIterator = CCatalogStore::GetList(
		array(),
		array('ISSUING_CENTER' => 'Y'),
		false,
		false,
		array('ID', 'TITLE')
	);
	while ($store = $storeIterator->GetNext())
		$arStore[$store['ID']] = "[".$store['ID']."] ".$store['TITLE'];

	$userFields = $USER_FIELD_MANAGER->GetUserFields("CAT_STORE", 0, LANGUAGE_ID);
	$propertyUF = array();

	foreach($userFields as $fieldName => $userField)
		$propertyUF[$fieldName] = $userField["LIST_COLUMN_LABEL"] ? $userField["LIST_COLUMN_LABEL"] : $fieldName;

	$arTemplateParameters['STORES'] = array(
		'PARENT' => 'STORE_SETTINGS',
		'NAME' => GetMessage('STORES'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arStore,
		'ADDITIONAL_VALUES' => 'Y'
	);
}
?>
