<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);

$APPLICATION->IncludeComponent(
	"aspro:basket.file.max",
	$arResult['TEMPLATE'],
	array(
		"ACTION" => $arResult['TEMPLATE'],
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"FILE_NAME" => "cart",
		"REGION_ID" => $arResult['REGION_ID'],
		"SAVE_TO_DIR" => "/upload/",
		"SHOW_ERRORS" => "N",
		"SITE_ID" => $arResult['SITE_ID'],
		"USER_ID" => $arResult['USER_ID'],
		"USE_CUSTOM_MESSAGES" => $arParams['USE_CUSTOM_MESSAGES'],
	),
	false,
	array("HIDE_ICONS" => "Y", "ACTIVE_COMPONENT" => "Y")
);