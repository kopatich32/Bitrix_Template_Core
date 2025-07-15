<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $arTheme, $APPLICATION;

$bError = \Bitrix\Main\Loader::includeModule('aspro.max') == false;

if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
	throw new \Exception('Error include solution constants');
}

/*Prepare params*/
$arParams["ELEMENT_COUNT"] = (isset($arParams["ELEMENT_COUNT"]) && $arParams["ELEMENT_COUNT"] ? $arParams["ELEMENT_COUNT"] : 5);
$arParams['FILTER_NAME'] = (isset($arParams['FILTER_NAME']) && $arParams['FILTER_NAME'] ? $arParams['FILTER_NAME'] : 'arFilterWrapper');

$arParams["COMPONENT_NAME"] = $componentName;
$arParams["TEMPLATE"] = $componentTemplate;

$arParams["IS_AJAX"] = CMax::checkAjaxRequest();
$arParams["USE_FAST_VIEW"] = CMax::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL');

$arParams['DISPLAY_WISH_BUTTONS'] = CMax::GetFrontParametrValue('CATALOG_DELAY');
/**/

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

/*fix global filter in ajax*/
if($_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']] ?? ''){
	$GLOBALS[$arParams['FILTER_NAME']] = $_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']];
}

if($arParams['FILTER_NAME'] == 'arrPopularSections'){
	$GLOBALS[$arParams['FILTER_NAME']] = array('UF_POPULAR' => 1);
}
/**/

if(!$bError){
	if($arParams["IS_AJAX"] != 'Y'){
		$this->IncludeComponentTemplate();
	}
	else{
		// $APPLICATION->ShowCss();
		// $APPLICATION->ShowHeadScripts();
		if (!isset($arParams["SHOW_AJAX_HEAD"]) || $arParams["SHOW_AJAX_HEAD"] !== 'N') {
			$APPLICATION->ShowAjaxHead();
		}

		// not load core.js in CJSCore:Init()
		CJSCore::markExtensionLoaded('core');

		global $arRegion;
		$arRegion = CMaxRegionality::getCurrentRegion();

		if($arRegion){
			if($arRegion['LIST_PRICES']){
				if(reset($arRegion['LIST_PRICES']) != 'component'){
					$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
				}
			}

			if($arRegion['LIST_STORES']){
				if(reset($arRegion['LIST_STORES']) != 'component'){
					$arParams['STORES'] = $arRegion['LIST_STORES'];
				}
			}

			if($arParams['FILTER_NAME'] == 'arRegionality' && isset($arParams['STORES']) && $arParams['STORES']){
				if(CMax::GetFrontParametrValue('STORES_SOURCE') != 'IBLOCK'){
					$GLOBALS[$arParams['FILTER_NAME']] = array('ID' => $arRegion['LIST_STORES']);
				}
				else{
					$GLOBALS[$arParams['FILTER_NAME']] = array('PROPERTY_STORE_ID' => $arRegion['LIST_STORES']);
				}
			}
		}

		$this->IncludeComponentTemplate('ajax');
	}
}
?>