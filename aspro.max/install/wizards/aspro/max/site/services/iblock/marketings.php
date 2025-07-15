<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";

$iblockShortCODE = "marketings";
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/".$iblockShortCODE.".xml";
if (!file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile)) {
	$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/ru/".$iblockShortCODE.".xml";
}
$iblockTYPE = "aspro_max_adv";
$iblockXMLID = "aspro_max_".$iblockShortCODE."_".WIZARD_SITE_ID;
$iblockCODE = "aspro_max_".$iblockShortCODE;
$iblockID = false;

$rsIBlock = CIBlock::GetList([], array("XML_ID" => $iblockXMLID, "TYPE" => $iblockTYPE));
if ($arIBlock = $rsIBlock->Fetch()) {
	$iblockID = $arIBlock["ID"];
	if (WIZARD_INSTALL_DEMO_DATA) {
		// delete if already exist & need install demo
		CIBlock::Delete($arIBlock["ID"]);
		$iblockID = false;
	}
}

if(WIZARD_INSTALL_DEMO_DATA){
	if(!$iblockID){
		// add new iblock
		$permissions = array("1" => "X", "2" => "R");
		$dbGroup = CGroup::GetList($by = "", $order = "", array("STRING_ID" => "content_editor"));
		if($arGroup = $dbGroup->Fetch()){
			$permissions[$arGroup["ID"]] = "W";
		};
		
		// replace macros IN_XML_SITE_ID & IN_XML_SITE_DIR in xml file - for correct url links to site
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back");
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_DIR" => WIZARD_SITE_DIR));
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_ID" => WIZARD_SITE_ID));
		$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, WIZARD_SITE_ID, $permissions);
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		if ($iblockID < 1)	return;
			
		// iblock fields
		$iblock = new CIBlock;
		$arFields = array(
			"ACTIVE" => "Y",
			"CODE" => $iblockCODE,
			"XML_ID" => $iblockXMLID,
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
	else{
		// attach iblock to site
		$arSites = []; 
		$db_res = CIBlock::GetSite($iblockID);
		while ($res = $db_res->Fetch())
			$arSites[] = $res["LID"]; 
		if (!in_array(WIZARD_SITE_ID, $arSites)){
			$arSites[] = WIZARD_SITE_ID;
			$iblock = new CIBlock;
			$iblock->Update($iblockID, array("LID" => $arSites));
		}
	}

	// iblock user fields
	$dbSite = CSite::GetByID(WIZARD_SITE_ID);
	if($arSite = $dbSite -> Fetch()) $lang = $arSite["LANGUAGE_ID"];
	if(!strlen($lang)) $lang = "ru";
	WizardServices::IncludeServiceLang('iblocks/'.$iblockShortCODE.'.php', $lang);
	WizardServices::IncludeServiceLang("iblocks/properties_hints_".$iblockShortCODE.".php", $lang);
	$arProperty = [];
	$dbProperty = CIBlockProperty::GetList([], array("IBLOCK_ID" => $iblockID));
	while($arProp = $dbProperty->Fetch())
		$arProperty[$arProp["CODE"]] = $arProp["ID"];

	// properties hints
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["LS_TIMEOUT"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_0")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["NO_OVERLAY"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_1")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["REQUIRED_CONFIRM"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_2")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["DISAGREE_MESSAGE"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_3")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["SHOW_ON_DEVICES"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_4")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["STOP_ACTIONS"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_5")));
	unset($ibp);
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["BG_COLOR"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_6")));
	unset($ibp);

	// edit form user options
	CUserOptions::SetOption("form", "form_element_".$iblockID, array(
		"tabs" => 'edit1--#--'.GetMessage("WZD_OPTION_0").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_2").'--,--NAME--#--'.GetMessage("WZD_OPTION_4").'--,--SORT--#--'.GetMessage("WZD_OPTION_6").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_8").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_10").'--,--CODE--#--'.GetMessage("WZD_OPTION_12").'--,--XML_ID--#--'.GetMessage("WZD_OPTION_14").'--,--edit1_csection1--#--'.GetMessage("WZD_OPTION_16").'--,--PROPERTY_'.$arProperty["MODAL_TYPE"].'--#--'.GetMessage("WZD_OPTION_18").'--,--PROPERTY_'.$arProperty["LINK_WEB_FORM"].'--#--'.GetMessage("WZD_OPTION_20").'--,--PROPERTY_'.$arProperty["HIDE_TITLE"].'--#--'.GetMessage("WZD_OPTION_22").'--,--PROPERTY_'.$arProperty["REQUIRED_CONFIRM"].'--#--'.GetMessage("WZD_OPTION_24").'--,--PROPERTY_'.$arProperty["DISAGREE_MESSAGE"].'--#--'.GetMessage("WZD_OPTION_26").'--,--PROPERTY_'.$arProperty["NO_OVERLAY"].'--#--'.GetMessage("WZD_OPTION_28").'--,--PROPERTY_'.$arProperty["SALE_TIMER"].'--#--'.GetMessage("WZD_OPTION_30").'--,--PROPERTY_'.$arProperty["COUPON_TEXT"].'--#--'.GetMessage("WZD_OPTION_32").'--,--edit1_csection2--#--'.GetMessage("WZD_OPTION_34").'--,--PROPERTY_'.$arProperty["FILTER_SHOW"].'--#--'.GetMessage("WZD_OPTION_36").'--,--PROPERTY_'.$arProperty["POSITION"].'--#--'.GetMessage("WZD_OPTION_38").'--,--PROPERTY_'.$arProperty["SHOW_ON_DEVICES"].'--#--'.GetMessage("WZD_OPTION_40").'--,--PROPERTY_'.$arProperty["DELAY_SHOW"].'--#--'.GetMessage("WZD_OPTION_42").'--,--PROPERTY_'.$arProperty["LS_TIMEOUT"].'--#--'.GetMessage("WZD_OPTION_44").'--,--PROPERTY_'.$arProperty["STOP_ACTIONS"].'--#--'.GetMessage("WZD_OPTION_46").'--;--edit1_csection1--#--'.GetMessage("WZD_OPTION_48").'--,--PROPERTY_'.$arProperty["BTN1_TEXT"].'--#--'.GetMessage("WZD_OPTION_50").'--,--PROPERTY_'.$arProperty["BTN1_LINK"].'--#--'.GetMessage("WZD_OPTION_52").'--,--PROPERTY_'.$arProperty["BTN1_TARGET"].'--#--'.GetMessage("WZD_OPTION_54").'--,--PROPERTY_'.$arProperty["BTN1_CLASS"].'--#--'.GetMessage("WZD_OPTION_56").'--,--PROPERTY_'.$arProperty["BTN2_TEXT"].'--#--'.GetMessage("WZD_OPTION_58").'--,--PROPERTY_'.$arProperty["BTN2_LINK"].'--#--'.GetMessage("WZD_OPTION_60").'--,--PROPERTY_'.$arProperty["BTN2_TARGET"].'--#--'.GetMessage("WZD_OPTION_62").'--,--PROPERTY_'.$arProperty["BTN2_CLASS"].'--#--'.GetMessage("WZD_OPTION_64").'--;--cedit1--#--'.GetMessage("WZD_OPTION_66").'--,--PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_68").'--,--PROPERTY_'.$arProperty["MAIN_LINK"].'--#--'.GetMessage("WZD_OPTION_70").'--,--PROPERTY_'.$arProperty["MAIN_TARGET"].'--#--'.GetMessage("WZD_OPTION_72").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_74").'--,--PROPERTY_'.$arProperty["BG_COLOR"].'--#--'.GetMessage("WZD_OPTION_76").'--;--cedit2--#--'.GetMessage("WZD_OPTION_78").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_78").'--;--cedit3--#--'.GetMessage("WZD_OPTION_80").'--,--PROPERTY_'.$arProperty["USER_GROUPS"].'--#--'.GetMessage("WZD_OPTION_82").'--,--PROPERTY_'.$arProperty["LINK_REGION"].'--#--'.GetMessage("WZD_OPTION_84").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_86").'--;----#--'.GetMessage("WZD_OPTION_88").'--;--',
	));
	// list user options
	CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockTYPE.".".$iblockID), array(
		'columns' => '', 'by' => '', 'order' => '', 'page_size' => '',
	));
}

if($iblockID){
	// replace macros IBLOCK_TYPE & IBLOCK_ID & IBLOCK_CODE
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MARKETINGS_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MARKETINGS_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_MARKETINGS_CODE" => $iblockCODE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MARKETINGS_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MARKETINGS_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MARKETINGS_CODE" => $iblockCODE));
}
?>
