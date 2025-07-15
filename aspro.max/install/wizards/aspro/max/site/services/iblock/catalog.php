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
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/".$iblockShortCODE.".xml";
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

		$tizersIBlockID = CMaxCache::$arIBlocks[WIZARD_SITE_ID]['aspro_max_content']['aspro_max_tizers'][0];
		CWizardUtil::ReplaceMacros($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile, Array("IN_XML_CATALOG_ID" => $tizersIBlockID));
		
		if(method_exists('\Bitrix\Catalog\Product\Sku', 'disableUpdateAvailable'))
			\Bitrix\Catalog\Product\Sku::disableUpdateAvailable();
		
		$iblockID = WizardServices::ImportIBlockFromXML($iblockXMLFile, $iblockCODE, $iblockTYPE, WIZARD_SITE_ID, $permissions);
		
		if(method_exists('\Bitrix\Catalog\Product\Sku', 'enableUpdateAvailable'))
			\Bitrix\Catalog\Product\Sku::enableUpdateAvailable();
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back")){
			@copy($_SERVER["DOCUMENT_ROOT"].$iblockXMLFile.".back", $_SERVER["DOCUMENT_ROOT"].$iblockXMLFile);
		}
		if ($iblockID < 1)	return;
		
		$_SESSION["WIZARD_MAX_CATALOG_IBLOCK_ID"] = $iblockID;
			
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
					"DEFAULT_VALUE" => "=today",
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
						"FROM_DETAIL" => "Y",
						"SCALE" => "Y",
						"WIDTH" => "200",
						"HEIGHT" => "200",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
						"DELETE_WITH_DETAIL" => "Y",
						"UPDATE_WITH_DETAIL" => "Y",
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
						"SCALE" => "Y",
						"WIDTH" => "2000",
						"HEIGHT" => "2000",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
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
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"CODE" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => array(
						"UNIQUE" => "Y",
						"TRANSLITERATION" => "Y",
						"TRANS_LEN" => 100,
						"TRANS_CASE" => "L",
						"TRANS_SPACE" => "_",
						"TRANS_OTHER" => "_",
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
						"FROM_DETAIL" => "Y",
						"SCALE" => "Y",
						"WIDTH" => "120",
						"HEIGHT" => "120",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
						"DELETE_WITH_DETAIL" => "Y",
						"UPDATE_WITH_DETAIL" => "Y",
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
						"SCALE" => "Y",
						"WIDTH" => "2000",
						"HEIGHT" => "2000",
						"IGNORE_ERRORS" => "N",
						"METHOD" => "resample",
						"COMPRESSION" => 75,
					),
				), 
				"SECTION_XML_ID" => array(
					"IS_REQUIRED" => "N",
					"DEFAULT_VALUE" => "",
				), 
				"SECTION_CODE" => array(
					"IS_REQUIRED" => "Y",
					"DEFAULT_VALUE" => array(
						"UNIQUE" => "Y",
						"TRANSLITERATION" => "Y",
						"TRANS_LEN" => 100,
						"TRANS_CASE" => "L",
						"TRANS_SPACE" => "_",
						"TRANS_OTHER" => "_",
						"TRANS_EAT" => "Y",
						"USE_GOOGLE" => "N",
					),
				), 
			),
		);
		
		$iblock->Update($iblockID, $arFields);
		
		//user fields for sections
		$arLanguages = Array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch())
			$arLanguages[] = $arLanguage["LID"];

		$arUserFields = array("UF_SECTION_TEMPLATE", "UF_SECTION_DESCR", "UF_POPULAR", "UF_FILTER_VIEW");
		foreach($arUserFields as $userField){
			$arLabelNames = Array();
			foreach($arLanguages as $languageID){
				WizardServices::IncludeServiceLang("catalog.php", $arLanguage["ID"]);
				$arLabelNames[$languageID] = GetMessage($userField);
			}

			$arProperty["EDIT_FORM_LABEL"] = $arLabelNames;
			$arProperty["LIST_COLUMN_LABEL"] = $arLabelNames;
			$arProperty["LIST_FILTER_LABEL"] = $arLabelNames;

			$dbRes = CUserTypeEntity::GetList(Array(), Array("ENTITY_ID" => 'IBLOCK_'.$iblockID.'_SECTION', "FIELD_NAME" => $userField));
			if($arRes = $dbRes->Fetch()){
				$userType = new CUserTypeEntity();
				$userType->Update($arRes["ID"], $arProperty);
			}
			//if($ex = $APPLICATION->GetException())
				//$strError = $ex->GetString();
		}
		
		// get DB charset
		$sql='SHOW VARIABLES LIKE "character_set_database";';
		if(method_exists('\Bitrix\Main\Application', 'getConnection')){
			$db=\Bitrix\Main\Application::getConnection();
			$arResult = $db->query($sql)->fetch();
			$isUTF8 = $arResult['Value'] == 'utf8';
		}elseif(defined("BX_USE_MYSQLI") && BX_USE_MYSQLI === true){
			if($result = @mysqli_query($sql)){
				$arResult = mysql_fetch_row($result);
				$isUTF8 = $arResult[1] == 'utf8';
			}
		}elseif($result = @mysql_query($sql)){
			$arResult = mysql_fetch_row($result);
			$isUTF8 = $arResult[1] == 'utf8';
		}
		
		// check iblock user field UF_SECTION_TEMPLATE
		$arUserFieldSectionTemplate = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => 'IBLOCK_'.$iblockID.'_SECTION', 'FIELD_NAME' => 'UF_SECTION_TEMPLATE'))->Fetch();
		$resUserFieldSectionTemplateEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arUserFieldSectionTemplate['ID']));
		while($arUserFieldSectionTemplateEnum = $resUserFieldSectionTemplateEnum->GetNext()){
			$obEnum = new CUserFieldEnum;
			$obEnum->SetEnumValues($arUserFieldSectionTemplate['ID'], array($arUserFieldSectionTemplateEnum['ID'] => array('DEL' => 'Y')));
		}
		$obEnum = new CUserFieldEnum;
		$obEnum->SetEnumValues($arUserFieldSectionTemplate['ID'], array(
			'n0' => array(
				'VALUE' => ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Плиткой') : 'Плиткой'),
				'XML_ID' => 'block',
			),
			'n1' => array(
				'VALUE' => ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Списком') : 'Списком'),
				'XML_ID' => 'list',
			),
			'n2' => array(
				'VALUE' => ($isUTF8 ? iconv('CP1251', 'UTF-8', 'Таблицей') : 'Таблицей'),
				'XML_ID' => 'table',
			),
		));
		$resUserFieldSectionTemplateEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID' => $arUserFieldSectionTemplate['ID']));
		while($arUserFieldSectionTemplateEnum = $resUserFieldSectionTemplateEnum->GetNext()){
			$arUserFieldSectionTemplateEnums[$arUserFieldSectionTemplateEnum['XML_ID']] = $arUserFieldSectionTemplateEnum['ID'];
		}
		$bs = new CIBlockSection;
		$resDB = CIBlockSection::GetList(array(), array('CODE' => 'sukhie_stroitelnye_smesi'), false, array('ID'));
		while($arRes = $resDB->Fetch()){
			$res = $bs->Update($arRes["ID"], array("UF_SECTION_TEMPLATE" => $arUserFieldSectionTemplateEnums['list']));
		}
		
		//demo discount
		$dbDiscount = CCatalogDiscount::GetList(array(), Array("SITE_ID" => WIZARD_SITE_ID));
		if(!($dbDiscount->Fetch())){
			$arEnumHit = array();
			$dbEenumHit = CIBlockProperty::GetPropertyEnum("HIT", Array(), Array("IBLOCK_ID" => $iblockID));
			while($arEnum = $dbEenumHit->GetNext()){
				$arEnumHit[$arEnum['XML_ID']] = $arEnum['ID'];
				$propHitID = $arEnum['PROPERTY_ID'];
			}
			
			if($arEnumHit && $propHitID){
				$dbSite = CSite::GetByID(WIZARD_SITE_ID);
				if($arSite = $dbSite -> Fetch())
					$lang = $arSite["LANGUAGE_ID"];
					
				$defCurrency = "EUR";
				if($lang == "ru")
					$defCurrency = "RUB";
				elseif($lang == "en")
					$defCurrency = "USD";
					
				$arF = Array (
					"SITE_ID" => WIZARD_SITE_ID,
					"ACTIVE" => "Y",
					//"ACTIVE_FROM" => ConvertTimeStamp(mktime(0,0,0,12,15,2011), "FULL"),
					//"ACTIVE_TO" => ConvertTimeStamp(mktime(0,0,0,03,15,2012), "FULL"),
					"RENEWAL" => "N",
					"NAME" => GetMessage("WIZ_DISCOUNT"),
					"SORT" => 100,
					"MAX_DISCOUNT" => 0,
					"VALUE_TYPE" => "P",
					"VALUE" => 10,
					"CURRENCY" => $defCurrency,
					"CONDITIONS" => Array (
						"CLASS_ID" => "CondGroup",
						"DATA" => array("All" => "OR", "True" => "True"),
						"CHILDREN"=> array(
							array(
								"CLASS_ID" => "CondIBProp:".$iblockID.":".$propHitID,
								"DATA" => array("logic" => "Equal", "value" => $arEnumHit['HIT']),
							),
							array(
								"CLASS_ID" => "CondIBProp:".$iblockID.":".$propHitID,
								"DATA" => array("logic" => "Equal", "value" => $arEnumHit['STOCK']),
							),
							array(
								"CLASS_ID" => "CondIBProp:".$iblockID.":".$propHitID,
								"DATA" => array("logic" => "Equal", "value" => $arEnumHit['RECOMMEND']),
							),
							array(
								"CLASS_ID" => "CondIBProp:".$iblockID.":".$propHitID,
								"DATA" => array("logic" => "Equal", "value" => $arEnumHit['NEW']),
							),
						)
					)
				);
				CCatalogDiscount::Add($arF);
			}
		}
		
		// add stores
		/*$dbStores = CCatalogStore::GetList(array(), array("ACTIVE" => 'Y'));
		if(!$dbStores->Fetch())
		{
			$storeImageId = 0;
			$storeImage = CFile::MakeFileArray(WIZARD_SERVICE_RELATIVE_PATH.'/images/storepoint.jpg');
			if (!empty($storeImage) && is_array($storeImage))
			{
				$storeImage['MODULE_ID'] = 'catalog';
				$storeImageId =  CFile::SaveFile($storeImage, 'catalog');
			}

			$arStoreFields = array(
				"TITLE" => GetMessage("CAT_STORE_NAME"),
				"ADDRESS" => GetMessage("STORE_ADR_1"),
				"DESCRIPTION" => GetMessage("STORE_DESCR_1"),
				"GPS_N" => GetMessage("STORE_GPS_N_1"),
				"GPS_S" => GetMessage("STORE_GPS_S_1"),
				"PHONE" => GetMessage("STORE_PHONE_1"),
				"SCHEDULE" => GetMessage("STORE_PHONE_SCHEDULE"),
				"IMAGE_ID" => $storeImageId
			);
			$newStoreId = CCatalogStore::Add($arStoreFields);
			if($newStoreId)
			{
				CCatalogDocs::synchronizeStockQuantity($newStoreId);
			}
		}*/
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

	// edit form user options
	CUserOptions::SetOption("form", "form_element_".$iblockID, array(
		"tabs" => 'edit1--#--'.GetMessage("WZD_OPTION_80").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_2").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_14").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_16").'--,--NAME--#--'.GetMessage("WZD_OPTION_4").'--,--CODE--#--'.GetMessage("WZD_OPTION_6").'--,--IBLOCK_ELEMENT_PROPERTY--#--'.GetMessage("WZD_OPTION_196").'--,--IBLOCK_ELEMENT_PROP_VALUE--#--'.GetMessage("WZD_OPTION_196").'--,--PROPERTY_'.$arProperty["SALE_TEXT"].'--#--'.GetMessage("WZD_OPTION_274").'--,--PROPERTY_'.$arProperty["FAVORIT_ITEM"].'--#--'.GetMessage("WZD_OPTION_276").'--,--PROPERTY_'.$arProperty["HIT"].'--#--'.GetMessage("WZD_OPTION_278").'--,--PROPERTY_'.$arProperty["BRAND"].'--#--'.GetMessage("WZD_OPTION_168").'--,--PROPERTY_'.$arProperty["CML2_ARTICLE"].'--#--'.GetMessage("WZD_OPTION_280").'--,--PROPERTY_'.$arProperty["CML2_BASE_UNIT"].'--#--'.GetMessage("WZD_OPTION_282").'--,--PROPERTY_'.$arProperty["PROP_2033"].'--#--'.GetMessage("WZD_OPTION_286").'--,--PROPERTY_'.$arProperty["COLOR_REF2"].'--#--'.GetMessage("WZD_OPTION_288").'--,--PROPERTY_'.$arProperty["PROP_159"].'--#--'.GetMessage("WZD_OPTION_290").'--,--PROPERTY_'.$arProperty["PROP_2052"].'--#--'.GetMessage("WZD_OPTION_292").'--,--PROPERTY_'.$arProperty["PROP_2053"].'--#--'.GetMessage("WZD_OPTION_296").'--,--PROPERTY_'.$arProperty["PROP_2083"].'--#--'.GetMessage("WZD_OPTION_298").'--,--PROPERTY_'.$arProperty["PROP_2026"].'--#--'.GetMessage("WZD_OPTION_302").'--,--PROPERTY_'.$arProperty["PROP_2065"].'--#--'.GetMessage("WZD_OPTION_308").'--,--PROPERTY_'.$arProperty["PROP_2054"].'--#--'.GetMessage("WZD_OPTION_310").'--,--PROPERTY_'.$arProperty["PROP_2103"].'--#--'.GetMessage("WZD_OPTION_312").'--,--PROPERTY_'.$arProperty["PROP_2017"].'--#--'.GetMessage("WZD_OPTION_316").'--;--edit5--#--'.GetMessage("WZD_OPTION_210").'--,--PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_136").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_176").'--;--edit6--#--'.GetMessage("WZD_OPTION_212").'--,--DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_138").'--,--PROPERTY_'.$arProperty["MORE_PHOTO"].'--#--'.GetMessage("WZD_OPTION_112").'--,--PROPERTY_'.$arProperty["PHOTO_GALLERY"].'--#--'.GetMessage("WZD_OPTION_320").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_132").'--;--cedit2--#--'.GetMessage("WZD_OPTION_164").'--,--PROPERTY_'.$arProperty["VIDEO_YOUTUBE"].'--#--'.GetMessage("WZD_OPTION_322").'--,--PROPERTY_'.$arProperty["INSTRUCTIONS"].'--#--'.GetMessage("WZD_OPTION_324").'--,--PROPERTY_'.$arProperty["EXPANDABLES"].'--#--'.GetMessage("WZD_OPTION_326").'--,--PROPERTY_'.$arProperty["ASSOCIATED"].'--#--'.GetMessage("WZD_OPTION_328").'--,--PROPERTY_'.$arProperty["SERVICES"].'--#--'.GetMessage("WZD_OPTION_218").'--,--PROPERTY_'.$arProperty["LINK_NEWS"].'--#--'.GetMessage("WZD_OPTION_242").'--,--PROPERTY_'.$arProperty["LINK_BLOG"].'--#--'.GetMessage("WZD_OPTION_256").'--,--PROPERTY_'.$arProperty["LINK_VACANCY"].'--#--'.GetMessage("WZD_OPTION_250").'--,--PROPERTY_'.$arProperty["LINK_STAFF"].'--#--'.GetMessage("WZD_OPTION_238").'--,--PROPERTY_'.$arProperty["LINK_SALE"].'--#--'.GetMessage("WZD_OPTION_330").'--;--edit14--#--'.GetMessage("WZD_OPTION_144").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_TITLE--#--'.GetMessage("WZD_OPTION_146").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS--#--'.GetMessage("WZD_OPTION_148").'--,--IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION--#--'.GetMessage("WZD_OPTION_150").'--,--IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE--#--'.GetMessage("WZD_OPTION_152").'--,--IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_154").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_156").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_158").'--,--IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_160").'--,--IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE--#--'.GetMessage("WZD_OPTION_162").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT--#--'.GetMessage("WZD_OPTION_156").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE--#--'.GetMessage("WZD_OPTION_158").'--,--IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME--#--'.GetMessage("WZD_OPTION_160").'--,--SEO_ADDITIONAL--#--'.GetMessage("WZD_OPTION_164").'--,--TAGS--#--'.GetMessage("WZD_OPTION_166").'--;--edit2--#--'.GetMessage("WZD_OPTION_100").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_100").'--;--cedit1--#--'.GetMessage("WZD_OPTION_332").'--,--SORT--#--'.GetMessage("WZD_OPTION_8").'--,--PROPERTY_'.$arProperty["MINIMUM_PRICE"].'--#--'.GetMessage("WZD_OPTION_334").'--,--PROPERTY_'.$arProperty["MAXIMUM_PRICE"].'--#--'.GetMessage("WZD_OPTION_336").'--,--PROPERTY_'.$arProperty["FORUM_MESSAGE_CNT"].'--#--'.GetMessage("WZD_OPTION_338").'--,--PROPERTY_'.$arProperty["vote_count"].'--#--'.GetMessage("WZD_OPTION_340").'--,--PROPERTY_'.$arProperty["rating"].'--#--'.GetMessage("WZD_OPTION_342").'--,--PROPERTY_'.$arProperty["CML2_TRAITS"].'--#--'.GetMessage("WZD_OPTION_344").'--,--PROPERTY_'.$arProperty["CML2_TAXES"].'--#--'.GetMessage("WZD_OPTION_346").'--,--PROPERTY_'.$arProperty["vote_sum"].'--#--'.GetMessage("WZD_OPTION_348").'--,--PROPERTY_'.$arProperty["FORUM_TOPIC_ID"].'--#--'.GetMessage("WZD_OPTION_350").'--,--PROPERTY_'.$arProperty["CML2_ATTRIBUTES"].'--#--'.GetMessage("WZD_OPTION_352").'--;--edit10--#--'.GetMessage("WZD_OPTION_354").'--,--CATALOG--#--'.GetMessage("WZD_OPTION_355").'--;----#--'.GetMessage("WZD_OPTION_10").'--;--',
	));
	// list user options
	CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockTYPE.".".$iblockID), array(
		'columns' => 'CATALOG_TYPE,NAME,ACTIVE,SORT,TIMESTAMP_X,ID,PREVIEW_PICTURE', 'by' => 'timestamp_x', 'order' => 'desc', 'page_size' => '20',
	));
}

