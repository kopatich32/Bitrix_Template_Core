<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arSites = array('' => Loc::getMessage('BSD_P_SITE_ID_EMPTY'));
$dbRes = \CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
while($arSite = $dbRes->Fetch()){
	$arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

if(
	Bitrix\Main\Loader::includeModule('iblock') &&
	Bitrix\Main\Loader::includeModule('catalog')
){
	$arExcludePropertiesCodes = array(
		'CML2_ARTICLE',
		'HIT',
		'IN_STOCK',
		'MINIMUM_PRICE',
		'MAXIMUM_PRICE',
		'YM_ELEMENT_ID',
		'EXTENDED_REVIEWS_COUNT',
		'EXTENDED_REVIEWS_RAITING',
		'FAVORIT_ITEM',
		'BIG_BLOCK',
		'vote_count',
		'vote_sum',
		'rating',
		'VIDEO_YOUTUBE',
		'POPUP_VIDEO',
		'FORUM_TOPIC_ID',
		'FORUM_MESSAGE_CNT',
		'SALE_TEXT',
		'HELP_TEXT',
		'BLOG_POST_ID',
		'BLOG_COMMENTS_CNT',
		'FAV_ITEM',
		'FINAL_PRICE',
		'STIKERS_PROP',
		'SALE_STIKER',
	);

	$parameters = array(
		'select' => array('IBLOCK_ID', 'NAME' => 'IBLOCK.NAME'),
		'order' => array('IBLOCK_ID' => 'ASC'),
	);

	$siteId = isset($_REQUEST['src_site']) && is_string($_REQUEST['src_site']) ? $_REQUEST['src_site'] : '';
	$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);

	if(!empty($siteId) && is_string($siteId)){
		$parameters['select']['SITE_ID'] = 'IBLOCK_SITE.SITE_ID';
		$parameters['filter'] = array('SITE_ID' => $siteId);
		$parameters['runtime'] = array(
			'IBLOCK_SITE' => array(
				'data_type' => 'Bitrix\Iblock\IblockSiteTable',
				'reference' => array(
					'ref.IBLOCK_ID' => 'this.IBLOCK_ID',
				),
				'join_type' => 'inner'
			)
		);
	}

	$catalogIterator = Bitrix\Catalog\CatalogIblockTable::getList($parameters);
	while($catalog = $catalogIterator->fetch()){
		$catalog['IBLOCK_ID'] = (int)$catalog['IBLOCK_ID'];
		$iblockIds[] = $catalog['IBLOCK_ID'];
	}
	unset($catalog, $catalogIterator);

	$listProperties = array();
	if(!empty($iblockIds)){
		$arProps = array();
		$propertyIterator = Bitrix\Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'CODE', 'NAME', 'IBLOCK_ID', 'PROPERTY_TYPE', 'USER_TYPE'),
			'filter' => array('@IBLOCK_ID' => $iblockIds, '=ACTIVE' => 'Y', '!=XML_ID' => CIBlockPropertyTools::XML_SKU_LINK),
			'order' => array('IBLOCK_ID' => 'ASC', 'SORT' => 'ASC', 'ID' => 'ASC')
		));
		while($property = $propertyIterator->fetch()){
			if(
				(
					!$property['USER_TYPE'] ||
					$property['USER_TYPE'] === 'directory'
				) &&
				(
					$property['PROPERTY_TYPE'] === 'S' ||
					$property['PROPERTY_TYPE'] === 'L' ||
					$property['PROPERTY_TYPE'] === 'N'
				)
			){
				$property['CODE'] = (string)$property['CODE'];

				if(!in_array($property['CODE'], $arExcludePropertiesCodes)){
					$property['ID'] = (int)$property['ID'];
					$property['IBLOCK_ID'] = (int)$property['IBLOCK_ID'];

					if($property['CODE'] === ''){
						$property['CODE'] = $property['ID'];
					}

					$listProperties[$property['CODE']] = $property['NAME'].' ['.$property['CODE'].'] [id:'.$property['ID'].']';
				}
			}
		}
		unset($property, $propertyIterator);
	}
}

