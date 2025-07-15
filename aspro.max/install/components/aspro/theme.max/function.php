<?function ShowOptions($optionCode, $arOption, $arParentOption = array()){
	$isRow = (isset($arOption['IS_ROW']) && $arOption['IS_ROW'] == 'Y');
	$isPreview = (isset($arOption['PREVIEW']) && $arOption['PREVIEW']);
	if($arOption['TYPE'] == 'checkbox'):?>
		<?$isChecked = ($arOption['VALUE'] == 'Y');?>
		<?if($isRow):?>
			<div class="<?=(isset($arOption['ROW_CLASS']) && $arOption['ROW_CLASS'] ? $arOption['ROW_CLASS'] : '');?>">
				<div class="link-item animation-boxs <?=(isset($arOption['POSITION_BLOCK']) && $arOption['POSITION_BLOCK'] ? $arOption['POSITION_BLOCK'] : '');?> <?=(!$isChecked ? 'disabled' : '');?>">
		<?endif;?>
			<?ob_start();?>
				<?if((!isset($arOption['HIDE_TITLE']) || $arOption['HIDE_TITLE'] != 'Y') && (isset($arOption['TITLE']) && $arOption['TITLE'])):?><span><?=$arOption['TITLE']?></span><?endif;?>
			<?$title = ob_get_contents();
			ob_end_clean();?>

			<?ob_start();?>
				<input type="checkbox" id="<?=$optionCode?>" <?=((isset($arOption['SMALL_TOGGLE']) && $arOption['SMALL_TOGGLE']) ? "data-height=22" : "");?> <?=((isset($arOption['SMALL2_TOGGLE']) && $arOption['SMALL2_TOGGLE']) ? "data-height=16" : "");?> class="custom-switch" name="<?=$optionCode?>" value="<?=$arOption['VALUE']?>" <?=($isChecked ? "checked" : "");?> />
				<div class="on-off-switch<?=((isset($arOption['SMALL_TOGGLE']) && $arOption['SMALL_TOGGLE']) ? ' on-off-switch--22' : '')?><?=((isset($arOption['SMALL2_TOGGLE']) && $arOption['SMALL2_TOGGLE']) ? ' on-off-switch--16' : '')?>">
					<div class="on-off-switch-track"></div>
					<div class="on-off-switch-thumb"></div>
				</div>
			<?$input = ob_get_contents();
			ob_end_clean();?>
			<?if(isset($arOption['IMG']) && $arOption['IMG']):?>
				<?=$title;?>
				<div class="img"><img class="<?=($arOption["COLORED_IMG"] ? 'colored_theme_bg' : '')?>" data-src="" src="<?=$arOption['IMG'];?>" alt="<?=$arOption['TITLE']?>" title="<?=$arOption['TITLE']?>"/></div>
				<div class="input"><?=$input;?></div>
			<?elseif(isset($arOption['GROUP']) && $arOption['GROUP']):?>
				<span class="inner-table-block"><?=$title;?></span>
				<span class="inner-table-block"><?=$input;?></span>
			<?else:?>
				<?if(isset($arOption['SHOW_TITLE'])):?>
					<div class="<?=$optionCode?> imgs">
						<div class="titles">
							<?=$title;?>
						</div>
						<div>
				<?endif;?>
				<?=$input;?>
				<?if(isset($arOption['SHOW_TITLE'])):?>
						</div>
					</div>
				<?endif;?>
			<?endif;?>
		<?if($isRow):?>
				</div>
			</div>
		<?endif;?>
	<?elseif($arOption['TYPE'] == 'selectbox' || $arOption['TYPE'] == 'multiselectbox'):?>
		<?if($arOption['SELECT_TYPE'] == 'SMALL_SELECT'):?>
			<div class="switcher-select">
				<?if($arOption['TITLE']):?>
					<div class="style-switcher__label style-switcher__label--font_11 switcher-select__title"><?=$arOption['TITLE']?></div>
				<?endif;?>
				<?if($arOption['LIST']):?>
					<div class="switcher-select__popup">
						<?
						$counter = 1;
						foreach($arOption['LIST'] as $listItemKey => $listItem) {
							$listItemValue = $listItemKey;
							$listItemTitle = $listItem;

							if( is_array($listItem) ) {
								$listItemTitle = $listItem['TITLE'];
							}

							if($arOption['VALUE'] == $listItemValue) {
								$currentListItem = $listItemTitle;
								$bCurrent = true;
							} else {
								$bCurrent = false;
							}
							?>

							<div class="switcher-select__popup-item style-switcher__title--xs <?=$counter == count($arOption['LIST']) ? 'switcher-select__popup-item--last' : ''?> <?=$bCurrent ? 'switcher-select__popup-item--current' : ''?>" data-value="<?=$listItemValue?>" data-title="<?=$listItemTitle?>">
								<?=$listItemTitle?>
							</div>

							<?
						$counter++;
						}
						if( !$currentListItem ) {
							$currentListItem = reset($arOption['LIST']);
							$currentListItem = $currentListItem['TITLE'] ? $currentListItem['TITLE'] : $currentListItem;
						}
						?>
					</div>
					<div class="switcher-select__current style-switcher__title style-switcher__title--xs"><?=$currentListItem;?><?=\CMax::showSpriteIconSvg(SITE_TEMPLATE_PATH."/images/svg/header_icons_srite.svg#Triangle_down", "svg-inline-down  switcher-select__icon", ['WIDTH' => 5,'HEIGHT' => 3])?></div>
					<input type="hidden" id="<?=$optionCode?>" class="hidden switcher-select__input" name="<?=$optionCode?>" value="<?=$arOption['VALUE']?>" data-index_type="<?=$key;?>" data-index_block="<?=$key2;?>" data-dynamic="Y">
				<?endif;?>
			</div>
		<?else:?>
			<input type="hidden" id="<?=$optionCode?>" name="<?=$optionCode?>" value="<?=$arOption['VALUE']?>" />
			<?if(isset($arOption['GROUPS']) && $arOption['GROUPS'] == 'Y'):?>
				<?
				$arGroups = array();
				foreach($arOption['LIST'] as $variantCode => $arVariant)
				{
					if(isset($arVariant['HIDE']) && $arVariant['HIDE'] == 'Y') continue;
					$group = ((isset($arVariant['GROUP']) && $arVariant['GROUP']) ? $arVariant['GROUP'] : GetMessage('NO_GROUP'));
					$arGroups[$group]['LIST'][$variantCode] = array(
						'TITLE' => ((isset($arVariant['VALUE']) && $arVariant['VALUE']) ? $arVariant['VALUE'] : $arVariant['TITLE']),
						'CURRENT' => ((isset($arVariant['CURRENT']) && $arVariant['CURRENT']) ? $arVariant['CURRENT'] : 'N')
					);
				}
				if($arGroups)
				{
					foreach($arGroups as $key => $arGroup)
					{?>
						<div class="group">
							<div class="title"><?=$key;?></div>
							<div class="values">
								<div class="inner-values">
									<?foreach($arGroup['LIST'] as $variantCode => $arVariant):?>
										<span data-option-id="<?=$optionCode?>" data-option-value="<?=$variantCode?>" class="link-item animation-boxs <?=$arVariant['CURRENT'] == 'Y' ? 'current' : ''?>">
											<?if(isset($arVariant['IMG']) && $arVariant['IMG']):?>
												<span><img class="<?=($arVariant["COLORED_IMG"] ? 'colored_theme_bg' : '')?>" data-src="" src="<?=$arVariant['IMG'];?>" alt="<?=$arVariant['TITLE']?>" title="<?=$arVariant['TITLE']?>"/></span>
											<?endif;?>
											<?if(!isset($arVariant['HIDE_TITLE']) || $arVariant['HIDE_TITLE'] != 'Y'):?><span><?=$arVariant['TITLE']?></span><?endif;?>
										</span>
									<?endforeach;?>
								</div>
							</div>
						</div>
					<?}
				}?>
			<?else:?>
				<?if($isRow):?>
					<div class="rows">
				<?endif;?>
				<?foreach($arOption['LIST'] as $variantCode => $arVariant):
					if(isset($arVariant['HIDE']) && $arVariant['HIDE'] == 'Y') continue;
					if(isset($arVariant['DISABLED']) && $arVariant['DISABLED'] == 'Y') continue;?>
					<?if($isRow):?>
						<div class="<?=(isset($arVariant['ROW_CLASS']) && $arVariant['ROW_CLASS'] ? $arVariant['ROW_CLASS'] : '');?>">
					<?endif;?>
					<div <?=($isPreview && (isset($arOption['PREVIEW']['SCROLL_BLOCK']) && $arOption['PREVIEW']['SCROLL_BLOCK']) ? "data-option-type='".$arOption['PREVIEW']['SCROLL_BLOCK']."' 222" : "");?> <?=($isPreview && isset($arOption['PREVIEW']['URL']) ? "data-option-url='".SITE_DIR.$arOption['PREVIEW']['URL']."'" : "");?> data-option-id="<?=$optionCode?>" data-option-value="<?=$variantCode?>" <?=$arOption['TYPE'] == 'multiselectbox' ? 'data-type="multi"' : ''?> class="link-item animation-boxs <?=(isset($arVariant['POSITION_BLOCK']) && $arVariant['POSITION_BLOCK'] ? $arVariant['POSITION_BLOCK'] : '');?> <?=$arVariant['CURRENT'] == 'Y' ? 'current' : ''?>">
						<?ob_start();?>
							<?if((!isset($arVariant['HIDE_TITLE']) || $arVariant['HIDE_TITLE'] != 'Y') && (isset($arVariant['TITLE']) && $arVariant['TITLE'])):?><span><?=$arVariant['TITLE']?></span><?endif;?>
						<?$title = ob_get_contents();
						ob_end_clean();?>

						<?ob_start();?>
							<span><img class="<?=($arVariant["COLORED_IMG"] ? 'colored_theme_bg' : '')?>" data-src="" src="<?=$arVariant['IMG'];?>" alt="<?=$arVariant['TITLE']?>" title="<?=$arVariant['TITLE']?>"/></span>
						<?$img = ob_get_contents();
						ob_end_clean();?>

						<?if(isset($arVariant['IMG']) && $arVariant['IMG']):?>
							<?if(isset($arVariant['POSITION_TITLE']) && $arVariant['POSITION_TITLE']):?>
								<?if($arVariant['POSITION_TITLE'] == 'left'):?>
									<span class="inner-table-block" <?=((isset($arVariant['TITLE_WIDTH']) && $arVariant['TITLE_WIDTH']) ? "style='width:".$arVariant['TITLE_WIDTH']."'" : "");?>><?=$title;?></span>
									<span class="inner-table-block"><?=$img;?></span>
								<?endif;?>
							<?else:?>
								<span class="title"><?=$title;?></span>
								<?=$img;?>
							<?endif;?>
						<?else:?>
							<?=$title;?>
						<?endif;?>

					<?if(!isset($arVariant['IN_BLOCK'])):?>
					</div>
					<?endif;?>
					<?if(isset($arVariant['ADDITIONAL_OPTIONS']) && $arVariant['ADDITIONAL_OPTIONS']):?>
						<div class="subs">
							<?foreach($arVariant['ADDITIONAL_OPTIONS'] as $key => $arSubOption):?>
								<div class="sub-item">
									<?if(strpos($optionCode, '_TEMPLATE') !== false):?>
										<?=ShowOptions(str_replace('_TEMPLATE', '_', $optionCode).$key.'_'.$variantCode, $arSubOption)?>
									<?else:?>
										<?=ShowOptions($key.'_'.$variantCode, $arSubOption)?>
									<?endif;?>
								</div>
							<?endforeach;?>
						</div>
					<?endif;?>

					<?if(isset($arVariant['IN_BLOCK'])):?>
					</div>
					<?endif;?>

					<?if($isRow):?>
						</div>
					<?endif;?>
				<?endforeach;?>
				<?if($isRow):?>
					</div>
				<?endif;?>
			<?endif;?>
		<?endif;?>
	<?elseif($arOption['TYPE'] == 'text'):?>
		<input type="text" class="form-control" id="<?=$optionCode?>" <?=((isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : '');?> name="<?=$optionCode?>" value="<?=$arOption['VALUE']?>" />
	<?elseif($arOption['TYPE'] == 'textarea'):?>
		<?// text here?>
	<?endif;?>
<?}?>

<?function ShowOptionsTitle($optionCode, $arOption){?>
	<?if(!isset($arOption['HIDE_TITLE']) || $arOption['HIDE_TITLE'] != 'Y'):?>
		<div class="title <?=((isset($arOption['TOP_BORDER']) && $arOption['TOP_BORDER'] == 'Y') ? 'with-border' : '');?>"><?=$arOption['TITLE'];?><?=((isset($arOption['HINT']) && $arOption['HINT']) ? "<span class='tooltip-link' data-placement='top' data-trigger='click' data-toggle='tooltip' data-original-title='".$arOption['HINT']."'>?</span>" : "");?></div>
	<?endif;?>
<?}?>