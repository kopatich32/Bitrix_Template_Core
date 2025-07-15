<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
	Bitrix\Main\Web\Json,
	CMaxCache as TSolutionCache,
	CMaxCondition as TSolutionCondition;

global $arTheme, $arRegion;
$arResult = $arTabs = $arSections = [];
	
if (
	!Loader::includeModule('iblock') ||
	!Loader::includeModule('aspro.max')
) {
	return;
}

if (!include_once($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/vendor/php/solution.php')) {
	throw new \Exception('Error include solution constants');
}


$context = \Bitrix\Main\Context::getCurrent();
$request = $context->getRequest();

// get current mainblock file & code
$arResult['BLOCK_FILE'] = '';
$arResult['BLOCK_CODE'] = 'catalog_tab';
foreach (array_column(debug_backtrace(), 'file') as $file) {
	if (preg_match('/([\/]include[\/]mainpage[\/]components[\/]([^\/]*)[\/].*)/', $file, $arMatch)) {
		//$arResult['BLOCK_FILE'] = '/'.ltrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $file), DIRECTORY_SEPARATOR);
		$arResult['BLOCK_FILE'] = '/'.ltrim(SITE_DIR.$arMatch[1], DIRECTORY_SEPARATOR);
		$arResult['BLOCK_CODE'] = $arMatch[2];
	}
}

$arParams['AJAX_LOAD'] = ($arParams['AJAX_LOAD'] ?? 'Y') === 'N' ? 'N' : 'Y';

// is ajax
$bAjax = TSolution::checkAjaxRequest();

// if mainblock pagination
$arParams['CHECK_REQUEST_BLOCK'] = TSolution::checkRequestBlock($arResult['BLOCK_CODE']);

if (
	$request->getQuery('BLOCK') &&
	!$arParams['CHECK_REQUEST_BLOCK']
) {
	return;
}

// if ajax
if ($bAjax) {
	// $APPLICATION->ShowCss();
	// $APPLICATION->ShowHeadScripts();
	$APPLICATION->ShowAjaxHead();
	
	// not load core.js in CJSCore:Init()
	CJSCore::markExtensionLoaded('core');
	
	// not load main.popup.bundle.js, ui.font.opensans.css
	$arParams['DISABLE_INIT_JS_IN_COMPONENT'] = 'Y';
}

// globals filter
if (
	!strlen($arParams['FILTER_NAME']) ||
	!preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams['FILTER_NAME'])
) {
	$arParams['FILTER_NAME'] = 'mainTabsFilter';
}
$arrFilter = is_array($GLOBALS[$arParams['FILTER_NAME']]) ? $GLOBALS[$arParams['FILTER_NAME']] : [];

// top section
$arTopSection = [];
if (
	$arParams['SECTION_ID'] ||
	$arParams['SECTION_CODE']
) {
	$arTopSectionFilter = [
		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
		'ACTIVE' => 'Y',
		'GLOBAL_ACTIVE' => 'Y',
		'ACTIVE_DATE' => 'Y',
	];
	if ($arParams['SECTION_ID']) {
		$arTopSectionFilter['ID'] = $arParams['SECTION_ID'];
	} elseif($arParams['SECTION_CODE']) {
		$arTopSectionFilter['CODE'] = $arParams['SECTION_CODE'];
	}

	if (
		$arTopSection = TSolutionCache::CIBLockSection_GetList(
			array(
				'SORT' => 'ASC',
				'CACHE' => array(
					'TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']),
					'MULTI' => 'N',
				)
			),
			$arTopSectionFilter,
			false,
			array('ID', 'CODE', 'DEPTH_LEVEL')
		)
	) {
		$topSectionDepthLevel = $arTopSection['DEPTH_LEVEL'];
	}
}

// elements filter
$arFilter = array('ACTIVE' => 'Y',  'IBLOCK_ID' => $arParams['IBLOCK_ID'], 'SECTION_GLOBAL_ACTIVE' => 'Y');

if ($arTopSection) {
	$arFilter['SECTION_ID'] = $arTopSection['ID'];
	$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
}

if (
	isset($arParams['CUSTOM_FILTER']) &&
	is_string($arParams['CUSTOM_FILTER'])
){
	try{
		$arTmpFilter = Json::decode(htmlspecialchars_decode($arParams['CUSTOM_FILTER']));
	}
	catch(\Exception $e){
		$arTmpFilter = array();
	}

	$cond = new TSolutionCondition();
	try{
		$arParams['CUSTOM_FILTER'] = $cond->parseCondition($arTmpFilter, array());
	}
	catch(\Exception $e){
		$arParams['CUSTOM_FILTER'] = array();
	}
}
else {
	$arParams['CUSTOM_FILTER'] = array();
}

if (
	$arParams['CUSTOM_FILTER'] &&
	is_array($arParams['CUSTOM_FILTER'])
) {
	$arFilter[] = $arParams['CUSTOM_FILTER'];
}

