<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_BSD_NAME'),
	'DESCRIPTION' => GetMessage('T_BSD_DESCRIPTION'),
	'ICON' => '/images/basket_share_detail.gif',
	'CACHE_PATH' => 'Y',
	'SORT' => 1050,
	'PATH' => array(
		'ID' => 'aspro',
		'NAME' => GetMessage('T_BSD_ASPRO'),
		'SORT' => 2,
	),
);
