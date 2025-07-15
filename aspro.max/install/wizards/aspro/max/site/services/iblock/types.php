<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;

use \Bitrix\Main\Config\Option;

if( Option::get("aspro.max", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA )
	return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";
	
$arTypes = Array(
	Array(
		"ID" => "aspro_max_catalog",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 100,
	),
	Array(
		"ID" => "aspro_max_content",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 200,
	),
	Array(
		"ID" => "aspro_max_adv",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 300,
	),
	Array(
		"ID" => "aspro_max_regionality",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 400,
	),
	Array(
		"ID" => "aspro_max_mainblocks",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 500,
	)
);
$arLanguages = Array();
$rsLanguage = CLanguage::GetList($by, $order, array());
while($arLanguage = $rsLanguage->Fetch())
	$arLanguages[] = $arLanguage["LID"];
	
$iblockType = new CIBlockType;
foreach($arTypes as $arType)
{
	$dbType = CIBlockType::GetList(Array(),Array("=ID" => $arType["ID"]));
	if($dbType->Fetch())
		continue;

	foreach($arLanguages as $languageID)
	{
		WizardServices::IncludeServiceLang("types.php", $languageID);
		$code = strtoupper($arType["ID"]);
		$arType["LANG"][$languageID]["NAME"] = GetMessage($code."_TYPE_NAME");
		$arType["LANG"][$languageID]["ELEMENT_NAME"] = GetMessage($code."_ELEMENT_NAME");

		if ($arType["SECTIONS"] == "Y")
			$arType["LANG"][$languageID]["SECTION_NAME"] = GetMessage($code."_SECTION_NAME");
	}
	$newIblockType = $iblockType->Add($arType);
	
    if(IntVal($newIblockType)<=0)
    { $GLOBALS["ASPRO_MAX_WIZARD_LAST_ERROR"]["UNABLE_TO_CREATE_IBLOCK_TYPE_".strtoupper($arType["ID"])] = $iblockType->LAST_ERROR;}
}

// replace macros IBLOCK_MAX_CATALOG_TYPE & IBLOCK_MAX_CONTENT_TYPE & IBLOCK_MAX_ADV_TYPE
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MAX_CATALOG_TYPE" => "aspro_max_catalog"));
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MAX_CONTENT_TYPE" => "aspro_max_content"));
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MAX_ADV_TYPE" => "aspro_max_adv"));
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MAX_MAINBLOCKS_TYPE" => "aspro_max_mainblocks"));
CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAX_CATALOG_TYPE" => "aspro_max_catalog"));
CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAX_CONTENT_TYPE" => "aspro_max_content"));
CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAX_ADV_TYPE" => "aspro_max_adv"));
CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAX_MAINBLOCKS_TYPE" => "aspro_max_mainblocks"));

Option::set('iblock','combined_list_mode','Y');
?>