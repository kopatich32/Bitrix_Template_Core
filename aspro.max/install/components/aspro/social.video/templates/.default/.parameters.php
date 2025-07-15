<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arTemplateParameters = [
	'BORDERED' => [
		'NAME' => Loc::getMessage('T_BORDERED'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
	'SHOW_TITLE' => [
		'NAME' => Loc::getMessage('T_SHOW_TITLE'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
	'MAXWIDTH_WRAP' => [
		'NAME' => Loc::getMessage('T_MAXWIDTH_WRAP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
	'MOBILE_SCROLLED' => [
		'NAME' => Loc::getMessage('T_MOBILE_SCROLLED'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
];