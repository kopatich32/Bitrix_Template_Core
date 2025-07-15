<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));

$arIBlock = array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_ASPRO_PROP_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			//"REFRESH" => "Y",
		),
		"OFFERS_IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_ASPRO_PROP_OFFERS_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			//"REFRESH" => "Y",
		),
		"MODULE_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_ASPRO_PROP_MODULE_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "aspro.max",
		),
		"OFFERS_MODE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_ASPRO_PROP_OFFERS_MODE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		// "TITLE" => array(
		// 	"PARENT" => "ADDITIONAL_SETTINGS",
		// 	"NAME" => GetMessage("TITLE_YOUTUBE"),
		// 	"TYPE" => "LIST",
		// 	"VALUES" => $arFromTheme,
		// 	"DEFAULT" => GetMessage("TITLE_YOUTUBE_VALUE"),
		// 	"ADDITIONAL_VALUES" => "Y",
		// ),
		
	),
);