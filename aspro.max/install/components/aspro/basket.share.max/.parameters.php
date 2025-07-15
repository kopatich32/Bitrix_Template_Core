<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arSites = array('' => Loc::getMessage('BS_P_SITE_ID_EMPTY'));
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
		'NEW' => array(
			'NAME' => Loc::getMessage('BS_G_NEW_TITLE'),
			'SORT' => '500',
		),
		'DETAIL' => array(
			'NAME' => Loc::getMessage('BS_G_DETAIL_TITLE'),
			'SORT' => '500',
		),
	),
	'PARAMETERS' => array(
		'VARIABLE_ALIASES' => array(
			'CODE' => array(
				'NAME' => Loc::getMessage('BS_P_VARIABLE_ALIASES_CODE'),
			),
		),
		'SEF_MODE' => array(
			'new' => array(
				'NAME' => Loc::getMessage('BS_P_NEW_PAGE'),
				'DEFAULT' => 'new/',
				'VARIABLES' => array(
				),
			),
			'detail' => array(
				'NAME' => Loc::getMessage('BS_P_DETAIL_PAGE'),
				'DEFAULT' => '#CODE#/',
				'VARIABLES' => array(
					'CODE',
				),
			),
		),
		'NEW_SET_PAGE_TITLE' => array(
			'PARENT' => 'NEW',
			'NAME' => GetMessage('BS_P_NEW_SET_PAGE_TITLE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'ADDITIONAL_VALUES' => 'N',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'NEW_SITE_ID' => array(
			'PARENT' => 'NEW',
			'NAME' => Loc::getMessage('BS_P_NEW_SITE_ID_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arSites,
			'DEFAULT' => '',
		),
		'NEW_USER_ID' => array(
			'PARENT' => 'NEW',
			'NAME' => Loc::getMessage('BS_P_NEW_USER_ID_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'NEW_SHOW_SHARE_SOCIALS' => array(
			'PARENT' => 'NEW',
			'NAME' => Loc::getMessage('BS_P_NEW_SHOW_SHARE_SOCIALS_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		),
	),
);

if($arCurrentValues['NEW_SHOW_SHARE_SOCIALS'] !== 'N'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'NEW_SHARE_SOCIALS' => array(
				'PARENT' => 'NEW',
				'NAME' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_TITLE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'VALUES' => array(
					'VKONTAKTE' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_VKONTAKTE'),
					'FACEBOOK' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_FACEBOOK'),
					'ODNOKLASSNIKI' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_ODNOKLASSNIKI'),
					'MOIMIR' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_MOIMIR'),
					'TWITTER' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_TWITTER'),
					'VIBER' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_VIBER'),
					'WHATSAPP' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_WHATSAPP'),
					'SKYPE' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_SKYPE'),
					'TELEGRAM' => Loc::getMessage('BS_P_NEW_SHARE_SOCIALS_TELEGRAM'),
				),
				'DEFAULT' => array(
					'VKONTAKTE',
					'FACEBOOK',
					'ODNOKLASSNIKI',
					'TWITTER',
				),
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'NEW_SHOW_QRCODE' => array(
			'PARENT' => 'NEW',
			'NAME' => Loc::getMessage('BS_P_NEW_SHOW_QRCODE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'NEW_USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'NEW',
			'NAME' => Loc::getMessage('BS_P_NEW_USE_CUSTOM_MESSAGES_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['NEW_USE_CUSTOM_MESSAGES'] === 'Y'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'NEW_MESS_TITLE' => array(
				'PARENT' => 'NEW',
				'NAME' => Loc::getMessage('BS_P_NEW_MESS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_TITLE_DEFAULT'),
			),
			'NEW_MESS_URL_COPY_HINT' => array(
				'PARENT' => 'NEW',
				'NAME' => Loc::getMessage('BS_P_NEW_MESS_URL_COPY_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_URL_COPY_HINT_DEFAULT'),
			),
			'NEW_MESS_URL_COPIED_HINT' => array(
				'PARENT' => 'NEW',
				'NAME' => Loc::getMessage('BS_P_NEW_MESS_URL_COPIED_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_URL_COPIED_HINT_DEFAULT'),
			),
			'NEW_MESS_URL_COPY_ERROR_HINT' => array(
				'PARENT' => 'NEW',
				'NAME' => Loc::getMessage('BS_P_NEW_MESS_URL_COPY_ERROR_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_URL_COPY_ERROR_HINT_DEFAULT'),
			),
		)
	);

	if($arCurrentValues['NEW_SHOW_SHARE_SOCIALS'] !== 'N'){
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'],
			array(
				'NEW_MESS_SHARE_SOCIALS_TITLE' => array(
					'PARENT' => 'NEW',
					'NAME' => Loc::getMessage('BS_P_NEW_MESS_SHARE_SOCIALS_TITLE_TITLE'),
					'TYPE' => 'STRING',
					'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_SHARE_SOCIALS_TITLE_DEFAULT'),
				),
			)
		);
	}

	if($arCurrentValues['NEW_SHOW_QRCODE'] !== 'N'){
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'],
			array(
				'NEW_MESS_QR_CODE_HINT' => array(
					'PARENT' => 'NEW',
					'NAME' => Loc::getMessage('BS_P_NEW_MESS_QR_CODE_HINT_TITLE'),
					'TYPE' => 'STRING',
					'DEFAULT' => Loc::getMessage('BS_P_NEW_MESS_QR_CODE_HINT_DEFAULT'),
				),
			)
		);
	}
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'DETAIL_SET_PAGE_TITLE' => array(
			'PARENT' => 'DETAIL',
			'NAME' => GetMessage('BS_P_DETAIL_SET_PAGE_TITLE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'ADDITIONAL_VALUES' => 'N',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'DETAIL_ACTUAL' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_ACTUAL_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'DETAIL_SHOW_VERSION_SWITCHER' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_VERSION_SWITCHER_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'DETAIL_USE_COMPARE' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_USE_COMPARE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'DETAIL_USE_DELAY' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_USE_DELAY_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'DETAIL_USE_FAST_VIEW' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_USE_FAST_VIEW_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'DETAIL_SHOW_ONE_CLICK_BUY' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_ONE_CLICK_BUY_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		),
		'DETAIL_SHOW_STICKERS' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_STICKERS_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['DETAIL_SHOW_STICKERS'] === 'Y'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'STIKERS_PROP' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_STIKERS_PROP_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'HIT',
			),
			'SALE_STIKER' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_SALE_STIKER_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => 'SALE_TEXT',
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'DETAIL_SHOW_AMOUNT' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_AMOUNT_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'DETAIL_SHOW_OLD_PRICE' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_OLD_PRICE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'DETAIL_SHOW_DISCOUNT_PERCENT' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_DISCOUNT_PERCENT_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['DETAIL_SHOW_DISCOUNT_PERCENT'] !== 'N'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'DETAIL_SHOW_DISCOUNT_PERCENT_NUMBER' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_SHOW_DISCOUNT_PERCENT_NUMBER_TITLE'),
				'TYPE' => 'CHECKBOX',
				'DEFAULT' => 'Y',
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'DETAIL_PRODUCT_PROPERTIES' => array(
			'PARENT' => 'DETAIL',
			'NAME' => GetMessage('BS_P_DETAIL_PRODUCT_PROPERTIES_TITLE'),
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
		'DETAIL_USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'DETAIL',
			'NAME' => Loc::getMessage('BS_P_DETAIL_USE_CUSTOM_MESSAGES_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['DETAIL_USE_CUSTOM_MESSAGES'] === 'Y'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'DETAIL_MESS_TITLE' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_TITLE_DEFAULT'),
			),
			'DETAIL_MESS_SHOW_ACTUAL_PRICES' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_SHOW_ACTUAL_PRICES_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_SHOW_ACTUAL_PRICES_DEFAULT'),
			),
			'DETAIL_MESS_TOTAL_PRICE' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_TOTAL_PRICE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_TOTAL_PRICE_DEFAULT'),
			),
			'DETAIL_MESS_ADD_TO_BASKET' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_ADD_TO_BASKET_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_ADD_TO_BASKET_DEFAULT'),
			),
			'DETAIL_MESS_REPLACE_BASKET' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_REPLACE_BASKET_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_REPLACE_BASKET_DEFAULT'),
			),
			'DETAIL_MESS_PRODUCT_ECONOMY' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_PRODUCT_ECONOMY_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_PRODUCT_ECONOMY_DEFAULT'),
			),
			'DETAIL_MESS_PRODUCT_NOT_EXISTS' => array(
				'PARENT' => 'DETAIL',
				'NAME' => Loc::getMessage('BS_P_DETAIL_MESS_PRODUCT_NOT_EXISTS_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BS_P_DETAIL_MESS_PRODUCT_NOT_EXISTS_DEFAULT'),
			),
		)
	);
}

if(Bitrix\Main\Loader::includeModule('iblock')){
	CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
}

if($arCurrentValues['SEF_MODE'] === 'Y'){
	$arComponentParameters['PARAMETERS']['VARIABLE_ALIASES'] = array();

	$arComponentParameters['PARAMETERS']['VARIABLE_ALIASES']['CODE'] = array(
		'NAME' => GetMessage('BS_P_VARIABLE_ALIASES_CODE'),
		'TEMPLATE' => '#CODE#',
	);
}
