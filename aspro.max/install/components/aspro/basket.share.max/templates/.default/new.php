<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);

$APPLICATION->IncludeComponent(
	"aspro:basket.share.new.max",
	".default",
	array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"DETAIL_URL_TEMPLATE" => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['detail'],
		"SET_PAGE_TITLE" => $arParams['NEW_SET_PAGE_TITLE'],
		"SITE_ID" => $arParams['NEW_SITE_ID'],
		"SHOW_SHARE_SOCIALS" => $arParams['NEW_SHOW_SHARE_SOCIALS'],
		"SHARE_SOCIALS" => $arParams['NEW_SHARE_SOCIALS'],
		"SHOW_QRCODE" => $arParams['NEW_SHOW_QRCODE'],
		"USER_ID" => $arParams['NEW_USER_ID'],
		"USE_CUSTOM_MESSAGES" => $arParams['NEW_USE_CUSTOM_MESSAGES'],
		"MESS_TITLE" => $arParams['NEW_MESS_TITLE'],
		"MESS_URL_COPY_HINT" => $arParams['NEW_MESS_URL_COPY_HINT'],
		"MESS_URL_COPIED_HINT" => $arParams['NEW_MESS_URL_COPIED_HINT'],
		"MESS_URL_COPY_ERROR_HINT" => $arParams['NEW_MESS_URL_COPY_ERROR_HINT'],
		"MESS_SHARE_SOCIALS_TITLE" => $arParams['NEW_MESS_SHARE_SOCIALS_TITLE'],
		"MESS_QR_CODE_HINT" => $arParams['NEW_MESS_QR_CODE_HINT'],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);

