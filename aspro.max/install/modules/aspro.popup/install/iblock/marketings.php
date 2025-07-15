<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard_sol/utils.php");

$lang = "ru";
$iblockShortCODE = "marketings";
$iblockXMLFile = __DIR__."/xml/".$lang."/".$iblockShortCODE.".xml";

$iblockTYPE = "aspro_popup_adv";
$iblockXMLID = "aspro_popup_".$iblockShortCODE;
$iblockCODE = "aspro_popup_".$iblockShortCODE;
$iblockID = false;

$rsIBlock = CIBlock::GetList([], array("XML_ID" => $iblockXMLID, "TYPE" => $iblockTYPE));
if ($arIBlock = $rsIBlock->Fetch()) {
	$iblockID = $arIBlock["ID"];
}

$arSites = [];
$rsSites = \CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));

while($arItem = $rsSites->Fetch()) {
	$arSites[] = $arItem["ID"];
}

if($arSites){
	$siteID = $arSites[0] ?? '';
}


if(!$iblockID){
	// add new iblock
	$permissions = array("1" => "X", "2" => "R");
	$dbGroup = CGroup::GetList($by = "", $order = "", array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup->Fetch()){
		$permissions[$arGroup["ID"]] = "W";
	};
	
	$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, $siteID, $permissions);

	if ($iblockID < 1)	return;
		
	// iblock fields
	$iblock = new CIBlock;
	$arFields = array(
		"ACTIVE" => "Y",
		"CODE" => $iblockCODE,
		"XML_ID" => $iblockXMLID,
		"SITE_ID" => $arSites,
		"FIELDS" => array(
			"IBLOCK_SECTION" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "Array",
			),
			"ACTIVE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE"=> "Y",
			),
			"ACTIVE_FROM" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			),
			"ACTIVE_TO" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			),
			"SORT" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "0",
			), 
			"NAME" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "",
			), 
			"PREVIEW_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "N",
					"SCALE" => "N",
					"WIDTH" => "",
					"HEIGHT" => "",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
					"DELETE_WITH_DETAIL" => "N",
					"UPDATE_WITH_DETAIL" => "N",
				),
			), 
			"PREVIEW_TEXT_TYPE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "text",
			), 
			"PREVIEW_TEXT" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			), 
			"DETAIL_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"SCALE" => "N",
					"WIDTH" => "",
					"HEIGHT" => "",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
				),
			), 
			"DETAIL_TEXT_TYPE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "text",
			), 
			"DETAIL_TEXT" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			), 
			"XML_ID" =>  array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "",
			), 
			"CODE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"UNIQUE" => "N",
					"TRANSLITERATION" => "N",
					"TRANS_LEN" => 100,
					"TRANS_CASE" => "L",
					"TRANS_SPACE" => "-",
					"TRANS_OTHER" => "-",
					"TRANS_EAT" => "Y",
					"USE_GOOGLE" => "N",
				),
			),
			"TAGS" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			), 
			"SECTION_NAME" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "",
			), 
			"SECTION_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"FROM_DETAIL" => "N",
					"SCALE" => "N",
					"WIDTH" => "",
					"HEIGHT" => "",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
					"DELETE_WITH_DETAIL" => "N",
					"UPDATE_WITH_DETAIL" => "N",
				),
			), 
			"SECTION_DESCRIPTION_TYPE" => array(
				"IS_REQUIRED" => "Y",
				"DEFAULT_VALUE" => "text",
			), 
			"SECTION_DESCRIPTION" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			), 
			"SECTION_DETAIL_PICTURE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"SCALE" => "N",
					"WIDTH" => "",
					"HEIGHT" => "",
					"IGNORE_ERRORS" => "N",
					"METHOD" => "resample",
					"COMPRESSION" => 95,
				),
			), 
			"SECTION_XML_ID" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => "",
			), 
			"SECTION_CODE" => array(
				"IS_REQUIRED" => "N",
				"DEFAULT_VALUE" => array(
					"UNIQUE" => "N",
					"TRANSLITERATION" => "N",
					"TRANS_LEN" => 100,
					"TRANS_CASE" => "L",
					"TRANS_SPACE" => "-",
					"TRANS_OTHER" => "-",
					"TRANS_EAT" => "Y",
					"USE_GOOGLE" => "N",
				),
			), 
		),
	);
	
	$iblock->Update($iblockID, $arFields);
}

// iblock user fields
$fileLang = LANGUAGE_ID === 'en' ? 'en' : 'ru';
Loc::loadLanguageFile(__DIR__.'/editform_useroptions_marketings.php', $fileLang);
Loc::loadLanguageFile(__DIR__.'/properties_hints_marketings.php', $fileLang);
$arProperty = [];
$dbProperty = CIBlockProperty::GetList([], array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch())
	$arProperty[$arProp["CODE"]] = $arProp["ID"];

