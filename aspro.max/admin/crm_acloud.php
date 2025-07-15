<?
use Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Aspro\Max\CRM\Acloud\Connection,
	Aspro\Max\CRM\Acloud\Lead,
	Aspro\Max\CRM;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = 'CMax';
$moduleID = 'aspro.max';
\Bitrix\Main\Loader::includeModule($moduleID);

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT >= 'R'){
	$GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
	$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('ASPRO_MAX_PAGE_TITLE'));

	$arSites = array();
	$db_res = CSite::GetList($by = 'id', $sort = 'asc', array('ACTIVE' => 'Y'));
	while($res = $db_res->Fetch()){
		$arSites[] = $res;
	}

	$arTabsForView = COption::GetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', '');
	if($arTabsForView) {
		$arTabsForView = explode(',' , $arTabsForView);
	}

	$arTabs = array();
	$bShowGenerate = false;
	foreach($arSites as $key => $arSite){
		if(
			!$arTabsForView ||
			in_array($arSite['ID'], $arTabsForView)
		){
			$optionsSiteID = $arSite['ID'];
			$connection = Connection::getInstance($optionsSiteID);

			// get web forms
			$arForms = CRM\Helper::getForms($optionsSiteID);

			// get person types
			$arPersonTypes = CRM\Helper::getOrdersPersonTypes($optionsSiteID);

			$arTabs[] = array(
				'DIV' => 'edit'.($key+1),
				'TAB' => Loc::getMessage('MAIN_OPTIONS_SITE_ASPRO_TITLE', array('#SITE_NAME#' => $arSite['NAME'], '#SITE_ID#' => $arSite['ID'])),
				'ICON' => 'settings',
				'PAGE_TYPE' => 'site_settings',
				'SITE_ID' => $optionsSiteID,
				'CONNECTION' => $connection,
				'ITEMS' => array(
					'CONFIG' => array(
						'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_CONFIG_ACLOUD'),
						'ITEMS' => array(
							'DOMAIN_ACLOUD' => array(
								'TYPE' => 'text',
								'VALUE' => $connection->domain,
								'SKIP_CHECK' => true,
							),
							'TOKEN_ACLOUD' => array(
								'TYPE' => 'text',
								'VALUE' => $connection->api_key,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_TOKEN_HINT'),
								'SKIP_CHECK' => true,
							),
							'ACTIVE_LINK_ACLOUD' => array(
								'TYPE' => 'hidden',
								'VALUE' => $connection->tested,
								'SKIP_CHECK' => true,
							),
							'ACTIVE_ACLOUD' => array(
								'TYPE' => 'checkbox',
								'VALUE' => $connection->active,
							),
							'USE_LOG_ACLOUD' => array(
								'TYPE' => 'checkbox',
								'VALUE' => $connection->logging,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_USE_LOG_ACLOUD_HINT', [
									'#LOG_BASEDIR#' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $connection->getLogBasePath()),
									'#LOG_FILENAME#' => $connection->getLogFilename(),
								])
							),
						)
					),
					'FORMS' => array(
						'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_FORMS_ACLOUD'),
						'ITEMS' => array(
							'AUTOMATE_SEND_ACLOUD' => array(
								'TYPE' => 'checkbox',
								'VALUE' => $connection->forms_autosend,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_AUTOMATE_SEND_ACLOUD_HINT')
							),
							'LEAD_NAME_ACLOUD_TITLE' => array(
								'TYPE' => 'text',
								'VALUE' => $connection->forms_lead_title,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_LEAD_NAME_ACLOUD_TITLE_HINT')
							),
							'WEB_FORM_ACLOUD' => array(
								'TYPE' => 'select',
								'VALUE' => Option::get($moduleID, 'WEB_FORM_ACLOUD', '', $optionsSiteID),
								'VALUES' => $arForms,
								'DINAMIC_FORMS' => 'Y',
							),
						)
					),
					'ORDERS' => array(
						'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_ORDERS_ACLOUD'),
						'ITEMS' => array(
							'AUTOMATE_SEND_ORDER_ACLOUD' => array(
								'TYPE' => 'checkbox',
								'VALUE' => $connection->orders_autosend,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_AUTOMATE_SEND_ORDER_ACLOUD_HINT'),
							),
							'LEAD_NAME_ORDER_ACLOUD_TITLE' => array(
								'TYPE' => 'text',
								'VALUE' => $connection->orders_lead_title,
								'HINT' => Loc::getMessage('ASPRO_MAX_MODULE_LEAD_NAME_ORDER_ACLOUD_TITLE_HINT')
							),
							'ORDER_PERSON_TYPE_ACLOUD' => array(
								'TYPE' => 'select',
								'VALUE' => Option::get($moduleID, 'ORDER_PERSON_TYPE_ACLOUD', '', $optionsSiteID),
								'VALUES' => $arPersonTypes,
								'DINAMIC_ORDERS' => 'Y',
							),
						)
					)
				),
				'ONSELECT' => "onMainTabSelected('site_settings');",
			);
		}
	}

	// add landing page
	$arLandingTab = array(
		'DIV' => 'landing_edit',
		'TAB' => GetMessage('ASPRO_MAX_LANDING_ACLOUD'),
		'LANDING_PAGE' => true,
		'ONSELECT' => "onMainTabSelected('landing');",
	);
	array_unshift($arTabs, $arLandingTab);

	$tabControl = new CAdminTabControl('tabControl', $arTabs);

	if (
		$REQUEST_METHOD == 'POST' &&
		strlen($Apply.$RestoreDefaults.$Auth.$SendCrm) &&
		$RIGHT >= 'W' &&
		check_bitrix_sessid()
	) {
		global $APPLICATION, $CACHE_MANAGER;
		$APPLICATION->RestartBuffer();

		if (
			$Auth ||
			$SendCrm
		) {
			$arResult = [];

			try {
				if (!strlen($SITE_ID)) {
					throw new \Exception('Empty fields');
				}

				if ($Auth) {
					if (
						!strlen($DOMAIN) ||
						!strlen($TOKEN)
					){
						throw new \Exception('Empty fields');
					}
					else {
						$config = [
							'domain' => $DOMAIN,
							'api_key' => $TOKEN,
						];

						$arResult['response'] = Connection::getInstance($SITE_ID)->test($config);
					}
				}
				else {
					if (strlen($ORDER_ID)) {
						$leadId = CRM\Helper::sendOrder($ORDER_ID, Connection::getInstance($SITE_ID));
						$arResult['response'] = [
							'id' => $leadId,
						];

						$url = CRM\Acloud\Lead::getUrl($leadId);
						if (strlen($url)) {
							$arResult['response']['url'] = $connection->domain.$url;
						}
					} else {
						if (
							!strlen($FORM_ID) ||
							!strlen($RESULT_ID)
						) {
							throw new \Exception('Empty fields');
						}
						else {
							$leadId = CRM\Helper::sendFormResult($FORM_ID, $RESULT_ID, Connection::getInstance($SITE_ID));
							$arResult['response'] = [
								'id' => $leadId,
							];

							$url = CRM\Acloud\Lead::getUrl($leadId);
							if (strlen($url)) {
								$arResult['response']['url'] = $connection->domain.$url;
							}
						}
					}
				}
			}
			catch (\Exception $e) {
				$arResult['error'] = $e->getMessage();
			}

			echo json_encode($arResult);

			die();
		} else {
			foreach($arTabs as $key => $arTab){
				if(isset($arTab['LANDING_PAGE'])){
					continue;
				}

				$optionsSiteID = $arTab['SITE_ID'];
				$connection = $arTab['CONNECTION'];

				foreach($arTab['ITEMS'] as $groupCode => $arOptions){
					foreach($arOptions['ITEMS'] as $optionCode => $arOption){
						if(strlen($RestoreDefaults)){
							Option::delete($moduleID, array('name' => $optionCode));
						}
						else{
							if($optionCode == 'DOMAIN_ACLOUD'){
								$_POST[$optionCode.'_'.$optionsSiteID] = Connection::fixDomain($_POST[$optionCode.'_'.$optionsSiteID]);
							}

							if($arOption['TYPE'] == 'checkbox'){
								if(!isset($_POST[$optionCode.'_'.$optionsSiteID])){
									$_POST[$optionCode.'_'.$optionsSiteID] = 'N';
								}
							}

							if(isset($_POST[$optionCode.'_'.$optionsSiteID])){
								Option::set($moduleID, $optionCode, $_POST[$optionCode.'_'.$optionsSiteID], $optionsSiteID);
							}
						}
					}
				}

				$tested = false;
				try {
					// reload config after updating
					$connection->loadConfig();

					// try to connect
					$tested = $connection->try();
				}
				catch (\Exception $e) {
				}

				// set integration with crm
				$connection->tested = $tested;

				// set forms field matching
				if($_POST['CRM_FORM_FIELD_'.$optionsSiteID] && $_POST['CRM_FORM_FORM_FIELD_'.$optionsSiteID]){
					$arPostFields = [];
					foreach($_POST['CRM_FORM_FIELD_'.$optionsSiteID] as $formID => $arFields){
						$arPostFields[$formID] = [];
						foreach($arFields as $keyProp => $value){
							if (
								$value != 'no' &&
								$_POST['CRM_FORM_FORM_FIELD_'.$optionsSiteID][$formID][$keyProp]
							) {
								if (!array_key_exists($value, $arPostFields[$formID])) {
									$arPostFields[$formID][$value] = $_POST['CRM_FORM_FORM_FIELD_'.$optionsSiteID][$formID][$keyProp];
								} else {
									$arPostFields[$formID][$value] = array_merge((array)$arPostFields[$formID][$value], (array)$_POST['CRM_FORM_FORM_FIELD_'.$optionsSiteID][$formID][$keyProp]);
								}
							}
						}
					}

					$connection->forms_matches = $arPostFields;
				}

				// set orders field matching
				if ($_POST['CRM_ORDER_FIELD_'.$optionsSiteID] && $_POST['CRM_ORDER_FORM_FIELD_'.$optionsSiteID]) {
					$arPostFields = [];
					foreach ($_POST['CRM_ORDER_FIELD_'.$optionsSiteID] as $personTypeId => $arFields) {
						$arPostFields[$personTypeId] = [];
						foreach($arFields as $keyProp => $value){
							if (
								$value != 'no' &&
								$_POST['CRM_ORDER_FORM_FIELD_'.$optionsSiteID][$personTypeId][$keyProp]
							) {
								if (!array_key_exists($value, $arPostFields[$personTypeId])) {
									$arPostFields[$personTypeId][$value] = $_POST['CRM_ORDER_FORM_FIELD_'.$optionsSiteID][$personTypeId][$keyProp];
								} else {
									$arPostFields[$personTypeId][$value] = array_merge((array)$arPostFields[$personTypeId][$value], (array)$_POST['CRM_ORDER_FORM_FIELD_'.$optionsSiteID][$personTypeId][$keyProp]);
								}
							}
						}
					}

					$connection->orders_matches = $arPostFields;
				}
			}

			LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&lang='.urlencode(LANGUAGE_ID).'&back_url_settings='.urlencode($_REQUEST['back_url_settings']).'&'.$tabControl->ActiveTabParam());
			die();
		}
	}

	CJSCore::Init(array('jquery3'));
	?>
	<?if(!count($arTabs)):?>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?=Loc::getMessage('ASPRO_MAX_NO_SITE_INSTALLED', array('#SESSION_ID#'=>bitrix_sessid_get()))?></div>
				<div class="adm-info-message-icon"></div>
			</div>
			<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
				<span>
					<?=GetMessage('TABS_SETTINGS')?>
				</span>
			</a>
		</div>
	<?else:?>
		<form method="post" class="max_options" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?$tabControl->Begin();?>
			<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
				<span>
					<?=GetMessage('TABS_SETTINGS')?>
				</span>
			</a>
			<?
			foreach($arTabs as $key => $arTab){
				$bLanding = isset($arTab['LANDING_PAGE']) && $arTab['LANDING_PAGE'];
				$arTabViewOptions = array(
					'showTitle' => false,
					'className' => $bLanding ? 'adm-detail-content-without-bg' : '',
				);

				$tabControl->BeginNextTab($arTabViewOptions);

				if($bLanding){
					?><iframe frameborder="0" class="landing-acloud-frame" src="https://aspro.ru/mc/acloud/"></iframe><?
				}

				if($arTab['SITE_ID']){
					$optionsSiteID = $arTab['SITE_ID'];
					$connection = $arTab['CONNECTION'];
					if ($connection->tested) {
						$domain = $connection->domain;

						$leadFieldsMap = Lead::getFieldsMap($connection);
						$leadCustomFieldsMap = [];
						try {
							$leadCustomFieldsMap = Lead::getCustomFieldsMap($connection);
						}
						catch (\Exception $e) {
						}

						$formsFieldsMap = CRM\Helper::getFormsFieldsMap();
						$ordersFieldsMap = CRM\Helper::getOrdersFieldsMap();
					}
					?>
					<tr>
						<td colspan="2">
							<?
							$arSubTabs = array(
								array('DIV' => 'subedit1_'.$optionsSiteID,
									'TAB' => GetMessage('ASPRO_MAX_MAIN_SETTINGS'),
									'TITLE'=>Loc::getMessage('ASPRO_MAX_MODULE_CONFIG_ACLOUD'),
								),
							);

							if($connection->tested){
								$arSubTabs[] = array(
									'DIV' => 'subedit2_'.$optionsSiteID,
									'TAB' => GetMessage('ASPRO_MAX_FORM_SETTINGS'),
									'TITLE'=>Loc::getMessage('ASPRO_MAX_MODULE_FORMS_ACLOUD'),
								);
								$arSubTabs[] = array(
									'DIV' => 'subedit3_'.$optionsSiteID,
									'TAB' => GetMessage('ASPRO_MAX_ORDER_SETTINGS'),
									'TITLE'=>Loc::getMessage('ASPRO_MAX_MODULE_ORDERS_ACLOUD'),
								);
							}
							$subTabControl = new CAdminViewTabControl('subTabControl'.$optionsSiteID, $arSubTabs);
							$subTabControl->Begin();
							?>

							<?foreach($arTab['ITEMS'] as $groupCode => $arOptions):?>
								<?$subTabControl->BeginNextTab();?>

								<?if($groupCode === 'FORMS' && !$arTab['ITEMS']['FORMS']['ITEMS']['WEB_FORM_ACLOUD']['VALUES']):?>
									<table cellpadding="0" cellspacing="0" border="0" width="100%">
										<tr>
											<td colspan="2" style="width:100%;text-align:center;">
												<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_NO_FORMS')?></div>
											</td>
										</tr>
									</table>
									<?continue;?>
								<?endif;?>

								<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-sub-table">
									<?foreach($arOptions['ITEMS'] as $optionCode => $arOption):?>
										<?$value = ($arOption['TYPE'] == 'checkbox' ? 'Y' : $arOption['VALUE']);?>
										<?if(!isset($arOption['SKIP_CHECK']) && !$arTab["ITEMS"]["CONFIG"]["ITEMS"]["ACTIVE_LINK_ACLOUD"]["VALUE"]):?>
											<?continue;?>
										<?endif;?>

										<?if($arOption['TYPE'] == 'hidden'):?>
											<input type="<?=$arOption['TYPE']?>" size="50" maxlength="255" value="" name="<?=htmlspecialcharsbx($optionCode).'_'.$optionsSiteID?>">
										<?else:?>
											<?if($arOption['TYPE'] !== 'DINAMIC_ORDERS'):?>
												<tr>
													<td class="adm-detail-content-cell-l" style="width:50%;<?=($arOption['HINT'] ? 'vertical-align: top;padding-top: 7px;' : '')?>">
														<?=Loc::getMessage('ASPRO_MAX_MODULE_'.$optionCode)?>
													</td>
													<td style="width:50%;">
														<?if($arOption['TYPE'] == 'select'):?>
															<select name="<?=htmlspecialcharsbx($optionCode).'_'.$optionsSiteID?>">
																<?if($arOption['VALUES']):?>
																	<?foreach($arOption['VALUES'] as $key => $arForm):?>
																		<option <?=($key == $value ? 'selected' : '')?> value="<?=$key?>">[<?=$arForm['ID']?>] <?=$arForm['NAME']?></option>
																	<?endforeach;?>
																<?endif;?>
															</select>
														<?else:?>
															<input type="<?=$arOption['TYPE']?>" <?=($arOption['TYPE'] == 'checkbox' ? ($arOption['VALUE'] == 'Y' ? 'checked' : '') : '')?> size="60" maxlength="255" value="<?=htmlspecialcharsbx($value)?>" name="<?=htmlspecialcharsbx($optionCode).'_'.$optionsSiteID?>" <?=($optionCode == 'password' ? 'autocomplete="off"' : '')?>>
														<?endif;?>
														<?if($arOption['HINT']):?>
															<br/><small style="color: #777;"><?=$arOption['HINT']?></small>
														<?endif;?>
													</td>
												</tr>
											<?endif;?>

											<?if($optionCode === 'TOKEN_ACLOUD'):?>
												<tr>
													<td colspan="2" style="width:100%;text-align:center;">
														<input type="submit" class="check_auth" value="<?=Loc::getMessage('ASPRO_MAX_MODULE_CHECK_AUTH')?>" data-site_id="<?=$optionsSiteID?>" />
														<div><span class="response"></span></div>
													</td>
												</tr>
											<?endif;?>

											<?if(
												isset($arOption['DINAMIC_FORMS']) &&
												$arOption['DINAMIC_FORMS']
											):?>
												<?if($arOption['VALUES']):?>
													<tr>
														<td colspan="2">
															<?
															$aFormsTabs = array(
																array(
																	'DIV' => 'edit_forms_field_'.$optionsSiteID,
																	'TAB' => Loc::getMessage('ASPRO_MAX_MODULE_FIELDS_ACLOUD'),
																	'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_ALL_FIELDS_ACLOUD'),
																	'ICON' => 'settings',
																	'PAGE_TYPE' => 'site_settings',
																),
																array(
																	'DIV' => 'edit_forms_result_'.$optionsSiteID,
																	'TAB' => Loc::getMessage('ASPRO_MAX_MODULE_RESULTS_ACLOUD'),
																	'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_ALL_RESULTS_ACLOUD'),
																	'ICON' => 'settings',
																	'PAGE_TYPE' => 'site_settings',
																),
															);

															$formsTabControl = new CAdminViewTabControl('formsTabControl'.$optionsSiteID, $aFormsTabs);
															$formsTabControl->Begin();
															$formsTabControl->BeginNextTab();
															?>
															<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
																<?foreach($arOption['VALUES'] as $formId => $arForm):?>
																	<?
																	$formsQuestionsMap = CRM\Helper::getFormsQuestionsMap($formId);
																	$arFormMatches = $connection->forms_matches[$formId];
																	?>
																	<tr class="form_<?=$formId?>" <?=($formId != $value ? 'style="display: none;"' : '')?>>
																		<td colspan="2">
																			<table class="internal" style="width:100%;">
																				<thead>
																					<tr class="heading">
																						<td width="50%"><?=Loc::getMessage('CRM_FORM_FIELD_TABLE')?></td>
																						<td width="50%"><?=Loc::getMessage('FORM_FIELD_TABLE')?></td>
																						<td width="17"></td>
																					</tr>
																				</thead>
																				<tbody>
																					<?if($arFormMatches):?>
																						<?foreach($arFormMatches as $crm_FORM_field_id => $form_field_ids):?>
																							<?foreach((array)$form_field_ids as $form_field_id):?>
																								<tr>
																									<td class="adm-detail-content-cell-l" style="width:50%;">
																										<select name="CRM_FORM_FIELD_<?=$optionsSiteID?>[<?=$formId?>][]" class="field_crm" style="width:300px;">
																											<option value="no"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD_NO')?></option>
																											<?if($leadFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD')?>">
																													<?foreach($leadFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $crm_FORM_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																											<?if($leadCustomFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_CUSTOMFIELDS_LEAD')?>">
																													<?foreach($leadCustomFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $crm_FORM_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																										</select>
																									</td>
																									<td style="width:50%;">
																										<select name="CRM_FORM_FORM_FIELD_<?=$optionsSiteID?>[<?=$formId?>][]" class="field_form" style="width:300px;">
																											<option value=""><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																											<?if($formsFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_FIELDS_TITLE')?>">
																													<?foreach($formsFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $form_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																											<?if($formsQuestionsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_QUESTIONS_TITLE')?>">
																													<?foreach($formsQuestionsMap as $key2 => $text):?>
																														<option <?=($key2 == $form_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																										</select>
																									</td>
																									<td><a href="javascript:void(0)" title="<?=Loc::getMessage('DELETE_NODE')?>" class="form-action-button action-delete"></a></td>
																								</tr>
																							<?endforeach;?>
																						<?endforeach;?>
																					<?endif;?>
																					<tr>
																						<td class="adm-detail-content-cell-l" style="width:50%;">
																							<select name="CRM_FORM_FIELD_<?=$optionsSiteID?>[<?=$formId?>][]" class="field_crm" style="width:300px;">
																								<option value="no"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD_NO')?></option>
																								<?if($leadFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD')?>">
																										<?foreach($leadFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																								<?if($leadCustomFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_CUSTOMFIELDS_LEAD')?>">
																										<?foreach($leadCustomFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																							</select>
																						</td>
																						<td style="width:50%;">
																							<select name="CRM_FORM_FORM_FIELD_<?=$optionsSiteID?>[<?=$formId?>][]" class="field_form" style="width:300px;">
																								<option value=""><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																								<?if($formsFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_FIELDS_TITLE')?>">
																										<?foreach($formsFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																								<?if($formsQuestionsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_QUESTIONS_TITLE')?>">
																										<?foreach($formsQuestionsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																							</select>
																						</td>
																						<td></td>
																					</tr>
																				</tbody>
																				<tfoot>
																					<tr>
																						<td colspan="3"><input type="button" class="addbtn" value="<?=htmlspecialcharsbx(Loc::getMessage('FORM_CRM_ADD'))?>"></td>
																					</tr>
																				</tfoot>
																			</table>
																		</td>
																	</tr>
																<?endforeach;?>
															</table>

															<?$formsTabControl->BeginNextTab();?>

															<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
																<?foreach($arOption['VALUES'] as $formId => $arForm):?>
																	<?
																	$sTableID = 'tbl_admbp_forms_list_'.$optionsSiteID.'_'.$formId;
																	$oSort = new \CAdminSorting(
																		$sTableID,
																		's_id',
																		'desc',
																		$by_name = 'fby_'.$optionsSiteID.'_'.$formId,
																		$order_name = 'forder_'.$optionsSiteID.'_'.$formId
																	);

																	$formResults = CRM\Helper::getFormResults(
																		[$oSort->getField() => $oSort->getOrder()],
																		$formId
																	);

																	$arFormMatches = $connection->forms_matches[$formId];
																	?>
																	<tr class="form_<?=$formId?>" <?=($formId != $value ? 'style="display: none;"' : '')?>>
																		<td colspan="2">
																			<?if(!$arFormMatches || !$connection->active):?>
																				<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_NO_FORM_FIELD_MATCHING')?></div>
																			<?elseif(
																				$formResults &&
																				$formResults->SelectedRowsCount()
																			):?>
																				<?
																				$lAdmin = new \CAdminList($sTableID, $oSort);
																				$lAdmin->AddHeaders(
																					[
																						['id' => 'ID', 'content' => Loc::getMessage('CRM_RESULT_LIST_FORM_FIELD_ID'), 'sort' => 'ID', 'default' => true],
																						['id' => 'DATE_CREATE', 'content' => Loc::getMessage('CRM_RESULT_LIST_FORM_FIELD_DATE_CREATE'), 'sort' => 'DATE_CREATE', 'default' => true],
																						['id' => 'USER', 'content' => Loc::getMessage('CRM_RESULT_LIST_FORM_FIELD_USER'), 'sort' => false, 'default' => true],
																						['id' => 'LEAD', 'content' => Loc::getMessage('CRM_RESULT_LIST_FORM_FIELD_LEAD'), 'sort' => false, 'default' => true],
																					]
																				);

																				$rsData = new \CAdminResult($formResults, $sTableID);
																				$rsData->navStart();
																				$lAdmin->navText($rsData->getNavPrint(Loc::getMessage('FORM_RESULT_FIELD_TABLE')));
																				while ($arFormResult = $rsData->fetch()) {
																					$row =& $lAdmin->AddRow($arFormResult['ID'], $arFormResult);
																					$row->AddField('ID', '<a target="_blank" href="/bitrix/admin/form_result_edit.php?lang='.LANG_CHARSET.'&WEB_FORM_ID='.$formId.'&RESULT_ID='.$arFormResult['ID'].'&WEB_FORM_NAME='.$arForm['SID'].'">'.$arFormResult['ID'].'</a>');

																					if ($arFormResult['USER_ID']) {
																						$arUser = \CUser::getById($arFormResult['USER_ID'])->Fetch();
																						if ($arUser) {
																							$arUser = [
																								'[<a target="_blank" href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arFormResult['USER_ID'].'">'.$arFormResult['USER_ID'].'</a>]',
																								'('.$arUser['LOGIN'].')',
																								$arUser['LAST_NAME'],
																								$arUser['NAME'],
																							];
																							$row->AddField('USER', trim(implode(' ', $arUser)));
																						} else {
																							$row->AddField('USER', '[<a target="_blank" href="/bitrix/admin/user_edit.php?lang=ru&ID='.$arFormResult['USER_ID'].'">'.$arFormResult['USER_ID'].'</a>]');
																						}
																					} else {
																						$row->AddField('USER', Loc::getMessage('CRM_RESULT_LIST_FORM_FIELD_USER_NONE'));
																					}

																					$arSendingResult = CRM\Helper::getSendingFormResult($arFormResult['ID'], $optionsSiteID);
																					$leadId = intval(isset($arSendingResult['ACLOUD']) ? (is_array($arSendingResult['ACLOUD']) ? $arSendingResult['ACLOUD'][$domain] : $arSendingResult['ACLOUD']) : 0);
																					$bSended = $leadId > 0;
																					if ($bSended) {
																						$url = Lead::getUrl($leadId);
																						$url = $url ? '<a href="'.$domain.$url.'" target="_blank">'.$leadId.'</a>' : $leadId;
																						$row->AddField('LEAD', $url);
																					} else {
																						$row->AddField('LEAD', '<a href="javascript:void(0)" title="'.Loc::getMessage('SEND_CRM').'" data-form_id="'.$formId.'" data-result_id="'.$arFormResult['ID'].'" data-site_id="'.$optionsSiteID.'" class="adm-btn action-button action-send">'.Loc::getMessage('SEND_CRM').'</a>');
																					}
																				}

																				if (
																					isset($_REQUEST['mode']) &&
																					$_REQUEST['mode'] === 'list' &&
																					isset($_REQUEST['table_id']) &&
																					$_REQUEST['table_id'] == $sTableID
																				) {
																					$GLOBALS['APPLICATION']->RestartBuffer();
																					$lAdmin->CheckListMode();
																				}

																				$lAdmin->DisplayList();
																				?>
																			<?else:?>
																				<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_NO_FORM_RESULTS')?></div>
																			<?endif;?>
																		</td>
																	</tr>
																<?endforeach;?>
															</table>

															<?$formsTabControl->End();?>
														</td>
													</tr>
												<?endif;?>
											<?endif;?>

											<?if(
												isset($arOption['DINAMIC_ORDERS']) &&
												$arOption['DINAMIC_ORDERS']
											):?>
												<?if($arOption['VALUES']):?>
													<tr>
														<td colspan="2">
															<?
															$aOrdersTabs = array(
																array(
																	'DIV' => 'edit_orders_field_'.$optionsSiteID,
																	'TAB' => Loc::getMessage('ASPRO_MAX_MODULE_FIELDS_ACLOUD'),
																	'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_ALL_FIELDS_ORDERS_ACLOUD'),
																	'ICON' => 'settings',
																	'PAGE_TYPE' => 'site_settings',
																),
																array(
																	'DIV' => 'edit_orders_result_'.$optionsSiteID,
																	'TAB' => Loc::getMessage('ASPRO_MAX_MODULE_ORDERS_LIST_ACLOUD'),
																	'TITLE' => Loc::getMessage('ASPRO_MAX_MODULE_ORDERS_LIST_ACLOUD'),
																	'ICON' => 'settings',
																	'PAGE_TYPE' => 'site_settings',
																),
															);

															$orderTabControl = new CAdminViewTabControl('orderTabControl'.$optionsSiteID, $aOrdersTabs);
															$orderTabControl->Begin();
															$orderTabControl->BeginNextTab();
															?>
															<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
																<?foreach($arOption['VALUES'] as $personTypeId => $arPersonType):?>
																	<?
																	$ordersPropertiesMap = CRM\Helper::getOrdersPropertiesMap($personTypeId);
																	$arOrderMatches = $connection->orders_matches[$personTypeId];
																	?>
																	<tr class="order_<?=$personTypeId?>" <?=($personTypeId != $value ? 'style="display: none;"' : '')?>>
																		<td colspan="2">
																			<table class="internal" style="width:100%;">
																				<thead>
																					<tr class="heading">
																						<td width="50%"><?=Loc::getMessage('CRM_FORM_FIELD_TABLE')?></td>
																						<td width="50%"><?=Loc::getMessage('ORDER_FIELD_TABLE')?></td>
																						<td width="17"></td>
																					</tr>
																				</thead>
																				<tbody>
																					<?if($arOrderMatches):?>
																						<?foreach($arOrderMatches as $crm_ORDER_field_id => $order_field_ids):?>
																							<?foreach((array)$order_field_ids as $order_field_id):?>
																								<tr>
																									<td class="adm-detail-content-cell-l" style="width:50%;">
																										<select name="CRM_ORDER_FIELD_<?=$optionsSiteID?>[<?=$personTypeId?>][]" class="field_crm" style="width:300px;">
																											<option value="no"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD_NO')?></option>
																											<?if($leadFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD')?>">
																													<?foreach($leadFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $crm_ORDER_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																											<?if($leadCustomFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_CUSTOMFIELDS_LEAD')?>">
																													<?foreach($leadCustomFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $crm_ORDER_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																										</select>
																									</td>
																									<td style="width:50%;">
																										<select name="CRM_ORDER_FORM_FIELD_<?=$optionsSiteID?>[<?=$personTypeId?>][]" class="field_order" style="width:300px;">
																											<option value=""><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																											<?if($ordersFieldsMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_ORDER_FIELDS_TITLE')?>">
																													<?foreach($ordersFieldsMap as $key2 => $text):?>
																														<option <?=($key2 == $order_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																											<?if($ordersPropertiesMap):?>
																												<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_ORDER_PROPS_TITLE')?>">
																													<?foreach($ordersPropertiesMap as $key2 => $text):?>
																														<option <?=($key2 == $order_field_id ? 'selected' : '')?> value="<?=$key2?>"><?=$text?></option>
																													<?endforeach;?>
																												</optgroup>
																											<?endif;?>
																										</select>
																									</td>
																									<td><a href="javascript:void(0)" title="<?=Loc::getMessage('DELETE_NODE')?>" class="form-action-button action-delete"></a></td>
																								</tr>
																							<?endforeach;?>
																						<?endforeach;?>
																					<?endif;?>
																					<tr>
																						<td class="adm-detail-content-cell-l" style="width:50%;">
																							<select name="CRM_ORDER_FIELD_<?=$optionsSiteID?>[<?=$personTypeId?>][]" class="field_crm" style="width:300px;">
																								<option value="no"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD_NO')?></option>
																								<?if($leadFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_LEAD')?>">
																										<?foreach($leadFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																								<?if($leadCustomFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_CUSTOMFIELDS_LEAD')?>">
																										<?foreach($leadCustomFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																							</select>
																						</td>
																						<td style="width:50%;">
																							<select name="CRM_ORDER_FORM_FIELD_<?=$optionsSiteID?>[<?=$personTypeId?>][]" class="field_order" style="width:300px;">
																								<option value=""><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																								<?if($ordersFieldsMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_ORDER_FIELDS_TITLE')?>">
																										<?foreach($ordersFieldsMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																								<?if($ordersPropertiesMap):?>
																									<optgroup label="<?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_ORDER_PROPS_TITLE')?>">
																										<?foreach($ordersPropertiesMap as $key2 => $text):?>
																											<option value="<?=$key2?>"><?=$text?></option>
																										<?endforeach;?>
																									</optgroup>
																								<?endif;?>
																							</select>
																						</td>
																						<td></td>
																					</tr>
																				</tbody>
																				<tfoot>
																					<tr>
																						<td colspan="3"><input type="button" class="addbtn" value="<?=htmlspecialcharsbx(Loc::getMessage('FORM_CRM_ADD'))?>"></td>
																					</tr>
																				</tfoot>
																			</table>
																		</td>
																	</tr>
																<?endforeach;?>
															</table>

															<?$orderTabControl->BeginNextTab();?>

															<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
																<?foreach($arOption['VALUES'] as $personTypeId => $arPersonType):?>
																	<?
																	$sTableID = 'tbl_admbp_orders_list_'.$optionsSiteID.'_'.$personTypeId;
																	$oSort = new \CAdminSorting(
																		$sTableID,
																		'ID',
																		'desc',
																		$by_name = 'oby_'.$optionsSiteID.'_'.$personTypeId,
																		$order_name = 'oorder_'.$optionsSiteID.'_'.$personTypeId
																	);

																	$orders = CRM\Helper::getOrders(
																		[$oSort->getField() => $oSort->getOrder()],
																		$personTypeId,
																		$optionsSiteID
																	);

																	$arOrderMatches = $connection->orders_matches[$personTypeId];
																	?>
																	<tr class="order_<?=$personTypeId?>" <?=($personTypeId != $value ? 'style="display: none;"' : '')?>>
																		<td colspan="2">
																			<?if(!$arOrderMatches || !$connection->active):?>
																				<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_NO_ORDER_FIELD_MATCHING')?></div>
																			<?elseif(
																				$orders &&
																				$orders->getSelectedRowsCount()
																			):?>
																				<?
																				$lAdmin = new \CAdminList($sTableID, $oSort);
																				$lAdmin->AddHeaders(
																					[
																						['id' => 'ID', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_ID'), 'sort' => 'ID', 'default' => true],
																						['id' => 'ACCOUNT_NUMBER', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_ACCOUNT_NUMBER'), 'sort' => 'ACCOUNT_NUMBER', 'default' => true],
																						['id' => 'DATE_INSERT', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_DATE_INSERT'), 'sort' => 'DATE_INSERT', 'default' => true],
																						['id' => 'STATUS_ID', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_STATUS'), 'sort' => 'STATUS_ID', 'default' => true],
																						['id' => 'PAYED', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_PAYED'), 'sort' => 'PAYED', 'default' => true],
																						['id' => 'CANCELED', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_CANCELED'), 'sort' => 'CANCELED', 'default' => true],
																						['id' => 'PRICE', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_PRICE'), 'sort' => 'PRICE', 'default' => true],
																						['id' => 'LEAD', 'content' => Loc::getMessage('CRM_RESULT_LIST_ORDER_FIELD_LEAD'), 'sort' => false, 'default' => true],
																					]
																				);

																				$rsData = new \CAdminResult($orders, $sTableID);
																				$rsData->navStart();
																				$lAdmin->navText($rsData->getNavPrint(Loc::getMessage('ASPRO_MAX_MODULE_ORDERS_LIST_ACLOUD')));
																				while ($arOrder = $rsData->fetch()) {
																					$row =& $lAdmin->AddRow($arOrder['ID'], $arOrder);
																					$row->AddField('ID', '<a target="_blank" href="/bitrix/admin/sale_order_view.php?lang='.LANG_CHARSET.'&ID='.$arOrder['ID'].'"><b>¹'.$arOrder['ID'].'</b></a>');
																					$row->AddField('STATUS_ID', $arOrder['SALE_INTERNALS_ORDER_STATUS_NAME']);
																					$row->AddField('PAYED', Loc::getMessage($arOrder['PAYED'] === 'Y' ? 'VALUE_Y' : 'VALUE_N'));
																					$row->AddField('CANCELED', Loc::getMessage($arOrder['CANCELED'] === 'Y' ? 'VALUE_Y' : 'VALUE_N'));
																					$row->AddField('PRICE', CCurrencyLang::CurrencyFormat($arOrder['PRICE'], $arOrder['CURRENCY']));

																					$arSendingResult = CRM\Helper::getSendingOrderResult($arOrder['ID'], $optionsSiteID);
																					$leadId = intval(isset($arSendingResult['ACLOUD']) ? (is_array($arSendingResult['ACLOUD']) ? $arSendingResult['ACLOUD'][$domain] : $arSendingResult['ACLOUD']) : 0);
																					$bSended = $leadId > 0;
																					if ($bSended) {
																						$url = Lead::getUrl($leadId);
																						$url = $url ? '<a href="'.$domain.$url.'" target="_blank">'.$leadId.'</a>' : $leadId;
																						$row->AddField('LEAD', $url);
																					} else {
																						$row->AddField('LEAD', '<a href="javascript:void(0)" title="'.Loc::getMessage('SEND_CRM').'" data-order_id="'.$arOrder['ID'].'" data-site_id="'.$optionsSiteID.'" class="adm-btn action-button action-send">'.Loc::getMessage('SEND_CRM').'</a>');
																					}
																				}

																				if (
																					isset($_REQUEST['mode']) &&
																					$_REQUEST['mode'] === 'list' &&
																					isset($_REQUEST['table_id']) &&
																					$_REQUEST['table_id'] == $sTableID
																				) {
																					$GLOBALS['APPLICATION']->RestartBuffer();
																					$lAdmin->CheckListMode();
																				}

																				$lAdmin->DisplayList();
																				?>
																			<?else:?>
																				<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_NO_ORDERS')?></div>
																			<?endif;?>
																		</td>
																	</tr>
																<?endforeach;?>
															</table>

															<?$orderTabControl->End();?>
														</td>
													</tr>
												<?endif;?>
											<?endif;?>
										<?endif;?>
									<?endforeach;?>

									<?if(
										$groupCode == 'CONFIG' &&
										!$arOptions['ITEMS']['ACTIVE_LINK_ACLOUD']['VALUE']
									):?>
										<tr>
											<td colspan="2" style="width:100%;text-align:center;">
												<div class="adm-info-message"><?=Loc::getMessage('ASPRO_MAX_MODULE_INTEGRATION_ACLOUD')?></div>
											</td>
										</tr>
									<?endif;?>
								</table>
							<?endforeach;?>

							<?$subTabControl->End();?>
						</td>
					</tr>
				<?
				}
			}
			?>
			<?$tabControl->Buttons()?>
			<?$hideButton = $tabControl->GetSelectedTab() === 'landing_edit';?>
			<input
				<?if($RIGHT < 'W') echo 'disabled'?>
				type="submit"
				name="Apply"
				<?if($hideButton):?>style="visibility: hidden"<?endif;?>
				class="action-button submit-btn adm-btn-save"
				value="<?=Loc::getMessage('ASPRO_MAX_MODULE_SAVE_OPTION')?>"
				title="<?=Loc::getMessage('ASPRO_MAX_MODULE_SAVE_OPTION')?>"
			>
			<input
				type="submit"
				name="RestoreDefaults"
				<?if($hideButton):?>style="visibility: hidden"<?endif;?>
				class="action-button"
				title="<?=Loc::getMessage('ASPRO_MAX_MODULE_DELETE_OPTION')?>"
				onclick="confirm('<?=Loc::getMessage('ASPRO_MAX_MODULE_DELETE_OPTION_TITLE')?>')"
				value="<?=Loc::getMessage('ASPRO_MAX_MODULE_DELETE_OPTION_TEXT')?>"
			>

			<script type="text/javascript">
			function onMainTabSelected(tabId){
				var saveButtons = document.querySelectorAll('.action-button');
				if(saveButtons){
					for(var j=0; j<saveButtons.length; j++){
						if (tabId === 'landing'){
							saveButtons[j].style.visibility = 'hidden';
						} else {
							saveButtons[j].style.cssText = '';
						}
					}
				}
			}

			BX.message({
				'CRM_SEND': '<?=Loc::getMessage('FORM_RESULT_SEND')?>',
			});

			$(document).on('click', '.tmpl', function(e) {
				e.preventDefault();

				var tmpl = $(this).text();
				var $input = $(this).closest('td').find('input[type=text]');
				if ($input.length) {
					$input.val($input.val() + tmpl);
				}
			});

			$(document).on('click', 'input.addbtn', function(){
				var _table = $(this).closest('.internal');
				$(_table.find('tbody tr:last').clone()).insertAfter(_table.find('tbody tr:last'));
			});

			$(document).on('change', 'select.field_crm,select.field_form,select.field_order', function(){
				var $this = $(this),
					value = $this.val();
				if (
					value.length &&
					value !== 'no'
				) {
					var $tr = $this.closest('tr');
					var index = $tr.index();
					if ($tr.closest('tbody').find('tr').length == index + 1) {
						var $friend = $this.closest('td').siblings().find('select');
						if (
							$friend.length &&
							$friend.val().length &&
							$friend.val() !== 'no'
						) {
							var $table = $this.closest('.internal');
							$($table.find('tbody tr:last').clone()).insertAfter($table.find('tbody tr:last'));
						}
					}
				}
			});

			$(document).on('click', '.action-delete', function(){
				var _tr = $(this).closest('tr');
				_tr.remove();
			});

			$(document).on('click', '.action-send', function(){
				var _this = $(this),
					tr = _this.closest('tr');

				if(_this.data('disabled') != 'disabled'){
					_this.attr('data-disabled', 'disabled');

					var data = {
						sessid: $('input[name=sessid]').val(),
						SendCrm: true,
						SITE_ID: _this.data('site_id'),
						FORM_ID: _this.data('form_id'),
						RESULT_ID: _this.data('result_id'),
					};

					if (_this.data('order_id')) {
						data.ORDER_ID = _this.data('order_id');
					} else {
						data.FORM_ID = _this.data('form_id');
						data.RESULT_ID = _this.data('result_id');
					}

					_this.parent().find('.status_send').remove();

					$.ajax({
						type: 'POST',
						dataType: 'json',
						data: data,
						success: function(data){
							if (
								typeof data === 'object' &&
								data &&
								(
									'error' in data ||
									'response' in data
								)
							){
								if('error' in data){
									_this.parent().append('<div class="status_send error"><br />' + data.error + '</div>');
								}
								else if('response' in data){
									if (
										typeof data.response === 'object' &&
										data.response &&
										'id' in data.response &&
										'url' in data.response
									) {
										if ('url' in data.response) {
											_this.parent().html('<a href="' + data.response.url + '" target="_blank">' + data.response.id + '</a>');
										} else {
											_this.parent().html(data.response.id);
										}
									}
								}

								return true;
							}
						},
						error: function(data){
							window.console&&console.log(data);
							_this.parent().append('<div class="status_send error"><br />' + data.responseText + '</div>');
						},
						complete: function() {
							_this.removeAttr('data-disabled');
						}
					});
				}
			});

			$(document).on('click', 'input.check_auth', function(){
				var _this = $(this),
					form = _this.closest('form'),
					tab = _this.closest('.adm-detail-content');

				_this.attr('disabled', 'disabled');

				$.ajax({
					type: 'POST',
					dataType: 'json',
					data: {
						sessid: $('input[name=sessid]').val(),
						Auth: 1,
						DOMAIN: tab.find('input[name^=DOMAIN_ACLOUD]').val(),
						TOKEN: tab.find('input[name^=TOKEN_ACLOUD]').val(),
						SITE_ID: _this.data('site_id'),
					},
					success: function(data){
						if (
							typeof data === 'object' &&
							data &&
							(
								'error' in data ||
								'response' in data
							)
						){
							if('error' in data){
								$('.response').removeClass('success').addClass('error').text(data.error);
							}
							else if('response' in data){
								$('.response').removeClass('error').addClass('success').text('ok');
							}

							return true;
						}

						// other response error
						$('.response').removeClass('success').addClass('error').text('error');
					},
					error: function(data){
						console.error(data);
						$('.response').removeClass('success').addClass('error').text('error');
					},
					complete: function() {
						_this.removeAttr('disabled');
					}
				});
			});

			$(document).on('change', 'select[name^="WEB_FORM"]', function(){
				$('tr[class^="form_"]').hide();
				$('tr.form_'+$(this).val()).css('display', '');
			});

			$(document).on('change', 'select[name^="ORDER_PERSON_TYPE"]', function(){
				$('tr[class^="order_"]').hide();
				$('tr.order_'+$(this).val()).css('display', '');
			});

			$(document).ready(function(){
				$('select[name^="WEB_FORM"]').change();
				$('select[name^="ORDER_PERSON_TYPE"]').change();
			});
			</script>
			<?$tabControl->End();?>
		</form>
	<?endif;?>
	<?
}
else {
	echo CAdminMessage::ShowMessage(Loc::getMessage('NO_RIGHTS_FOR_VIEWING'));
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
