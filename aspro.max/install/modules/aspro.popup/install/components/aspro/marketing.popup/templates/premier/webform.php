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
			<div class="marketing-popup__main-block height-100 p p--32 <?=$bgStyle ? 'marketing-popup-bg-block' : ''?>" <?if($bgStyle):?>style="<?=$bgStyle?>"<?endif;?>>
				<?if($templateData["USE_COUNTDOWN"]){?>
					<div class="marketing-popup__timer mb mb--12">
						<?
						$discountDateTo = $arResult['PROPERTIES']['SALE_TIMER']['VALUE'];
						\Aspro\Functions\CAsproPremier::showDiscountCounter([
							'ICONS' => true,
							'DATE' => $discountDateTo,
							'ITEM' => $arResult
						]);
						?>
					</div>
				<?}?>

				<?if(!empty($arResult['PROPERTIES']['COUPON_TEXT']['VALUE'])):?>
					<div class="marketing-popup__coupon mt mt--20">
						<div class="coupon-block bordered white-bg p-inline p-inline--20 p-block p-block--12" title="<?=Loc::getMessage('COUPON_COPY')?>">
							<div class="coupon-block__text fw-500 font_16 color_222 lineclamp-3"><?=$arResult['PROPERTIES']['COUPON_TEXT']['VALUE']?></div>
							<?if(!empty($arResult['PROPERTIES']['COUPON_TEXT']['DESCRIPTION'])):?>
								<div class="coupon-block__description font_13 secondary-color mt mt--2 lineclamp-3"><?=$arResult['PROPERTIES']['COUPON_TEXT']['DESCRIPTION']?></div>
							<?endif;?>
						</div>
					</div>
				<?endif;?>
				
				<?$APPLICATION->IncludeComponent(
					"aspro:form.premier", "popup",
					Array(
						"IBLOCK_TYPE" => "aspro_premier_form",
						"IBLOCK_ID" => $webFormId,
						"AJAX_MODE" => "Y",
						"AJAX_OPTION_JUMP" => "N",
						"AJAX_OPTION_STYLE" => "N",
						"AJAX_OPTION_HISTORY" => "N",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "100000",
						"AJAX_OPTION_ADDITIONAL" => "",
						"SUCCESS_MESSAGE" => Loc::getMessage('MARKETING_POPUP_SUCCESS_MESSAGE'),
						"SEND_BUTTON_NAME" => Loc::getMessage('MARKETING_POPUP_SEND_BUTTON_NAME'),
						"SEND_BUTTON_CLASS" => "btn btn-default",
						"DISPLAY_CLOSE_BUTTON" => "Y",
						"POPUP" => "Y",
						"CLOSE_BUTTON_NAME" => Loc::getMessage('MARKETING_POPUP_CLOSE_BUTTON_NAME'),
						"CLOSE_BUTTON_CLASS" => "jqmClose btn btn-default bottom-close",
						"IGNORE_AJAX_HEAD" => "Y",
						"HIDE_FORM_TITLE" => $arResult['PROPERTIES']['HIDE_TITLE']['VALUE'],
					)
				);?>
				
			</div>
		<?endif;?>
	<?else:?>
		ERROR
	<?endif;?>
</div>