// properties hints
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["NO_OVERLAY"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_0")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["REQUIRED_CONFIRM"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_1")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["DISAGREE_MESSAGE"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_2")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["SHOW_ON_DEVICES"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_3")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["STOP_ACTIONS"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_4")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["BG_COLOR"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_5")));
unset($ibp);
$ibp = new CIBlockProperty;
$ibp->Update($arProperty["LS_TIMEOUT"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_6")));
unset($ibp);

// edit form user options
CUserOptions::SetOption("form", "form_element_".$iblockID, array(
	"tabs" => 'edit1--#--'.GetMessage("WZD_OPTION_0").'--,--DATE_CREATE--#--'.GetMessage("WZD_OPTION_2").'--,--TIMESTAMP_X--#--'.GetMessage("WZD_OPTION_4").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_6").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_8").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_10").'--,--NAME--#--'.GetMessage("WZD_OPTION_12").'--,--CODE--#--'.GetMessage("WZD_OPTION_14").'--,--XML_ID--#--'.GetMessage("WZD_OPTION_16").'--,--SORT--#--'.GetMessage("WZD_OPTION_18").'--,--edit1_csection1--#--'.GetMessage("WZD_OPTION_20").'--,--PROPERTY_'.$arProperty["MODAL_TYPE"].'--#--'.GetMessage("WZD_OPTION_22").'--,--PROPERTY_'.$arProperty["POSITION"].'--#--'.GetMessage("WZD_OPTION_24").'--,--PROPERTY_'.$arProperty["TEXT_POSITION"].'--#--'.GetMessage("WZD_OPTION_26").'--,--PROPERTY_'.$arProperty["LINK_WEB_FORM"].'--#--'.GetMessage("WZD_OPTION_28").'--,--PROPERTY_'.$arProperty["HIDE_TITLE"].'--#--'.GetMessage("WZD_OPTION_30").'--,--PROPERTY_'.$arProperty["REQUIRED_CONFIRM"].'--#--'.GetMessage("WZD_OPTION_32").'--,--PROPERTY_'.$arProperty["DISAGREE_MESSAGE"].'--#--'.GetMessage("WZD_OPTION_34").'--,--PROPERTY_'.$arProperty["NO_OVERLAY"].'--#--'.GetMessage("WZD_OPTION_36").'--,--PROPERTY_'.$arProperty["SALE_TIMER"].'--#--'.GetMessage("WZD_OPTION_38").'--,--PROPERTY_'.$arProperty["COUPON_TEXT"].'--#--'.GetMessage("WZD_OPTION_40").'--,--IBLOCK_ELEMENT_PROP_VALUE--#--'.GetMessage("WZD_OPTION_42").'--,--PROPERTY_'.$arProperty["FILTER_SHOW"].'--#--'.GetMessage("WZD_OPTION_44").'--,--PROPERTY_'.$arProperty["USER_ACTION"].'--#--'.GetMessage("WZD_OPTION_100").'--,--PROPERTY_'.$arProperty["SHOW_ON_DEVICES"].'--#--'.GetMessage("WZD_OPTION_46").'--,--PROPERTY_'.$arProperty["DELAY_SHOW"].'--#--'.GetMessage("WZD_OPTION_48").'--,--PROPERTY_'.$arProperty["LS_TIMEOUT"].'--#--'.GetMessage("WZD_OPTION_50").'--,--PROPERTY_'.$arProperty["STOP_ACTIONS"].'--#--'.GetMessage("WZD_OPTION_52").'--;--cedit2--#--'.GetMessage("WZD_OPTION_54").'--,--cedit2_csection1--#--'.GetMessage("WZD_OPTION_56").'--,--PROPERTY_'.$arProperty["BTN1_TEXT"].'--#--'.GetMessage("WZD_OPTION_58").'--,--PROPERTY_'.$arProperty["BTN1_LINK"].'--#--'.GetMessage("WZD_OPTION_60").'--,--PROPERTY_'.$arProperty["BTN1_TARGET"].'--#--'.GetMessage("WZD_OPTION_62").'--,--PROPERTY_'.$arProperty["BTN1_CLASS"].'--#--'.GetMessage("WZD_OPTION_64").'--,--cedit2_csection2--#--'.GetMessage("WZD_OPTION_66").'--,--PROPERTY_'.$arProperty["BTN2_TEXT"].'--#--'.GetMessage("WZD_OPTION_68").'--,--PROPERTY_'.$arProperty["BTN2_LINK"].'--#--'.GetMessage("WZD_OPTION_70").'--,--PROPERTY_'.$arProperty["BTN2_TARGET"].'--#--'.GetMessage("WZD_OPTION_72").'--,--PROPERTY_'.$arProperty["BTN2_CLASS"].'--#--'.GetMessage("WZD_OPTION_74").'--;--edit5--#--'.GetMessage("WZD_OPTION_76").'--,--PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_78").'--,--PROPERTY_'.$arProperty["MAIN_LINK"].'--#--'.GetMessage("WZD_OPTION_80").'--,--PROPERTY_'.$arProperty["MAIN_TARGET"].'--#--'.GetMessage("WZD_OPTION_82").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_84").'--,--PROPERTY_'.$arProperty["BG_COLOR"].'--#--'.GetMessage("WZD_OPTION_86").'--;--edit6--#--'.GetMessage("WZD_OPTION_88").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_88").'--;--cedit1--#--'.GetMessage("WZD_OPTION_90").'--,--PROPERTY_'.$arProperty["USER_GROUPS"].'--#--'.GetMessage("WZD_OPTION_92").'--,--PROPERTY_'.$arProperty["LINK_REGION"].'--#--'.GetMessage("WZD_OPTION_94").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_96").'--;----#--'.GetMessage("WZD_OPTION_98").'--;--',
));
// list user options
CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockTYPE.".".$iblockID), array(
	'columns' => '', 'by' => '', 'order' => '', 'page_size' => '',
));

?>
