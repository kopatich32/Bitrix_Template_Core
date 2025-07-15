<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Aspro\Max\Preset;

global $USER;
global $APPLICATION;
global $DB;

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 36000000;

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
	$arParams["IBLOCK_TYPE"] = "news";
$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);


$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if(strlen($arParams["SORT_BY1"])<=0)
	$arParams["SORT_BY1"] = "ACTIVE_FROM";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]))
	$arParams["SORT_ORDER1"]="DESC";

if(strlen($arParams["SORT_BY2"])<=0)
	$arParams["SORT_BY2"] = "SORT";
if(!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"]))
	$arParams["SORT_ORDER2"]="ASC";

if(!isset($arParams["FILTER_NAME"]) || strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arParams["FILTER_NAME"] = "arRegionLink";
	$arrFilter = array();
}

$arrFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
if(!is_array($arrFilter))
	$arrFilter = array();

$arParams["CHECK_DATES"] = $arParams["CHECK_DATES"]!="N";
$arParams["BANNER_TYPE_THEME_CHILD"] = $arParams["BANNER_TYPE_THEME_CHILD"] ?? '';
$arParams["BANNER_TYPE_THEME_CHILD2"] = $arParams["BANNER_TYPE_THEME_CHILD2"] ?? '';
$arParams["PREVIEW_TRUNCATE_LEN"] = $arParams["PREVIEW_TRUNCATE_LEN"] ?? 0;
$arParams["SET_TITLE"] = $arParams["SET_TITLE"] ?? 'N';

if(!isset($arParams["FIELD_CODE"]) || !is_array($arParams["FIELD_CODE"]))
	$arParams["FIELD_CODE"] = array();
foreach($arParams["FIELD_CODE"] as $key=>$val)
	if(!$val)
		unset($arParams["FIELD_CODE"][$key]);

if(!is_array($arParams["PROPERTY_CODE"]))
	$arParams["PROPERTY_CODE"] = array();
foreach($arParams["PROPERTY_CODE"] as $key=>$val)
	if($val==="")
		unset($arParams["PROPERTY_CODE"][$key]);

$arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]);
if($arParams["NEWS_COUNT"]<=0)
	$arParams["NEWS_COUNT"] = 20;