$arParams["COMPATIBLE_MODE"] = "Y";
$arParams["SET_SKU_TITLE"] = (TSolution::GetFrontParametrValue("CHANGE_TITLE_ITEM_LIST") == "Y" ? "Y" : "");
$arParams["SHOW_PROPS"] = (TSolution::GetFrontParametrValue("SHOW_PROPS_BLOCK") == "Y" ? "Y" : "N");
$arParams["DISPLAY_TYPE"] = "block";
$arParams["TYPE_SKU"] = "TYPE_1";
$arParams["MAX_SCU_COUNT_VIEW"] = TSolution::GetFrontParametrValue("MAX_SCU_COUNT_VIEW");
$arParams["USE_CUSTOM_RESIZE_LIST"] = TSolution::GetFrontParametrValue("USE_CUSTOM_RESIZE_LIST");
$arParams["IS_COMPACT_SLIDER"] = TSolution::GetFrontParametrValue("MOBILE_CATALOG_LIST_ELEMENTS_COMPACT") == 'Y' && TSolution::GetFrontParametrValue("MOBILE_COMPACT_LIST_ELEMENTS") == 'slider';
$arParams["USE_FAST_VIEW"] = TSolution::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL');
$arParams["DISPLAY_WISH_BUTTONS"] = TSolution::GetFrontParametrValue('CATALOG_DELAY');
$arParams["SHOW_POPUP_PRICE"] = TSolution::GetFrontParametrValue('SHOW_POPUP_PRICE');
$arParams['TYPE_VIEW_BASKET_BTN'] = TSolution::GetFrontParametrValue('TYPE_VIEW_BASKET_BTN');
$arParams['REVIEWS_VIEW'] = TSolution::GetFrontParametrValue('REVIEWS_VIEW') ==  'EXTENDED';

$arParams['OFFER_TREE_PROPS'] = $arParams['OFFER_TREE_PROPS'] ?? [];
if ($arParams['OFFER_TREE_PROPS']) {
	$keys = array_search('ARTICLE', $arParams['OFFER_TREE_PROPS']);
	if (false !== $keys) {
		unset($arParams['OFFER_TREE_PROPS'][$keys]);
	}
}

if (!in_array('DETAIL_PAGE_URL', $arParams['OFFERS_FIELD_CODE'])) {
	$arParams['OFFERS_FIELD_CODE'][] = 'DETAIL_PAGE_URL';
}

if (!in_array('NAME', $arParams['OFFERS_FIELD_CODE'])) {
	$arParams['OFFERS_FIELD_CODE'][] = 'NAME';
}

