<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use \Bitrix\Main\Localization\Loc,
	\Aspro\Max\MarketingPopup;
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
	?>
	<div class="form marketing-popup <?=$templateName?> <?=$bgStyle ? 'marketing-popup-bg-block' : ''?>" data-classes="<?=$addClasses?>" data-ls="mw_<?=$arResult['ID']?>" <?if($bgStyle):?>style="<?=$bgStyle?>"<?endif;?>>
		<?if($arResult):?>
			<?if($bNeedWrap):?>
				<div class="marketing-popup__wrapper">
			<?endif;?>

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


			<?if( $arResult['PROPERTIES']['HIDE_TITLE']['VALUE'] != 'Y' ):?>
				<div class="marketing-popup__title font_exlg darken option-font-bold"><?=$arResult["NAME"];?></div>
			<?endif;?>

			<div class="marketing-popup__text font_sm">
				<?$obParser = new CTextParser;?>
				<?=$obParser->html_cut($arResult["PREVIEW_TEXT"], 500);?>
			</div>

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


			<?if($bNeedWrap):?>
				</div>
			<?endif;?>

			<?if($btn1 || $btn2):?>
				<div class="marketing-popup__btn">
					<?if($btn1):?>
						<?MarketingPopup::showNoindex($link1);?>
						<<?=$link1 ? 'a' : 'span'?> class="btn <?=($arResult['PROPERTIES']['BTN1_CLASS']["VALUE_XML_ID"] ?: "btn-default");?>" <?=$href1?> data-marketing-action="btn1">
							<?=$btn1;?>
						</<?=$link1 ? 'a' : 'span'?>>
						<?MarketingPopup::showNoindex($link1, true);?>
					<?endif;?>
					<?if($btn2):?>
						<?MarketingPopup::showNoindex($link2);?>
						<<?=$link2 ? 'a' : 'span'?>  class="btn <?=($arResult['PROPERTIES']['BTN2_CLASS']["VALUE_XML_ID"] ?: "btn-default white");?>" <?=$href2?> data-marketing-action="btn2">
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