<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arSites = array('' => Loc::getMessage('BFW_P_SITE_ID_EMPTY'));
$dbRes = \CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
while($arSite = $dbRes->Fetch()){
	$arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

$arComponentParameters = array(
	'GROUPS' => array(
		'MESSAGES' => array(
			'NAME' => Loc::getMessage('BFA_G_MESSAGES_TITLE'),
			'SORT' => '800',
		),
	),
	'PARAMETERS' => array(
		'USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'MESSAGES',
			'NAME' => Loc::getMessage('BFW_P_USE_CUSTOM_MESSAGES_TITLE'),
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
				'NAME' => Loc::getMessage('BFW_P_MESS_BASKET_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BFW_P_MESS_BASKET_TITLE_DEFAULT'),
			),
			'MESS_BASKET_CAN_BUY_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BFW_P_MESS_BASKET_CAN_BUY_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BFW_P_MESS_BASKET_CAN_BUY_ITEMS_TITLE_DEFAULT'),
			),
			'MESS_BASKET_DELAY_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BFW_P_MESS_BASKET_DELAY_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BFW_P_MESS_BASKET_DELAY_ITEMS_TITLE_DEFAULT'),
			),
			'MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BFW_P_MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BFW_P_MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE_DEFAULT'),
			),
		)
	);
}
