<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @var array $templateProperties
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

use Bitrix\Main\Loader,
	Bitrix\Main\Web\Json,
	Bitrix\Main\Localization\Loc,
	Aspro\Max\Property\CustomFilter;

if (!Loader::includeModule('iblock')) {
	return;
}

$boolCatalog = CModule::IncludeModule('catalog');

$arTypesEx = CIBlockParameters::GetIBlockTypes([
	'-' => ' ',
]);

$arIBlocks = [];
$dbRes = CIBlock::GetList(
	['SORT' => 'ASC'],
	[
		'SITE_ID' => $_REQUEST['site'],
		'TYPE' => ($arCurrentValues['IBLOCK_TYPE'] != '-' ? $arCurrentValues['IBLOCK_TYPE'] : '')
	]
);
while ($arRes = $dbRes->Fetch()) {
	$arIBlocks[$arRes['ID']] = $arRes['NAME'];
}

$arSorts = [
	'ASC' => GetMessage('T_IBLOCK_DESC_ASC'),
	'DESC' => GetMessage('T_IBLOCK_DESC_DESC'),
];

$arSortFields = [
	'ID' => GetMessage('T_IBLOCK_DESC_FID'),
	'NAME' => GetMessage('T_IBLOCK_DESC_FNAME'),
	'ACTIVE_FROM' => GetMessage('T_IBLOCK_DESC_FACT'),
	'SORT' => GetMessage('T_IBLOCK_DESC_FSORT'),
	'TIMESTAMP_X' => GetMessage('T_IBLOCK_DESC_FTSAMP'),
];

