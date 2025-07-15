<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arSites = array('' => Loc::getMessage('BSN_P_SITE_ID_EMPTY'));
$dbRes = \CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
while($arSite = $dbRes->Fetch()){
	$arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

$arComponentParameters = array(
	'GROUPS' => array(
		'VISUAL' => array(
			'NAME' => Loc::getMessage('BSN_G_VISUAL_TITLE'),
			'SORT' => '500',
		),
		'MESSAGES' => array(
			'NAME' => Loc::getMessage('BSN_G_MESSAGES_TITLE'),
			'SORT' => '800',
		),
	),
	'PARAMETERS' => array(
		'SET_PAGE_TITLE' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('BSN_P_SET_PAGE_TITLE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'ADDITIONAL_VALUES' => 'N',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'SITE_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BSN_P_SITE_ID_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arSites,
			'DEFAULT' => '',
		),
		'USER_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BSN_P_USER_ID_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'PATH_TO_SHARE_BASKET' => array(
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('BSN_P_PATH_TO_SHARE_BASKET_TITLE'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		'SHOW_SHARE_SOCIALS' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSN_P_SHOW_SHARE_SOCIALS_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'Y',
		),
	)
);

if($arCurrentValues['SHOW_SHARE_SOCIALS'] !== 'N'){
	$arComponentParameters['PARAMETERS'] = array_merge(
		$arComponentParameters['PARAMETERS'],
		array(
			'SHARE_SOCIALS' => array(
				'PARENT' => 'VISUAL',
				'NAME' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TITLE'),
				'TYPE' => 'LIST',
				'MULTIPLE' => 'Y',
				'VALUES' => array(
					'VKONTAKTE' => Loc::getMessage('BSN_P_SHARE_SOCIALS_VKONTAKTE'),
					'FACEBOOK' => Loc::getMessage('BSN_P_SHARE_SOCIALS_FACEBOOK'),
					'ODNOKLASSNIKI' => Loc::getMessage('BSN_P_SHARE_SOCIALS_ODNOKLASSNIKI'),
					'MOIMIR' => Loc::getMessage('BSN_P_SHARE_SOCIALS_MOIMIR'),
					'TWITTER' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TWITTER'),
					'VIBER' => Loc::getMessage('BSN_P_SHARE_SOCIALS_VIBER'),
					'WHATSAPP' => Loc::getMessage('BSN_P_SHARE_SOCIALS_WHATSAPP'),
					'SKYPE' => Loc::getMessage('BSN_P_SHARE_SOCIALS_SKYPE'),
					'TELEGRAM' => Loc::getMessage('BSN_P_SHARE_SOCIALS_TELEGRAM'),
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
		'SHOW_QRCODE' => array(
			'PARENT' => 'VISUAL',
			'NAME' => Loc::getMessage('BSN_P_SHOW_QRCODE_TITLE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
			'REFRESH' => 'N',
		),
		'USE_CUSTOM_MESSAGES' => array(
			'PARENT' => 'MESSAGES',
			'NAME' => Loc::getMessage('BSN_P_USE_CUSTOM_MESSAGES_TITLE'),
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
				'NAME' => Loc::getMessage('BSN_P_MESS_TITLE_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSN_P_MESS_TITLE_DEFAULT'),
			),
			'MESS_URL_COPY_HINT' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPY_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPY_HINT_DEFAULT'),
			),
			'MESS_URL_COPIED_HINT' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPIED_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPIED_HINT_DEFAULT'),
			),
			'MESS_URL_COPY_ERROR_HINT' => array(
				'PARENT' => 'MESSAGES',
				'NAME' => Loc::getMessage('BSN_P_MESS_URL_COPY_ERROR_HINT_TITLE'),
				'TYPE' => 'STRING',
				'DEFAULT' => Loc::getMessage('BSN_P_MESS_URL_COPY_ERROR_HINT_DEFAULT'),
			),
		)
	);

	if($arCurrentValues['SHOW_SHARE_SOCIALS'] !== 'N'){
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'],
			array(
				'MESS_SHARE_SOCIALS_TITLE' => array(
					'PARENT' => 'MESSAGES',
					'NAME' => Loc::getMessage('BSN_P_MESS_SHARE_SOCIALS_TITLE_TITLE'),
					'TYPE' => 'STRING',
					'DEFAULT' => Loc::getMessage('BSN_P_MESS_SHARE_SOCIALS_TITLE_DEFAULT'),
				),
			)
		);
	}

	if($arCurrentValues['SHOW_QRCODE'] !== 'N'){
		$arComponentParameters['PARAMETERS'] = array_merge(
			$arComponentParameters['PARAMETERS'],
			array(
				'MESS_QR_CODE_HINT' => array(
					'PARENT' => 'MESSAGES',
					'NAME' => Loc::getMessage('BSN_P_MESS_QR_CODE_HINT_TITLE'),
					'TYPE' => 'STRING',
					'DEFAULT' => Loc::getMessage('BSN_P_MESS_QR_CODE_HINT_DEFAULT'),
				),
			)
		);
	}
}