if($iblockID){
	// replace macros IBLOCK_TYPE & IBLOCK_ID & IBLOCK_CODE
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_ID" => $iblockID));
	CWizardUtil::ReplaceMacrosRecursive($bitrixTemplateDir, Array("IBLOCK_CATALOG_CODE" => $iblockCODE));

	unset($_SESSION["NEXT_CATALOG_ID"]);
	$_SESSION["NEXT_CATALOG_ID"] = $iblockID;

	\Bitrix\Main\Config\Option::set("aspro.max", "CATALOG_IBLOCK_ID", $iblockID, WIZARD_SITE_ID);

	//set link iblock (services, articles, news, stock)
	function setLinkPropIBlock($arIBlockCodes=array(), $id, $siteID=WIZARD_SITE_ID){
		if(is_array($arIBlockCodes) && $arIBlockCodes){
			$arPropsID=array();
			foreach($arIBlockCodes as $code){
				$arIBlock=CIBlock::GetList(array(), array("ACTIVE"=>"Y", "SITE_ID"=>$siteID, "CODE"=>$code))->Fetch();
				if($arIBlock["ID"]){
					$arProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arIBlock["ID"], "CODE"=>"LINK_GOODS"))->Fetch();
					if($arProp["ID"]){
						$arPropsID[]=$arProp["ID"];
					}
				}
			}
			if($arPropsID){
				foreach($arPropsID as $prop_id){
					$ibp = new CIBlockProperty();
					$ibp->Update($prop_id, array("LINK_IBLOCK_ID"=>$id));
				}
			}
		}
	}

	setLinkPropIBlock(array("aspro_max_services", "aspro_max_articles", "aspro_max_news", "aspro_max_stock", "aspro_max_projects"), $iblockID);
}
?>