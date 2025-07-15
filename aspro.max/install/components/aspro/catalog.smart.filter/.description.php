<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_NAME"),
	"DESCRIPTION" => GetMessage("T_DESCRIPTION"),
	"ICON" => "/images/iblock_filter.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 90,
	"PATH" => array(
		"ID" => "aspro",
		"NAME" => GetMessage("ASPRO"),
		"SORT" => 1,
	),
);
?>