$arParams["USE_PERMISSIONS"] = isset($arParams["USE_PERMISSIONS"]) && $arParams["USE_PERMISSIONS"]=="Y";
if(!isset($arParams["GROUP_PERMISSIONS"]) || !is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

global $arRegion, $arTheme;
if($arRegion)
	$arrFilter["REGION_BANNER_ID"] = $arRegion["ID"];

$strCurrentTemplate = $arTheme["INDEX_TYPE"]["SUB_PARAMS"][$arTheme["INDEX_TYPE"]["VALUE"]]["BIG_BANNER_INDEX"]["TEMPLATE"]["VALUE"];
$bShowAdditionalBanners = ($arTheme["INDEX_TYPE"]["SUB_PARAMS"][$arTheme["INDEX_TYPE"]["VALUE"]]["BIG_BANNER_INDEX"]["TEMPLATE"]["LIST"][$strCurrentTemplate]["ADDITIONAL_OPTIONS"]["BOTTOM_BANNERS"]["VALUE"] == "Y");
if($bShowAdditionalBanners && $arParams['BANNER_TYPE_THEME'] == 'TOP')
	$arParams["BANNER_TYPE_THEME_CHILD2"] = "ADDITIONAL_BANNERS";

$arParams["MORE_HEIGHT"] = $arTheme["INDEX_TYPE"]["SUB_PARAMS"][$arTheme["INDEX_TYPE"]["VALUE"]]["BIG_BANNER_INDEX"]["TEMPLATE"]["LIST"][$strCurrentTemplate]["ADDITIONAL_OPTIONS"]["MORE_HEIGHT"]["VALUE"] == "Y";

$APPLICATION->SetAdditionalCss("/bitrix/components/aspro/com.banners.max/styles.css");

$BIGBANNER_MOBILE = \Bitrix\Main\Config\Option::get('aspro.max', 'BIGBANNER_MOBILE', '1', SITE_ID);
$arParams['CURRENT_BANNER_INDEX'] = Preset::getCurrentPresetBannerIndex(SITE_ID);
$arParams['REVIEWS_VIEW'] = $arTheme["REVIEWS_VIEW"]["VALUE"];
$arParams['SLIDER_VIEW_MOBILE'] = $arTheme["MOBILE_BIG_BANNER_INDEX"]["VALUE"];
$arParams['SIDE_SLIDER_VIEW_MOBILE'] = $arTheme["MOBILE_SIDE_BANNER_INDEX"]["VALUE"];
$arParams["CONVERT_CURRENCY"] = CMax::GetFrontParametrValue('CONVERT_CURRENCY');
$arParams["CURRENCY_ID"] = CMax::GetFrontParametrValue('CURRENCY_ID');

if($this->StartResultCache(false, array(($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $bUSER_HAVE_ACCESS, $arrFilter, $BIGBANNER_MOBILE)))
{
	if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	if(is_numeric($arParams["IBLOCK_ID"]))
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"ID" => $arParams["IBLOCK_ID"],
		));
	}
	else
	{
		$rsIBlock = CIBlock::GetList(array(), array(
			"ACTIVE" => "Y",
			"CODE" => $arParams["IBLOCK_ID"],
			"SITE_ID" => SITE_ID,
		));
	}
	if($arResult = $rsIBlock->GetNext())
	{
		$arResult["USER_HAVE_ACCESS"] = $bUSER_HAVE_ACCESS;
		//SELECT
		$arSelect = array_merge($arParams["FIELD_CODE"], array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"NAME",
			"ACTIVE_FROM",			
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"PREVIEW_TEXT",
			"SORT",
		));
		$bGetProperty = count($arParams["PROPERTY_CODE"])>0;
		if($bGetProperty)
			$arSelect[]="PROPERTY_*";
		//WHERE
		$bannerTypeID=0;
		$arBannersCode = $arCode = array();

		if($arParams["BANNER_TYPE_THEME"])
		{
			$arCode[] = $arParams["BANNER_TYPE_THEME"];
			if($arParams["BANNER_TYPE_THEME_CHILD"])
				$arCode[] = $arParams["BANNER_TYPE_THEME_CHILD"];
			if($arParams["BANNER_TYPE_THEME_CHILD2"])
				$arCode[] = $arParams["BANNER_TYPE_THEME_CHILD2"];

			$rsItem = CIBlockElement::GetList(Array("SORT"=>"ASC", "ID" => "ASC"),  Array("IBLOCK_ID" => $arParams["TYPE_BANNERS_IBLOCK_ID"], "CODE" => $arCode), false, false, Array("IBLOCK_ID", "ID", "CODE"));
			while($arItem = $rsItem->Fetch())
				$arBannersCode[$arItem["CODE"]] = $arItem["ID"];
		}
		if(!$arBannersCode)
		{
			$this->abortResultCache();
			return;
		}

		$arFilter = array (
			"IBLOCK_ID" => $arResult["ID"],
			"IBLOCK_LID" => SITE_ID,
			"ACTIVE" => "Y",
		);
		
		if($arParams["CHECK_DATES"])
			$arFilter["ACTIVE_DATE"] = "Y";


		//ORDER BY
		$arSort = array(
			$arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"],
			$arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"],
		);
		if(!array_key_exists("ID", $arSort))
			$arSort["ID"] = "DESC";

		$obParser = new CTextParser;
		$arResult["ITEMS"] = array();
		$arResult["ELEMENTS"] = array();
		$arResult['HAS_SLIDE_BANNERS'] = $arResult['HAS_CHILD_BANNERS'] = false;
		$j = 1;

		foreach($arBannersCode as $key => $arTypeBaner)
		{
			$arFilter2 = array();
			$count = ($j == 1 ? '' : $j);
			++$j;

			if($arParams["BANNER_TYPE_THEME_CHILD"] && $key == $arParams["BANNER_TYPE_THEME_CHILD"] && $arParams["SECTION_ID"])
				$arFilter2["SECTION_ID"] = $arParams["SECTION_ID"];

			if($arParams['SECTION_ITEM_CODE'])
				$arFilter2["SECTION_CODE"] = $arParams["SECTION_ITEM_CODE"];

			if( $arParams["NEWS_COUNT".$count] ) {
				$rsElement = CIBlockElement::GetList($arSort, array_merge($arFilter, $arrFilter, $arFilter2, array("PROPERTY_TYPE_BANNERS.CODE" => $key)), false, array("nTopCount" => $arParams["NEWS_COUNT".$count]), $arSelect);
			}
			while($obElement = $rsElement->GetNextElement())
			{
				$arItem = $obElement->GetFields();
				$arItem["TYPE_BANNER"] = $key;

				$arButtons = CIBlock::GetPanelButtons(
					$arItem["IBLOCK_ID"],
					$arItem["ID"],
					0,
					array("SECTION_BUTTONS"=>false, "SESSID"=>false)
				);
				$arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];
				
				$arItem["FORMAT_NAME"]=str_replace("&lt;br/&gt;", "", $arItem["NAME"]);

				if($arParams["PREVIEW_TRUNCATE_LEN"] > 0)
					$arItem["PREVIEW_TEXT"] = $obParser->html_cut($arItem["PREVIEW_TEXT"], $arParams["PREVIEW_TRUNCATE_LEN"]);

				if(strlen($arItem["ACTIVE_FROM"])>0)
					$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($arItem["ACTIVE_FROM"], CSite::GetDateFormat()));
				else
					$arItem["DISPLAY_ACTIVE_FROM"] = "";

				if(array_key_exists("DETAIL_PICTURE", $arItem))
					$arItem["DETAIL_PICTURE"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
					
				if(array_key_exists("PREVIEW_PICTURE", $arItem))
					$arItem["PREVIEW_PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);

				$arItem["FIELDS"] = array();
				foreach($arParams["FIELD_CODE"] as $code)
					if(array_key_exists($code, $arItem))
						$arItem["FIELDS"][$code] = $arItem[$code];

				if($bGetProperty)
					$arItem["PROPERTIES"] = $obElement->GetProperties();

				$arItem["DISPLAY_PROPERTIES"]=array();
				foreach($arParams["PROPERTY_CODE"] as $pid)
				{
					$prop = &$arItem["PROPERTIES"][$pid];
					if(
						(is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
						|| (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0)
					)
					{
						$arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "news_out");
					}
				}						
				$arResult["ITEMS"][] = $arItem;
				$arResult["ELEMENTS"][] = $arItem["ID"];
			}
		}
		if($arRegion)
		{
			$arParams["USE_REGION"] = "Y";
			if ($arRegion['LIST_STORES']) {
				$arParams['STORES'] = array_values($arRegion['LIST_STORES']);
			}

			if ($arRegion['LIST_PRICES']) {
				$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
			}
			$arParams["PRICE_CODE_IDS"] = $arRegion["LIST_PRICES"];

		}

		if(!$arParams['PRICE_CODE'])
		{
			if(\Bitrix\Main\Loader::includeModule('catalog'))
			{
				$arPrice = CCatalogGroup::GetBaseGroup();
				$arParams['PRICE_CODE'] = [$arPrice['NAME']];
				$arParams["PRICE_CODE_IDS"] = $arPrice["ID"];
			}
		}

		$arResult['BIGBANNER_MOBILE'] = $BIGBANNER_MOBILE;
		
		$this->SetResultCacheKeys(array(
			"ID",
			"IBLOCK_TYPE_ID",
			"LIST_PAGE_URL",			
			"NAME",
			"SECTION",
			"ELEMENTS",
			"BIGBANNER_MOBILE",
		));		

		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("T_NEWS_NEWS_NA"));
		@define("ERROR_404", "Y");
		if($arParams["SET_STATUS_404"]==="Y")
			CHTTP::SetStatus("404 Not Found");
	}
}

