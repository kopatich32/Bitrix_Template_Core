<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("aspro.max"))
	return;

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"TOKEN" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("TOKEN"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"GROUP_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("GROUP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"TITLE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("TITLE_VALUE"),
		),
		"CACHE_TIME"  =>  array(
			"DEFAULT" => 86400,
		),
		"CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("CP_BNL_CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
	),
);
