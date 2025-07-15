<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arExtensions = ['ui-card', 'ui-card.ratio', 'video'];

if (file_exists($_SERVER['DOCUMENT_ROOT'].$templateData['TEMPLATE_FOLDER'].'/types/js/'.$arParams['VIDEO_SOURCE'].'.js')) {
	$APPLICATION->oAsset->addJs($templateData['TEMPLATE_FOLDER'].'/types/js/'.$arParams['VIDEO_SOURCE'].'.js');
}

TSolution\Extensions::init($arExtensions);

TSolution::$arCssVariables['--template__images__svg__social_mainpage'] = "url('".TSolution\Utils::getPathWithTimestamp(SITE_TEMPLATE_PATH.'/images/svg/social/social_mainpage.svg')."')";