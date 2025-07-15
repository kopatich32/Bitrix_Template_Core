<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;
if(!CModule::IncludeModule("catalog")) return;

if(!defined("WIZARD_SITE_ID")) return;
if(!defined("WIZARD_SITE_DIR")) return;
if(!defined("WIZARD_SITE_PATH")) return;
if(!defined("WIZARD_TEMPLATE_ID")) return;
if(!defined("WIZARD_TEMPLATE_ABSOLUTE_PATH")) return;
if(!defined("WIZARD_THEME_ID")) return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."/";
//$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"]."/local/templates/".WIZARD_TEMPLATE_ID."/";

$iblockShortCODE = "catalog";
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_part1.xml";
$iblockTYPE = "aspro_max_catalog";
$iblockXMLID = "aspro_max_".$iblockShortCODE."_".WIZARD_SITE_ID;
$iblockCODE = "aspro_max_".$iblockShortCODE;
$iblockID = false;

set_time_limit(0);

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockXMLID, "TYPE" => $iblockTYPE));
if ($arIBlock = $rsIBlock->Fetch()) {
	$iblockID = $arIBlock["ID"];
	if (WIZARD_INSTALL_DEMO_DATA) {
		// delete if already exist & need install demo
		$arCatalog = CCatalog::GetByIDExt($arIBlock["ID"]); 
		if (is_array($arCatalog) && (in_array($arCatalog['CATALOG_TYPE'],array('P','X'))) == true){
			CCatalog::UnLinkSKUIBlock($arIBlock["ID"]);
			CIBlock::Delete($arCatalog['OFFERS_IBLOCK_ID']);
		}
		CIBlock::Delete($arIBlock["ID"]);
		$iblockID = false;
		COption::SetOptionString("next", "demo_deleted", "N", "", WIZARD_SITE_ID);
		
		$dbRes = CUserTypeEntity::GetList(Array(), Array("ENTITY_ID" => 'IBLOCK_'.$arIBlock["ID"].'_SECTION'));
		while($arRes = $dbRes->Fetch()){
			$userType = new CUserTypeEntity();
			$userType->Delete($arRes["ID"]);
		}
	}
}

