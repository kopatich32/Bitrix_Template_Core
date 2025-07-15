<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!isset($arParams['CACHE_TIME']))
	$arParams['CACHE_TIME'] = 36000000;
if(\Bitrix\Main\Loader::includeModule('aspro.max'))
{
	if($this->getTemplateName() == 'mail')
	{
		// include CMainPage
		require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/mainpage.php");
		// get site_id by host
		$obMainPage = new CMainPage();
		$site_id = $obMainPage->GetSiteByHost();
		if(!$site_id || $site_id == "ru")
		    $site_id = "s1";
		$arFrontParametrs = CMax::GetFrontParametrsValues($site_id);
	}
	else
		$arFrontParametrs = CMax::GetFrontParametrsValues(SITE_ID);
	$arResult['SOCIAL_VK'] = $arFrontParametrs['SOCIAL_VK'];
	$arResult['SOCIAL_FACEBOOK'] = $arFrontParametrs['SOCIAL_FACEBOOK'];
	$arResult['SOCIAL_TWITTER'] = $arFrontParametrs['SOCIAL_TWITTER'];
	$arResult['SOCIAL_INSTAGRAM'] = $arFrontParametrs['SOCIAL_INSTAGRAM'];
	$arResult['SOCIAL_TELEGRAM'] = $arFrontParametrs['SOCIAL_TELEGRAM'];
	$arResult['SOCIAL_ODNOKLASSNIKI'] = $arFrontParametrs['SOCIAL_ODNOKLASSNIKI'];
	//$arResult['SOCIAL_GOOGLEPLUS'] = $arFrontParametrs['SOCIAL_GOOGLEPLUS'];
	$arResult['SOCIAL_YOUTUBE'] = $arFrontParametrs['SOCIAL_YOUTUBE'];
	$arResult['SOCIAL_MAIL'] = $arFrontParametrs['SOCIAL_MAIL'];
	$arResult['SOCIAL_VIBER'] = $arFrontParametrs['SOCIAL_VIBER'];
	$arResult['SOCIAL_VIBER_CUSTOM_DESKTOP'] = $arFrontParametrs['SOCIAL_VIBER_CUSTOM_DESKTOP'];
	$arResult['SOCIAL_VIBER_CUSTOM_MOBILE'] = $arFrontParametrs['SOCIAL_VIBER_CUSTOM_MOBILE'];
	$arResult['SOCIAL_WHATS'] = $arFrontParametrs['SOCIAL_WHATS'];
	$arResult['SOCIAL_WHATS_TEXT'] = $arFrontParametrs['SOCIAL_WHATS_TEXT'];
	$arResult['SOCIAL_WHATS_CUSTOM'] = $arFrontParametrs['SOCIAL_WHATS_CUSTOM'];
	$arResult['SOCIAL_ZEN'] = $arFrontParametrs['SOCIAL_ZEN'];
	$arResult['SOCIAL_PINTEREST'] = $arFrontParametrs['SOCIAL_PINTEREST'];
	$arResult['SOCIAL_SNAPCHAT'] = $arFrontParametrs['SOCIAL_SNAPCHAT'];
	$arResult['SOCIAL_TIKTOK'] = $arFrontParametrs['SOCIAL_TIKTOK'];
	$arResult['SOCIAL_LINKEDIN'] = $arFrontParametrs['SOCIAL_LINKEDIN'];
	$arResult['SOCIAL_RUTUBE'] = $arFrontParametrs['SOCIAL_RUTUBE'];

	if($this->StartResultCache(false, array(($arParams['CACHE_GROUPS'] === 'N'? false : $USER->GetGroups()), $arResult))){
		$this->SetResultCacheKeys(array(
			'SOCIAL_VK',
			'SOCIAL_FACEBOOK',
			'SOCIAL_TWITTER',
			'SOCIAL_INSTAGRAM',
			'SOCIAL_ODNOKLASSNIKI',
			//'SOCIAL_GOOGLEPLUS',
			'SOCIAL_YOUTUBE',
			'SOCIAL_MAIL',
			'SOCIAL_VIBER',
			'SOCIAL_VIBER_CUSTOM_DESKTOP',
			'SOCIAL_VIBER_CUSTOM_MOBILE',
			'SOCIAL_WHATS',
			'SOCIAL_WHATS_TEXT',
			'SOCIAL_ZEN',
			'SOCIAL_TIKTOK',
			'SOCIAL_PINTEREST',
			'SOCIAL_SNAPCHAT',
			'SOCIAL_RUTUBE',
		));

		$this->IncludeComponentTemplate();
	}
}
?>
