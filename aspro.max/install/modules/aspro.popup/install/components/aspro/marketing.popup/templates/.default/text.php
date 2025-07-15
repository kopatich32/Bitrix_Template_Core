<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use \Bitrix\Main\Localization\Loc,
	\Aspro\Popup\MarketingPopup;
?>
<?$frame = $this->createFrame()->begin('');?>
	<?
	$bPicture = ($arResult['PREVIEW_PICTURE']);

	$type = 'TEXT';

	$position = $arResult['PROPERTIES']['POSITION']["VALUE_XML_ID"] ?? 'BOTTOM_LEFT';

	$btn1 = strtoupper(strip_tags($arResult['PROPERTIES']['BTN1_TEXT']['VALUE']));
	$btn2 = strtoupper(strip_tags($arResult['PROPERTIES']['BTN2_TEXT']['VALUE']));

	$link1 = MarketingPopup::getItemLink($arResult['PROPERTIES']["BTN1_LINK"]["VALUE"]);
	$link2 = MarketingPopup::getItemLink($arResult['PROPERTIES']["BTN2_LINK"]["VALUE"]);

	$link1Target = !empty($arResult['PROPERTIES']['BTN1_TARGET']["VALUE_XML_ID"]) ? 'target="'.$arResult['PROPERTIES']['BTN1_TARGET']["VALUE_XML_ID"].'"' : '';
	$link2Target = !empty($arResult['PROPERTIES']['BTN2_TARGET']["VALUE_XML_ID"]) ? 'target="'.$arResult['PROPERTIES']['BTN2_TARGET']["VALUE_XML_ID"].'"' : '';
	
	$href1 = $link1 ? 'href="'.$link1.'" rel="nofollow" '.$link1Target : '';
	$href2 = $link2 ? 'href="'.$link2.'" rel="nofollow" '.$link2Target : '';
	
	$bNeedWrap = $position == 'BOTTOM_CENTER' || $position == 'TOP_CENTER';
	$addClasses = "{$type} {$position}";
	if($bNeedWrap){
		$addClasses .= ' TEXT--wide maxwidth-theme-popup';
	}

	$discountTimer = $arResult['PROPERTIES']['SALE_TIMER']['VALUE'] ?? '';
	$templateData["USE_COUNTDOWN"] = false;

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
	?>
	<div class="form marketing-popup p p--24 <?=$templateName?> <?=$bgStyle ? 'marketing-popup-bg-block' : ''?>" data-classes="<?=$addClasses?>" data-ls="mw_<?=$arResult['ID']?>" <?if($bgStyle):?>style="<?=$bgStyle?>"<?endif;?>>
		<?if($arResult):?>
			<?if($bNeedWrap):?>
				<div class="marketing-popup__wrapper">
			<?endif;?>

			<?if( $arResult['PROPERTIES']['HIDE_TITLE']['VALUE'] != 'Y' ):?>
				<div class="marketing-popup__title mb mb--12 color_222 font_18 switcher-title"><?=$arResult["NAME"];?></div>
			<?endif;?>

			<div class="marketing-popup__text font_15">
				<?$obParser = new CTextParser;?>
				<?=$obParser->html_cut($arResult["PREVIEW_TEXT"], 500);?>
			</div>

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


			<?if($bNeedWrap):?>
				</div>
			<?endif;?>

			<?if($btn1 || $btn2):?>
				<div class="marketing-popup__btn mt mt--24">
					<?if($btn1):?>
						<?MarketingPopup::showNoindex($link1);?>
						<<?=$link1 ? 'a' : 'span'?> class="btn <?=($arResult['PROPERTIES']['BTN1_CLASS']["VALUE_XML_ID"] ?: "btn-default");?>" <?=$href1?> data-marketing-action="btn1">
							<?=$btn1;?>
						</<?=$link1 ? 'a' : 'span'?>>
						<?MarketingPopup::showNoindex($link1, true);?>
					<?endif;?>
					<?if($btn2):?>
						<?MarketingPopup::showNoindex($link2);?>
						<<?=$link2 ? 'a' : 'span'?>  class="btn <?=($arResult['PROPERTIES']['BTN2_CLASS']["VALUE_XML_ID"] ?: "btn-secondary-black");?>" <?=$href2?> data-marketing-action="btn2">
							<?=$btn2;?>
						</<?=$link2 ? 'a' : 'span'?>>
						<?MarketingPopup::showNoindex($link2, true);?>
					<?endif;?>
				</div>
			<?endif;?>
		<?else:?>
			ERROR
		<?endif;?>
	</div>

<?$frame->end();?>