if(WIZARD_INSTALL_DEMO_DATA){
	if(!$iblockID){
		$shopLocalization = $wizard->GetVar("shopLocalization");
		switch($shopLocalization){
			case 'ua':
				if(!CCurrency::GetByID('UAH')){
					$arFields = array(
						"CURRENCY" => "UAH",
						"AMOUNT" => 39.41,
						"AMOUNT_CNT" => 10,
						"SORT" => 400
					);
					CCurrency::Add($arFields);

					$dbLangs = CLanguage::GetList($b, $o, array("ACTIVE" => "Y"));
					while($arLangs = $dbLangs->Fetch()){
						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install_lang.php", $arLangs["LID"]);
						$arFields = array(
							"LID" => $arLangs["LID"],
							"CURRENCY" => "UAH",
							"FORMAT_STRING" => GetMessage("CUR_INSTALL_UAH_FORMAT_STRING") ? GetMessage("CUR_INSTALL_UAH_FORMAT_STRING") : "",
							"FULL_NAME" => GetMessage("CUR_INSTALL_UAH_FULL_NAME"),
							"DEC_POINT" => GetMessage("CUR_INSTALL_UAH_DEC_POINT"),
							"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_UAH_THOUSANDS_SEP"),
							"THOUSANDS_SEP" => false,
							"DECIMALS" => 2,
							"HIDE_ZERO" => "Y"
						);
						if (!empty($arFields))
							CCurrencyLang::Add($arFields);
					}
				}
				break;
			case 'bl':
				if (!CCurrency::GetByID('BYR')){
					$arFields = array(
						"CURRENCY" => "BYR",
						"AMOUNT" => 36.72,
						"AMOUNT_CNT" => 10000,
						"SORT" => 500
					);
					CCurrency::Add($arFields);

					$dbLangs = CLanguage::GetList($b, $o, array("ACTIVE" => "Y"));
					while($arLangs = $dbLangs->Fetch()){
						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install_lang.php", $arLangs["LID"]);
						$arFields = array(
							"LID" => $arLangs["LID"],
							"CURRENCY" => "BYR",
							"FORMAT_STRING" => GetMessage("CUR_INSTALL_BYR_FORMAT_STRING") ? GetMessage("CUR_INSTALL_BYR_FORMAT_STRING") : "",
							"FULL_NAME" => GetMessage("CUR_INSTALL_BYR_FULL_NAME"),
							"DEC_POINT" => GetMessage("CUR_INSTALL_BYR_DEC_POINT"),
							"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_BYR_THOUSANDS_SEP"),
							"THOUSANDS_SEP" => false,
							"DECIMALS" => 2,
							"HIDE_ZERO" => "Y"
						);
						if (!empty($arFields))
							CCurrencyLang::Add($arFields);
					}
				}
				break;
		}
	
		$dbResultList = CCatalogGroup::GetList(Array(), Array("CODE" => "BASE"));
		if(!($dbResultList->Fetch())){
			$arFields = Array();
			$rsLanguage = CLanguage::GetList($by, $order, array());
			while($arLanguage = $rsLanguage->Fetch()){
				WizardServices::IncludeServiceLang("catalog.php", $arLanguage["ID"]);
				$arFields["USER_LANG"][$arLanguage["ID"]] = GetMessage("WIZ_PRICE_NAME");
			}
			$arFields["BASE"] = "Y";
			$arFields["SORT"] = 100;
			$arFields["NAME"] = "BASE";
			$arFields["USER_GROUP"] = Array(1, 2);
			$arFields["USER_GROUP_BUY"] = Array(1, 2);
			CCatalogGroup::Add($arFields);
		}
	
		// add new iblock
		$permissions = array("1" => "X", "2" => "R");
		$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "sale_administrator"));
		if($arGroup = $dbGroup -> Fetch()){
			$permissions[$arGroup["ID"]] = 'W';
		}
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
		
		/*if(method_exists('\Bitrix\Catalog\Product\Sku', 'disableUpdateAvailable'))
			\Bitrix\Catalog\Product\Sku::disableUpdateAvailable();*/
		
		$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, WIZARD_SITE_ID, $permissions);
		
		/*if(method_exists('\Bitrix\Catalog\Product\Sku', 'enableUpdateAvailable'))
			\Bitrix\Catalog\Product\Sku::enableUpdateAvailable();*/
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		if ($iblockID < 1)	return;
		
		$_SESSION["WIZARD_MAXIMUM_CATALOG_IBLOCK_ID"] = $iblockID;
			
		
	}
	else{
		// attach iblock to site
		$arSites = array(); 
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
	WizardServices::IncludeServiceLang("editform_useroptions.php", $lang);
	WizardServices::IncludeServiceLang("properties_hints.php", $lang);
	$arProperty = array();
	$dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblockID));
	while($arProp = $dbProperty->Fetch())
		$arProperty[$arProp["CODE"]] = $arProp["ID"];

	// properties hints
	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["PROP_2033"], array("HINT" => GetMessage("WZD_PROPERTY_HINT_0")));
	unset($ibp);

	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["ASSOCIATED_FILTER"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
	unset($ibp);

	$ibp = new CIBlockProperty;
	$ibp->Update($arProperty["EXPANDABLES_FILTER"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
	unset($ibp);

	$ibp = new CIBlockProperty;
	$arStockProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_STOCK_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
	if($arStockProps["ID"])
	{
		$ibp->Update($arStockProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_STOCK_IBLOCK_ID"]);
	}

	$ibp = new CIBlockProperty;
	$arArticlesProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_ARTICLES_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
	if($arArticlesProps["ID"])
	{
		$ibp->Update($arArticlesProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_ARTICLES_IBLOCK_ID"]);
	}

	$ibp = new CIBlockProperty;
	$arNewsProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_NEWS_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
	if($arNewsProps["ID"])
	{
		$ibp->Update($arNewsProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_NEWS_IBLOCK_ID"]);
	}

	$ibp = new CIBlockProperty;
	$arProjectsProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_PROJECTS_IBLOCK_ID"], "CODE" => "LINK_GOODS_FILTER"))->Fetch();
	if($arProjectsProps["ID"])
	{
		$ibp->Update($arProjectsProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_PROJECTS_IBLOCK_ID"]);
	}

	$ibp = new CIBlockProperty;
	$arCrossProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"], "CODE" => "PRODUCTS_FILTER"))->Fetch();
	if($arCrossProps["ID"])
	{
		$ibp->Update($arCrossProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp);
	}

	$ibp = new CIBlockProperty;
	$arCrossProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"], "CODE" => "EXT_PRODUCTS_FILTER"))->Fetch();
	if($arCrossProps["ID"])
	{
		$ibp->Update($arCrossProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_CROSS_SALE_IBLOCK_ID"]);
	}

	$ibp = new CIBlockProperty;
	$arCrossProps = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"], "CODE" => "CUSTOM_FILTER"))->Fetch();
	if($arCrossProps["ID"])
	{
		$ibp->Update($arCrossProps["ID"], array('USER_TYPE' => 'SAsproCustomFilterMax','USER_TYPE_SETTINGS' => array('IBLOCK_ID' => $iblockID)));
		unset($ibp, $_SESSION["WIZARD_MAXIMUM_SEARCH_IBLOCK_ID"]);
	}

}

if($iblockID){
	// replace macros IBLOCK_TYPE & IBLOCK_ID & IBLOCK_CODE
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));

	unset($_SESSION["MAXIMUM_CATALOG_ID"]);
	$_SESSION["MAXIMUM_CATALOG_ID"] = $iblockID;

	\Bitrix\Main\Config\Option::set("aspro.max", "CATALOG_IBLOCK_ID", $iblockID, WIZARD_SITE_ID);
}
?>