if (
	$bAjax || 
	$arParams['AJAX_LOAD'] === 'N'
) {
	$bUseModuleProps = \Bitrix\Main\Config\Option::get("iblock", "property_features_enabled", "N") === "Y";
	if ($bUseModuleProps) {
		$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
		$arParams['OFFERS_CART_PROPERTIES'] = (array)\Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y']);
	}

	if($arParams['STORES'])
	{
		foreach($arParams['STORES'] as $key => $store)
		{
			if(!$store)
				unset($arParams['STORES'][$key]);
		}
	}
	$arFilterStores = array();
	global $arRegion;
	if(CMax::GetFrontParametrValue('USE_REGIONALITY') == 'Y')
		$arParams['USE_REGION'] = 'Y';

	$arRegion = CMaxRegionality::getCurrentRegion();
	if($arRegion && $arParams['USE_REGION'] == 'Y')
	{
		if(CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y'){
			$arFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
			CMax::makeElementFilterInRegion($arFilter, $arRegion['ID']);
		}
		
		if($arRegion['LIST_PRICES'])
		{
			if(reset($arRegion['LIST_PRICES']) != 'component')
			{
				$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
				$arParams['~PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
			}
		}
		if($arRegion['LIST_STORES'])
		{
			if(reset($arRegion['LIST_STORES']) != 'component')
			{
				$arParams['STORES'] = $arRegion['LIST_STORES'];
				$arParams['~STORES'] = $arRegion['LIST_STORES'];
			}

			if($arParams["HIDE_NOT_AVAILABLE"] == "Y")
			{				
				$arRegionStoresFilter = TSolution\Filter::getAvailableByStores($arParams['STORES']);
				if($arRegionStoresFilter){
					$arFilterStores[] = $arRegionStoresFilter;
				}
			}
		}
	}
	

	if ($arParams['SHOW_TABS'] === 'N') {
		$arItemsFilter = array_merge($arFilter, $arrFilter, $arFilterStores);
		$arItems = TSolutionCache::CIBlockElement_GetList(
			[
				'CACHE' => [
					'MULTI' => 'Y',
					'TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID'])
				]
			],
			$arItemsFilter,
			false,
			['
				nTopCount' => 1,
			],
			['ID']
		);
	
		if ($arItems) {
			$arTabs[] = [
				'CODE' => 'all',
				'FILTER' => $arItemsFilter,
			];
		}
		else {
			// no elements
			return;
		}
	}
	else {
		if ($arParams['TABS_FILTER'] === 'SECTION') {
			$arItemsFilter = array_merge($arFilter, $arrFilter, $arFilterStores);
			$arItems = TSolutionCache::CIBlockElement_GetList(
				array(
					'CACHE' => array(
						'MULTI' => 'Y',
						'TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID'])
					)
				),
				$arItemsFilter,
				false,
				false,
				array('ID', 'IBLOCK_SECTION_ID')
			);
		
			if ($arItems) {
				$topSectionDepthLevel = $arTopSection ? $arTopSection['DEPTH_LEVEL'] : 0;
				$arSectionsID = [];
				
				foreach ($arItems as $arItem) {
					if ($arItem['IBLOCK_SECTION_ID']) {
						$arSectionsID = array_merge($arSectionsID, (array)$arItem['IBLOCK_SECTION_ID']);
					}
				}
		
				if ($arSectionsID) {
					$arSectionsFilter = array("IBLOCK_ID" => $arParams['IBLOCK_ID'], "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "ACTIVE_DATE" => "Y");
					$arSectionsTmp = TSolutionCache::CIBLockSection_GetList(array("SORT" => "ASC", 'CACHE' => array('TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']))), array_merge($arSectionsFilter, ['ID' => $arSectionsID]), false, array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "CODE"));
		
					if ($arSectionsTmp) {
						foreach ($arSectionsTmp as $arSection) {
							if (!empty($arSection)) {
								$arSections[] = TSolutionCache::CIBLockSection_GetList(array("SORT" => "ASC", 'CACHE' => array('TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'N')), array_merge($arSectionsFilter, array("<=LEFT_BORDER" => $arSection["LEFT_MARGIN"], ">=RIGHT_BORDER" => $arSection["RIGHT_MARGIN"], "DEPTH_LEVEL" => $topSectionDepthLevel + 1)), false, array('ID', 'NAME', 'CODE'));
							}
		
							if ($arSections) {
								foreach ($arSections as $arSectionTmp) {
									if (!$arTabs[$arSectionTmp['ID']]){
										$arTabs[$arSectionTmp['ID']]["CODE"] = $arSectionTmp['ID'];
										$arTabs[$arSectionTmp['ID']]["TITLE"] = $arSectionTmp["NAME"];
										$arTabs[$arSectionTmp['ID']]["SORT"] = $arSectionTmp["SORT"];
		
										$arTabs[$arSectionTmp['ID']]['FILTER'] = array_merge(
											$arItemsFilter,
											[
												'INCLUDE_SUBSECTIONS' => 'Y',
												'SECTION_ID' => $arSectionTmp['ID'],
											]
										);
									}
								}
							}
						}
	
						uasort($arTabs, function($a, $b) {
							return $a['SORT'] <=> $b['SORT'];
						});
					}
				}		
			}
			else {
				// no elements
				return;
			}
		}
		elseif($arParams['HIT_PROP']) {
			// get items grouped by HIT_PROP values
			$arItems = TSolutionCache::CIBlockElement_GetList(
				array(
					'CACHE' => array(
						'MULTI' => 'Y',
						'TAG' => TSolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']),
						'GROUP' => array('PROPERTY_HIT_ENUM_ID'),
					)
				),
				array_merge($arFilter, $arrFilter, $arFilterStores, ['!PROPERTY_'.$arParams['HIT_PROP'] => false]),
				false,
				false,
				array('ID', 'PROPERTY_'.$arParams['HIT_PROP'])
			);			
		
			if ($arItems) {
				// get some HIT_PROP values
				$arShowProp = [];
				$rsProp = CIBlockPropertyEnum::GetList(
					array(
						'sort' => 'asc',
						'id' => 'desc',
					),
					array(
						'ACTIVE' => 'Y',
						'IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'CODE' => $arParams['HIT_PROP'],
						'ID' => array_keys($arItems),
					)
				);
				while ($arProp = $rsProp->Fetch()) {
					$arShowProp[$arProp['EXTERNAL_ID']] = $arProp['VALUE'];
				}
			
				if ($arShowProp) {
					foreach ($arShowProp as $key => $prop) {
						$arFilterProp = array('PROPERTY_'.$arParams['HIT_PROP'].'_VALUE' => array($prop));				
						$arTabs[$key] = array(
							'CODE' => $key,
							'TITLE' => $prop,
							'FILTER' => array_merge($arFilterProp, $arFilter, $arFilterStores)
						);
					}
				} else {
					return;
				}
				
				$arParams['PROP_CODE'] = $arParams['HIT_PROP'];
			}
			else {
				// no elements
				return;
			}
		}
		else {
			return;
		}
	}

	$arResult['TABS'] = $arTabs ?: [[]];

	$this->IncludeComponentTemplate('ajax');
}
else {
	if ($arParams['AJAX_LOAD'] === 'Y') {
		if ($arResult['BLOCK_FILE']) {
			$strTemplateName = basename($arResult['BLOCK_FILE'], '.php');
			$subtype = $arResult['BLOCK_CODE'];
			$APPLICATION->SetPageProperty('CUSTOM_BLOCK_CLASS/'.$subtype.'/'.$strTemplateName, 'js-load-block loader_circle');
		}
	}

	$this->IncludeComponentTemplate();
}
