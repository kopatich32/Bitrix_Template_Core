<?
use	Bitrix\Main\Config\Option,
	CMax as Solution,
	Aspro\Max\Preset;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(false);

$customColorExist = isset($arResult['BASE_COLOR']['LIST']['CUSTOM']) && isset($arResult['BASE_COLOR_CUSTOM']);
$_COOKIE['styleSwitcherType'] = $_COOKIE['styleSwitcherType'] ?? '';
$_COOKIE['styleSwitcherSubType'] = $_COOKIE['styleSwitcherSubType'] ?? '';
$_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] = $_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] ?? '';

$bUseDemoLink = Option::get(Solution::moduleID, 'USE_DEMO_LINK', 'N', SITE_ID) === 'Y';
?>
<div class="style-switcher">
	<div class="close_block">
		<div><a href="javascript:void(0)" title="<?=GetMessage('SWITCH_CLOSE_TITLE')?>"><?=CMax::showIconSvg("close", $templateFolder."/images/svg/close.svg");?></a></div>
		<div class="closes"><?=CMax::showIconSvg("close_small", $templateFolder."/images/svg/close_small.svg");?></div>
	</div>
	<div class="top_block_switch">
		<div class="switch presets_action<?=($_COOKIE['styleSwitcherType'] === 'presets' ? ' active' : '')?>">
			<?=CMax::showSpriteIconSvg($templateFolder."/images/svg/icons_sprite.svg#prepared", "preset", ['WIDTH' => 24,'HEIGHT' => 24, 'INLINE' => 'N']);?>
			<div class="tooltip">
				<div class="wrap">
					<div class="title"><?=GetMessage('SWITCH_PRESETS_TOOLTIP_TITLE')?></div>
					<div class="text"><?=GetMessage('SWITCH_PRESETS_TOOLTIP_DESCRIPTION')?></div>
				</div>
			</div>
		</div>
		<div class="switch<?=($_COOKIE['styleSwitcherType'] === 'parametrs' ? ' active' : '')?>">
			<?=CMax::showSpriteIconSvg($templateFolder."/images/svg/icons_sprite.svg#finetune", "config", ['WIDTH' => 24,'HEIGHT' => 24, 'INLINE' => 'N']);?>
			<div class="tooltip">
				<div class="wrap">
					<div class="title"><?=GetMessage('SWITCH_PARAMETRS_TOOLTIP_TITLE')?></div>
					<div class="text"><?=GetMessage('SWITCH_PARAMETRS_TOOLTIP_DESCRIPTION')?></div>
				</div>
			</div>
		</div>
	</div>
	<div class="style-switcher-body loading_block">
		<?CMax::checkRestartBuffer(true, 'widget');?>
		<?if(CMax::checkAjaxRequest()):?>
			<script>
				arAsproOptions = Object.assign(arAsproOptions, {
					"PRESETS": <?=CUtil::PhpToJSObject(
						array(
							'VALUE' => Preset::getCurrentPreset(SITE_ID),
							'LIST' => Preset::$arPresetsList,
						)
					)?>,
					"THEMATICS": <?=CUtil::PhpToJSObject(
						array(
							'VALUE' => Preset::getCurrentThematic(SITE_ID),
							'LIST' => Preset::$arThematicsList,
						)
					)?>
				});
			</script>
			<?// widget.css?>
			<link href="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/css/widget.min.css') ? $templateFolder.'/css/widget.min.css' : $templateFolder.'/css/widget.css').'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/css/widget.css')?>" rel="stylesheet">

			<?// spectrum.css?>
			<link href="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/css/spectrum.min.css') ? SITE_TEMPLATE_PATH.'/css/spectrum.min.css' : SITE_TEMPLATE_PATH.'/css/spectrum.css')?>" rel="stylesheet">

			<div>
				
				<?$strBanner = GetMessage("THEME_BANNER");?>
				<?if($strBanner):?>
					<div class="banner-block"><?=$strBanner;?></div>
				<?endif;?>
				<div class="left-block <?=($strBanner ? 'with-banner' : '');?> scrollblock">
					<div class="section-block presets_tab <?=($_COOKIE['styleSwitcherType'] === 'presets' ? ' active' : '')?>" data-type="presets"><?=CMax::showIconSvg("smpresents", $templateFolder."/images/svg/prepared_small.svg");?><?=GetMessage("TITLE_TAB_PRESETS");?></div>
					<div class="section-block parametrs_tab <?=($_COOKIE['styleSwitcherType'] === 'parametrs' ? ' active' : '')?>" data-type="parametrs">
						<div><?=CMax::showIconSvg("smparameters", $templateFolder."/images/svg/finetune_small.svg");?><?=GetMessage("TITLE_TAB_PARAMETRS");?></div>
						<div class="subitems">
							<?$arParametrs = CMax::$arParametrsList;
							$i = 0;?>
							<?foreach($arParametrs as $blockCode => $arBlock)
							{
								if(isset($arBlock['THEME'] ) && $arBlock['THEME'] == 'Y'):?>
									<?
									$active = '';
									if($_COOKIE['styleSwitcherSubType'])
									{
										if($i == $_COOKIE['styleSwitcherSubType'])
											$active = 'active toggle_initied';
									}
									elseif(!$i)
										$active = 'active toggle_initied';?>
									<div class="subsection-block <?=$active;?>"><?=$arBlock['TITLE']?></div>
									<?$i++;?>
								<?else:?>
									<?unset($arParametrs[$blockCode]);?>
								<?endif;?>
							<?}?>
						</div>
					</div>
					<div class="section-block share_tab loading_state" data-type="share"><?=CMax::showIconSvg("share", $templateFolder."/images/svg/share.svg");?><?=GetMessage("TITLE_TAB_SHARE");?></div>
					<div class="section-block updates_tab loading_state" data-type="updates"><?=CMax::showIconSvg("updates", $templateFolder."/images/svg/updates.svg");?><?=GetMessage("TITLE_TAB_UPDATES");?></div>
					<div class="section-block demos_tab hidden" data-type="demos"><?=CMax::showIconSvg("demo_small", $templateFolder."/images/svg/demo_small.svg");?><?=GetMessage("TITLE_TAB_DEMOS");?></div>
				</div>

				<div class="right-block">
					<div class="inner-content <?=($arResult['SHOW_RESET'] ? 'with-action-block' : '');?>">
						<div class="contents presets<?=($_COOKIE['styleSwitcherType'] === 'presets' ? ' active' : '')?>">
							<div class="presets_subtabs">
								<div class="presets_subtab<?=(!$_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] ? ' active' : '')?>">
									<?
									$arThematics = Preset::$arThematicsList;
									$curThematic = Preset::getCurrentThematic(SITE_ID);
									?>
									<div class="item">
										<div class="title"><?=CMax::showIconSvg("theme", $templateFolder."/images/svg/theme.svg");?><?=GetMessage("PRESET_TOP_TEMATIK");?></div>
										<div class="desc"><?=(strlen($curThematic) ? $arThematics[$curThematic]['TITLE'] : "&mdash;");?></div>
									</div>
								</div>
								<div class="presets_subtab<?=($_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] == 1 ? ' active' : '')?>">
									<?
									$arPresets = Preset::$arPresetsList;
									$curPreset = Preset::getCurrentPreset(SITE_ID);
									if(strlen($curPreset)){
										$title = $arPresets[$curPreset]['TITLE'];
										$arPresets[$curPreset]['CURRENT'] = 'Y';
									}
									else{
										$title = '';
									}
									?>
									<div class="item">
										<div class="title"><?=CMax::showIconSvg("configuration", $templateFolder."/images/svg/configuration.svg");?><?=GetMessage("PRESET_TOP_CONFIG");?></div>
										<div class="desc"><?=($title ? $title : "&mdash;");?></div>
									</div>
								</div>
							</div>
							<div class="presets_block">
								<?//thematics?>
								<div class="options thematik <?=(!$_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] ? "active" : "")?> scrollblock">
									<div class="rows items">
										<?foreach($arThematics as $arThematic):?>
											<div class="item col-md-4 col-sm-6 col-xs-12<?=($curThematic === $arThematic['CODE'] ? ' active' : '')?>" data-code="<?=$arThematic['CODE']?>">
												<div class="inner">
													<div class="img">
														<div class="img_inner">
															<img data-src="" src="<?=$arThematic['PREVIEW_PICTURE'].'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$arThematic['PREVIEW_PICTURE'])?>" alt="<?=$arThematic['TITLE']?>" title="<?=$arThematic['TITLE']?>" class="img-responsive">
														</div>
													</div>
													<div class="title"><?=$arThematic['TITLE']?></div>
												</div>
											</div>
										<?endforeach;?>
									</div>
								</div>

								<?//configuration?>
								<div class="options conf <?=($_COOKIE['STYLE_SWITCHER_CONFIG_BLOCK'] == 1 ? "active" : "")?> scrollblock">
									<div class="rows items">
										<?foreach($arPresets as $arPreset):?>
											<?$bHidden = !strlen($curThematic) || !$arThematics[$curThematic] || !in_array($arPreset['ID'], $arThematics[$curThematic]['PRESETS']['LIST']);?>
											<div class="item col-md-6 col-sm-6 col-xs-12<?=($bHidden ? ' hidden' : '')?>">
												<div class="preset-block<?=($arPreset['CURRENT'] ? ' current' : '')?><?=($arPreset['PREVIEW_PICTURE'] ? '' : ' no_img')?>" data-id="<?=$arPreset['ID']?>">
													<?if($arPreset['PREVIEW_PICTURE']):?>
														<div class="image">
															<div class="status_btn">
																<div class="action_btn">
																	<div class="apply_conf_block"><div class="btn btn-default"><?=CMax::showIconSvg("choose", $templateFolder."/images/svg/choose.svg");?><?=GetMessage("THEME_CONFIG_APPLY");?></div></div>
																	<div class="preview_conf_block"><div class="btn btn-default white"><?=CMax::showIconSvg("fastview", $templateFolder."/images/svg/fastview.svg");?><?=GetMessage("THEME_CONFIG_FAST_VIEW");?></div></div>
																</div>
																<div class="checked_wrapper">
																	<div class="checked"><?=CMax::showIconSvg("check_configuration", $templateFolder."/images/svg/check_configuration.svg");?></div>
																</div>
															</div>
															<img data-src="" src="<?=$arPreset['PREVIEW_PICTURE'].'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$arPreset['PREVIEW_PICTURE'])?>" title="<?=$arPreset['TITLE']?>" class="img-responsive" />
														</div>
													<?endif;?>
													<div class="info">
														<div class="title"><?=$arPreset['TITLE']?></div>
														<div class="description" data-img="<?=$arPreset['DETAIL_PICTURE'].'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$arPreset['DETAIL_PICTURE'])?>"><?=$arPreset['DESCRIPTION']?></div>
													</div>
													<div class="clearfix"></div>
												</div>
											</div>
										<?endforeach;?>
									</div>
								</div>
							</div>
						</div>

						<div class="contents parametrs<?=($_COOKIE['styleSwitcherType'] === 'parametrs' ? ' active' : '')?>">
							<div class="style-switcher-parametrs__action-block action_block<?= $arResult['CAN_SAVE'] ? ' can_save' : ''; ?>">
								<div class="action_block_inner">
									<div class="style-switcher-line-block style-switcher-line-block--12">
										<div class="style-switcher-line-block__item">
											<a href="" rel="nofollow" class="dark_link fill-theme-hover sharepreset-trigger-open" title="<?=GetMessage('TITLE_TAB_SHARE')?>">
												<?=CMax::showIconSvg("share", $templateFolder."/images/svg/share.svg");?><span><?=GetMessage("TITLE_TAB_SHARE");?></span>
											</a>
										</div>
									</div>

									<div class="style-switcher-line-block style-switcher-line-block--12">
										<div class="style-switcher-line-block__item">
											<div class="header-inner reset" title="<?=GetMessage('THEME_RESET_TITLE')?>">
												<?=CMax::showIconSvg("default", $templateFolder."/images/svg/default.svg");?><?=GetMessage('THEME_RESET')?>
											</div>
										</div>

										<?if($GLOBALS['USER']->IsAdmin()):?>
											<div class="style-switcher-line-block__item style-switcher--save-btn">
												<div class="save_btn header-inner" title="<?=GetMessage("SAVE_CONFIG_TITLE")?>">
													<?=CMax::showIconSvg("save", $templateFolder."/images/svg/save.svg");?><?=GetMessage("SAVE_CONFIG")?>
												</div>
											</div>
										<?endif;?>
									</div>
								</div>
							</div>

							<div class="right-block scrollblock">
								<div class="content-body">
									<form method="POST" name="style-switcher">
										<?if($arParametrs)
										{
											$i = 0;
											foreach($arParametrs as $blockCode => $arBlock):?>
												<?
												$active = '';
												if($_COOKIE['styleSwitcherSubType'])
												{
													if($i == $_COOKIE['styleSwitcherSubType'])
														$active = 'active';
												}
												elseif(!$i)
													$active = 'active';?>
												<div class="block-item <?=$active;?> <?=$blockCode;?>">
													<?foreach($arResult as $optionCode => $arOption)
													{
														if((isset($arOption['TYPE_BLOCK']) && $arOption['TYPE_BLOCK'] == $blockCode) && (isset($arOption['THEME']) && $arOption['THEME'] == 'Y') && $optionCode !== 'BASE_COLOR_CUSTOM' && $optionCode !== 'CUSTOM_BGCOLOR_THEME' && !isset($arOption['GROUPS_EXT']) && !isset($arOption['TABS'])):?>
															<?if($optionCode == 'BGCOLOR_THEME' && $arResult['SHOW_BG_BLOCK']['VALUE'] != 'Y' || isset($arOption['TAB_GROUP_BLOCK']))
															{
																continue;
															}?>
															<div class="item <?=$optionCode;?>">
																<?if(isset($arOption['TYPE_EXT']) && $arOption['TYPE_EXT'] == 'colorpicker'):?>
																	<div class="picker_wrapper picker">
																<?endif;?>
																<?if($arOption['TYPE'] == 'checkbox' && (isset($arOption['ONE_ROW']) && $arOption['ONE_ROW'] == 'Y')):?>
																	<div class="options pull-left" data-code="<?=$optionCode?>">
																		<?=ShowOptions($optionCode, $arOption);?>
																	</div>
																	<?=ShowOptionsTitle($optionCode, $arOption);?>
																<?else:?>
																	<?=ShowOptionsTitle($optionCode, $arOption);?>
																	<?if($arOption['EXT_HINT']):?>
																		<div class="ext_hint_title dark-color"><?=$arOption['EXT_HINT']['TITLE'];?></div>
																		<?if($arOption['EXT_HINT']['TEXT']):?>
																			<div class="ext_hint_desc"><?=$arOption['EXT_HINT']['TEXT'];?></div>
																		<?endif;?>
																	<?endif;?>
																	<div class="options <?=((isset($arOption['REFRESH']) && $arOption['REFRESH'] == 'Y') ? 'refresh-block' : '');?>" data-code="<?=$optionCode?>" <?=(isset($arOption['AJAX']) ? 'data-ajax="Y"' : '')?>>
																		<?if(isset($arOption['TYPE_EXT']) && $arOption['TYPE_EXT'] == 'colorpicker'):?>
																			<input type="hidden" id="<?=$optionCode?>" name="<?=$optionCode?>" value="<?=$arOption['VALUE']?>" />
																			<?foreach($arOption['LIST'] as $colorCode => $arColor):?>
																				<?if($colorCode !== 'CUSTOM'):?>
																					<div class="base_color <?=($arColor['CURRENT'] == 'Y' ? 'current' : '')?>" data-value="<?=$colorCode?>" data-color="<?=$arColor['COLOR']?>">
																						<span class="animation-all click_block"  data-option-id="<?=$optionCode?>" data-option-value="<?=$colorCode?>" title="<?=$arColor['TITLE']?>"><span style="background-color: <?=$arColor['COLOR']?>;"></span></span>
																					</div>
																				<?endif;?>
																			<?endforeach;?>

																		<?else:?>
																			<?=ShowOptions($optionCode, $arOption);?>
																		<?endif;?>
																	</div>
																	<?if(isset($arOption['TYPE_EXT']) && $arOption['TYPE_EXT'] == 'colorpicker'):?>
																		</div>
																		<div class="custom_block picker">
																			<div class="title"><?=GetMessage("USER_CUSTOM_COLOR");?></div>
																			<div class="options">
																				<?if($customColorExist && (isset($arResult['BASE_COLOR_CUSTOM']['PARENT_PROP']) && $arResult['BASE_COLOR_CUSTOM']['PARENT_PROP'] == $optionCode)):?>
																					<?$customColor = str_replace('#', '', (strlen($arResult['BASE_COLOR_CUSTOM']['VALUE']) ? $arResult['BASE_COLOR_CUSTOM']['VALUE'] : $arResult['BASE_COLOR']['LIST'][$arResult['BASE_COLOR']['DEFAULT']]['COLOR']));?>
																					<?$arColor = $arOption['LIST']['CUSTOM'];?>
																					<div class="base_color base_color_custom <?=($arColor['CURRENT'] == 'Y' ? 'current' : '')?>" data-name="BASE_COLOR_CUSTOM" data-value="CUSTOM" data-color="#<?=$customColor?>">

																						<span class="animation-all click_block" data-option-id="<?=$optionCode?>" data-option-value="CUSTOM" title="<?=$arColor['TITLE']?>" ><span class="vals">#<?=($arColor['CURRENT'] == 'Y' ? $customColor : '')?></span><span class="bg" data-color="<?=$customColor?>" style="background-color: #<?=$customColor?>;"></span></span>
																						<input type="hidden" id="custom_picker" name="BASE_COLOR_CUSTOM" value="<?=$customColor?>" />
																					</div>
																				<?endif;?>
																				<?if($customColorExist && (isset($arResult['CUSTOM_BGCOLOR_THEME']['PARENT_PROP']) && $arResult['CUSTOM_BGCOLOR_THEME']['PARENT_PROP'] == $optionCode)):?>
																					<?$customColor = str_replace('#', '', (strlen($arResult['CUSTOM_BGCOLOR_THEME']['VALUE']) ? $arResult['CUSTOM_BGCOLOR_THEME']['VALUE'] : $arResult['CUSTOM_BGCOLOR_THEME']['LIST'][$arResult['CUSTOM_BGCOLOR_THEME']['DEFAULT']]['COLOR']));?>
																					<?$arColor = $arOption['LIST']['CUSTOM'];?>
																					<div class="base_color base_color_custom <?=($arColor['CURRENT'] == 'Y' ? 'current' : '')?>" data-name="CUSTOM_BGCOLOR_THEME" data-value="CUSTOM" data-color="#<?=$customColor?>">
																						<span class="animation-all click_block" data-option-id="<?=$optionCode?>" data-option-value="CUSTOM" title="<?=$arColor['TITLE']?>" style="border-color: #<?=$customColor?>;"><span class="vals">#<?=($arColor['CURRENT'] == 'Y' ? $customColor : '')?></span><span class="bg" style="background-color: #<?=$customColor?>;"></span></span>
																						<input type="hidden" id="custom_picker2" name="CUSTOM_BGCOLOR_THEME" value="<?=$customColor?>" />
																					</div>
																				<?endif;?>
																			</div>
																		</div>
																	<?endif;?>
																	<?if(isset($arOption['SUB_PARAMS']) && $arOption['LIST'] && (isset($arOption['REFRESH']) && $arOption['REFRESH'] == 'Y')):?>
																		<div>
																			<?foreach($arOption['LIST'] as $key => $arListOption):?>
																				<?if($arOption['SUB_PARAMS'][$key]):?>
																					<?foreach($arOption['SUB_PARAMS'][$key] as $key2 => $arSubOptions)
																					{
																						if($arSubOptions['THEME'] == 'N' || $arSubOptions['VISIBLE'] == 'N')
																							unset($arOption['SUB_PARAMS'][$key][$key2]);
																					}?>

																					<?if($arOption['SUB_PARAMS'][$key]):?>
																						<div class="sup-params options refresh-block s_<?=$key;?> <?=($key == $arOption['VALUE'] ? 'active' : '');?>">
																							<div class="block-title"><span class="dotted-block"><?=GetMessage('SUB_PARAMS')?></span></div>
																							<div class="values">
																								<?$param = "SORT_ORDER_".$optionCode."_".$key;?>
																								<?if($arResult[$param])
																								{
																									$arOrder = explode(",", $arResult[$param]);
																									$arIndexList = array_keys($arOption['SUB_PARAMS'][$key]);
																									$arNewBlocks = array_diff($arIndexList, $arOrder);
																									if($arNewBlocks) {
																										$arOrder = array_merge($arOrder, $arNewBlocks);
																									}
																									$arTmp = array();
																									foreach($arOrder as $name)
																									{
																										$arTmp[$name] = $arOption['SUB_PARAMS'][$key][$name];
																									}
																									$arOption['SUB_PARAMS'][$key] = $arTmp;
																									unset($arTmp);
																								}
																								?>
																								<?$j = 0;?>
																								<div class="inner-wrapper" data-key="<?=$key;?>">
																									<?foreach($arOption['SUB_PARAMS'][$key] as $key2 => $arSubOptions):?>
																										<?if (
																											$arSubOptions &&
																											is_array($arSubOptions)
																										):?>
																											<?$isRow = (($arSubOptions['TYPE'] == 'checkbox' && (isset($arSubOptions['ONE_ROW']) && $arSubOptions['ONE_ROW'] == 'Y')) ? true : false);?>
																											<div class="option-wrapper <?=((isset($arSubOptions['DRAG']) && $arSubOptions['DRAG'] == 'N') ? "no_drag" : "");?> <?=(($arSubOptions['VALUE'] == 'N' && $isRow) ? "disabled" : "");?>">
																												<div class="drag"><?=\CMax::showSpriteIconSvg('/bitrix/components/aspro/theme.max/templates/.default/images/svg/sprite.svg#drag', 'svg-drag')?></div>
																												<?if($isRow):?>
																													<table class="">
																														<tr>
																															<td><div class="blocks"></div></td>
																															<td><div class="blocks block-title <?=((isset($arSubOptions['TEMPLATE']) && $arSubOptions['TEMPLATE']) ? 'subtitle' : '')?>"><span><?=$arSubOptions['TITLE'];?></span></div></td>
																															<td>
																																<?if(isset($arSubOptions['FON'])):?>
																																	<div class="filter label_block sm">
																																		<input type="checkbox" id="fon<?=$key.$key2?>" name="fon<?=$key.$key2?>" value="<?=$arResult['FON_PARAMS']['fon'.$key.$key2]?>" <?=($arResult['FON_PARAMS']['fon'.$key.$key2] == 'Y' ? "checked" : "");?> data-index_type="<?=$key;?>" data-index_block="<?=$key2;?>" data-dynamic="Y">
																																		<label for="fon<?=$key.$key2?>" class="hover_color_theme"><?=\Bitrix\Main\Localization\Loc::getMessage('FON_BLOCK');?></label>
																																	</div>
																																<?endif;?>
																																<div class="blocks value">
																																	<?=ShowOptions($key.'_'.$key2, $arSubOptions, $arOption);?>
																																</div>
																															</td>
																														</tr>
																													</table>
																												<?else:?>
																													<div class="block-title"><?=$arSubOptions['TITLE'];?></div>
																													<div class="value">
																														<?=ShowOptions($key.'_'.$key2, $arSubOptions);?>
																													</div>
																												<?endif;?>
																												<?if(isset($arSubOptions['TEMPLATE']) && $arSubOptions['TEMPLATE']):?>
																													<div class="template_block">
																														<?$code = $key.'_'.$key2.'_TEMPLATE';?>
																														<div class="item <?=str_replace('_TEMPLATE', '', $code);?>" <?=(isset($_COOKIE['STYLE_SWITCHER_TEMPLATE'.$j]) && $_COOKIE['STYLE_SWITCHER_TEMPLATE'.$j] == 'Y' ? "style='display:block;'" : "");?>>
																															<div class="options" data-code="<?=$code?>">
																																<?=ShowOptions($code, $arResult['TEMPLATE_PARAMS'][$key][$code]);?>
																															</div>
																														</div>
																													</div>
																												<?endif;?>
																											</div>
																										<?endif;?>
																										<?$j++;?>
																									<?endforeach;?>
																								</div>
																							</div>
																							<input type="hidden" name="<?=$param;?>" value="<?=$arResult[$param];?>" />

																						</div>
																					<?endif;?>
																				<?endif;?>
																			<?endforeach;?>
																		</div>
																	<?endif;?>
																<?endif;?>
																<?if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) // show dependent options
																{
																	foreach($arOption['DEPENDENT_PARAMS'] as $key => $arSubOptions)
																	{
																		if((!isset($arSubOptions['CONDITIONAL_VALUE']) || ($arSubOptions['CONDITIONAL_VALUE'] && $arResult[$optionCode]['VALUE'] == $arSubOptions['CONDITIONAL_VALUE'])) && $arSubOptions['THEME'] == 'Y')
																		{?>
																			<?if($arSubOptions['TYPE'] == 'checkbox' && (isset($arSubOptions['ONE_ROW']) && $arSubOptions['ONE_ROW'] == 'Y')):?>
																				<div class="borders item">
																					<div class="options dependent pull-left" data-code="<?=$key?>">
																						<?=ShowOptions($key, $arSubOptions);?>
																					</div>
																					<?=ShowOptionsTitle($key, $arSubOptions);?>
																				</div>
																			<?else:?>
																				<?=ShowOptionsTitle($key, $arSubOptions);?>
																				<div class="options dependent" data-code="<?=$key;?>">
																					<?echo ShowOptions($key, $arSubOptions);?>
																				</div>
																			<?endif;?>
																		<?}
																	}
																}?>
															</div>
														<?elseif((isset($arOption['OPTIONS']) && $arOption['OPTIONS']) && (isset($arOption['GROUPS_EXT']) && $arOption['GROUPS_EXT'] == 'Y') && $arOption['TYPE_BLOCK'] == $blockCode && (isset($arOption['THEME']) && $arOption['THEME'] == 'Y')): // show groups options?>
															<div class="item groups">
																<?=ShowOptionsTitle($blockCode, $arOption);?>
																<div class="rows options">
																	<?foreach($arOption['OPTIONS'] as $key => $arValue):?>
																		<?echo ShowOptions($key, $arValue);?>
																	<?endforeach;?>
																</div>
															</div>
														<?elseif($optionCode === 'TABS' && $arOption[$blockCode]): // show groups options?>
															<div class="item groups-tab">
																<div class="tabs bottom-line" data-parent="<?=$blockCode?>">
																	<ul class="nav nav-tabs">
																		<?$j = 0;?>
																		<?foreach(array_keys($arOption[$blockCode]) as $key):?>
																			<?
																			$class = '';
																			if(isset($_COOKIE['styleSwitcherTabs'.$blockCode]))
																			{
																				if($_COOKIE['styleSwitcherTabs'.$blockCode] == $j)
																					$class = 'active';
																			}
																			else
																			{
																				if(!$j)
																					$class = 'active';
																			}
																			$j++;
																			?>
																			<li class="<?=$class;?>"><a href="#<?=$key?>" data-toggle="tab" class="linked colored_theme_hover_text"><?=GetMessage($key);?></a></li>
																		<?endforeach;?>
																	</ul>
																</div>
																<div class="tab-content">
																	<?$j = 0;?>
																	<?foreach($arOption[$blockCode] as $key => $arGroups):?>
																		<?
																		$class = '';
																		if(isset($_COOKIE['styleSwitcherTabs'.$blockCode]))
																		{
																			if($_COOKIE['styleSwitcherTabs'.$blockCode] == $j)
																				$class = 'active';
																		}
																		else
																		{
																			if(!$j)
																				$class = 'active';
																		}
																		$j++;
																		?>
																		<div class="tab-pane <?=$class;?>" id="<?=$key;?>">
																			<?foreach($arGroups['OPTIONS'] as $key2 => $arGroupItem):?>
																				<div class="item <?=$key2;?>">
																					<?if($arGroupItem['TYPE'] == 'checkbox' && (isset($arGroupItem['ONE_ROW']) && $arGroupItem['ONE_ROW'] == 'Y')):?>
																						<div class="options pull-left" data-code="<?=$key2?>">
																							<?=ShowOptions($key2, $arGroupItem);?>
																						</div>
																						<?=ShowOptionsTitle($key2, $arGroupItem);?>
																					<?else:?>
																						<?=ShowOptionsTitle($key2, $arGroupItem);?>
																						<div class="options" data-code="<?=$key2;?>">
																							<?echo ShowOptions($key2, $arGroupItem);?>
																						</div>
																					<?endif;?>
																					<?if(isset($arGroupItem['DEPENDENT_PARAMS']) && $arGroupItem['DEPENDENT_PARAMS']) // show dependent options
																					{
																						foreach($arGroupItem['DEPENDENT_PARAMS'] as $key3 => $arSubOptions)
																						{
																							if((!isset($arSubOptions['CONDITIONAL_VALUE']) || ($arSubOptions['CONDITIONAL_VALUE'] && $arResult[$key2]['VALUE'] == $arSubOptions['CONDITIONAL_VALUE'])) && $arSubOptions['THEME'] == 'Y')
																							{?>
																								<?if($arSubOptions['TYPE'] == 'checkbox' && (isset($arSubOptions['ONE_ROW']) && $arSubOptions['ONE_ROW'] == 'Y')):?>
																									<div class="borders item">
																										<div class="options dependent pull-left" data-code="<?=$key?>">
																											<?=ShowOptions($key3, $arSubOptions);?>
																										</div>
																										<?=ShowOptionsTitle($key3, $arSubOptions);?>
																									</div>
																								<?else:?>
																									<?=ShowOptionsTitle($key3, $arSubOptions);?>
																									<div class="options dependent" data-code="<?=$key3;?>">
																										<?=ShowOptions($key3, $arSubOptions);?>
																									</div>
																								<?endif;?>
																							<?}
																						}
																					}?>
																				</div>
																			<?endforeach;?>
																		</div>
																	<?endforeach;?>
																</div>
															</div>
														<?endif;?>
													<?}?>
													<?$i++;?>
												</div>
											<?endforeach;?>
										<?}?>
									</form>
								</div>
							</div>
						</div>

						<div class="contents share" data-script="<?=$this->{'__folder'}.'/share.php'?>"></div>

						<div class="contents updates">
							<div class="right-block">
								<div class="content-body body">
									<div class="title_block">
										<div class="title"><?=GetMessage("SWITCH_UPDATES_TOOLTIP_TITLE");?></div>
										<div class="link">
											<!-- noindex -->
												<a href="https://aspro.ru/company/news/obnovleniya/" target="_blank" rel="nofollow"><?=GetMessage("SWITCH_UPDATES_TOOLTIP_TITLE_ALL");?></a>
											<!-- /noindex -->
										</div>
									</div>
									<div class="body_block scrollblock">
										<div class="news">
										<?for ($i = 0; $i < 4; ++$i):?>
												<div class="item clearfix">
													<div class="image image-rounded-x switcher-skeleton-item" style="height:112px"></div>													
													<div class="info">
														<div class="period outer-rounded-x switcher-skeleton-item" style="height:26px"></div>
														<div class="title outer-rounded-x switcher-skeleton-item" style="height:52px"></div>
													</div>
												</div>
											<?endfor;?>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="contents demos">
							<div class="right-block scrollblock">
								<div class="content-body body"><div class="loading-state">Form</div></div>
							</div>
						</div>

						<div class="contents wizard scrollblock" data-script="<?=$this->{'__folder'}.'/ajax.php'?>"></div>
					</div>
				</div>
			</div>

			<?// spectrum.js?>
			<script src="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/js/spectrum.min.js') ? SITE_TEMPLATE_PATH.'/js/spectrum.min.js' : SITE_TEMPLATE_PATH.'/js/spectrum.js')?>" type="text/javascript"></script>
			
			<?// Sortable.js?>
			<script src="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/aspro.max/sort/Sortable.min.js') ? '/bitrix/js/aspro.max/sort/Sortable.min.js' : '/bitrix/js/aspro.max/sort/Sortable.js')?>" type="text/javascript"></script>

			<?// widget.js?>
			<script src="<?=(file_exists($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/js/widget.min.js') ? $templateFolder.'/js/widget.min.js' : $templateFolder.'/js/widget.js').'?'.filemtime($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/js/widget.js')?>" type="text/javascript"></script>
																					
		<?endif;?>
		<?CMax::checkRestartBuffer(true, 'widget');?>
	</div>
	<div class="clearfix"></div>
</div>
<?$this->SetViewTarget('themeScriptJS');?>
<script>
if (typeof arMaxOptions === "object" && arMaxOptions) {
	arMaxOptions.USE_DEMO_LINK = '<?=($bUseDemoLink ? 'Y' : 'N')?>';
}
</script>
<?$this->EndViewTarget();?> 