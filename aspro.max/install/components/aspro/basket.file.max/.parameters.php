<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arSites = array('' => Loc::getMessage('BF_P_SITE_ID_EMPTY'));
$dbRes = \CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
while($arSite = $dbRes->Fetch()){
	$arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

$arComponentParameters = array(
	'GROUPS' => array(
		'VISUAL' => array(
			'NAME' => Loc::getMessage('BF_G_VISUAL_TITLE'),
			'SORT' => '500',
		),
		'MESSAGES' => array(
			'NAME' => Loc::getMessage('BF_G_MESSAGES_TITLE'),
			'SORT' => '800',
		),
	),
	'PARAMETERS' => array(
		'ACTION' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BF_P_ACTION_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'DOWNLOAD' => Loc::getMessage('BF_P_ACTION_DOWNLOAD'),
				'SAVE' => Loc::getMessage('BF_P_ACTION_SAVE'),
			),
			'DEFAULT' => 'DOWNLOAD',
			'REFRESH' => 'Y',
		),
		'FILE_NAME' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BF_P_FILE_NAME_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => 'cart',
		),
	)
);

if($arCurrentValues['ACTION'] === 'SAVE'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'SAVE_TO_DIR' => array(
				'PARENT' => 'BASE',
				'NAME' => Loc::getMessage('BF_P_SAVE_TO_DIR_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => '/upload/',
			),
		)
	);
}

$arComponentParameters['PARAMETERS'] = array_merge(
	$arComponentParameters['PARAMETERS'],
	array(
		'SITE_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BF_P_SITE_ID_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arSites,
			'DEFAULT' => '',
		),
		'USER_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BF_P_USER_ID_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'REGION_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BF_P_REGION_ID_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'SHOW_ERRORS' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BF_P_SHOW_ERRORS_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		),
		'USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'MESSAGES',
			'NAME' => Loc::getMessage('BF_P_USE_CUSTOM_MESSAGES_TITLE'),
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
			'MESS_BASKET_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BF_P_MESS_BASKET_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BF_P_MESS_BASKET_TITLE_DEFAULT'),
			),
			'MESS_BASKET_CAN_BUY_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BF_P_MESS_BASKET_CAN_BUY_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BF_P_MESS_BASKET_CAN_BUY_ITEMS_TITLE_DEFAULT'),
			),
			'MESS_BASKET_DELAY_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BF_P_MESS_BASKET_DELAY_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BF_P_MESS_BASKET_DELAY_ITEMS_TITLE_DEFAULT'),
			),
			'MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BF_P_MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BF_P_MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE_DEFAULT'),
			),
		)
	);
}
