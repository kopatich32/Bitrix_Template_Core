<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

$arRequirements = array(
	array(
		'TITLE' => Loc::getMessage('BF_T_REQUIRE_PHP_VERSION'),
		'PASSED' => version_compare(PHP_VERSION, '7.1.0') >= 0,
	),
	array(
		'TITLE' => Loc::getMessage('BF_T_REQUIRE_MBSTRING_EXTENSION'),
		'PASSED' => function_exists('mb_strlen'),
	),
	array(
		'TITLE' => Loc::getMessage('BF_T_REQUIRE_DOM_EXTENSION'),
		'PASSED' => class_exists('DOMDocument'),
	),
);

return $arRequirements;
?>