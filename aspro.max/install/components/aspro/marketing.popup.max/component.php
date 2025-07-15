<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
?>
<?global $arTheme, $APPLICATION;?>
<?
$bError = false;

/*Prepare params*/
$arParams["ELEMENT_COUNT"] = (isset($arParams["ELEMENT_COUNT"]) && $arParams["ELEMENT_COUNT"] ? $arParams["ELEMENT_COUNT"] : 5);
$arParams['FILTER_NAME'] = (isset($arParams['FILTER_NAME']) && $arParams['FILTER_NAME'] ? $arParams['FILTER_NAME'] : 'arFilterWrapper');
/**/

$arParams["COMPONENT_NAME"] = $componentName;
$arParams["TEMPLATE"] = $componentTemplate;

$arParams["IS_AJAX"] = (CMax::checkAjaxRequest() && $arParams['SHOW_FORM'] == 'Y');

$context=\Bitrix\Main\Application::getInstance()->getContext();
$request=$context->getRequest();

/*fix global filter in ajax*/
if(isset($_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']]) && $_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']])
	$GLOBALS[$arParams['FILTER_NAME']] = $_SESSION['ASPRO_FILTER'][$arParams['FILTER_NAME']];

/**/

$isWebform = strtolower($request['template']) === "webform";

if($arParams['IS_AJAX'] == 'Y')
{
	$APPLICATION->ShowAjaxHead();
}

if(!$bError && \Bitrix\Main\Loader::includeModule('aspro.max'))
{
	if($arParams["IS_AJAX"] != 'Y' && !$isWebform)
	{
		$ob = new Aspro\Max\MarketingPopup();
		$rules = $ob->GetRules();
		if($rules && (isset($rules['ALL']) && $rules['ALL']))
		{
			$arResult = $rules['ALL'];
		}
		$this->IncludeComponentTemplate();
	}
	else
	{
		if(!isset($arParams['CACHE_TIME'])){
			$arParams['CACHE_TIME'] = 36000000;
		}
		$elementId = (int)$request['id'];
		$iblockId = (int)$request['iblock_id'];

		if(
			$this->startResultCache(
				$arParams['CACHE_TIME'],
				[$elementId, $iblockId]
			)
		){
			if($iblockId && $elementId)
			{
				$arResult = [];

				$arFilter = array(
					"IBLOCK_ID" => $iblockId,
					"ACTIVE"=>"Y",
					"ID" => $elementId
				);
				$arSelect = array(
					"ID",
					"IBLOCK_ID",
					"NAME",
					"PREVIEW_TEXT",
					"PREVIEW_PICTURE",
					"DETAIL_PICTURE",
				);

				$dbRes = \CIBlockElement::GetList(
					array(),
					array(
						'ID' => $elementId,
						'IBLOCK_ID' => $iblockId,
						'ACTIVE_DATE' => 'Y',
					),
					false,
					false,
					$arSelect
				);
				
				while($obElement = $dbRes->GetNextElement()){
					$arResult = $obElement->GetFields();
					$arResult['PROPERTIES'] = $obElement->GetProperties();
				}

			}
			$this->endResultCache();
		}
		
		$this->IncludeComponentTemplate( strtolower($request['template']) );
	}
}
?>