$arComponentParameters = array(
	'GROUPS' => array(
		'VISUAL' => array(
			'NAME' => Loc::getMessage('BSD_G_VISUAL_TITLE'),
			'SORT' => '500',
		),
		'MESSAGES' => array(
			'NAME' => Loc::getMessage('BSD_G_MESSAGES_TITLE'),
			'SORT' => '800',
		),
	),
	'PARAMETERS' => array(
		'CODE' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BSD_P_CODE_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'SET_PAGE_TITLE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('BSD_P_SET_PAGE_TITLE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'ADDITIONAL_VALUES' => 'N',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'ACTUAL' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_ACTUAL_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'SHOW_VERSION_SWITCHER' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_VERSION_SWITCHER_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'USE_COMPARE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_USE_COMPARE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'USE_DELAY' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_USE_DELAY_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'USE_FAST_VIEW' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_USE_FAST_VIEW_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'SHOW_ONE_CLICK_BUY' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_ONE_CLICK_BUY_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'SHOW_STICKERS' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_STICKERS_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['SHOW_STICKERS'] === 'Y'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'STIKERS_PROP' => array(
				'PARENT' => 'ADDITIONAL_SETTINGS',
				'NAME' => Loc::getMessage('BSD_P_STIKERS_PROP_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'HIT',
				"ADDITIONAL_VALUES" => "Y",
			),
			'SALE_STIKER' => array(
				'PARENT' => 'ADDITIONAL_SETTINGS',
				'NAME' => Loc::getMessage('BSD_P_SALE_STIKER_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'SALE_TEXT',
				"ADDITIONAL_VALUES" => "Y",
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'SHOW_AMOUNT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_AMOUNT_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'SHOW_OLD_PRICE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_OLD_PRICE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'SHOW_DISCOUNT_PERCENT' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSD_P_SHOW_DISCOUNT_PERCENT_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['SHOW_DISCOUNT_PERCENT'] !== 'N'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'SHOW_DISCOUNT_PERCENT_NUMBER' => array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('BSD_P_SHOW_DISCOUNT_PERCENT_NUMBER_TITLE'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'Y',
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'PRODUCT_PROPERTIES' => array(
			'PARENT' => 'VISUAL',
			'NAME' => GetMessage('BSD_P_PRODUCT_PROPERTIES_TITLE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $listProperties,
			'DEFAULT' => array(
				'CML2_ARTICLE',
			),
			'ADDITIONAL_VALUES' => 'N',
			'COLS' => 25,
			'SIZE' => 7,
		),
		'USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'MESSAGES',
			'NAME' => Loc::getMessage('BSD_P_USE_CUSTOM_MESSAGES_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['USE_CUSTOM_MESSAGES'] === 'Y'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'MESS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_TITLE_DEFAULT'),
			),
			'MESS_SHOW_ACTUAL_PRICES' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_SHOW_ACTUAL_PRICES_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_SHOW_ACTUAL_PRICES_DEFAULT'),
			),
			'MESS_TOTAL_PRICE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_TOTAL_PRICE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_TOTAL_PRICE_DEFAULT'),
			),
			'MESS_ADD_TO_BASKET' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_ADD_TO_BASKET_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_ADD_TO_BASKET_DEFAULT'),
			),
			'MESS_REPLACE_BASKET' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_REPLACE_BASKET_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_REPLACE_BASKET_DEFAULT'),
			),
			'MESS_PRODUCT_ECONOMY' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_PRODUCT_ECONOMY_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_PRODUCT_ECONOMY_DEFAULT'),
			),
			'MESS_PRODUCT_NOT_EXISTS' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSD_P_MESS_PRODUCT_NOT_EXISTS_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSD_P_MESS_PRODUCT_NOT_EXISTS_DEFAULT'),
			),
		)
	);
}
