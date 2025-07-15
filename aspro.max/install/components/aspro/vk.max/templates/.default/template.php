<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */

$this->setFrameMode(true);?>
<?if($arResult['ITEMS']):?>
	<?
	$bNoMargin = ($arParams['NO_MARGIN'] == 'Y');
	$bWide = ($arParams['WIDE_BLOCK'] == 'Y');
	$bWideFirstBlock = ($arParams['WIDE_FIRST_BLOCK'] == 'Y');

	if($bWideFirstBlock)
		$bNoMargin = true;

	$col = floor(12/$arParams['LINE_ELEMENT_COUNT']);
	if($arParams['LINE_ELEMENT_COUNT'] == 5)
		$col = '20';
	elseif($arParams['LINE_ELEMENT_COUNT'] == 8)
		$col = '12-5';
	if(!$col)
		$col = 3;

	$bSmallBlocks = $arParams['LINE_ELEMENT_COUNT'] >= 6;

	$sTemplateMobile = (isset($arResult['MOBILE_TEMPLATE']) ? $arResult['MOBILE_TEMPLATE'] : '');
	$bSlider = true;//($sTemplateMobile === 'slider');
	$sGroupLink = htmlspecialcharsbx('https://vk.com/'.$arResult['GROUP']['screen_name']);
	$bSquareBlock = $arParams['VIEW_TYPE'] === 'SQUARE';
	$bShowScrollbar = $bSquareBlock;
	?>
	<div class="content_wrapper_block <?=$templateName;?> <?=$bSmallBlocks ? 'small' : ''?>">
		<div class="maxwidth-theme<?=($bWide ? ' wide' : '');?>">
			<div class="vk_wrapper<?= $arParams['VIEW_TYPE'] ? ' vk_wrapper--type-'.strtolower($arParams['VIEW_TYPE']) : '';?>">
				<?$obParser = new CTextParser;?>
				<div class="item-views front blocks ">
					<?if(!$bWide):?>
						<?if($arResult['DOP_TEXT'] && !$bWideFirstBlock):?>
							<div class="with-text-block-wrapper">
								<div class="row">
									<div class="col-md-3">
									<?$titleText = ($arResult['TITLE'] ? $arResult['TITLE'] : \Bitrix\Main\Localization\Loc::getMessage('TITLE'));?>
									<?=Aspro\Functions\CAsproMax::showTitleH($titleText);?>
										<?// intro text?>
										<?if($arParams['INCLUDE_FILE']):?>
											<div class="text_before_items font_sm">
												<?$APPLICATION->IncludeComponent(
													"bitrix:main.include",
													"",
													Array(
														"AREA_FILE_SHOW" => "file",
														"PATH" => $arResult['DOP_TEXT'],
														"EDIT_TEMPLATE" => ""
													)
												);?>
											</div>
										<?endif;?>
										<a href="<?= $sGroupLink; ?>" class="btn btn-transparent-border-color btn-sm"><?=($arResult['ALL_TITLE'] ?: \Bitrix\Main\Localization\Loc::getMessage('VK_ALL_ITEMS'));?></a>
									</div>
									<div class="col-md-9 vk_body">
						<?else:?>
							<div class="top_block">
								<?$titleText = ($arResult['TITLE'] ? $arResult['TITLE'] : \Bitrix\Main\Localization\Loc::getMessage('TITLE'));?>
								<?=Aspro\Functions\CAsproMax::showTitleH($titleText);?>
								<a class="pull-right font_upper muted" href="<?= $sGroupLink; ?>" target="_blank">
									<?=CMax::showIconSvg("resume", SITE_TEMPLATE_PATH."/images/svg/social/vk_main.svg", "", "inline", true, false);?>
									<span><?=($arResult['ALL_TITLE'] ?: \Bitrix\Main\Localization\Loc::getMessage('VK_ALL_ITEMS'));?></span>
								</a>
							</div>
						<?endif;?>
					<?endif;?>
					<div class="vk clearfix">
						<?$index = 0;?>
						<div class="vk-list items row flexbox<?=($bNoMargin ? ' margin0 rounded3' : '');?> <?=$sTemplateMobile;?><?=($bSlider ? ' mobile-slider' : '');?> <?=($bSlider && !$bWideFirstBlock ? ' swipeignore mobile-overflow mobile-margin-16 ' : '');?>">
							<?if($bWideFirstBlock):?>
								<?
									$arItem = array_shift($arResult['ITEMS']);
									$arItem['permalink'] = htmlspecialcharsbx($sGroupLink.'?w=wall'.$arItem['owner_id']."_".$arItem['id']);
									$arItem['LINK'] = '';
									if (isset($arItem['attachments']) && count($arItem['attachments'])) {
										$arItem['attachments'] = array_filter($arItem['attachments'], function($attachment){
											return in_array($attachment['type'], ['video', 'photo']);
										});

										switch ($arItem['attachments'][0]['type']) {
											case "photo":
												$maxWidth = 0;
												foreach($arItem['attachments'][0]['photo']['sizes'] as $key => $arImagePamrasSize) {
													if($arImagePamrasSize['width'] > $maxWidth){
														$maxWidth = $arImagePamrasSize['width'];
														$arItem['LINK'] = htmlspecialcharsbx($arItem['attachments'][0]['photo']['sizes'][$key]['url']);
													}
												}
												break;
											case "video":
												$maxWidth = 0;
												foreach($arItem['attachments'][0]['video']['image'] as $key => $arVideoPamrasSize) {
													if($arVideoPamrasSize['width'] > $maxWidth) {
														$maxWidth = $arVideoPamrasSize['width'];
														$arItem['LINK'] = htmlspecialcharsbx($arItem['attachments'][0]['video']['image'][$key]['url']);
													}
												}
												break;
										}
									}
									$bImage = !empty($arItem['LINK']) ?? "";
								?>
								<div class="item custom">
									<div class="item-wrapper bg-white<?= !$bSquareBlock ? ' bordered' : ''; ?>">
										<div class="image shine" style="background:url(<?=$arItem['LINK'];?>) center center/cover no-repeat;"><a href="<?=$arItem['permalink']?>" target="_blank"></a></div>
										<a class="wrap<?= $bShowScrollbar ? ' scrollblock' : ''; ?>" href="<?=$arItem['permalink']?>" target="_blank" rel="nofollow">
											<span class="wrapper">
												<span class="date font_xs">
													<span><?=FormatDate('d F Y', $arItem['date'], 'SHORT');?></span>
													<?= isset($arItem['copy_history']) ? CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/content_icons_sprite.svg#icon-repost", 'svg-repost', [
														'WIDTH' => 14,
														'HEIGHT' => 16,
													]) : ''; ?>
												</span>
												<?if($arItem['caption']):?>
													<span class="text font_sm"><?=($obParser->html_cut($arItem['caption'], $arResult['TEXT_LENGTH']));?></span>
												<?endif;?>
											</span>
										</a>
									</div>
								</div>
								<div class="custom <?=($bSlider && $bWideFirstBlock ? ' swipeignore mobile-overflow mobile-margin-16 ' : '');?>">
									<div class="item col-lg-<?=$col;?> col-sm-4 col-xs-6 col-xxs-6 _adaptive <?=($bSlider ? ' item-width-261' : '');?>">
										<div class="item-wrapper bg-white<?= !$bSquareBlock ? ' bordered' : ''; ?>">
											<div class="image shine" style="background:url(<?=$arItem['LINK'];?>) center center/cover no-repeat;"><a href="<?=$arItem['permalink']?>" target="_blank"></a></div>
											<a class="wrap<?= $bShowScrollbar ? ' scrollblock' : ''; ?>" href="<?=$arItem['permalink']?>" target="_blank" rel="nofollow">
												<span class="wrapper">
													<span class="date font_xs">
														<span><?=FormatDate('d F Y', $arItem['date'], 'SHORT');?></span>
														<?= isset($arItem['copy_history']) ? CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/content_icons_sprite.svg#icon-repost", 'svg-repost', [
															'WIDTH' => 14,
															'HEIGHT' => 16,
														]) : ''; ?>
													</span>
													<?if($arItem['caption']):?>
														<span class="text font_sm"><?=($obParser->html_cut($arItem['caption'], $arResult['TEXT_LENGTH']));?></span>
													<?endif;?>
												</span>
											</a>
										</div>
									</div>
							<?endif;?>
							<?foreach($arResult['ITEMS'] as $arItem):?>
								<?
									$arItem['permalink'] = htmlspecialcharsbx($sGroupLink.'?w=wall'.$arItem['owner_id']."_".$arItem['id']);
									$arItem['LINK'] = '';
									if (isset($arItem['attachments']) && count($arItem['attachments'])) {
										$arItem['attachments'] = array_filter($arItem['attachments'], function($attachment){
											return in_array($attachment['type'], ['video', 'photo']);
										});

										switch ($arItem['attachments'][0]['type']) {
											case "photo":
												$maxWidth = 0;
												foreach($arItem['attachments'][0]['photo']['sizes'] as $key => $arImagePamrasSize) {
													if($arImagePamrasSize['width'] > $maxWidth){
														$maxWidth = $arImagePamrasSize['width'];
														$arItem['LINK'] = htmlspecialcharsbx($arItem['attachments'][0]['photo']['sizes'][$key]['url']);
													}
												}
												break;
											case "video":
												$maxWidth = 0;
												foreach($arItem['attachments'][0]['video']['image'] as $key => $arVideoPamrasSize) {
													if($arVideoPamrasSize['width'] > $maxWidth) {
														$maxWidth = $arVideoPamrasSize['width'];
														$arItem['LINK'] = htmlspecialcharsbx($arItem['attachments'][0]['video']['image'][$key]['url']);
													}
												}
												break;
										}
									}
									$bImage = !!$arItem['LINK'];
									$bSquareHasImage = $bImage && $bSquareBlock;
									$bSquareHasText = $arItem['text'] && $bSquareBlock;

									$sTextLineClamp = !$bSquareBlock
										? ($bImage ? 'lineclamp-3' : 'lineclamp-12')
										: '';

								?>
								<div class="vk-list__wrapper<?= !$bSquareBlock ? ' vk-list__wrapper--fill-text' : ''; ?> flexbox col-lg-<?=$col;?> col-sm-4 col-xs-6 col-xxs-6<?=($bSlider ? ' item-width-261' : '');?><?= $bImage ? ' item--icon-'.$arItem['attachments'][0]['type'] : ' vk-list__wrapper--no-image'; ?>">
									<div class="vk-list__item bg-white box-shadow rounded3<?= !$bSquareBlock ? ' bordered' : ''; ?>">
										<a class="vk-list__link <?= $bSquareHasImage ? 'vk-list__link--has-image' : ''; ?> flexbox flexbox--reverse" href="<?=$arItem['permalink']?>" target="_blank" rel="nofollow">
											<? if ($bImage): ?>
												<span class="vk-list__item-image-wrapper shine" style="background-image: url(<?=$arItem['LINK'];?>);">
													<span class="vk-list__item-image-post"></span>
												</span>
											<? endif; ?>

											<span class="vk-list__item-text-wrapper <?= $bSquareHasImage ? 'vk-list__item-text-wrapper--has-image' : ''; ?> <?= $bShowScrollbar ? 'scrollblock' : ''; ?>">
												<span class="vk-list__item-text-post-wrapper">
													<span class="vk-list__item-period-wrapper flexbox flexbox--row font_xs">
														<span class="vk-list__item-period-date"><?=FormatDate('d F Y', $arItem['date'], 'SHORT');?></span>
														<? if ($arItem['copy_history']): ?>
															<span class="vk-list__item-period-icon">
																<?= CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/content_icons_sprite.svg#icon-repost", 'svg-repost', [
																	'WIDTH' => 14,
																	'HEIGHT' => 16,
																]); ?>
															</span>
														<? endif; ?>
													</span>

													<?if($arItem['text']):?>
														<span class="vk-list__item-text-post <?= $sTextLineClamp; ?> font_sm">
															<?=($obParser->html_cut($arItem['text'], $arResult['TEXT_LENGTH']));?>
														</span>
													<?endif;?>
												</span>
											</span>
										</a>
									</div>
								</div>
							<?endforeach;?>

							<?if($bWideFirstBlock):?>
								</div>
							<?endif;?>
						</div>
					</div>
					<?if(!$bWide):?>
						<?if($arResult['DOP_TEXT'] && !$bWideFirstBlock):?>
							</div></div></div>
						<?endif;?>
					<?endif;?>
				</div>
			</div>
		</div>
	</div>
<?endif;?>