if(isset($arResult["ID"]))
{
	$arTitleOptions = null;
	if($USER->IsAuthorized())
	{
		if(
			$APPLICATION->GetShowIncludeAreas()
			|| (
				isset($GLOBALS["INTRANET_TOOLBAR"])
				&& is_object($GLOBALS["INTRANET_TOOLBAR"])
				&& (
					!isset($arParams["INTRANET_TOOLBAR"])
					|| $arParams["INTRANET_TOOLBAR"] !== "N" 
				)
			)
			|| $arParams["SET_TITLE"]
		)
		{
			if(CModule::IncludeModule("iblock"))
			{
				$arButtons = CIBlock::GetPanelButtons(
					$arResult["ID"],
					0,
					$arParams["PARENT_SECTION"] ?? 0,
					array("SECTION_BUTTONS"=>false)
				);

				if($APPLICATION->GetShowIncludeAreas())
					$this->AddIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $arButtons));

				if(
					is_array($arButtons["intranet"])
					&& isset($GLOBALS["INTRANET_TOOLBAR"])
					&& is_object($GLOBALS["INTRANET_TOOLBAR"])
					&& (
						!isset($arParams["INTRANET_TOOLBAR"])
						|| $arParams["INTRANET_TOOLBAR"] !== "N"
					)
				)
				{
					$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
					foreach($arButtons["intranet"] as $arButton)
						$GLOBALS["INTRANET_TOOLBAR"]->AddButton($arButton);
				}

				if($arParams["SET_TITLE"])
				{
					$arTitleOptions = array(
						'ADMIN_EDIT_LINK' => $arButtons["submenu"]["edit_iblock"]["ACTION"],
						'PUBLIC_EDIT_LINK' => "",
						'COMPONENT_NAME' => $this->GetName(),
					);
				}
			}
		}
	}

	// $this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);
	
	return $arResult["ELEMENTS"];
}?>