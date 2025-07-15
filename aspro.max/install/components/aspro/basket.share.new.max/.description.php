<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_BSN_NAME'),
	'DESCRIPTION' => GetMessage('T_BSN_DESCRIPTION'),
	'ICON' => '/images/basket_share_new.gif',
	'CACHE_PATH' => 'Y',
	'SORT' => 1040,
	'PATH' => array(
		'ID' => 'aspro',
		'NAME' => GetMessage('T_BSN_ASPRO'),
		'SORT' => 2,
	),
);
