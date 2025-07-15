<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

$wizardSiteId = defined("WIZARD_SITE_ID") ? WIZARD_SITE_ID : $GLOBALS['WIZARD_SITE_ID'];
$wizardSiteDir = defined("WIZARD_SITE_DIR") ? WIZARD_SITE_DIR : $GLOBALS['WIZARD_SITE_DIR'];
$wizardSitePath = defined("WIZARD_SITE_PATH") ? WIZARD_SITE_PATH : $GLOBALS['WIZARD_SITE_PATH'];
$wizardLangId = defined("WIZARD_LANGUAGE_ID") ? WIZARD_LANGUAGE_ID : $GLOBALS['WIZARD_LANGUAGE_ID'];
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";

$iblockShortCODE = "mainblocks";
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/".$iblockShortCODE.".xml";
if (!file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile)) {
	$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/ru/".$iblockShortCODE.".xml";
}
$iblockTYPE = "aspro_max_mainblocks";
$iblockXMLID = "aspro_max_".$iblockShortCODE."_".$wizardSiteId;
$iblockCODE = "aspro_max_".$iblockShortCODE;
$iblockID = false;

// different catalog content
$thematic = isset($_REQUEST['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) ? strtolower($_REQUEST['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) : 'universal';
if ($thematic === 'active') {
	$sectionName1 = 'Все для рыбалки';
	$sectionCode1 = 'rybalka';

	$sectionName2 = 'Мебель для кемпинга';
	$sectionCode2 = 'kempingovaya_mebel';
}
elseif ($thematic === 'mebel') {
	$sectionName1 = 'Мебель для комфортной работы';
	$sectionCode1 = 'rabochee_mesto';

	$sectionName2 = 'Модульная кухня';
	$sectionCode2 = 'modulnye_kukhni';
}
elseif ($thematic === 'volt') {
	$sectionName1 = 'Музыка на любой вкус';
	$sectionCode1 = 'audiotekhnika';

	$sectionName2 = 'Стильные смартфоны';
	$sectionCode2 = 'smartfony';
}
elseif ($thematic === 'moda') {
	$sectionName1 = 'Одежда для юных модников';
	$sectionCode1 = 'detyam';

	$sectionName2 = 'Новая мужская коллекция';
	$sectionCode2 = 'men';
}
elseif ($thematic === 'home') {
	$sectionName1 = 'Декор для дома';
	$sectionCode1 = 'dekor';

	$sectionName2 = 'Кухонные принадлежности';
	$sectionCode2 = 'kukhonnye_prinadlezhnosti';
}
else {
	$sectionName1 = 'Товары для дома и дачи';
	$sectionCode1 = 'tovary_dlya_doma_i_dachi';

	$sectionName2 = 'Телевизоры';
	$sectionCode2 = 'televizory';
}
$sectionUrl1 = $sectionUrl2 = '';

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

		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_DIR" => $wizardSiteDir));
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_SITE_ID" => $wizardSiteId));

		// fix incorrect charset
		$tmp1 = trim(iconv(SITE_CHARSET, 'windows-1251', $sectionName1));
		if (!strlen($tmp1)) {
			$tmp1 = $sectionCode1;
		}
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("CATALOG_SECTION_NAME_1" => $tmp1));

		$tmp2 = trim(iconv(SITE_CHARSET, 'windows-1251', $sectionName2));
		if (!strlen($tmp2)) {
			$tmp2 = $sectionCode2;
		}
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("CATALOG_SECTION_NAME_2" => $tmp2));

		@unlink($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".debug");
		@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".debug");

		$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, $wizardSiteId, $permissions);
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
					"DEFAULT_VALUE" => "500",
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
		if (!in_array($wizardSiteId, $arSites)){
			$arSites[] = $wizardSiteId;
			$iblock = new CIBlock;
			$iblock->Update($iblockID, array("LID" => $arSites));
		}
	}

	// iblock user fields
	$dbSite = CSite::GetByID($wizardSiteId);
	if($arSite = $dbSite -> Fetch()) $lang = $arSite["LANGUAGE_ID"];
	if(!strlen($lang)) $lang = "ru";
	WizardServices::IncludeServiceLang("editform_useroptions_".$iblockShortCODE.".php", $lang);
	WizardServices::IncludeServiceLang("properties_hints_".$iblockShortCODE.".php", $lang);
	$arProperty = [];
	$dbProperty = CIBlockProperty::GetList([], array("IBLOCK_ID" => $iblockID));
	while($arProp = $dbProperty->Fetch())
		$arProperty[$arProp["CODE"]] = $arProp["ID"];

	// properties hints

	// edit form user options
	CUserOptions::SetOption("form", "form_element_".$iblockID, array(
		"tabs" => 'edit1--#--'.GetMessage("WZD_OPTION_0").'--,--ID--#--'.GetMessage("WZD_OPTION_2").'--,--DATE_CREATE--#--'.GetMessage("WZD_OPTION_4").'--,--TIMESTAMP_X--#--'.GetMessage("WZD_OPTION_6").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_8").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_10").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_12").'--,--NAME--#--'.GetMessage("WZD_OPTION_14").'--,--CODE--#--'.GetMessage("WZD_OPTION_16").'--,--XML_ID--#--'.GetMessage("WZD_OPTION_18").'--,--SORT--#--'.GetMessage("WZD_OPTION_20").'--;--edit14--#--'.GetMessage("WZD_OPTION_22").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_TITLE--#--'.GetMessage("WZD_OPTION_24").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS--#--'.GetMessage("WZD_OPTION_26").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION--#--'.GetMessage("WZD_OPTION_28").'--,--IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE--#--'.GetMessage("WZD_OPTION_30").'--,--IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_32").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_34").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_36").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_38").'--,--IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_40").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_34").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_36").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_38").'--,--SEO_ADDITIONAL--#--'.GetMessage("WZD_OPTION_42").'--,--TAGS--#--'.GetMessage("WZD_OPTION_44").'--;--edit2--#--'.GetMessage("WZD_OPTION_46").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_46").'--;----#--'.GetMessage("WZD_OPTION_48").'--;--',
	));
	// list user options
	CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockTYPE.".".$iblockID), array(
		'columns' => '', 'by' => '', 'order' => '', 'page_size' => '',
	));
}

