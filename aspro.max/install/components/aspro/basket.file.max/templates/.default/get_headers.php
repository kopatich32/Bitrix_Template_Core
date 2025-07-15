<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arHeaders = array(
	'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
	'Content-Disposition' => 'attachment; filename='.$arResult['FILE_NAME'],
	'Expires' => '0',
	'Cache-Control' => 'private',
);

return $arHeaders;
?>