<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
$arParams['FILTER_PROP_CODE'] = (isset($arParams['FILTER_PROP_CODE']) && $arParams['FILTER_PROP_CODE'] ? $arParams['FILTER_PROP_CODE'] : 'FAVORIT_ITEM');
$arParams['SALE_STICKER'] = (isset($arParams['SALE_STICKER']) && $arParams['SALE_STICKER'] ? $arParams['SALE_STICKER'] : 'SALE_TEXT');
$arParams['STIKERS_PROP'] = (isset($arParams['STIKERS_PROP']) && $arParams['STIKERS_PROP'] ? $arParams['STIKERS_PROP'] : 'HIT');
$arParams["CONVERT_CURRENCY"] = CMax::GetFrontParametrValue('CONVERT_CURRENCY');
$arParams["CURRENCY_ID"] = CMax::GetFrontParametrValue('CURRENCY_ID');

$GLOBALS[$arParams['FILTER_NAME']]['PROPERTY_'.$arParams['FILTER_PROP_CODE'].'_VALUE'] = 'Y';
?>