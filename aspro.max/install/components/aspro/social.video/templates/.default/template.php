<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?
if ($arResult['error']) {
	if ($GLOBALS['USER']->IsAdmin()) {
		?>
		<div class="alert alert-danger">
			<?=$arResult['error']['error_msg'];?>
		</div>
		<?
	}
	return;
}

if (empty($arResult['ITEMS'])) return;

$templateData['TEMPLATE_FOLDER'] = $this->__folder;

$arParams["SHOW_TITLE"] = $arParams['TITLE'] && $arParams['SHOW_TITLE'];

$bWide = $arParams["WIDE"] === 'Y';
$bBordered = ($arParams["BORDERED"] === 'Y');
$bMaxWidthWrap = (
	!isset($arParams['MAXWIDTH_WRAP']) ||
	(isset($arParams['MAXWIDTH_WRAP']) && $arParams['MAXWIDTH_WRAP'] !== "N")
);
$bMobileScrolledItems = (
	!isset($arParams['MOBILE_SCROLLED']) || 
	(isset($arParams['MOBILE_SCROLLED']) && $arParams['MOBILE_SCROLLED'])
);

$gridClass = ['grid-list grid-items-'.$arParams['ELEMENTS_ROW']];
$gridClass[] = TSolution\Functions::getGridClassByCount(['992', '1200'], $arParams['ELEMENTS_ROW']);
if ($bMobileScrolledItems) {
	$gridClass[] = 'mobile-scrolled mobile-scrolled--items-2 mobile-offset';
} else {
	$gridClass[] = 'grid-list--normal';
}
$gridClass = TSolution\Utils::implodeClasses($gridClass);
?>
<div class="social-video <?=$templateName?>-template type-<?=$typeBlock?>">
	<?=\TSolution\Functions::showTitleBlock([
		'PATH' => '',
		'PARAMS' => $arParams,
		'TITLE_PREFIX' => TSolution::showSpriteIconSvg($templateData['TEMPLATE_FOLDER'].'/images/icons.svg#'.$arParams['VIDEO_SOURCE'], 'mr mr--12', ['WIDTH' => 24,'HEIGHT' => 24]),
	]);?>

	<?if ($bMaxWidthWrap):?>
		<div class="maxwidth-theme<?=$bWide ? ' maxwidth-theme--no-maxwidth' : '';?>">
	<?endif;?>

			<div class="<?=$gridClass?> social-video__items">
				<?foreach ($arResult['ITEMS'] as $arItem):?>
					<?
					$attributes = [];
					if ($arItem['SRC']) {
						$attributes[] = 'data-url="'.$arItem['SRC'].'"';
					}
					if ($arItem['ID']) {
						$attributes[] = 'id="'.$arParams['VIDEO_SOURCE'].'-player-id-'.$arItem['ID'].'"';
						$attributes[] = 'data-video-id="'.$arItem['ID'].'"';
					}
					$attributes = TSolution\Utils::implodeClasses($attributes);
					?>
					<div class="social-video__item grid-list__item">
						<div class="ui-card ui-card--image-scale height-100 flexbox flex-auto color-theme-parent-all">
							<div class="social-video__image ui-card__image ui-card__image--ratio-16-9 outer-rounded-x">
								<button type="button" 
									class="btn--no-btn-appearance width-100 height-100 _<?=$arParams['VIDEO_SOURCE'];?>-video" 
									<?=$attributes;?>
								>
									<img class="ui-card__img" 
										src="<?=$arItem['IMAGE'];?>" 
										alt="<?=$arItem['TITLE'];?>" title="<?=$arItem['TITLE'];?>" 
										decoding="async" loading="lazy"
									>

									<?if ($arItem['DURATION']):?>
										<time class="video-block-duration video-block-duration--bottom-right font_13 mb mb--12 mr mr--12"><?=$arItem['DURATION'];?></time>
									<?endif;?>

									<span class="video-block video-block--cover">
										<span class="video-block__play video-block__play--transparent video-block__play--circle ml ml--12 mb mb--12"></span>
									</span>
								</button>
							</div>

							<a href="<?=$arItem['ORIGIN_SRC']?>" 
								class="social-video__info ui-card__info dark_link color-theme-target flexbox mt mt--12" 
								target="_blank"
							>
								<div class="ui-card__title switcher-title font_16 linecamp-3">
									<?=$arItem['TITLE'];?>
								</div>

								<div class="social-video__date ui-card__text color_999 font_13 pt pt--8">
									<time><?=FormatDate('d F Y', $arItem['DATE'], 'SHORT');?></time>
								</div>
							</a>
						</div>
						
					</div>
				<?endforeach;?>
			</div>

	<?if ($bMaxWidthWrap):?>
		</div>
	<?endif;?>
</div>