<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('T_BFW_NAME'),
	'DESCRIPTION' => GetMessage('T_BFW_DESCRIPTION'),
	'ICON' => '/images/basket_file_wrapper.gif',
	'CACHE_PATH' => 'Y',
	'SORT' => 1020,
	'PATH' => array(
		'ID' => 'aspro',
		'NAME' => GetMessage('T_BFW_ASPRO'),
		'SORT' => 2,
	),
);
