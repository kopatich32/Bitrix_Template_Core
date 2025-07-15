<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use \Bitrix\Main\Localization\Loc,
	\Aspro\Max\MarketingPopup;
?>
<?$frame = $this->createFrame()->begin('');?>
	<?
	$bPicture = ($arResult['PREVIEW_PICTURE']);
	$type = 'IMAGE';
	$bNoOverlay = $arResult['PROPERTIES']["NO_OVERLAY"]["VALUE"] === "Y";

	$position = $arResult['PROPERTIES']['POSITION']["VALUE_XML_ID"] ?? 'CENTER_CENTER';

	$mainLink = MarketingPopup::getItemLink($arResult['PROPERTIES']['MAIN_LINK']['VALUE']);
	$mainLinkTarget = !empty($arResult['PROPERTIES']['MAIN_TARGET']["VALUE_XML_ID"]) ? 'target="'.$arResult['PROPERTIES']['MAIN_TARGET']["VALUE_XML_ID"].'"' : '';
	
	$addClasses = "{$type} {$position} light-close-btn";
	if($bNoOverlay){
		$addClasses .= ' mp-no-overlay';
	}
	?>
	<div class="form marketing-popup marketing-popup--only-image popup-only-image <?=$templateName?>" data-classes="<?=$addClasses?>" data-ls="mw_<?=$arResult['ID']?>">
		<?if($arResult && $bPicture):?>
			<?if($mainLink):?>
				<a href=<?=$mainLink?> <?=$mainLinkTarget?> class="popup-only-image__link" data-marketing-action="img-link">
			<?endif;?>
				<img src="<?=CFile::GetPath($arResult["PREVIEW_PICTURE"]);?>" data-src="" class="popup-only-image__picture">
			<?if($mainLink):?>
				</a>
			<?endif;?>
		<?else:?>
			ERROR
		<?endif;?>
	</div>

<?$frame->end();?>