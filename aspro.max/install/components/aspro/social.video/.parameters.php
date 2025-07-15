<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arFromTheme = [];
if (isset($_REQUEST['src_path'])) {
	$_SESSION['src_path_component'] = $_REQUEST['src_path'];
}

if (strpos($_SESSION['src_path_component'], 'custom') === false) {
	$arFromTheme = ['FROM_THEME' => Loc::getMessage('ASPRO__SELECT_PARAM__FROM_THEME')];
}

$arComponentParameters = [
	'GROUPS' => [
		'LIST' => [
			'NAME' => Loc::getMessage('LIST_SETTINGS'),
			'SORT' => '120',
		],
	],
	'PARAMETERS' => [
		'VIDEO_SOURCE' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('VIDEO_SOURCE_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => [
				'rutube' => Loc::getMessage('VIDEO_PLATFORM_RUTUBE'),
				'vk' => Loc::getMessage('VIDEO_PLATFORM_VK_VIDEO'),
				'youtube' => Loc::getMessage('VIDEO_PLATFORM_YOUTUBE'),
			],
			'DEFAULT' => 'rutube',
			'REFRESH' => 'Y',
		],
		'API_TOKEN' => [
			'PARENT' => 'BASE',
			'SORT' => 105,
			'NAME' => Loc::getMessage('API_TOKEN'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme,
			'DEFAULT' => 'FROM_THEME',
			'HIDDEN' => $arCurrentValues['VIDEO_SOURCE'] === 'rutube' ? 'Y' : 'N',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'CHANNEL_ID' => [
			'PARENT' => 'BASE',
			'SORT' => 110,
			'NAME' => Loc::getMessage('CHANNEL_ID_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' =>$arFromTheme,
			'DEFAULT' => '',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'PLAYLIST_ID' => [
			'PARENT' => 'BASE',
			'SORT' => 120,
			'NAME' => Loc::getMessage('PLAYLIST_ID_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme,
			'DEFAULT' => '',
			'HIDDEN' => $arCurrentValues['VIDEO_SOURCE'] !== 'youtube' ? 'Y' : 'N',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'SORT' => [
			'PARENT' => 'BASE',
			'SORT' => 106,
			'NAME' => Loc::getMessage('SORT_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme + [
				'date' => Loc::getMessage('SORT_DATE_TITLE'),
				'rating' => Loc::getMessage('SORT_RATING_TITLE'),
			],
			'DEFAULT' => 'date',
			'HIDDEN' => $arCurrentValues['VIDEO_SOURCE'] === 'vk' ? 'Y' : 'N',
		],
		'TITLE' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => Loc::getMessage('TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme,
			'DEFAULT' => Loc::getMessage('TITLE_VALUE'),
			'ADDITIONAL_VALUES' => 'Y',
		],
		'SHOW_TITLE' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => Loc::getMessage('T_SHOW_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme + [
				'Y' => Loc::getMessage('ASPRO__SELECT_PARAM__YES'),
				'N' => Loc::getMessage('ASPRO__SELECT_PARAM__NO'),
			],
			'DEFAULT' => 'Y',
		],
		'RIGHT_LINK' => [
			'NAME' => Loc::getMessage('T_RIGHT_LINK'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme,
			'DEFAULT' =>  'FROM_THEME',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'RIGHT_TITLE' => [
			'NAME' => Loc::getMessage('T_RIGHT_TITLE'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme,
			'DEFAULT' =>  'FROM_THEME',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'ELEMENTS_ROW' => [
			'NAME' => Loc::getMessage('ELEMENTS_ROW'),
			'TYPE' => 'LIST',
			'VALUES' => $arFromTheme + [
				'3' => '3',
				'4' => '4',
			],
			'DEFAULT' => '3',
		],
		'CACHE_TIME'  =>  [
			'DEFAULT' => 86400,
		],
	],
];