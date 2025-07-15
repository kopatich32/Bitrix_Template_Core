<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_NAME"),
	"DESCRIPTION" => GetMessage("T_DESCRIPTION"),
	"ICON" => "/images/news_list.gif",
	"SORT" => 60,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "aspro",
		"NAME" => GetMessage("ASPRO"),
		"SORT" => 1,
	),
);

?>