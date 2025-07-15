<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if($arParams['SHOW_VIEW_CONTENT'] === 'Y'){
	$this->SetViewTarget($arParams['CODE_VIEW_CONTENT']);
}
?>
<?if($arResult['LIST']):?>
	<?if(!function_exists('showAsproSmartSeoTags')):?>
		<?function showAsproSmartSeoTags($arParams = [
			'ITEMS' => [],
			'COUNT' => 10,
			'PARAMS' => []
		]){
			ob_start();
			$i = 0;
			$bFilled = ($arParams['PARAMS']['BG_FILLED'] == 'Y');
			$bModile = ($arParams['PARAMS']['MOBILED'] == 'Y');
			$bSlider = ($arParams['PARAMS']['VIEW_TYPE'] == 'slider' && $bModile);
			?>
			<?if($bModile):?>
				<!-- noindex -->
			<?endif;?>
			<div class="landings-list__info <?=($bModile ? 'landings-list__info--mobiled visible-xs' : 'hidden-xs')?> <?=($bSlider ? 'swipeignore' : '')?>">
				<?
				$textExpand = Loc::getMessage('SHOW_STEP_ALL');
				$textHide = Loc::getMessage('HIDE');
				$opened = 'N';
				$classOpened = '';
				$arParams['COUNT'] = (int)$arParams['COUNT'];
				$count = count($arParams['ITEMS']);

				if($bSlider){
					$arParams['COUNT'] = 0;
				}

				$bWithHidden = $bCheckItemActive = $bHiddenOK = false;
				?>
				<?foreach($arParams['ITEMS'] as $key => $arItem){
					++$i;
					$bHidden = ($i > $arParams['COUNT'] && $arParams['COUNT']);

					if($bHidden){
						$bWithHidden = true;
					}

					$url = $arItem['URL'];

					if($url){
						$arFilterQuery = \Aspro\Functions\CAsproMax::checkActiveFilterPage($arParams['PARAMS']['URL_TEMPLATES']['smart_filter']);
						$bActiveFilter = ($arFilterQuery && !in_array('clear', $arFilterQuery));

						if($arItem['SELECTED']){
							if($bActiveFilter){
								$arParams['ITEMS'][$key]['ACTIVE'] = 'Y';
								$arParams['ITEMS'][$key]['ACTIVE_URL'] = $arItem['SELECTED'] ? 'Y' : 'N';
							}
						}
					}
				}?>
				<?
				$i = $lastConditionId = 0;
				?>
				<div class="d-inline landings-list__info-wrapper from_smartseo <?=($bWithHidden ? 'last' : '');?> flexbox flexbox--row <?=($bSlider ? 'with-slider' : 'flexbox--wrap');?>">
					<?foreach($arParams['ITEMS'] as $arItem):?>
						<?if ($arParams['PARAMS']['SHOW_BY_GROUPS'] === 'Y'):?>
							<?if (
								$lastConditionId && 
								$lastConditionId != $arParams['INFO'][$arItem['TAG_ID']]['FILTER_CONDITION_ID']
							):?>
								<?if($bHidden):?>
									<div class="landings-list__item font_xs">
										<span class="landings-list__name landings-list__item--js-more colored_theme_text_with_hover <?=$classOpened?>" data-opened="<?=$opened?>" data-visible="<?=$arParams['COUNT']?>">
											<span data-opened="<?=$opened?>" data-text="<?=$textHide?>"><?=$textExpand?></span><?=CMax::showIconSvg('wish ncolor', SITE_TEMPLATE_PATH.'/images/svg/arrow_showmoretags.svg');?>
										</span>
									</div>
								<?endif;?>
								</div>
								<div class="d-inline landings-list__info-wrapper from_smartseo <?=($bWithHidden ? 'last' : '');?> flexbox flexbox--row <?=($bSlider ? 'with-slider' : 'flexbox--wrap');?>">
								<?$i = 0;?>
							<?endif;?>

							<?if(
								!$lastConditionId ||
								$lastConditionId != $arParams['INFO'][$arItem['TAG_ID']]['FILTER_CONDITION_ID']
							):?>
								<?$conditionName = $arParams['INFO'][$arItem['TAG_ID']]['FILTER_CONDITION_NAME'];?>
								<?if(strlen($conditionName)):?>
									<div class="landings-list__section-title font_xs option-font-bold darken <?=$bModile ? 'landings-list__section-title--mobiled visible-xs' : 'hidden-xs'?>"><?=$conditionName?></div>
								<?endif;?>
							<?endif;?>
							<?$lastConditionId = $arParams['INFO'][$arItem['TAG_ID']]['FILTER_CONDITION_ID'];?>
						<?endif;?>
						<?
						++$i;
						$bHidden = ($i > $arParams['COUNT'] && $arParams['COUNT']);

						$url = $arItem['URL'];

						$class = '';
						if($bHidden){
							if($arItem['ACTIVE_URL'] != 'Y'){
								$class .= 'hidden js-hidden';
							}
							else{
								if($i > $arParams['COUNT'] && $count == $i){
									$bHidden = false;
								}
							}
						}
						if($arItem['ACTIVE_URL'] == 'Y'){
							$class = 'active';
						}
						?>
						<div class="landings-list__item font_xs <?=$class?>">
							<div>
								<?if(strlen($url)):?>
									<?if($arItem['ACTIVE_URL'] == 'Y'):?>
										<span class="landings-list__name rounded3 landings-list__item--active <?=($bActiveFilter ? 'landings-list__item--reset' : '')?>"><span><?=htmlspecialcharsback($arItem['NAME'])?></span>
											<?if($arItem['ACTIVE']):?>
												<span class="landings-list__clear-filter colored_theme_bg_hovered_hover" title="<?=Loc::getMessage('RESET_LANDING')?>">
													<?=CMax::showIconSvg('delete_filter', SITE_TEMPLATE_PATH.'/images/svg/catalog/cancelfilter.svg', '', '', false, false);?>
												</span>
											<?endif;?>
										</span>
									<?else:?>
										<a class="landings-list__name<?=($bFilled ? ' landings-list__item--filled-bg box-shadow-sm' : ' landings-list__item--hover-bg')?> rounded3" href="<?=$url?>"><span><?=htmlspecialcharsback($arItem['NAME'])?></span></a>
									<?endif;?>
								<?else:?>
									<span class="landings-list__name<?=($bFilled ? ' landings-list__item--filled-bg box-shadow-sm' : ' landings-list__item--hover-bg')?> rounded3"><span><?=htmlspecialcharsback($arItem['NAME'])?></span></span>
								<?endif?>
							</div>
						</div>
					<?endforeach;?>
					<?if($bHidden):?>
						<div class="landings-list__item font_xs">
							<span class="landings-list__name landings-list__item--js-more colored_theme_text_with_hover <?=$classOpened?>" data-opened="<?=$opened?>" data-visible="<?=$arParams['COUNT']?>">
								<span data-opened="<?=$opened?>" data-text="<?=$textHide?>"><?=$textExpand?></span><?=CMax::showIconSvg('wish ncolor', SITE_TEMPLATE_PATH.'/images/svg/arrow_showmoretags.svg');?>
							</span>
						</div>
					<?endif;?>
				</div>
			</div>
			<?if($bModile):?>
				<!-- /noindex -->
			<?endif;?>
			<?$html = ob_get_clean();
			return $html;
		}
		?>
	<?endif;?>

	<div id="aspro-smartseo-tags__wrapper_<?=$arResult['UNIQUE']?>" class="landings-list landings-list--smartseo <?=$templateName?> with-<?=$arParams['VIEW_TYPE']?> aspro-smartseo-tags__wrapper">
		<?$bInFilterShow = ($arParams['VIEW_TYPE'] === 'filter')?>
		<?if($arParams['TITLE_BLOCK']):?>
			<div class="landings-list__title darken font_mlg"><?=$arParams['TITLE_BLOCK']?></div>
		<?endif;?>
		<?if($bInFilterShow):?>
			<div class="with-filter-wrapper from_smartseo">
				<div class="bx_filter_parameters_box">
					<div class="bx_filter_parameters_box_title title rounded3 box-shadow-sm colored_theme_hover_bg-block">
						<div>
							<div><?=Loc::getMessage('TAGS_TITLE_FILTER')?></div>
						</div>
						<?=CMax::showIconSvg("down colored_theme_hover_bg-el", SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_down.svg', '', '', true, false);?>
					</div>
					<div class="bx_filter_block">
		<?endif;?>
			<?=showAsproSmartSeoTags([
				'ITEMS' => $arResult['LIST'],
				'INFO' => $arResult['INFO'],
				'COUNT' => $arParams['SHOW_COUNT_MOBILE'],
				'PARAMS' => $arParams + ['MOBILED' => 'Y']
			]);?>
		<?if($bInFilterShow):?>
					</div>
				</div>
			</div>
		<?endif;?>
		<?=showAsproSmartSeoTags([
			'ITEMS' => $arResult['LIST'],
			'INFO' => $arResult['INFO'],
			'COUNT' => $arParams['SHOW_COUNT'],
			'PARAMS' => $arParams
		]);?>
	</div>
<?endif;?>
<?
if($arParams['SHOW_VIEW_CONTENT'] === 'Y'){
	$this->EndViewTarget();
}
