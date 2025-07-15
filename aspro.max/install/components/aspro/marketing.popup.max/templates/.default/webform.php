<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?
use \Bitrix\Main\Localization\Loc;
$bPicture = ($arResult['PREVIEW_PICTURE']);
$type = 'WEBFORM';
$webFormId = $arResult['PROPERTIES']['LINK_WEB_FORM']['VALUE'];
$bNoOverlay = $arResult['PROPERTIES']["NO_OVERLAY"]["VALUE"] === "Y";
$position = $arResult['PROPERTIES']['POSITION']["VALUE_XML_ID"] ?? 'CENTER_CENTER';

$popupWrapperClassList = '';
if ($bPicture) {
	$popupWrapperClassList .= ' marketing-popup--has-img';
}
if ($templateName) {
	$popupWrapperClassList .= ' '.$templateName;
}

$addClasses = "{$type} {$position}";

$discountTimer = $arResult['PROPERTIES']['SALE_TIMER']['VALUE'] ?? '';
$templateData["USE_COUNTDOWN"] = false;
if($discountTimer && time() <= strtotime($discountTimer)){
	$templateData["USE_COUNTDOWN"] = true;
}

$backgroundImageSrc = $arResult['DETAIL_PICTURE'] ? CFile::GetPath($arResult["DETAIL_PICTURE"]) : '';
$backgroundColor = $arResult['PROPERTIES']['BG_COLOR']['VALUE'] ?? '';
$bgStyle = '';
if($backgroundImageSrc){
    $bgStyle.="background-image:url('".$backgroundImageSrc."');";
}
if($backgroundColor){
    $bgStyle.="background-color:".$backgroundColor.";";
}
if($bgStyle){
	$addClasses .= " light-close-btn";
}
if($bNoOverlay){
	$addClasses .= ' mp-no-overlay';
}
?>
<div class="form marketing-popup with_web_form <?=$popupWrapperClassList;?>" data-classes="<?=$addClasses?>" data-ls="mw_<?=$arResult['ID']?>">
	<?if ($arResult):?>

		<?if ($arResult['PREVIEW_PICTURE']):?>
			<div class="marketing-popup__picture">
				<div style="background-image: url(<?=CFile::GetPath($arResult["PREVIEW_PICTURE"]);?>)"></div>
			</div>
		<?endif;?>

		<?if ((int)$webFormId > 0):?>
			<div class="marketing-popup__main-block <?=$bgStyle ? 'marketing-popup-bg-block' : ''?>" <?if($bgStyle):?>style="<?=$bgStyle?>"<?endif;?>>
				<?if($templateData["USE_COUNTDOWN"]){?>
					<div class="marketing-popup__timer">
						<?
						$arDiscount = [
							"ACTIVE_TO" => $arResult['PROPERTIES']['SALE_TIMER']['VALUE']
						];
						\Aspro\Functions\CAsproMax::showDiscountCounter(0, $arDiscount, [], [], '', 'v2 grey all-info');
						?>
					</div>
				<?}?>

				<?if(!empty($arResult['PROPERTIES']['COUPON_TEXT']['VALUE'])):?>
					<div class="marketing-popup__coupon">
						<div class="coupon-block" title="<?=Loc::getMessage('COUPON_COPY')?>">
							<div class="coupon-block__start"></div>
							<div class="coupon-block__body">
								<div class="coupon-block__text"><?=$arResult['PROPERTIES']['COUPON_TEXT']['VALUE']?></div>
								<?if(!empty($arResult['PROPERTIES']['COUPON_TEXT']['DESCRIPTION'])):?>
									<div class="coupon-block__description"><?=$arResult['PROPERTIES']['COUPON_TEXT']['DESCRIPTION']?></div>
								<?endif;?>
							</div>
							<div class="coupon-block__end"></div>
						</div>
					</div>
				<?endif;?>

				<?
				$APPLICATION->IncludeComponent(
					"bitrix:form.result.new",
					"popup",
					array(
						"AJAX_MODE" => "Y",
						"SEF_MODE" => "N",
						"WEB_FORM_ID" => $webFormId,
						"START_PAGE" => "new",
						"SHOW_LIST_PAGE" => "N",
						"SHOW_EDIT_PAGE" => "N",
						"SHOW_VIEW_PAGE" => "N",
						"SUCCESS_URL" => "",
						"SHOW_ANSWER_VALUE" => "N",
						"SHOW_ADDITIONAL" => "N",
						"SHOW_STATUS" => "N",
						"EDIT_ADDITIONAL" => "N",
						"EDIT_STATUS" => "Y",
						"NOT_SHOW_FILTER" => "",
						"NOT_SHOW_TABLE" => "",
						"CHAIN_ITEM_TEXT" => "",
						"CHAIN_ITEM_LINK" => "",
						"IGNORE_CUSTOM_TEMPLATE" => "N",
						"USE_EXTENDED_ERRORS" => "Y",
						"CACHE_GROUPS" => "N",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600000",
						"AJAX_OPTION_JUMP" => "N",
						"AJAX_OPTION_STYLE" => "Y",
						"AJAX_OPTION_HISTORY" => "N",
						"SHOW_LICENCE" => CMax::GetFrontParametrValue('SHOW_LICENCE'),
						"HIDDEN_CAPTCHA" => CMax::GetFrontParametrValue('HIDDEN_CAPTCHA'),
						"VARIABLE_ALIASES" => array(
							"action" => "action"
						),
						"IGNORE_AJAX_HEAD" => "Y",
						"HIDE_FORM_TITLE" => $arResult['PROPERTIES']['HIDE_TITLE']['VALUE'],
					)
				);
				?>
			</div>
		<?endif;?>
	<?else:?>
		ERROR
	<?endif;?>
</div>