<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_BS_NAME'),
	'DESCRIPTION' => GetMessage('T_BS_DESCRIPTION'),
	'ICON' => '/images/basket_share.gif',
	'CACHE_PATH' => 'Y',
	'SORT' => 1030,
	'PATH' => array(
		'ID' => 'aspro',
		'NAME' => GetMessage('T_BS_ASPRO'),
		'SORT' => 2,
	),
);
