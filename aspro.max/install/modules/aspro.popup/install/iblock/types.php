<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

use \Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

// add iblock types
$arTypes = array(
	array(
		"ID" => "aspro_popup_adv",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 100,
		"LANG" => array(),
	),
);
$arLanguages = array();
$rsLanguage = CLanguage::GetList($by, $order, array());
while($arLanguage = $rsLanguage->Fetch())
	$arLanguages[] = $arLanguage["LID"];

$iblockType = new CIBlockType;



foreach($arTypes as $arType){
	$dbType = CIBlockType::GetList(array(), array("=ID" => $arType["ID"]));
	if($dbType->Fetch()) // already exist - don`t add
		continue;

	foreach($arLanguages as $languageID) {
		$languageID_include = in_array($languageID, array('ru', 'en')) ? $languageID : 'en';
		// WizardServices::IncludeServiceLang("types.php", $languageID_include);
		Loc::loadLanguageFile(__FILE__, $languageID_include);
		$code = strtoupper($arType["ID"]."_".$languageID_include);
		
		$arType["LANG"][$languageID]["NAME"] = GetMessage($code."_TYPE_NAME");
		$arType["LANG"][$languageID]["ELEMENT_NAME"] = GetMessage($code."_ELEMENT_NAME");
		if ($arType["SECTIONS"] == "Y")
			$arType["LANG"][$languageID]["SECTION_NAME"] = GetMessage($code."_SECTION_NAME");

	}

	$iblockType->Add($arType);
}

// Option::set('iblock','combined_list_mode','Y');

?>
