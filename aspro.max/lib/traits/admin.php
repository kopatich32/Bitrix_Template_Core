<?namespace Aspro\Max\Traits;

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	Aspro\Functions\CAsproMax as SolutionFunctions;

use CMax as Solution;
Loc::loadMessages(__FILE__);

trait Admin {
    public static function showAllAdminRows($optionCode, $arTab, $arOption, $module_id, $arPersonTypes, $optionsSiteID, $arDeliveryServices, $arPaySystems, $arCurrency, $arOrderPropertiesByPerson, $bSearchMode){

		if(array_key_exists($optionCode, $arTab["OPTIONS"]) || $arOption["TYPE"] == 'note' || $arOption["TYPE"] == 'includefile')
		{
			$arControllerOption = \CControllerClient::GetInstalledOptions(self::moduleID);
			if($optionCode === "ONECLICKBUY_PERSON_TYPE"){
				$arOption['LIST'] = $arPersonTypes[$arTab["SITE_ID"]];
			}
			elseif($optionCode === "ONECLICKBUY_DELIVERY"){
				$arOption['LIST'] = $arDeliveryServices[$arTab["SITE_ID"]];
			}
			elseif($optionCode === "ONECLICKBUY_PAYMENT"){
				$arOption['LIST'] = $arPaySystems;
			}
			elseif($optionCode === "ONECLICKBUY_CURRENCY"){
				$arOption['LIST'] = $arCurrency;
			}
			elseif($optionCode === "ONECLICKBUY_PROPERTIES" || $optionCode === "ONECLICKBUY_REQUIRED_PROPERTIES"){
				$defaultPersonType = '';

				if (
					$arPersonTypes &&
					$arPersonTypes[$arTab["SITE_ID"]]
				) {
					$defaultPersonType = key($arPersonTypes[$arTab["SITE_ID"]]);
				}

				$arOption['LIST'] = $arOrderPropertiesByPerson[Option::get(self::moduleID, 'ONECLICKBUY_PERSON_TYPE', $defaultPersonType, $arTab["SITE_ID"])];
			}

			$searchClass = '';
			if($bSearchMode)
			{
				if(isset($arOption["SEARCH_FIND"]) && $arOption["SEARCH_FIND"]) {
					$searchClass = 'visible_block';
				}
			}

			if($arOption['TYPE'] === 'array')
			{
				$itemsCount = Option::get(self::moduleID, $optionCode, 0, $optionsSiteID);
				if($arOption['OPTIONS'] && is_array($arOption['OPTIONS']))
				{
					$arOptionsKeys = array_keys($arOption['OPTIONS']);
					$newItemHtml = '';
					?>
					<div class="title"><?=$arOption["TITLE"]?></div>
					<div class="item array <?=($itemsCount ? '' : 'empty_block');?> js_block" data-class="<?=$optionCode;?>" data-search="<?=$searchClass;?>">
						<div >
							<div class="aspro-admin-item">
								<?if($optionCode !== 'HEADER_PHONES'):?>
									<div class="wrapper has_title no_drag">
										<div class="inner_wrapper">
											<?foreach($arOptionsKeys as $_optionKey):?>
												<div class="inner">
													<div class="title_wrapper"><div class="subtitle"><?=$arOption['OPTIONS'][$_optionKey]['TITLE']?></div></div>
													<?=self::ShowAdminRow(
														$optionCode.'_array_'.$_optionKey.'_#INDEX#',
														$arOption['OPTIONS'][$_optionKey],
														$arTab,
														$arControllerOption
													);?>
												</div>
											<?endforeach;?>
										</div>
									</div>
								<?endif;?>
								<?for($itemIndex = 0; $itemIndex <= $itemsCount; ++$itemIndex):?>
									<?$bNew = $itemIndex == $itemsCount;?>
									<?if($bNew):?><?ob_start();?><?endif;?>
										<div class="wrapper">
											<div class="inner_wrapper">
												<?foreach($arOptionsKeys as $_optionKey):?>
													<?if($_optionKey === 'PHONE_ICON'):?><br /><?endif;?>
													<div class="inner">
														<?=self::ShowAdminRow(
															$optionCode.'_array_'.$_optionKey.'_'.($bNew ? '#INDEX#' : $itemIndex),
															$arOption['OPTIONS'][$_optionKey],
															$arTab,
															$arControllerOption
														);?>
													</div>
												<?endforeach;?>
												<div class="remove" title="<?=Loc::getMessage("REMOVE_ITEM")?>"></div>
												<div class="drag" title="<?=Loc::getMessage("TRANSFORM_ITEM")?>"></div>
											</div>
										</div>
									<?if($bNew):?><?$newItemHtml = ob_get_clean();?><?endif;?>
								<?endfor;?>
							</div>
							<div class="new-item-html" style="display:none;"><?=str_replace('no_drag', '', $newItemHtml)?></div>
							<div>
								<a href="javascript:;" class="adm-btn adm-btn-save adm-btn-add"><?=GetMessage('OPTIONS_ADD_BUTTON_TITLE')?></a>
							</div>
						</div>
					</div>
				<?}
			}
			else
			{
				if($arOption["TYPE"] == 'note')
				{
					if($optionCode === 'CONTACTS_EDIT_LINK_NOTE')
					{
						$contactsHref = str_replace('//', '/', $arTab['SITE_DIR'].'/contacts/?bitrix_include_areas=Y');
						$arOption["TITLE"] = GetMessage('CONTACTS_OPTIONS_EDIT_LINK_NOTE', array('#CONTACTS_HREF#' => $contactsHref));
					}
					?>
					<div class="notes-block visible_block1" data-option_code="<?=$optionCode;?>">
						<div align="center">
							<?=BeginNote('align="center" name="'.htmlspecialcharsbx($optionCode)."_".$optionsSiteID.'"');?>
							<?=($arOption["TITLE"] ? $arOption["TITLE"] : $arOption["NOTE"])?>
							<?=EndNote();?>
						</div>
					</div>
					<?
				}
				else
				{
					$optionName = $arOption["TITLE"];
					$optionType = $arOption["TYPE"];
					$optionList = $arOption["LIST"];
					$optionDefault = $arOption["DEFAULT"];
					$optionVal = $arTab["OPTIONS"][$optionCode];
					$optionSize = $arOption["SIZE"];
					$optionCols = $arOption["COLS"];
					$optionRows = $arOption["ROWS"];
					$optionChecked = $optionVal == "Y" ? "checked" : "";
					$optionDisabled = isset($arControllerOption[$optionCode]) || array_key_exists("DISABLED", $arOption) && $arOption["DISABLED"] == "Y" ? "disabled" : "";
					$optionSup_text = array_key_exists("SUP", $arOption) ? $arOption["SUP"] : "";
					$optionController = isset($arControllerOption[$optionCode]) ? "title='".GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT")."'" : "";
					?>
					<div class="item js_block <?=$optionType;?> <?=((isset($arOption["WITH_HINT"]) && $arOption["WITH_HINT"] == "Y") ? 'with-hint' : '');?> <?=((isset($arOption["BIG_BLOCK"]) && $arOption["BIG_BLOCK"] == "Y") ? 'big-block' : '');?>" data-class="<?=$optionCode;?>" data-search="<?=$searchClass;?>">
						<?if($arOption["HIDDEN"] != "Y"):?>
							<div data-optioncode="<?=$optionCode;?>" <?=$style;?> class="js_block1">
								<div class="inner_wrapper <?=($optionType == "checkbox" ? "checkbox" : "");?>">
									<?=self::ShowAdminRow($optionCode, $arOption, $arTab, $arControllerOption);?>
								</div>
								<?if(isset($arOption["IMG"]) && $arOption["IMG"]):?>
									<div class="img"><img src="<?=$arOption["IMG"];?>" alt="<?=$arOption["TITLE"];?>" title="<?=$arOption["TITLE"];?>"></div>
								<?endif;?>
							</div>
						<?endif;?>
						<?if(isset($arOption['SUB_PARAMS']) && $arOption['SUB_PARAMS'] && (isset($arOption['LIST']) && $arOption['LIST'])): //nested params?>
							<?foreach($arOption['LIST'] as $key => $value):?>
								<?foreach((array)$arOption['SUB_PARAMS'][$key] as $key2 => $arValue)
								{
									if(isset($arValue['VISIBLE']) && $arValue['VISIBLE'] == 'N')
										unset($arOption['SUB_PARAMS'][$key][$key2]);
								}
								if($arOption['SUB_PARAMS'][$key]):?>
									<div class="parent-wrapper js-sub block_<?=$key.'_'.$optionsSiteID;?>" <?=($optionVal == $key ? "style='display:block;'" : "")?>>
										<?$param = "SORT_ORDER_".$optionCode."_".$key;?>
										<?
										/* get custom blocks */
										if (method_exists('Aspro\Functions\CAsproMax', 'getCustomBlocks')) { // not use alias
											$arIndexTemplate = array();
											$arNewOptions = SolutionFunctions::getCustomBlocks($optionsSiteID);

											if ($arNewOptions) {
												$arOption['SUB_PARAMS'][$key] += $arNewOptions;
												foreach ($arNewOptions as $keyOption => $arNewOption) {
													$fieldTemplate = $key.'_'.$keyOption.'_TEMPLATE';
													if (!$arTab['OPTIONS'][$fieldTemplate]) {

														$arTab['OPTIONS'][$fieldTemplate] = $arNewOption['TEMPLATE']['DEFAULT'];
														$arTab['OPTIONS'][$fieldTemplate] = Option::get(static::moduleID, $fieldTemplate, $arNewOption['TEMPLATE']['DEFAULT'], $optionsSiteID);
													}
													$fieldKey = $key.'_'.$keyOption;
													if (!$arTab['OPTIONS'][$fieldKey]) {
														$arTmpValues = static::unserialize(Option::get(static::moduleID, 'NESTED_OPTIONS_'.$optionCode.'_'.$key, serialize(array()), $optionsSiteID));

														if ($arTmpValues && $arTmpValues[$keyOption]) {
															$arTab['OPTIONS'][$fieldKey] = $arTmpValues[$keyOption];
														} else {
															$arTab['OPTIONS'][$fieldKey] = $arNewOption['DEFAULT'];
														}
													}
												}
											}
										}
										/* */
										?>
										<div data-parent='<?=$optionCode."_".$arTab["SITE_ID"]?>' class="block <?=$key?>" <?=($key == $arTab["OPTIONS"][$optionCode] ? "style='display:block'" : "style='display:none'");?>>
											<?if($arOption['SUB_PARAMS'][$key]):?><div><?=GetMessage('SUB_PARAMS');?></div><?endif;?>
										</div>
										<div class="aspro-admin-item" data-key="<?=$key;?>" data-site="<?=$optionsSiteID;?>">
											<?if($arTab['OPTIONS'][$param])
											{
												$arOrder = explode(",", $arTab['OPTIONS'][$param]);
												$arIndexList = array_keys($arOption['SUB_PARAMS'][$key]);

												$arOldBlocks = array_diff($arOrder, $arIndexList);
												if($arOldBlocks) {
													$arOrder = array_filter($arOrder, function($e) use ($arOldBlocks) {
														return !in_array($e, $arOldBlocks);
													});
												}

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
											}?>
											<?$arIndexTemplate = array();?>
											<?foreach((array)$arOption['SUB_PARAMS'][$key] as $key2 => $arValue):
												if(
													$arValue &&
													is_array($arValue) &&
													$arValue['VISIBLE'] != 'N'
												):?>
													<div data-parent='<?=$optionCode."_".$arTab["SITE_ID"]?>' class="block sub <?=$key?> <?=($arValue['DRAG'] == 'N' ? 'no_drag' : '');?>" <?=($key == $arTab["OPTIONS"][$optionCode] ? "style='display:block'" : "style='display:none'");?>>
														<div class="inner_wrapper <?=($arValue["TYPE"] == "checkbox" ? "checkbox" : "");?>">
															<?=self::ShowAdminRow($key.'_'.$key2, $arValue, $arTab, $arControllerOption);?>
															<?if($arValue['DRAG'] != 'N'):?>
																<div class="drag" title="<?=Loc::getMessage("TRANSFORM_ITEM")?>"></div>
															<?endif;?>
															<?if($arValue['FON']):?>
																<?$fon_option = 'fon'.$key.$key2?>
																<?$fon_value = Option::get(self::moduleID, $fon_option, $arValue['FON'], $arTab["SITE_ID"]);?>
																<?$fon_option .= '_'.$arTab["SITE_ID"]?>
																<div class="inner_wrapper fons">
																	<div class="title_wrapper">
																		<div class="subtitle">
																			<label for="<?=$fon_option?>"><?=Loc::getMessage("FON_BLOCK")?></label>
																		</div>
																	</div>
																	<div class="value_wrapper">
																		<input type="checkbox" id="<?=$fon_option?>" name="<?=$fon_option?>" value="Y" <?=($fon_value == 'Y' ? "checked" : "");?> class="adm-designed-checkbox">
																		<label class="adm-designed-checkbox-label" for="<?=$fon_option?>" title=""></label>
																	</div>
																</div>
															<?endif;?>
														</div>
													</div>
												<?endif;?>
												<?
												if(isset($arValue['TEMPLATE']) && $arValue['TEMPLATE'])
												{
													$code_tmp = $key2.'_TEMPLATE';
													$arIndexTemplate[$code_tmp] = $arValue['TEMPLATE'];
												}
												?>
											<?endforeach;?>
										</div>
										<input type="hidden" name="<?=$param.'_'.$arTab["SITE_ID"];?>" value="<?=implode(',', array_keys($arOption['SUB_PARAMS'][$key]))?>" />
									</div>
									<?//show template index components?>
									<?if($arIndexTemplate):?>
										<div class="template-wrapper js-sub block_<?=$key.'_'.$optionsSiteID;?>" data-key="<?=$key;?>" data-site="<?=$optionsSiteID;?>" <?=($key == $arTab["OPTIONS"][$optionCode] ? "style='display:block'" : "style='display:none'");?>>
											<div class="title"><?=Loc::getMessage("FRONT_TEMPLATE_GROUP")?></div>
											<div class="sub-block item">
												<?foreach($arIndexTemplate as $key2 => $arValue):?>
													<div data-parent='<?=$optionCode."_".$arTab["SITE_ID"]?>' class="block <?=$key?>" <?=($key == $arTab["OPTIONS"][$optionCode] ? "style='display:block'" : "style='display:none'");?>>
														<?=self::ShowAdminRow($key.'_'.$key2, $arValue, $arTab, $arControllerOption);?>
													</div>
												<?endforeach;?>
											</div>
										</div>
									<?endif;?>
								<?endif;?>
							<?endforeach;?>
						<?endif;?>
						<?if(isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']): //dependent params?>
							<?foreach($arOption['DEPENDENT_PARAMS'] as $key => $arValue):?>
								<?
								$searchClass = "";
								if($bSearchMode)
								{
									if(isset($arValue["SEARCH_FIND"]) && $arValue["SEARCH_FIND"])
										$searchClass = 'visible_block';
								}?>
								<?if(!isset($arValue['CONDITIONAL_VALUE']) || ($arValue['CONDITIONAL_VALUE'] && $arTab["OPTIONS"][$optionCode] == $arValue['CONDITIONAL_VALUE']))
								{
									$style = "style='display:block'";
								}
								else
								{
									$style = "style='display:none'";
									$searchClass = "";
								}
								?>
								<div data-optioncode="<?=$key;?>" class="depend-block js_block1 <?=$key?> <?=((isset($arValue['TO_TOP']) && $arValue['TO_TOP']) ? "to_top" : "");?>  <?=$arValue["TYPE"];?> <?=((isset($arValue['ONE_BLOCK']) && $arValue['ONE_BLOCK'] == "Y") ? "ones" : "");?>" <?=((isset($arValue['CONDITIONAL_VALUE']) && $arValue['CONDITIONAL_VALUE']) ? "data-show='".$arValue['CONDITIONAL_VALUE']."'" : "");?> data-class="<?=$key;?>" data-search="<?=$searchClass;?>" data-parent='<?=$optionCode."_".$arTab["SITE_ID"]?>' <?=$style;?>>
									<div class="inner_wrapper <?=($arValue["TYPE"] == "checkbox" ? "checkbox" : "");?>">
										<?=self::ShowAdminRow($key, $arValue, $arTab, $arControllerOption);?>
									</div>
								</div>
							<?endforeach;?>
						<?endif;?>
					</div>
					<?
				}
			}
		}
	}

	public static function ShowAdminRow($optionCode, $arOption, $arTab, $arControllerOption, $btable = false){
        static $arUserGroups, $arAgreement;

		$optionName = $arOption['TITLE'];
		$optionType = $arOption['TYPE'];
		$optionList = $arOption['LIST'];
		$optionDefault = $arOption['DEFAULT'];
		$optionVal = $arTab['OPTIONS'][$optionCode];
		$optionSize = $arOption['SIZE'];
		$optionCols = $arOption['COLS'];
		$optionRows = $arOption['ROWS'];
		$optionChecked = $optionVal == 'Y' ? 'checked' : '';
		$optionDisabled = isset($arControllerOption[$optionCode]) || array_key_exists('DISABLED', $arOption) && $arOption['DISABLED'] == 'Y' ? 'disabled' : '';
		$optionSup_text = array_key_exists('SUP', $arOption) ? $arOption['SUP'] : '';
		$optionController = isset($arControllerOption[$optionCode]) ? "title='".GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT")."'" : "";
		$optionsSiteID = $arTab['SITE_ID'];
		$isArrayItem = strpos($optionCode, '_array_') !== false;
		?>

		<?if($optionType == 'dynamic_iblock'):?>
			<?if(Loader::IncludeModule('iblock')):?>
				<div colspan="2">
					<div class="title"  align="center"><b><?=$optionName;?></b></div>
					<?
					$arIblocks = array();
					$arSort = array(
						"SORT" => "ASC",
						"ID" => "ASC"
					);
					$arFilter = array(
						"ACTIVE" => "Y",
						"SITE_ID" => $optionsSiteID,
						"TYPE" => "aspro_max_form"
					);
					$rsItems = CIBlock::GetList($arSort, $arFilter);
					while($arItem = $rsItems->Fetch()){
						if($arItem["CODE"] != "aspro_max_example" && $arItem["CODE"] != "aspro_max_order_page")
						{
							$arItem['THEME_VALUE'] = Option::get(self::moduleID, htmlspecialcharsbx($optionCode)."_".htmlspecialcharsbx(strtoupper($arItem['CODE'])), '', $optionsSiteID);
							$arIblocks[] = $arItem;
						}
					}
					if($arIblocks):?>
						<table width="100%">
							<?foreach($arIblocks as $arIblock):?>
								<tr>
									<td class="adm-detail-content-cell-l" width="50%">
										<?=GetMessage("SUCCESS_SEND_FORM", array("#IBLOCK_CODE#" => $arIblock["NAME"]));?>
									</td>
									<td class="adm-detail-content-cell-r" width="50%">
										<input type="text" <?=((isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : '');?> <?=$optionController?> size="<?=$optionSize?>" maxlength="255" value="<?=htmlspecialcharsbx($arIblock['THEME_VALUE'])?>" name="<?=htmlspecialcharsbx($optionCode)."_".htmlspecialcharsbx($arIblock['CODE'])."_".$optionsSiteID?>" <?=$optionDisabled?>>
									</td>
								</tr>
							<?endforeach;?>
						</table>
					<?endif;?>
				</div>
			<?endif;?>
		<?elseif($optionType == "note"):?>
			<?if($optionCode == 'GOALS_NOTE')
			{
				$FORMS_GOALS_LIST = '';
				if(\Bitrix\Main\Loader::includeModule('form'))
				{
					if($optionsSiteID)
					{
						if($arForms = \CMaxCache::CForm_GetList($by = array('by' => 's_id', 'CACHE' => array('TAG' => 'forms')), $order = 'asc', array('SITE' => $optionsSiteID, 'SITE_EXACT_MATCH' => 'Y'), $is_filtered))
						{
							foreach($arForms as $arForm)
								$FORMS_GOALS_LIST .= $arForm['NAME'].' - <i>goal_webform_success_'.$arForm['ID'].'</i><br />';
						}
					}
				}
				$arOption["NOTE"] = str_replace('#FORMS_GOALS_LIST#', $FORMS_GOALS_LIST, $arOption["NOTE"]);
			}
			?>
			<?if(!$btable):?>
				<div colspan="2" align="center">
			<?else:?>
				<td colspan="2" align="center">
			<?endif;?>
				<?=BeginNote('align="center"');?>
					<?=($arOption["TITLE"] ? $arOption["TITLE"] : $arOption["NOTE"])?>
				<?=EndNote();?>
			<?if(!$btable):?>
				</div>
			<?else:?>
				</td>
			<?endif;?>
		<?else:?>
			<?if(!$isArrayItem):?>
				<?if(!isset($arOption['HIDE_TITLE_ADMIN']) || $arOption['HIDE_TITLE_ADMIN'] != 'Y'):?>
					<?if(!$btable):?>
						<div class="title_wrapper<?=(in_array($optionType, array("multiselectbox", "textarea", "statictext", "statichtml")) ? "adm-detail-valign-top" : "")?>">
					<?else:?>
						<td class="adm-detail-content-cell-l <?=(in_array($optionType, array("multiselectbox", "textarea", "statictext", "statichtml")) ? "adm-detail-valign-top" : "")?>" width="50%">
					<?endif;?>
						<div class="subtitle">
							<?if($optionType == "checkbox"):?>
								<label for="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>"><?=$optionName?></label>
							<?else:?>
								<?if($optionCode == 'PAGE_CONTACTS'):?>
									<?$optionName = Loc::getMessage("BLOCK_VIEW_TITLE");?>
								<?endif;?>
								<?=$optionName.($optionCode == "BASE_COLOR_CUSTOM" ? ' #' : '')?>
							<?endif;?>
							<?if(strlen($optionSup_text)):?>
								<span class="required"><sup><?=$optionSup_text?></sup></span>
							<?endif;?>
						</div>
					<?if(!$btable):?>
						</div>
					<?else:?>
						</td>
					<?endif;?>
				<?endif;?>
			<?endif;?>
			<?if(!$btable):?>
				<div class="value_wrapper">
			<?else:?>
				<td<?=(!$isArrayItem ? ' width="50%" ' : '')?>>
			<?endif;?>
				<?
				if($optionCode == 'PAGE_CONTACTS')
				{
					$siteDir = str_replace('//', '/', $arTab['SITE_DIR']).'/';
					if($arPageBlocks = self::GetIndexPageBlocks($_SERVER['DOCUMENT_ROOT'].$siteDir.'contacts', 'page_contacts_', '')){
						$arTmp = array();
						foreach($arPageBlocks as $page => $value)
						{
							$value_ = str_replace('page_contacts_', '', $value);
							$arTmp[$value_] = $value;
						}
						foreach($arOption['LIST'] as $key_list => $arValue)
						{
							if(isset($arTmp[$key_list]))
								;
							else
								unset($arOption['LIST'][$key_list]);
						}
					}
					$optionList = $arOption['LIST'];
				}
				elseif($optionCode == 'BLOG_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/blog');
				}
				elseif($optionCode == 'SERVIES_PAGE_SECTIONS')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/services');
				}
				elseif($optionCode == 'SERVIES_PAGE_SECTION')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/services');
				}
				elseif($optionCode == 'SERVIES_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/services', 'LIST');
				}
				elseif($optionCode == 'NEWS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/news');
				}
				elseif($optionCode == 'PROJECTS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/projects');
				}
				elseif($optionCode == 'STAFF_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/staff');
				}
				elseif($optionCode == 'PARTNERS_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/partners');
				}
				elseif($optionCode == 'PARTNERS_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/partners', 'ELEMENT');
				}
				elseif($optionCode == 'CATALOG_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/catalog/main', 'ELEMENT');
				}
				elseif($optionCode == 'USE_FAST_VIEW_PAGE_DETAIL')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/catalog/main', 'FAST_VIEW_ELEMENT');
				}
				elseif($optionCode == 'USE_FAST_VIEW_SERVICES')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/services', 'FAST_VIEW_ELEMENT');
				}
				elseif($optionCode == 'VACANCY_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/vacancy');
				}
				elseif($optionCode == 'LICENSES_PAGE')
				{
					$optionList = self::getActualParamsValue( $arTab, $arOption, '/components/bitrix/news/licenses');
				}
				elseif($optionCode == 'GRUPPER_PROPS')
				{
					$optionList = array_merge(
						(array)$optionList,
						array(
							'GRUPPER' => array(
								'TITLE' => Loc::getMessage('GRUPPER_PROPS_GRUPPER'),
								'REQUIREMENTS' => array(
									array(
										'TITLE' => Loc::getMessage('MODULE_REQUIRED', array('#MODULE_NAME#' => 'redsign.grupper')),
										'PASSED' => true,
									)
								),
							),
							'WEBDEBUG' => array(
								'TITLE' => Loc::getMessage('GRUPPER_PROPS_WEBDEBUG'),
								'REQUIREMENTS' => array(
									array(
										'TITLE' => Loc::getMessage('MODULE_REQUIRED', array('#MODULE_NAME#' => 'webdebug.utilities')),
										'PASSED' => true,
									)
								),
							),
							'YENISITE_GRUPPER' => array(
								'TITLE' => Loc::getMessage('GRUPPER_PROPS_YENISITE_GRUPPER'),
								'REQUIREMENTS' => array(
									array(
										'TITLE' => Loc::getMessage('MODULE_REQUIRED', array('#MODULE_NAME#' => 'yenisite.infoblockpropsplus')),
										'PASSED' => true,
									)
								),
							),
						)
					);

					if(!IsModuleInstalled('redsign.grupper')){
						$optionList['GRUPPER']['DISABLED'] = 'Y';
						$optionList['GRUPPER']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'redsign.grupper'));
						$optionList['GRUPPER']['REQUIREMENTS']['PASSED'] = false;
					}

					if(!IsModuleInstalled('webdebug.utilities')){
						$optionList['WEBDEBUG']['DISABLED'] = 'Y';
						$optionList['WEBDEBUG']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'webdebug.utilities'));
						$optionList['WEBDEBUG']['REQUIREMENTS']['PASSED'] = false;
					}

					if(!IsModuleInstalled('yenisite.infoblockpropsplus')){
						$optionList['YENISITE_GRUPPER']['DISABLED'] = 'Y';
						$optionList['YENISITE_GRUPPER']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'yenisite.infoblockpropsplus'));
						$optionList['YENISITE_GRUPPER']['REQUIREMENTS']['PASSED'] = false;
					}
				}
				elseif($optionCode == 'BONUS_SYSTEM')
				{
					$optionList = array_merge(
						(array)$optionList,
						array(
							'LOGICTIM' => array(
								'TITLE' => Loc::getMessage('BONUS_SYSTEM_LOGICTIM'),
								'REQUIREMENTS' => array(
									array(
										'TITLE' => Loc::getMessage('MODULE_REQUIRED', array('#MODULE_NAME#' => 'logictim.balls')),
										'PASSED' => true,
									)
								),
							),
						)
					);

					if(!IsModuleInstalled('logictim.balls')){
						$optionList['LOGICTIM']['DISABLED'] = 'Y';
						$optionList['LOGICTIM']['TITLE'] .= Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'logictim.balls'));
						$optionList['LOGICTIM']['REQUIREMENTS']['PASSED'] = false;
					}
				}

				$bIBlocks = false;
				?>
				<?if($optionType == "checkbox"):?>
					<input type="checkbox" <?=((isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) ? "class='depend-check'" : "");?> <?=$optionController?> id="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" value="Y" <?=$optionChecked?> <?=$optionDisabled?> <?=(strlen($optionDefault) ? $optionDefault : "")?>>
				<?elseif($optionType == "text" || $optionType == "password"):?>
					<?if(isset($arOption["PICKER"]) && $arOption["PICKER"] == "Y"):?>
						<?
						$defaultCode = (($optionCode == "CUSTOM_BGCOLOR_THEME") ? 'MAIN' : 0);
						$customColor = str_replace('#', '', (strlen($optionVal) ? $optionVal : self::$arParametrsList[$defaultCode]['OPTIONS'][$arOption["PARENT_PROP"].'_GROUP']['ITEMS'][$arOption["PARENT_PROP"]]['LIST'][self::$arParametrsList[$defaultCode]['OPTIONS'][$arOption["PARENT_PROP"].'_GROUP']['ITEMS'][$arOption["PARENT_PROP"]]['DEFAULT']]['COLOR']));?>
						<div class="custom_block picker">
							<div class="options">
								<div class="base_color base_color_custom <?=($arTab['OPTIONS'][$arOption["PARENT_PROP"]] == 'CUSTOM' ? 'current' : '')?>" data-name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" data-value="CUSTOM" data-color="#<?=$customColor?>">

									<span class="animation-all click_block" data-option-id="<?=$arOption["PARENT_PROP"]."_".$optionsSiteID?>" data-option-value="CUSTOM" <?=($arTab['OPTIONS'][$arOption["PARENT_PROP"]] == 'CUSTOM' ? "style='border-color:#".$customColor."'" : '')?>><span class="vals">#<?=($arTab['OPTIONS'][$arOption["PARENT_PROP"]] == 'CUSTOM' ? $customColor : '')?></span><span class="bg" data-color="<?=$customColor?>" style="background-color: #<?=$customColor?>;"></span></span>
									<input type="hidden" id="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" value="<?=$customColor?>" />
								</div>
							</div>
						</div>
					<?elseif($optionCode === 'PRIORITY_SECTION_DESCRIPTION_SOURCE'):?>
						<?
						$arPriority = explode(',', $optionVal);
						if(!in_array('SMARTSEO', $arPriority)){
							$arPriority[] = 'SMARTSEO';
						}
						if(!in_array('SOTBIT_SEOMETA', $arPriority)){
							$arPriority[] = 'SOTBIT_SEOMETA';
						}
						if(!in_array('IBLOCK', $arPriority)){
							$arPriority[] = 'IBLOCK';
						}
						?>
						<div class="item array js_block" data-class="<?=$optionCode;?>" data-search="">
							<div>
								<div class="aspro-admin-item">
									<?foreach($arPriority as $i => $priorityCode):?>
										<?
										$bDisabled = false;
										$subtitle = Loc::getMessage('PRIORITY_SECTION_DESCRIPTION_SOURCE_'.$priorityCode);
										if($priorityCode === 'SOTBIT_SEOMETA'){
											if(!IsModuleInstalled('sotbit.seometa')){
												$bDisabled = true;
												$subtitle .= ' '.Loc::getMessage('NOT_INSTALLED', array('#MODULE_NAME#' => 'sotbit.seometa'));
											}
										}
										?>
										<div class="wrapper <?=($bDisabled ? 'disabled' : '')?>">
											<div class="inner_wrapper">
												<div class="inner">
													<div class="title_wrapper"><div class="subtitle"><?=$subtitle?></div></div>
												</div>
												<div class="drag" title="<?=Loc::getMessage("TRANSFORM_ITEM")?>"></div>
												<input type="hidden" value="<?=$priorityCode?>" name="<?=htmlspecialcharsbx($optionCode).'_'.$optionsSiteID.'[]'?>" />
											</div>
										</div>
									<?endforeach;?>
								</div>
							</div>
						</div>
					<?elseif(strpos($optionCode, 'HEADER_PHONES_array_PHONE_ICON') !== false):?>
						<div class="iconset_value" data-code="header_phones" title="<?=htmlspecialcharsbx($arOption['TITLE'])?>"><div class="iconset_value_wrap"><?=\Aspro\Max\Iconset::showIcon($optionVal)?></div><input type="hidden" value="<?=htmlspecialcharsbx($optionVal)?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>"></div>
					<?else:?>

						<input type="<?=$optionType?>" <?=((isset($arOption['PARAMS']) && isset($arOption['PARAMS']['WIDTH'])) ? 'style="width:'.$arOption['PARAMS']['WIDTH'].'"' : '');?> <?=$optionController?> <?=($arOption['PLACEHOLDER'] ? "placeholder='".$arOption['PLACEHOLDER']."'" : '');?> size="<?=$optionSize?>" maxlength="255" value="<?=htmlspecialcharsbx($optionVal)?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" <?=$optionDisabled?> <?=($optionCode == "password" ? "autocomplete='off'" : "")?>>
					<?endif;?>
				<?elseif($optionType == "selectbox"):?>
					<?
					if(isset($arOption['TYPE_SELECT']))
					{
						if($arOption['TYPE_SELECT'] == 'STORES')
						{
							static $bStores;
							if ($bStores === null){
								$bStores = false;
								if(\Bitrix\Main\Loader::includeModule('catalog')){
									if(class_exists('CCatalogStore')){
										$dbRes = \CCatalogStore::GetList(array(), array(), false, false, array());
										if($c = $dbRes->SelectedRowsCount()){
											$bStores = true;
										}
									}
								}
							}
							if(!$bStores)
								unset($optionList['STORES']);
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK')
						{

							\Bitrix\Main\Loader::includeModule('iblock');
							$rsIBlock=\CIBlock::GetList(array("SORT" => "ASC", "ID" => "DESC"), array("LID" => $optionsSiteID));
							$arIBlocks=array();
							while($arIBlock=$rsIBlock->Fetch()){
								$arIBlocks[$arIBlock["ID"]]["NAME"]="(".$arIBlock["ID"].") ".$arIBlock["NAME"]."[".$arIBlock["CODE"]."]";
								$arIBlocks[$arIBlock["ID"]]["CODE"]=$arIBlock["CODE"];
							}
							if($arIBlocks)
							{
								$bIBlocks = true;
							}
						}
						elseif($arOption['TYPE_SELECT'] == 'GROUP')
						{
							if($arUserGroups === null){
								$DefaultGroupID = 0;
								$rsGroups = CGroup::GetList($by = "id", $order = "asc", array("ACTIVE" => "Y"));
								while($arItem = $rsGroups->Fetch()){
									$arUserGroups[$arItem["ID"]] = $arItem["NAME"];
									if($arItem["ANONYMOUS"] == "Y"){
										$DefaultGroupID = $arItem["ID"];
									}
								}
							}
							$optionList = $arUserGroups;
						}
						elseif($arOption['TYPE_SELECT'] == 'CURRENCY')
						{
							static $arCurrency;
							if($arCurrency === null){
								$arCurrency['N'] = '- '.Loc::getMessage("SERVICES_CURRENCY_EMPTY");
								$dbCurrency = \CCurrency::GetList(($by="name"), ($order="asc"));
								while($res_cur = $dbCurrency->Fetch())
								{
									$arCurrency[$res_cur['CURRENCY']] = $res_cur["FULL_NAME"];
								}
							}
							$optionList = $arCurrency;
						}
						elseif(is_array($arOption['TYPE_SELECT']) && $arOption['TYPE_SELECT']['TYPE'] == 'JS')
						{?>
							<script>
								if (typeof allCountries === 'undefined') {
									$.ajax({
										url: "<?=$arOption['TYPE_SELECT']['SRC'];?>",
										async: false,
										success: function (data) {}
									})
								}
								var selectOptions = selectOptions || '';
								if (allCountries && !selectOptions) {
									for (let i = 0; i < allCountries.length; i++) {
										selectOptions += '<option value="'+allCountries[i].iso2+'">'+allCountries[i].name+'</option>';
									}
									$('select[name=<?=$optionCode."_".$optionsSiteID;?>]').html(selectOptions)
								}
							</script>
							<?
						}
						elseif($arOption['TYPE_SELECT'] == 'CURRENCIES')
						{
							static $arCurrencies;
							if (Loader::includeModule('currency')) {
								if($arCurrencies === null){
									$currencyIterator = \CCurrency::GetList($by='sort',$order='asc','ru');
									while ($currency = $currencyIterator->Fetch()) {
										$arCurrencies[$currency['CURRENCY']] = "[".$currency['CURRENCY']."] ".$currency['FULL_NAME'];
									}
								}
							}
							$optionList = $arCurrencies;
						}
						elseif($arOption['TYPE_SELECT'] == 'ENUM')
						{
							$arEnum = ['' => '-'];
							$iblockIDCatalog = Option::get(self::moduleID, 'CATALOG_IBLOCK_ID', \CMaxCache::$arIBlocks[$optionsSiteID]['aspro_max_catalog']['aspro_max_catalog'][0], $optionsSiteID);

							$rsResult = \CIBlockProperty::GetPropertyEnum("HIT", Array(), Array("IBLOCK_ID"=> $iblockIDCatalog));
							while($arResult = $rsResult->GetNext())
							{

								$arEnum[$arResult['EXTERNAL_ID']] = $arResult['VALUE'];
							}
							$optionList = $arEnum;
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK_PROPS') {
							$optionList = static::getProps($arOption, $optionsSiteID);
						}
                        elseif($arOption['TYPE_SELECT'] == 'AGREEMENT')
						{
							if($arAgreement === null){

								$arAgreement = \Bitrix\Main\UserConsent\Agreement::getActiveList();
								foreach ($arAgreement as $id => $agreeName) {
									$arAgreement[$id] = '['. $id .'] ' . $agreeName;
								}
								unset($id, $agreeName);
							}

							$optionList = $arAgreement;

							if(isset($arOption['ADD_DEFAULT']) && $arOption['ADD_DEFAULT'] === 'Y'){
								$optionList['DEFAULT'] = Loc::getMessage("AGREEMENT_DEFAULT");
							} else {
                                $optionList['0'] = Loc::getMessage("AGREEMENT_NO_CHOOSE");
                            }
							ksort($optionList, SORT_NUMERIC);
						}
					}

					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);

					if(isset($arOption["TYPE_EXT"]) && $arOption["TYPE_EXT"] == "colorpicker"):?>
						<div class="bases_block">
							<input type="hidden" id="<?=$optionCode?>" name="<?=$optionCode."_".$optionsSiteID;?>" value="<?=$optionVal?>" />
							<?foreach($arOption['LIST'] as $colorCode => $arColor):?>
								<?if($colorCode !== 'CUSTOM'):?>
									<div class="base_color <?=($colorCode == $optionVal ? 'current' : '')?>" data-value="<?=$colorCode?>" data-color="<?=$arColor['COLOR']?>">
										<span class="animation-all click_block status-block"  data-option-id="<?=$optionCode?>" data-option-value="<?=$colorCode?>" title="<?=$arColor['TITLE']?>"><span style="background-color: <?=$arColor['COLOR']?>;"></span></span>
									</div>
								<?endif;?>
							<?endforeach;?>
						</div>
					<?elseif((isset($arOption["IS_ROW"]) && $arOption["IS_ROW"] == "Y") ||(isset($arOption["SHOW_IMG"]) && $arOption["SHOW_IMG"] == "Y")):?>
						<?if($arOption["HIDDEN"] != "Y"):?>
							<div class="block_with_img <?=(isset($arOption["ROWS"]) && $arOption["ROWS"] == "Y" ? 'in_row' : '');?>">
								<input type="hidden" id="<?=$optionCode?>" name="<?=$optionCode."_".$optionsSiteID;?>" value="<?=$optionVal?>" />
								<div class="rows flexbox">
									<?foreach($arOption['LIST'] as $code => $arValue):?>
										<?if($arValue["TITLE"] == 'page_contacts_custom' || $arValue["TITLE"] == 'list_elements_custom' || $arValue["TITLE"] == 'element_custom')
											$arValue["TITLE"] = 'custom';?>
										<div>
											<div class="link-item animation-boxs block status-block <?=($code == $optionVal ? 'current' : '')?>" <?=($code == $optionVal ? 'data-current="Y"' : '')?> data-value="<?=$code?>" data-site="<?=$optionsSiteID;?>">
												<span class="title"><?=$arValue["TITLE"];?></span>
												<?if($arValue["IMG"]):?>
													<span><img src="<?=$arValue["IMG"];?>" alt="<?=$arValue["TITLE"];?>" title="<?=$arValue["TITLE"];?>" class="<?=($arValue["COLORED_IMG"] ? 'colored_theme_bg' : '')?>" /></span>
												<?if(isset($arValue['ADDITIONAL_OPTIONS']) && $arValue['ADDITIONAL_OPTIONS']):?>
													<div class="subs">
														<?foreach($arValue['ADDITIONAL_OPTIONS'] as $key => $arSubOption):?>
															<div class="sub-item inner_wrapper <?=($arSubOption['TYPE'] ?? 'checkbox');?>">
																<?//print_r($arSubOption);?>
																<?$codeTmp = (strpos($optionCode, '_TEMPLATE') !== false ? str_replace('_TEMPLATE', '_', $optionCode).$key.'_'.$code : $key.'_'.$code);?>
																<?=self::ShowAdminRow($codeTmp, $arSubOption, $arTab, array())?>
															</div>
														<?endforeach;?>
													</div>
												<?endif;?>
												<?endif;?>
											</div>
										</div>
									<?endforeach;?>
								</div>
							</div>
						<?endif;?>
					<?else:?>
						<select <?=((isset($arOption['DEPENDENT_PARAMS']) && $arOption['DEPENDENT_PARAMS']) ? "class='depend-check'" : "");?> data-site="<?=$optionsSiteID?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" <?=$optionController?> <?=$optionDisabled?>>
							<?if($bIBlocks)
							{
								foreach($arIBlocks as $key => $arValue) {
									$selected="";
									if(!$optionVal && $optionCode === "SERVICES_IBLOCK_ID" && $arValue["CODE"] === "aspro_max_services"){
										$selected="selected";
									}elseif(!$optionVal && $optionCode === "CATALOG_IBLOCK_ID" && $arValue["CODE"]=="aspro_max_catalog"){
										$selected="selected";
									}elseif($optionVal && $optionVal==$key){
										$selected="selected";
									}?>
									<option value="<?=$key;?>" <?=$selected;?>><?=htmlspecialcharsbx($arValue["NAME"]);?></option>
								<?}
							}
							elseif(
								$optionCode == 'GRUPPER_PROPS' ||
								$optionCode == 'BASKET_FILE_DOWNLOAD_TEMPLATE'
							){
								foreach($optionList as $key => $arValue):
									$selected = $disabled = '';

									if($optionVal && $optionVal == $key)
										$selected = 'selected';

									if(isset($arValue['DISABLED']))
										$disabled = 'disabled';
									?>
									<option value="<?=$key;?>" <?=$selected?> <?=$disabled?>><?=htmlspecialcharsbx($arValue["TITLE"]);?></option>
								<?endforeach;?>
							<?}
							else
							{
								for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
									<option value="<?=$arr_keys[$j]?>" <?if($optionVal == $arr_keys[$j]) echo "selected"?> <?=(isset($optionList[$arr_keys[$j]]['DISABLED']) ? 'disabled' : '');?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option>
								<?endfor;
							}?>
						</select>
					<?endif;?>
				<?elseif($optionType == "multiselectbox"):?>
					<?
					if(isset($arOption['TYPE_SELECT']))
					{
						if($arOption['TYPE_SELECT'] == 'STORES')
						{
							$arStores = [];
							if (\Bitrix\Main\Loader::includeModule('catalog')) {
								if (class_exists('CCatalogStore')) {
									$dbRes = \CCatalogStore::GetList(array(), array(), false, false, array('ID', 'TITLE'));
									while ($arStore = $dbRes->Fetch()) {
										$arStores[$arStore['ID']] = '['.$arStore['ID'].'] '.$arStore['TITLE'];
									}
								}
								if ($arStores) {
									$optionList = $arStores;
								}
							}
						}
						if($arOption['TYPE_SELECT'] == 'PRICES')
						{
							\Bitrix\Main\Loader::includeModule('catalog');
							$arPrices = array();
							$rsPrice = \CCatalogGroup::GetList(array("SORT" => "ASC"), array());
							while($arPrice = $rsPrice->GetNext())
							{
								$name = ($arPrice["NAME_LANG"] ? $arPrice["NAME_LANG"] : $arPrice["NAME"]);
								$arPrices[$arPrice["ID"]]["TITLE"] = "(".$arPrice["ID"].") ".$name." [".$arPrice["XML_ID"]."]";
							}
							$optionList = $arPrices;
						}
						elseif($arOption['TYPE_SELECT'] == 'PRICES_CODE')
						{
							\Bitrix\Main\Loader::includeModule('catalog');
							$arPrices = array();
							$rsPrice = \CCatalogGroup::GetList(array("SORT" => "ASC"), array());
							while($arPrice = $rsPrice->GetNext())
							{
								$name = ($arPrice["NAME_LANG"] ? $arPrice["NAME_LANG"] : $arPrice["NAME"]);
								$arPrices[$arPrice["NAME"]]["TITLE"] = "(".$arPrice["ID"].") ".$name." [".$arPrice["XML_ID"]."]";
							}
							$optionList = $arPrices;
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK')
						{
							static $bIBlocks;
							if ($bIBlocks === null || !$arIBlocks){
								$bIBlocks = false;
								\Bitrix\Main\Loader::includeModule('iblock');
								$rsIBlock=\CIBlock::GetList(array("SORT" => "ASC", "ID" => "DESC"), array("LID" => $optionsSiteID));
								$arIBlocks=array();
								while($arIBlock=$rsIBlock->Fetch()){
									$arIBlocks[$arIBlock["ID"]]["NAME"]="(".$arIBlock["ID"].") ".$arIBlock["NAME"]." [".$arIBlock["CODE"]."]";
									$arIBlocks[$arIBlock["ID"]]["CODE"]=$arIBlock["CODE"];
								}
								if($arIBlocks)
								{
									$bIBlocks = true;
								}
							}
						} elseif(is_array($arOption['TYPE_SELECT']) && $arOption['TYPE_SELECT']['TYPE'] == 'JS') {?>
							<script>
								if (typeof allCountries === 'undefined') {
									$.ajax({
										url: "<?=$arOption['TYPE_SELECT']['SRC'];?>",
										async: false,
										success: function (data) {}
									})
								}
								var selectOptions = selectOptions || '';
								if (allCountries && !selectOptions) {
									for (let i = 0; i < allCountries.length; i++) {
										selectOptions += '<option value="'+allCountries[i].iso2+'">'+allCountries[i].name+'</option>\n';
									}
								}
							</script>
							<?
						}
						elseif($arOption['TYPE_SELECT'] == 'IBLOCK_PROPS')
						{
							$optionList = static::getProps($arOption, $optionsSiteID);
						}
						elseif($arOption['TYPE_SELECT'] == 'GROUP')
						{
							if($arUserGroups === null){
								$DefaultGroupID = 0;
								$rsGroups = \CGroup::GetList($by = "id", $order = "asc", array("ACTIVE" => "Y"));
								while($arItem = $rsGroups->Fetch()){
									$arUserGroups[$arItem["ID"]] = $arItem["NAME"];
									if($arItem["ANONYMOUS"] == "Y"){
										$DefaultGroupID = $arItem["ID"];
									}
								}
							}
							$optionList = $arUserGroups;
						}
						elseif($arOption['TYPE_SELECT'] == 'SITE')
						{
							static $arSites;
							if($arSites === null){
								$rsSites = \CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));

								while($arItem = $rsSites->Fetch()){
									$arSites[$arItem["ID"]] = $arItem["NAME"];
								}
							}
							$optionList = $arSites;
						}
						elseif($arOption['TYPE_SELECT'] == 'PRICES_TYPE')
						{
							static $arPricesType;
							if (Loader::includeModule('catalog')) {
								if($arPricesType === null){
									$arPricesType = \CCatalogIBlockParameters::getPriceTypesList();
								}
							}
							$optionList = $arPricesType;
							if (!$arPricesType) {
								$textNote = GetMessage('NEED_ADD_PRICES_TYPE');
							}
						}
					}

					if(!is_array($optionList)) $optionList = (array)$optionList;
					$arr_keys = array_keys($optionList);
					$optionValTmp = $optionVal;
					$optionVal = explode(",", $optionVal);
					if(!is_array($optionVal)) $optionVal = (array)$optionVal;
					?>
					<?if(isset($arOption['SHOW_CHECKBOX']) && $arOption['SHOW_CHECKBOX'] == 'Y'):?>
						<div class="props">
							<?for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
								<div class="outer_wrapper <?=(in_array($arr_keys[$j], $optionVal) ? "checked" : "");?>">
									<div class="inner_wrapper checkbox">
										<div class="title_wrapper">
											<div class="subtitle"><label for="<?=$optionCode."_".$optionsSiteID."_".$j?>"><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></label></div>
										</div>
										<div class="value_wrapper">
											<input type="checkbox" id="<?=$optionCode."_".$optionsSiteID."_".$j?>" name="temp_<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" value="<?=$arr_keys[$j]?>" <?=(in_array($arr_keys[$j], $optionVal) ? "checked" : "");?>><label for="<?=$optionCode."_".$optionsSiteID."_".$j?>"></label>
										</div>
									</div>
								</div>
							<?endfor;?>
						</div>
					<?endif;?>
					<?//else:?>
						<select data-site="<?=$optionsSiteID?>" size="<?=$optionSize?>" <?=$optionController?> <?=$optionDisabled?> multiple name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>[]" >
							<?
							if(
								$optionCode === 'SEARCHTITLE_CATALOG_CATS' ||
								$optionCode === 'SEARCHTITLE_SITE_CATS'
							){
								$selected = is_array($optionVal) && $optionVal && in_array('main', $optionVal) ? 'selected' : '';
								?><option value="main" <?=$selected?>><?=Loc::getMessage("SEARCHTITLE_CATS_MAIN")?></option><?
								if(\CMaxCache::$arIBlocks[$optionsSiteID]){
									foreach(\CMaxCache::$arIBlocks[$optionsSiteID] as $iblockTypeId => $arIblockCodes) {
										?><optgroup label="[iblock] <?=$iblockTypeId?>"><?
											$key = 'iblock_'.$iblockTypeId;
											$selected = is_array($optionVal) && $optionVal && in_array($key, $optionVal) ? 'selected' : '';
											?><option value="<?=$key?>" <?=$selected?>><?=Loc::getMessage("SEARCHTITLE_CATS_ALL")?></option><?

											foreach($arIblockCodes as $iblockCode => $iblocksIDs) {
												if ($iblocksIDs) {
													foreach($iblocksIDs as $iblockId) {
														$key = 'iblock_'.$iblockId;
														$selected = is_array($optionVal) && $optionVal && in_array($key, $optionVal) ? 'selected' : '';
														?><option value="<?=$key?>" <?=$selected?>><?='['.\CMaxCache::$arIBlocksInfo[$iblockId]["ID"].'] '.htmlspecialcharsbx(\CMaxCache::$arIBlocksInfo[$iblockId]["NAME"])?></option><?
													}
												}
											}
										?></optgroup><?
									}
								}

								if (Loader::includeModule('blog')){
									static $arSearchBlogs;

									if (!isset($arSearchBlogs)) {
										$rsBlog = \CBlog::GetList();
										while ($arBlog = $rsBlog->Fetch()) {
											$arSearchBlogs[$arBlog['ID']] = '['.$arBlog['ID'].'] '.$arBlog['NAME'];
										}
									}

									if ($arSearchBlogs) {
										$selected = is_array($optionVal) && $optionVal && in_array('blog_all', $optionVal) ? 'selected' : '';
										?><optgroup label="<?=Loc::getMessage('SEARCHTITLE_CATS_FORUM')?>"><?
										?><option value="blog_all" <?=$selected?>><?=Loc::getMessage("SEARCHTITLE_CATS_ALL")?></option><?
										foreach($arSearchBlogs as $key => $name) {
											$key = 'blog_'.$key;
											$selected = is_array($optionVal) && $optionVal && in_array($key, $optionVal) ? 'selected' : '';
											?><option value="<?=$key;?>" <?=$selected;?>><?=htmlspecialcharsbx($name);?></option><?
										}
										?></optgroup><?
									}
								}

								if (Loader::includeModule('forum')){
									static $arSearchForum;

									if (!isset($arSearchForum)) {
										$rsForum = \CForumNew::GetList();
										while ($arForum = $rsForum->Fetch()) {
											$arSearchForum[$arForum['ID']] = '['.$arForum['ID'].'] '.$arForum['NAME'];
										}
									}

									if ($arSearchForum) {
										$selected = is_array($optionVal) && $optionVal && in_array('forum_all', $optionVal) ? 'selected' : '';
										?><optgroup label="<?=Loc::getMessage('SEARCHTITLE_CATS_FORUM')?>"><?
										?><option value="forum_all" <?=$selected?>><?=Loc::getMessage("SEARCHTITLE_CATS_ALL")?></option><?
										foreach($arSearchForum as $key => $name) {
											$key = 'forum_'.$key;
											$selected = is_array($optionVal) && $optionVal && in_array($key, $optionVal) ? 'selected' : '';
											?><option value="<?=$key;?>" <?=$selected;?>><?=htmlspecialcharsbx($name);?></option><?
										}
										?></optgroup><?
									}
								}
							}
							elseif($bIBlocks){
								foreach($arIBlocks as $key => $arValue) {
									$selected = is_array($optionVal) && $optionVal && in_array($key, $optionVal) ? 'selected' : '';
									?><option value="<?=$key;?>" <?=$selected;?>><?=htmlspecialcharsbx($arValue["NAME"]);?></option><?
								}
							}
							else {
								?>
								<?for($j = 0, $c = count($arr_keys); $j < $c; ++$j):?>
									<option value="<?=$arr_keys[$j]?>" <?if(in_array($arr_keys[$j], $optionVal)) echo "selected"?>><?=htmlspecialcharsbx((is_array($optionList[$arr_keys[$j]]) ? $optionList[$arr_keys[$j]]["TITLE"] : $optionList[$arr_keys[$j]]))?></option>
								<?endfor;?>
								<?
							}
							?>
						</select>
					<?if(is_array($arOption['TYPE_SELECT']) && $arOption['TYPE_SELECT']['TYPE'] == 'JS') {?>
						<?if ($arOption['TYPE_SELECT']['NAME'] == 'PHONE') {?>
							<script>
								if (allCountries && selectOptions) {
									$('select[name^=<?=$optionCode."_".$optionsSiteID;?>]').html(selectOptions);
									<?if ($optionVal):?>
										let values = '<?=$optionValTmp;?>';
										$('select[name^=<?=$optionCode."_".$optionsSiteID;?>] option').each(function(node){
											if (values.includes(this.value)) {
												this.setAttribute('selected', 'selected')
											}
										})
									<?endif;?>
								}
							</script>
						<?}?>
					<?}?>
				<?elseif($optionType == "textarea"):?>
					<textarea <?=$optionController?> <?=$optionDisabled?> rows="<?=$optionRows?>" cols="<?=$optionCols?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>"><?=htmlspecialcharsbx($optionVal)?></textarea>
				<?elseif($optionType == "statictext"):?>
					<?=htmlspecialcharsbx($optionVal)?>
				<?elseif($optionType == "statichtml"):?>
					<?=$optionVal?>
				<?elseif($optionType == "file"):?>
					<?$val = self::unserialize(Option::get(self::moduleID, $optionCode, serialize(array()), $optionsSiteID));

					$arOption['MULTIPLE'] = 'N';
					if($optionCode == 'LOGO_IMAGE' || $optionCode == 'LOGO_IMAGE_WHITE'){
						$arOption['WIDTH'] = 394;
						$arOption['HEIGHT'] = 140;
					}
					elseif($optionCode == 'FAVICON_IMAGE'){
						$arOption['WIDTH'] = 16;
						$arOption['HEIGHT'] = 16;
					}
					elseif($optionCode == 'APPLE_TOUCH_ICON_IMAGE'){
						$arOption['WIDTH'] = 180;
						$arOption['HEIGHT'] = 180;
					}
					self::__ShowFilePropertyField($optionCode."_".$optionsSiteID, $arOption, $val);?>
				<?elseif($optionType === 'includefile'):?>
					<?
					if(!is_array($arOption['INCLUDEFILE'])){
						$arOption['INCLUDEFILE'] = array($arOption['INCLUDEFILE']);
					}
					foreach($arOption['INCLUDEFILE'] as $includefile){
						$includefile = str_replace('//', '/', str_replace('#SITE_DIR#', $arTab['SITE_DIR'].'/', $includefile));
						$includefile = str_replace('//', '/', str_replace('#TEMPLATE_DIR#', $arTab['TEMPLATE']['DIR'].'/', $includefile));
						if(strpos($includefile, '#') === false){
							$template = (isset($arOption['TEMPLATE']) && strlen($arOption['TEMPLATE']) ? $arOption['TEMPLATE'] : 'include_area.php');
							if(strpos($includefile, 'invis-counter') === false)
							{
								$href = (!strlen($includefile) ? "javascript:;" : "javascript: new BX.CAdminDialog({'content_url':'/bitrix/admin/public_file_edit.php?site=".$arTab['SITE_ID']."&bxpublic=Y&from=includefile".($arOption['NO_EDITOR'] == 'Y' ? "&noeditor=Y" : "")."&templateID=".$arTab['TEMPLATE']['ID']."&path=".$includefile."&lang=".LANGUAGE_ID."&template=".$template."&subdialog=Y&siteTemplateId=".$arTab['TEMPLATE']['ID']."','width':'1009','height':'503'}).Show();");
							}
							else
							{

								$href = (!strlen($includefile) ? "javascript:;" : "javascript: new BX.CAdminDialog({'content_url':'/bitrix/admin/public_file_edit.php?site=".$arTab['SITE_ID']."&bxpublic=Y&from=includefile&noeditor=Y&templateID=".$arTab['TEMPLATE']['ID']."&path=".$includefile."&lang=".LANGUAGE_ID."&template=".$template."&subdialog=Y&siteTemplateId=".$arTab['TEMPLATE']['ID']."','width':'1009','height':'503'}).Show();");
							}
							?><a class="adm-btn" href="<?=$href?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" title="<?=GetMessage('OPTIONS_EDIT_BUTTON_TITLE')?>"><?=GetMessage('OPTIONS_EDIT_BUTTON_TITLE')?></a>&nbsp;<?
						}
					}
					?>
				<?endif;?>
			<?if(!$btable):?>
				</div>
			<?else:?>
				</td>
			<?endif;?>
		<?endif;?>
	<?}

	public static function getProps(array $arOption, string $optionsSiteID) : array {
		$arIblockProps = [];
		if (Loader::includeModule('iblock')) {
			if($arOption['PROPS_SETTING']){

				$arFilter = [];
				if(isset($arOption['PROPS_SETTING']['IBLOCK_ID_OPTION']) || isset($arOption['PROPS_SETTING']['IBLOCK_CODE'])){
					if ($iblockID = Option::get(self::moduleID, $arOption['PROPS_SETTING']['IBLOCK_ID_OPTION'], '', $optionsSiteID)) {
						$arFilter['IBLOCK_ID'] = $iblockID;
					} elseif ($arOption['PROPS_SETTING']['IBLOCK_CODE']) {
						$arFilter['IBLOCK_CODE'] = $arOption['PROPS_SETTING']['IBLOCK_CODE'];
					}
				} else {
					$iblockIDCatalog = Option::get(self::moduleID, 'CATALOG_IBLOCK_ID', \CMaxCache::$arIBlocks[$optionsSiteID]['aspro_max_catalog']['aspro_max_catalog'][0], $optionsSiteID);
					$iblockIDSCU = \CCatalog::GetByID($iblockIDCatalog)['OFFERS_IBLOCK_ID'];
					if ($iblockIDCatalog) {
						$arFilter['IBLOCK_ID'] = $iblockIDSCU;
					}
				}

				if ($arOption['PROPS_SETTING']['FILTER']) {
					$arFilter = array_merge($arFilter, $arOption['PROPS_SETTING']['FILTER']);
				}
				$arIblockProps = ['' => '-'];
				$rsProps = \CIBlockProperty::GetList(
					array('SORT' => 'ASC', 'NAME' => 'ASC'),
					$arFilter,
				);
				while ($arProp = $rsProps->GetNext()) {
					if ($arOption['PROPS_SETTING']['IS_TREE']) {
						if (
							'L' == $arProp['PROPERTY_TYPE']
							|| 'E' == $arProp['PROPERTY_TYPE']
							|| ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'])
						) {
							$arIblockProps[$arProp['CODE']] = static::setPropsValue($arProp);
						}
					} elseif ($arOption['PROPS_SETTING']['IS_SELECTION_PICTURE']) {
						if ('S' == $arProp['PROPERTY_TYPE'] && 'directory' == $arProp['USER_TYPE'] && \CIBlockPriceTools::checkPropDirectory($arProp) && strlen($arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])){
							$arIblockProps[$arProp['CODE']] = static::setPropsValue($arProp);
						}
					} elseif ($arOption['PROPS_SETTING']['IS_FILE']) {

						if ('F' == $arProp['PROPERTY_TYPE']){
							$arIblockProps[$arProp['CODE']] = static::setPropsValue($arProp);
						}
					} else {
						$arIblockProps[$arProp['CODE']] = static::setPropsValue($arProp);
					}
				}
			}
		}
		return $arIblockProps;
	}

	public static function setPropsValue(array $arProp) : string {
		return "[{$arProp['ID']}] {$arProp['NAME']} ({$arProp['CODE']})";
	}
}
