<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_BF_NAME'),
	'DESCRIPTION' => GetMessage('T_BF_DESCRIPTION'),
	'ICON' => '/images/basket_file.gif',
	'CACHE_PATH' => 'Y',
	'SORT' => 1010,
	'PATH' => array(
		'ID' => 'aspro',
		'NAME' => GetMessage('T_BF_ASPRO'),
		'SORT' => 2,
	),
);
