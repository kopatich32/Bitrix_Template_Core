<?
use Bitrix\Main\Localization\Loc,
	CMax as Solution;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(false);
Loc::loadMessages(__FILE__);

$bUseCustomMessages = $arParams['USE_CUSTOM_MESSAGES'] === 'Y';
$arMess = array();
foreach(
	array(
		'TITLE',
		'URL_COPY_HINT',
		'URL_COPIED_HINT',
		'URL_COPY_ERROR_HINT',
		'SHARE_SOCIALS_TITLE',
		'QR_CODE_HINT',
	) as $code
){
	if(
		$bUseCustomMessages &&
		isset($arParams['MESS_'.$code]) &&
		strlen($arParams['MESS_'.$code])
	){
		$arMess[$code] = $arParams['MESS_'.$code];
	}
	else{
		$arMess[$code] = Loc::getMessage('BSN_T_'.$code);
	}
}
?>
<?/* hide main block while template css not loaded */?>
<div id="basket-share-new-<?=$arResult['RAND']?>" class="basket-share-new form <?=($arResult['ERRORS'] ? 'haserror' : '')?>" style="height:0;">
	<div class="basket-share-new-title">
		<h2><?=$arMess['TITLE']?></h2>
	</div>
	<div class="basket-share-new-error">
		<div class="basket-share-new-error-icon"><?=Solution::showIconSvg('fail colored', $this->{'__folder'}.'/images/svg/fail.svg')?></div>
		<div class="basket-share-new-error-text">
			<?if($arResult['ERRORS']):?>
				<?=implode('<br />', $arResult['ERRORS'])?>
			<?endif;?>
		</div>
	</div>
	<?if(!$arResult['ERRORS']):?>
		<?$GLOBALS['APPLICATION']->AddHeadScript('https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.min.js');?>
		<?$fieldId = 'BASKET_SHARE_URL_'.$arResult['RAND'];?>
		<div class="form_body">
			<div class="form-control basket-share-new-url">
				<div class="btn btn-default btn-lg btn-wide basket-share-new-copy-url" data-clipboard-text="<?=$arResult['URL']?>">
					<span class="basket-share-new-url-icon">
						<?=Solution::showSpriteIconSvg($this->{'__folder'}.'/images/svg/icons.svg#copy', 'copy', ['WIDTH' => 20,'HEIGHT' => 20]);?>
						<?=Solution::showSpriteIconSvg($this->{'__folder'}.'/images/svg/icons.svg#copied', 'copied hidden', ['WIDTH' => 20,'HEIGHT' => 20]);?>
					</span><span class="basket-share-new-url-text"><?=$arMess['URL_COPY_HINT']?></span>
				</div>
			</div>

			<?if ($arParams['SHOW_SHARE_SOCIALS'] === 'Y' && $arParams['SHARE_SOCIALS']):?>
				<div class="social_block basket-share-new-socials">
					<div class="soc-avt">
						<div class="title"><?=$arMess['SHARE_SOCIALS_TITLE']?></div>
						<div class="row clearfix">
							<script type="text/javascript" src="//yastatic.net/share2/share.js" async="async" data-charset="utf-8"></script>
							<div class="ya-share2 yashare-auto-init hover-block__item-wrapper" data-services="<?=strtolower(implode(',', $arParams['SHARE_SOCIALS']))?>" data-url="<?=$arResult['URL']?>"><div class="basket-share-new-socials-preloader loadings"></div></div>
							<div style="clear:both;"></div>
						</div>
					</div>
				</div>
			<?endif;?>

			<?if ($arParams['SHOW_QRCODE'] === 'Y' && $arResult['QR_CODE']):?>
				<div class="qrcode_block basket-share-new-qrcode">
					<?=$arResult['QR_CODE']?>
					<span><?=$arMess['QR_CODE_HINT']?></span>
				</div>
			<?endif;?>
		</div>
	<?endif;?>
	<script>
	BX.message({
		BSN_T_URL_COPY_HINT: '<?=$arMess['URL_COPY_HINT']?>',
		BSN_T_URL_COPIED_HINT: '<?=$arMess['URL_COPIED_HINT']?>',
		BSN_T_URL_COPY_ERROR_HINT: '<?=$arMess['URL_COPY_ERROR_HINT']?>',
	});

	new JCBasketShareNew('<?=$arResult['RAND']?>', <?=CUtil::PhpToJSObject($arParams, false, true)?>);
	</script>
</div>