$includePath = str_replace('//', '/', $wizardSitePath.'/include/mainpage/');

if($iblockID){
	// replace macros IBLOCK_TYPE & IBLOCK_ID & IBLOCK_CODE
	CWizardUtil::ReplaceMacrosRecursive($includePath, Array("IBLOCK_MAINBLOCKS_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive($includePath, Array("IBLOCK_MAINBLOCKS_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($includePath, Array("IBLOCK_MAINBLOCKS_CODE" => $iblockCODE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAINBLOCKS_TYPE" => $iblockTYPE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAINBLOCKS_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_MAINBLOCKS_CODE" => $iblockCODE));
}

\Bitrix\Main\Loader::includeModule(ASPRO_MODULE_NAME);

$arCatalogSections = [
	$sectionCode1,
	$sectionCode2,
];
foreach ($arCatalogSections as $i => $sectionCode) {
	$n = $i + 1;

	CWizardUtil::ReplaceMacrosRecursive($includePath, Array("CATALOG_SECTION_CODE_".$n => $sectionCode));

	$sectionNameVar = 'sectionName'.$n;
	CWizardUtil::ReplaceMacrosRecursive($includePath, Array("CATALOG_SECTION_NAME_".$n => $$sectionNameVar));
}

$catalogIblockId = \Bitrix\Main\Config\Option::get(ASPRO_MODULE_NAME, 'CATALOG_IBLOCK_ID', '', $wizardSiteId);
if (
	!$catalogIblockId &&
	class_exists('CMaxCache')
) {
	$catalogIblockId = CMaxCache::$arIBlocks[$wizardSiteId]['aspro_max_catalog']['aspro_max_catalog'][0] ?? '';
}

if ($catalogIblockId) {
	\CWizardUtil::ReplaceMacrosRecursive($includePath, Array("IBLOCK_CATALOG_ID" => $catalogIblockId));

	$dbRes = \CIBlockSection::GetList(
		[],
		[
			'IBLOCK_ID' => $catalogIblockId,
			'CODE' => $arCatalogSections,
			'INCLUDE_SUBSECTIONS' => 'Y',
		],
		false,
		[
			'ID',
			'IBLOCK_ID',
			'CODE',
			'SECTION_PAGE_URL',
		]
	);
	while ($arSection = $dbRes->GetNext()) {
		$n = array_search($arSection['CODE'], $arCatalogSections);
		if ($n !== false) {
			++$n;
			$sectionUrl = ltrim($arSection['SECTION_PAGE_URL'], '/');
			CWizardUtil::ReplaceMacrosRecursive($includePath, Array("CATALOG_SECTION_URL_".$n => $sectionUrl));
		}
	}
}

if (class_exists('CMaxCache')) {
	$companyIblockId = CMaxCache::$arIBlocks[$wizardSiteId]['aspro_max_content']['aspro_max_company'][0] ?? '';
	if ($companyIblockId) {
		\CWizardUtil::ReplaceMacrosRecursive($includePath, Array("IBLOCK_COMPANY_ID" => $companyIblockId));
	}
}
?>