if (0 < intval($arCurrentValues['IBLOCK_ID'])) {
	$rsProp = CIBlockProperty::GetList(
		[
			'sort' => 'asc', 
			'name' => 'asc',
		], 
		[
			'IBLOCK_ID' => $arCurrentValues['IBLOCK_ID'],
			'ACTIVE' => 'Y',
		]
	);
	while ($arr = $rsProp->Fetch()) {
		if ($arr['PROPERTY_TYPE'] != 'F') {
			$arProperty[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
		}

		if ($arr['PROPERTY_TYPE'] == 'N') {
			$arProperty_N[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
		}
		elseif ($arr['PROPERTY_TYPE'] == 'S') {
			$arProperty_S[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
		}
		elseif ($arr['PROPERTY_TYPE'] == 'F') {
			$arProperty_F[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
		}

		if ($arr['PROPERTY_TYPE'] != 'F') {
			if ($arr['MULTIPLE'] == 'Y' && $arr['PROPERTY_TYPE'] == 'L') {
				$arProperty_XL[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
			}
			elseif ($arr['PROPERTY_TYPE'] == 'L') {
				$arProperty_X[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
			}
			elseif ($arr['PROPERTY_TYPE'] == 'E' && $arr['LINK_IBLOCK_ID'] > 0) {
				$arProperty_X[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
			}
		}
	}
}

$arOffers = CIBlockPriceTools::GetOffersIBlock($arCurrentValues['IBLOCK_ID']);
$OFFERS_IBLOCK_ID = is_array($arOffers) ? $arOffers['OFFERS_IBLOCK_ID'] : 0;
$arProperty_Offers = [];
if ($OFFERS_IBLOCK_ID) {
	$rsProp = CIBlockProperty::GetList(
		[
			'sort' => 'asc', 
			'name' => 'asc',
		], 
		[
			'IBLOCK_ID' => $OFFERS_IBLOCK_ID, 
			'ACTIVE' => 'Y',
		]
	);
	while ($arr = $rsProp->Fetch()) {
		if ($arr['PROPERTY_TYPE'] != 'F') {
			$arProperty_Offers[$arr['CODE']] = '['.$arr['CODE'].'] '.$arr['NAME'];
		}
	}
}

$arSort = CIBlockParameters::GetElementSortFields(
	['SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'],
	['KEY_LOWERCASE' => 'Y'],
);

$arPrice = [];
if ($boolCatalog) {
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
	$rsPrice = CCatalogGroup::GetList($v1 = 'sort', $v2 = 'asc');
	while ($arr = $rsPrice->Fetch()) {
		$arPrice[$arr['NAME']] = '['.$arr['NAME'].'] '.$arr['NAME_LANG'];
	}
}
else {
	$arPrice = $arProperty_N;
}

$arComponentParameters = [
	'GROUPS' => [
		'PRICES' => [
			'NAME' => GetMessage('IBLOCK_PRICES'),
		],
	],
	'PARAMETERS' => [
		'IBLOCK_TYPE' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('T_IBLOCK_DESC_LIST_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => $arTypesEx,
			'DEFAULT' => 'news',
			'REFRESH' => 'Y',
		],
		'IBLOCK_ID' => [
			'PARENT' => 'BASE',
			'NAME' => GetMessage('T_IBLOCK_DESC_LIST_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
			'DEFAULT' => '={$_REQUEST["ID"]}',
			'REFRESH' => 'Y',
		],
		'SECTION_ID' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_SECTION_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'SECTION_CODE' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('IBLOCK_SECTION_CODE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
	]
];

$filterDataValues = [];

if (
	(int)$arCurrentValues['IBLOCK_ID'] > 0 &&
	Loader::includeModule('aspro.max')
) {
	$filterDataValues['iblockId'] = (int)$arCurrentValues['IBLOCK_ID'];

	if ($filterDataValues) {
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'],
			[
				'CUSTOM_FILTER' => [
					'PARENT' => 'DATA_SOURCE',
					'NAME' => GetMessage('CP_BCS_CUSTOM_FILTER'),
					'TYPE' => 'CUSTOM',
					'AJAX_FILE' => CustomFilter::getAjaxFile(),
					'JS_FILE' => CustomFilter::getJSFile(),
					'JS_EVENT' => 'initAsproMaxCustomFilterControl',
					'JS_MESSAGES' => Json::encode([
						'invalid' => GetMessage('CP_BCS_SETTINGS_INVALID_CONDITION')
					]),
					'JS_DATA' => Json::encode($filterDataValues),
					'DEFAULT' => '',
				]
			]
		);
	}
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	[
		'ELEMENT_SORT_FIELD' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_DESC_IBORD1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'ACTIVE_FROM',
			'VALUES' => $arSortFields,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'ELEMENT_SORT_ORDER' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_DESC_IBBY1'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'DESC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'ELEMENT_SORT_FIELD2' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_DESC_IBORD2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'SORT',
			'VALUES' => $arSortFields,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'ELEMENT_SORT_ORDER2' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_DESC_IBBY2'),
			'TYPE' => 'LIST',
			'DEFAULT' => 'ASC',
			'VALUES' => $arSorts,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'FILTER_NAME' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_FILTER'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'AJAX_LOAD' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('AJAX_LOAD'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'LINE_ELEMENT_COUNT' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('T_LINE_ELEMENT_COUNT'),
			'TYPE' => 'LIST',
			'VALUES' => [5 => 5, 4 => 4],
			'DEFAULT' => 5,
		],
		'INCLUDE_SUBSECTIONS' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS'),
			'TYPE' => 'LIST',
			'VALUES' => [
				'Y' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ALL'),
				'A' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_ACTIVE'),
				'N' => GetMessage('CP_BCS_INCLUDE_SUBSECTIONS_NO'),
			],
			'DEFAULT' => 'Y',
		],
		'SHOW_ALL_WO_SECTION' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('CP_BCS_SHOW_ALL_WO_SECTION'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'DISPLAY_COMPARE' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('T_IBLOCK_DESC_DISPLAY_COMPARE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_MEASURE' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SHOW_MEASURE_NAME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		/*'DISPLAY_WISH_BUTTONS' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('DISPLAY_WISH_BUTTONS_NAME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],*/
		'SHOW_DISCOUNT_PERCENT' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SHOW_DISCOUNT_PERCENT_NAME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_DISCOUNT_PERCENT_NUMBER' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SHOW_DISCOUNT_PERCENT_NUMBER_NAME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'SHOW_DISCOUNT_TIME' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SHOW_DISCOUNT_TIME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_MEASURE_WITH_RATIO' => [
			'NAME' => GetMessage('SHOW_MEASURE_WITH_RATIO'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'SHOW_OLD_PRICE' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SHOW_OLD_PRICE_NAME'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_DISCOUNT_TIME_EACH_SKU' => [
			'NAME' => GetMessage('SHOW_DISCOUNT_TIME_EACH_SKU'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'SORT' => 100,
			'PARENT' => 'ADDITIONAL_SETTINGS',
		],
		'PAGE_ELEMENT_COUNT' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('IBLOCK_PAGE_ELEMENT_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '30',
		],
		'PROPERTY_CODE' => [
			'PARENT' => 'DATA_SOURCE',
			'NAME' => GetMessage('T_IBLOCK_PROPERTY'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty,
			'ADDITIONAL_VALUES' => 'Y',
		],
		'FIELD_CODE' => CIBlockParameters::GetFieldCode(GetMessage('IBLOCK_FIELD'), 'DATA_SOURCE'),
		'OFFERS_FIELD_CODE' => CIBlockParameters::GetFieldCode(GetMessage('CP_BCS_OFFERS_FIELD_CODE'), 'VISUAL'),
		'OFFERS_SORT_FIELD' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCS_OFFERS_SORT_FIELD'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'sort',
		],
		'OFFERS_SORT_ORDER' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCS_OFFERS_SORT_ORDER'),
			'TYPE' => 'LIST',
			'VALUES' => $arSorts,
			'DEFAULT' => 'asc',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'OFFERS_SORT_FIELD2' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCS_OFFERS_SORT_FIELD2'),
			'TYPE' => 'LIST',
			'VALUES' => $arSort,
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => 'id',
		],
		'OFFERS_SORT_ORDER2' => [
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('CP_BCS_OFFERS_SORT_ORDER2'),
			'TYPE' => 'LIST',
			'VALUES' => $arSorts,
			'DEFAULT' => 'DESC',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'PRICE_CODE' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_PRICE_CODE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arPrice,
		],
		'USE_PRICE_COUNT' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_USE_PRICE_COUNT'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'SHOW_PRICE_COUNT' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_SHOW_PRICE_COUNT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '1',
		],
		'PRICE_VAT_INCLUDE' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('IBLOCK_VAT_INCLUDE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'ADD_PROPERTIES_TO_BASKET' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BC_ADD_PROPERTIES_TO_BASKET'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y'
		],
		'PRODUCT_PROPERTIES' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCS_PRODUCT_PROPERTIES'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $arProperty_X,
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		],
		'PARTIAL_PRODUCT_PROPERTIES' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BC_PARTIAL_PRODUCT_PROPERTIES'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
		],
		'USE_PRODUCT_QUANTITY' => [
			'PARENT' => 'PRICES',
			'NAME' => GetMessage('CP_BCS_USE_PRODUCT_QUANTITY'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		],
		'CACHE_TIME' => ['DEFAULT' => 36000000],
		'CACHE_FILTER' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('IBLOCK_CACHE_FILTER'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'CACHE_GROUPS' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_BCS_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_RATING' => [
			'NAME' => GetMessage('SHOW_RATING'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'STIKERS_PROP' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('STIKERS_PROP_TITLE'),
			'TYPE' => 'LIST',
			'DEFAULT' => '-',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => array_merge(['-' => ' '], (array)$arProperty_XL),
		],
		'SALE_STIKER' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('SALE_STIKER'),
			'TYPE' => 'LIST',
			'DEFAULT' => '-',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => array_merge(['-' => ' '], (array)$arProperty_S),
		],
		'FAV_ITEM' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('T_FAV_ITEM'),
			'TYPE' => 'LIST',
			'DEFAULT' => '-',
			'ADDITIONAL_VALUES' => 'Y',
			'VALUES' => array_merge(['-' => ' '], (array)$arProperty_X),
		],
		'DETAIL_URL' => CIBlockParameters::GetPathTemplateParam(
			'DETAIL',
			'DETAIL_URL',
			GetMessage('T_IBLOCK_DESC_DETAIL_PAGE_URL'),
			'',
			'URL_TEMPLATES'
		),
		'CACHE_TIME' => ['DEFAULT' => 36000000],
		'CACHE_FILTER' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('IBLOCK_CACHE_FILTER'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'CACHE_GROUPS' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => GetMessage('CP_BNL_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
		'SHOW_TABS' => [
			'NAME' => GetMessage('T_SHOW_TABS'),
			// 'TYPE' => 'CHECKBOX',
			'TYPE' => 'LIST',
			'VALUES' => [
				'Y' => Loc::getMessage('T_YES'),
				'N' => Loc::getMessage('T_NO'),
			],
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		],
	]
);

if ($arCurrentValues['SHOW_TABS'] !== 'N') {
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'], 
		[
			'TABS_FILTER' => [
				'NAME' => GetMessage('T_TABS_FILTER'),
				'TYPE' => 'LIST',
				'VALUES' => [
					'PROPERTY' => GetMessage('T_TABS_FILTER_PROPERTY'),
					'SECTION' => GetMessage('T_TABS_FILTER_SECTION'),
				],
				'DEFAULT' => 'PROPERTY',
				'REFRESH' => 'Y',
			],
		]
	);

	if ($arCurrentValues['TABS_FILTER'] !== 'SECTION') {
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'], 
			[
				'HIT_PROP' => [
					'NAME' => GetMessage('HIT_PROP'),
					'DEFAULT' => 'HIT',
					'TYPE' => 'LIST',
					'MULTIPLE' => 'N',
					'VALUES' => $arProperty_XL,			
				],
			]
		);
	}
}

if ($boolCatalog) {
	$arComponentParameters['PARAMETERS']['HIDE_NOT_AVAILABLE'] = [
		'PARENT' => 'DATA_SOURCE',
		'NAME' => GetMessage('CP_BCS_HIDE_NOT_AVAILABLE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	];
}

if (!$OFFERS_IBLOCK_ID) {
	unset($arComponentParameters['PARAMETERS']['OFFERS_FIELD_CODE']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_PROPERTY_CODE']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_FIELD']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_ORDER']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_FIELD2']);
	unset($arComponentParameters['PARAMETERS']['OFFERS_SORT_ORDER2']);
}
else {
	$arComponentParameters['PARAMETERS']['OFFERS_CART_PROPERTIES'] = [
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCS_OFFERS_CART_PROPERTIES'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arProperty_Offers,
		'HIDDEN' => (isset($arCurrentValues['ADD_PROPERTIES_TO_BASKET']) && $arCurrentValues['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'Y' : 'N')
	];
}

if (isset($arCurrentValues['USE_PRODUCT_QUANTITY']) && 'Y' == $arCurrentValues['USE_PRODUCT_QUANTITY']) {
	$arComponentParameters['PARAMETERS']['QUANTITY_FLOAT'] = [
		'PARENT' => 'PRICES',
		'NAME' => GetMessage('CP_BCS_QUANTITY_FLOAT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	];
}

if ($boolCatalog) {
	global $USER_FIELD_MANAGER;

	$arStore = [];
	$storeIterator = CCatalogStore::GetList(
		[],
		['ISSUING_CENTER' => 'Y'],
		false,
		false,
		['ID', 'TITLE']
	);
	while ($store = $storeIterator->GetNext()) {
		$arStore[$store['ID']] = '['.$store['ID'].'] '.$store['TITLE'];
	}

	$propertyUF = [];
	$userFields = $USER_FIELD_MANAGER->GetUserFields('CAT_STORE', 0, LANGUAGE_ID);
	foreach ($userFields as $fieldName => $userField) {
		$propertyUF[$fieldName] = $userField['LIST_COLUMN_LABEL'] ? $userField['LIST_COLUMN_LABEL'] : $fieldName;
	}

	$arComponentParameters['PARAMETERS']['STORES'] = [
		'PARENT' => 'STORE_SETTINGS',
		'NAME' => GetMessage('STORES'),
		'TYPE' => 'LIST',
		'MULTIPLE' => 'Y',
		'VALUES' => $arStore,
		'ADDITIONAL_VALUES' => 'Y',
	];
}
