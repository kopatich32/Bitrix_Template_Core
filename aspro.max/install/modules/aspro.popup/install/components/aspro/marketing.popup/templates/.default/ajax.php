<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?use \Bitrix\Main\Localization\Loc;?>
<?$frame = $this->createFrame()->begin('');?>
	<?$bPicture = ($arResult['ITEM']['PREVIEW_PICTURE'])?>
	<?$bBtn1 = ($arResult['ITEM']['PROPERTY_BTN1_TEXT_VALUE'] && $arResult['ITEM']['PROPERTY_BTN1_LINK_VALUE'])?>
	<?$bBtn2 = ($arResult['ITEM']['PROPERTY_BTN2_TEXT_VALUE'] && $arResult['ITEM']['PROPERTY_BTN2_LINK_VALUE'])?>
	<div class="form marketing-popup popup-text-info<?=($bPicture ? " popup-text-info--has-img" : "");?> <?=$templateName?>">
		<?if($arResult['ITEM']):?>
			<?if($arResult['ITEM']):?>
				<?if($arResult['ITEM']['PREVIEW_PICTURE']):?>
					<div class="popup-text-info__picture"><div style="background-image: url(<?=CFile::GetPath($arResult['ITEM']["PREVIEW_PICTURE"]);?>)"></div></div>
				<?endif;?>
				<div class="popup-text-info__title font_exlg darken"><?=$arResult['ITEM']["NAME"];?></div>
				<div class="popup-text-info__text font_sm">
					<?$obParser = new CTextParser;?>
					<?=$obParser->html_cut($arResult['ITEM']["PREVIEW_TEXT"], 500);?>
					<?//print_r($arResult);?>
					<?if($bBtn1 || $bBtn2):?>
						<div class="popup-text-info__btn">
							<?if($bBtn1):?>
								<a class="btn <?=($arResult['ITEM']['PROPERTY_BTN1_CLASS_INFO'] ? $arResult['ITEM']['PROPERTY_BTN1_CLASS_INFO']['XML_ID'] : "btn-default");?> btn-lg" href="<?=SITE_DIR?><?=$arResult['ITEM']["PROPERTY_BTN1_LINK_VALUE"];?>"><?=$arResult['ITEM']['PROPERTY_BTN1_TEXT_VALUE'];?></a>
							<?endif;?>
							<?if($bBtn2):?>
								<a class="btn <?=($arResult['ITEM']['PROPERTY_BTN2_CLASS_INFO'] ? $arResult['ITEM']['PROPERTY_BTN2_CLASS_INFO']['XML_ID'] : "btn-transparent-border-color");?> btn-lg" href="<?=SITE_DIR?><?=$arResult['ITEM']["PROPERTY_BTN2_LINK_VALUE"];?>"><?=$arResult['ITEM']['PROPERTY_BTN2_TEXT_VALUE'];?></a>
							<?endif;?>
						</div>
					<?endif;?>
				</div>
			<?endif;?>
		<?else:?>
			ERROR
		<?endif;?>
	</div>

<?$frame->end();?>