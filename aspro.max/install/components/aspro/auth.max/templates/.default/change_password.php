<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();?>
<?\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);?>
<?$APPLICATION->AddChainItem(GetMessage("TITLE"));?>
<?$APPLICATION->SetTitle(GetMessage("TITLE"));?>
<?global $USER, $APPLICATION;
if( !$USER->IsAuthorized() ){?>
	<?
		$arParams = [
			"AUTH_URL" => $arParams["SEF_FOLDER"],
			"URL" => $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["change"],
		];

		if(isset($_SESSION['arAuthResult'])){
			$arParams['AUTH_RESULT'] = $APPLICATION->arAuthResult = $_SESSION['arAuthResult'];
			unset($_SESSION['arAuthResult']);
		}
	?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:system.auth.changepasswd",
		"main",
		$arParams,
		false
	);?>
<?}else{
	LocalRedirect( $arParams["PERSONAL"] );
}?>