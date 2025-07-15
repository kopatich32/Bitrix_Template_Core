<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
use \Bitrix\Main\Localization\Loc,
	\Aspro\Popup\MarketingPopup;
?>
<?$frame = $this->createFrame()->begin('');?>
	<?if($arResult):
		foreach ($arResult as $key => $arItem):?>
			<?
			$type = ($arItem['PROPERTY_MODAL_TYPE_ENUM_ID']);
			$type = $type ? CIBlockPropertyEnum::GetByID( $type )['XML_ID'] : 'MAIN';
			$bNoOverlay = $arItem["PROPERTY_NO_OVERLAY_VALUE"] === "Y";
			$bRequiredConfirm = $arItem["PROPERTY_REQUIRED_CONFIRM_VALUE"] === "Y";
			$strMediaQuery = MarketingPopup::getMediaQuery($arItem);
			$strStopActions = MarketingPopup::getStopActions($arItem);
			$bShowAlways = $bRequiredConfirm || !empty($strStopActions);
			$jsActionProp = $arItem['PROPERTY_USER_ACTION_ENUM_ID'] ?? '';
			$jsAction = $jsActionProp ? CIBlockPropertyEnum::GetByID( $jsActionProp )['XML_ID'] : '';
			
			?>
			<div 
				class="dyn_mp_jqm" 
				data-name="dyn_mp_jqm" 
				data-event="mp_jqm" 
				data-param-form_id="marketing"
				<?if($jsAction):?>
					data-js-action = "<?=$jsAction?>"
				<?endif;?>
				data-param-type="marketing" 
				data-param-id="<?=$arItem['ID']?>" 
				data-param-iblock_id="<?=$arItem['IBLOCK_ID']?>"
				data-param-delay="<?=$arItem['PROPERTY_DELAY_SHOW_VALUE']?>"
				data-no-mobile="Y"
				data-ls="mw_<?=$arItem['ID']?>"
				data-ls_timeout="<?=$arItem['PROPERTY_LS_TIMEOUT_VALUE']?>"
				data-no-overlay="<?=$type == 'TEXT' || $bNoOverlay ? 'Y' : ''?>"
				data-param-template="<?=$type?>"
				data-media-query="<?=htmlspecialcharsbx($strMediaQuery)?>"
				<?if($bRequiredConfirm):?>
					data-jqm-overlay-class="jqmOverlay--dark"
				<?endif;?>
				<?if($bShowAlways):?>
					data-show_always="Y"
				<?endif;?>
				data-stop-actions="<?=!$bRequiredConfirm ? htmlspecialcharsbx($strStopActions) : ''?>"
			></div>
		<?endforeach;?>
		<script src="<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($templateFolder . '/js/jqmInit.js')?>"></script>
		<script src="<?=$GLOBALS['APPLICATION']->oAsset->getFullAssetPath($componentPath . '/js/script.js')?>"></script>
	<?endif;?>
<?$frame->end();?>