<?
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!Bitrix\Main\Loader::includeModule('iblock')) {
	return;
}

$arTypesEx = CIBlockParameters::GetIBlockTypes(['-' => ' ']);

$arIBlocks = [];
$dbRes = CIBlock::GetList(['sort' => 'asc'], ['ACTIVE' => 'Y']);
while($arIBlock = $dbRes->Fetch()) {
	$arIBlocks[$arIBlock['ID']] = '['.$arIBlock['ID'].'] '.$arIBlock['NAME'];
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'IBLOCK_ID' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('T_ASPRO_PROP_IBLOCK_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
			'DEFAULT' => '',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'OFFERS_IBLOCK_ID' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('T_ASPRO_PROP_OFFERS_IBLOCK_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arIBlocks,
			'DEFAULT' => '',
			'ADDITIONAL_VALUES' => 'Y',
		],
		'OFFERS_MODE' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('T_ASPRO_PROP_OFFERS_MODE'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'N',
		],
		'NO_GROUP_BOTTOM' => [
			'PARENT' => 'BASE',
			'NAME' => Loc::getMessage('T_ASPRO_PROP_NO_GROUP_BOTTOM'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
	],
];
