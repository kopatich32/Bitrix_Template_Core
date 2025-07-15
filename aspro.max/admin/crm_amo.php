<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = "CMax";
$moduleID = "aspro.max";
\Bitrix\Main\Loader::includeModule($moduleID);

use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Main\Web\Json,
	\Aspro\Functions\CAsproMaxCRM,
	CMax as Solution;

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT >= "R"){
	$GLOBALS['APPLICATION']->AddHeadScript('https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.16/clipboard.min.js');
	$GLOBALS['APPLICATION']->SetAdditionalCss('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/style.css");
	$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage("ASPRO_MAX_PAGE_TITLE"));

	$by = "id";
	$sort = "asc";

	$arSites = array();
	$db_res = CSite::GetList($by, $sort, array("ACTIVE"=>"Y"));
	while($res = $db_res->Fetch()){
		$arSites[] = $res;
	}

	$arTabsForView = COption::GetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', '');
	if($arTabsForView){
		$arTabsForView = explode(',' , $arTabsForView);
	}

	$arTabs = array();
	$bShowGenerate = false;
	foreach($arSites as $key => $arSite){
		if(!$arTabsForView || in_array($arSite['ID'], $arTabsForView)){
			$arSite['DIR'] = str_replace('//', '/', '/'.$arSite['DIR']);
			if(!strlen($arSite['DOC_ROOT'])){
				$arSite['DOC_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
			}
			$arSite['DOC_ROOT'] = str_replace('//', '/', $arSite['DOC_ROOT'].'/');
			$siteDir = str_replace('//', '/', $arSite['DOC_ROOT'].$arSite['DIR']);
			$optionsSiteID = $arSite["ID"];

			//get web forms
			$arItems = array();
			if(\Bitrix\Main\Loader::includeModule("form")){
				$rsForms = CForm::GetList($by = "s_id", $order = "ASC", array('ACTIVE' => 'Y', 'SITE' => array($optionsSiteID)), $is_filtered);
				while($arForm = $rsForms->Fetch()){
					$arItems[$arForm['ID']] = $arForm;
				}
			}

			$arTabs[] = array(
				"DIV" => "edit".($key+1),
				"TAB" => Loc::getMessage("MAIN_OPTIONS_SITE_ASPRO_TITLE", array("#SITE_NAME#" => $arSite["NAME"], "#SITE_ID#" => $arSite["ID"])),
				"ICON" => "settings",
				"PAGE_TYPE" => "site_settings",
				"SITE_ID" => $optionsSiteID,
				"SITE_DIR" => $arSite["DIR"],
				"SITE_DIR_FORMAT" => $siteDir,
				"FORMS" => $arItems,
				"ITEMS" => array(
					"CONFIG" => array(
						"TITLE" => Loc::getMessage("ASPRO_MAX_MODULE_CONFIG_AMO_CRM"),
						"ITEMS" => array(
							"DOMAIN_AMO_CRM" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "DOMAIN_AMO_CRM", "", $optionsSiteID),
								"HINT" => Loc::getMessage("ASPRO_MAX_MODULE_DOMAIN_HINT")
							),
							"INTEGRATION_NOTE_AMO_CRM" => array(
								"TYPE" => "note",
								"TITLE" => Loc::getMessage(
									"ASPRO_MAX_MODULE_INTEGRATION_NOTE_AMO_CRM",
									array(
										'#REDIRECT_URL#' => ($APPLICATION->IsHTTPS() ? 'https://': 'http://').$_SERVER['SERVER_NAME'],
									)
								),
							),
							"CLIENT_SECRET_AMO_CRM" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "CLIENT_SECRET_AMO_CRM", "", $optionsSiteID),
							),
							"CLIENT_ID_AMO_CRM" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "CLIENT_ID_AMO_CRM", "", $optionsSiteID)
							),
							"AUTH_CODE_AMO_CRM" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "AUTH_CODE_AMO_CRM", "", $optionsSiteID),
							),
						)
					),
					"LINK" => array(
						"TITLE" => Loc::getMessage("ASPRO_MAX_MODULE_LINK_AMO_CRM"),
						"ITEMS" => array(
							"ACTIVE_LINK_AMO_CRM" => array(
								"TYPE" => "hidden",
								"VALUE" => Option::get($moduleID, "ACTIVE_LINK_AMO_CRM", "", $optionsSiteID),
							),
							"ACTIVE_AMO_CRM" => array(
								"TYPE" => "checkbox",
								"VALUE" => Option::get($moduleID, "ACTIVE_AMO_CRM", "N", $optionsSiteID),
							),
							"AUTOMATE_SEND_AMO_CRM" => array(
								"TYPE" => "checkbox",
								"VALUE" => Option::get($moduleID, "AUTOMATE_SEND_AMO_CRM", "Y", $optionsSiteID),
								"HINT" => Loc::getMessage("ASPRO_MAX_MODULE_AUTOMATE_SEND_AMO_CRM_HINT")
							),
							"USE_LOG_AMO_CRM" => array(
								"TYPE" => "checkbox",
								"VALUE" => Option::get($moduleID, "USE_LOG_AMO_CRM", "N", $optionsSiteID),
								"HINT" => Loc::getMessage("ASPRO_MAX_MODULE_USE_LOG_AMO_CRM_HINT")
							),
							"LEAD_NAME_AMO_CRM_TITLE" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "LEAD_NAME_AMO_CRM_TITLE", Loc::getMessage("ASPRO_MAX_MODULE_LEAD_NAME_AMO_CRM"), $optionsSiteID),
							),
							"TAGS_AMO_CRM_TITLE" => array(
								"TYPE" => "text",
								"VALUE" => Option::get($moduleID, "TAGS_AMO_CRM_TITLE", Loc::getMessage("ASPRO_MAX_MODULE_TAGS_AMO_CRM"), $optionsSiteID),
							),
							"WEB_FORM_AMO_CRM" => array(
								"TYPE" => "select",
								"VALUE" => Option::get($moduleID, "WEB_FORM_AMO_CRM", "", $optionsSiteID),
								"VALUES" => $arItems,
								"DINAMIC_FORMS" => "Y",
							),
						)
					)
				),
			);
		}
	}

	$tabControl = new CAdminTabControl("tabControl", $arTabs);

	if($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults.$action) && $RIGHT >= "W" && check_bitrix_sessid()){
		global $APPLICATION, $CACHE_MANAGER;
		$APPLICATION->RestartBuffer();

		if($action === 'checkAuth'){
			$siteId = isset($_POST['siteId']) ? $_POST['siteId'] : '';
			$domain = isset($_POST['domain']) ? $_POST['domain'] : '';
			$clientSecret = isset($_POST['clientSecret']) ? $_POST['clientSecret'] : '';
			$clientId = isset($_POST['clientId']) ? $_POST['clientId'] : '';
			$authCode = isset($_POST['authCode']) ? $_POST['authCode'] : '';

			// new hash
			$hash = md5(serialize(array($domain, $clientSecret, $clientId, $authCode)));

			// restore saved
			$arOAuth = array();
			$arConfig = array(
				'type' => 'AMO_CRM',
				'siteId' => $siteId,
			);
			CAsproMaxCRM::restore(
				$arOAuth,
				$arConfig
			);

			// if options was changed, than delete access token
			if(
				$hash !== $arConfig['hash']
			){
				$arOAuth['accessToken'] = '';
				$arOAuth['authCode'] = isset($_POST['authCode']) ? $_POST['authCode'] : '';

				$arConfig = array(
					'type' => 'AMO_CRM',
					'siteId' => $siteId,
					'domain' => $domain,
					'clientSecret' => $clientSecret,
					'clientId' => $clientId,
					'redirectUrl' => ($APPLICATION->IsHTTPS() ? 'https://': 'http://').$_SERVER['SERVER_NAME'],
				);
			}

			if(strlen($arConfig['domain'])){
				// check auth
				$arResponse = CAsproMaxCRM::checkOAuth($arOAuth, $arConfig);
				if(
					$arResponse &&
					is_array($arResponse) &&
					!$arResponse['error']
				){
					// if auth is ok, than save
					Option::set($moduleID, 'ACTIVE_LINK_AMO_CRM', 'Y', $siteId);
					CAsproMaxCRM::save($arOAuth, $arConfig);
				}
			}
			else{
				$arResponse = array(
					'error' => 'Empty account domain. Enter your account in amoCRM',
				);
			}

			echo Json::encode($arResponse);
			die();
		}
		elseif($action === 'sendCrm'){
			$siteId = isset($_POST['siteId']) ? $_POST['siteId'] : false;
			$formId = isset($_POST['formId']) ? intval($_POST['formId']) : false;
			$resultId = isset($_POST['resultId']) ? intval($_POST['resultId']) : false;

			if(
				strlen($siteId) &&
				$formId &&
				$resultId
			){
				$dataDeal = array(
					'name' => iconv(LANG_CHARSET, 'UTF-8', 'Текст')
				);
				$data['request']['leads']['add'] = array($dataDeal);

				echo \Aspro\Functions\CAsproMax::sendLeadCrmFromForm($formId, $resultId, 'AMO_CRM', $siteId, false, false);
			}
			else{
				$arResponse = array(
					'error' => 'empty fields',
				);
				echo Json::encode($arResponse);
			}

			die();
		}
		else{
			foreach($arTabs as $key => $arTab){
				$optionsSiteID = $arTab["SITE_ID"];
				foreach($arTab["ITEMS"] as $groupCode => $arOptions){
					foreach($arOptions["ITEMS"] as $optionCode => $arOption){
						if($arOption['TYPE'] !== 'note'){
							if(strlen($RestoreDefaults)){
								Option::delete($moduleID, array("name" => $optionCode));
							}
							else{
								if($arOption["TYPE"] == "checkbox"){
									if(!isset($_POST[$optionCode."_".$optionsSiteID])){
										$_POST[$optionCode."_".$optionsSiteID] = "N";
									}
								}

								if(isset($_POST[$optionCode."_".$optionsSiteID])){
									Option::set($moduleID, $optionCode, $_POST[$optionCode."_".$optionsSiteID], $optionsSiteID);
								}
							}
						}
					}
				}

				// restore saved
				$arOAuth = array();
				$arConfig = array(
					'type' => 'AMO_CRM',
					'siteId' => $optionsSiteID,
				);
				CAsproMaxCRM::restore(
					$arOAuth,
					$arConfig
				);

				// new options hash
				$hash = md5(serialize(array($arConfig['domain'], $arConfig['clientSecret'], $arConfig['clientId'], $arOAuth['authCode'])));

				// if options was changed, than delete access token
				if($hash !== $arConfig['hash']){
					$arOAuth['accessToken'] = '';
				}

				// check auth
				if(
					strlen($arOAuth['authCode']) &&
					strlen($arConfig['domain'])
				){
					$arResponse = CAsproMaxCRM::checkOAuth($arOAuth, $arConfig);
					$bOAuthChecked = $arResponse && is_array($arResponse) && !$arResponse['error'];
				}
				else{
					$bOAuthChecked = false;
				}
				Option::set($moduleID, 'ACTIVE_LINK_AMO_CRM', ($bOAuthChecked ? 'Y' : ''), $optionsSiteID);
				CAsproMaxCRM::save($arOAuth, $arConfig);

				//set field matching
				if($_POST['CRM_FIELD_'.$optionsSiteID] && $_POST['CRM_FORM_FIELD_'.$optionsSiteID]){
					foreach($_POST['CRM_FIELD_'.$optionsSiteID] as $formID => $arFields){
						$arPostFields = array();
						foreach($arFields as $keyProp => $value){
							if($_POST['CRM_FORM_FIELD_'.$optionsSiteID][$formID][$keyProp]){
								$arPostFields[$value] = $_POST['CRM_FORM_FIELD_'.$optionsSiteID][$formID][$keyProp];
							}
						}

						Option::set($moduleID, 'AMO_CRM_FIELDS_MATCH_'.$formID, serialize($arPostFields), $optionsSiteID);
					}
				}

				//set fields array from amo crm
				if(isset($_POST['CUSTOM_FIELD_AMO_CRM_'.$optionsSiteID])){
					Option::set($moduleID, 'CUSTOM_FIELD_AMO_CRM', urldecode($_POST['CUSTOM_FIELD_AMO_CRM_'.$optionsSiteID]), $optionsSiteID);
				}
			}
		}

		// send statistics
		// if(\Aspro\Max\GS::isEnabled()){
		// 	if(\Aspro\Max\GS::register()){
		// 		\Aspro\Max\GS::sendData(
		// 			\Aspro\Max\GS::mkData(array('sites', 'options'))
		// 		);
		// 	}
		// }
	}

	CJSCore::Init(array("jquery3"));?>
	<?if(!count($arTabs)):?>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?=Loc::getMessage("ASPRO_MAX_NO_SITE_INSTALLED", array("#SESSION_ID#"=>bitrix_sessid_get()))?></div>
				<div class="adm-info-message-icon"></div>
			</div>
			<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
				<span>
					<?=GetMessage('TABS_SETTINGS')?>
				</span>
			</a>
		</div>
	<?else:?>
		<?$tabControl->Begin();?>
		<?$bShowBtn = true;?>
		<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
			<span>
				<?=GetMessage('TABS_SETTINGS')?>
			</span>
		</a>
		<form method="post" class="max_options" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
		<?=bitrix_sessid_post();?>
		<?
		foreach($arTabs as $key => $arTab)
		{
			$tabControl->BeginNextTab();
			if($arTab["SITE_ID"])
			{
				$optionsSiteID = $arTab["SITE_ID"];

				// restore saved
				$arOAuth = array();
				$arConfig = array(
					'type' => 'AMO_CRM',
					'siteId' => $optionsSiteID,
				);
				CAsproMaxCRM::restore(
					$arOAuth,
					$arConfig
				);

				// check auth
				if(
					strlen($arOAuth['authCode']) &&
					strlen($arConfig['domain'])
				){
					$arResponse = CAsproMaxCRM::checkOAuth($arOAuth, $arConfig);
					$bOAuthChecked = $arResponse && is_array($arResponse) && !$arResponse['error'];
					$arHeaders = array(
						'Authorization' => 'Bearer '.$arOAuth['accessToken']
					);
				}
				else{
					$bOAuthChecked = false;
					$arHeaders = array();
				}
				?>
				<?if(!$arTab["FORMS"]):?>
					<tr>
						<td colspan="2" style="width:100%;text-align:center;">
							<div class="adm-info-message"><?=Loc::getMessage("ASPRO_MAX_MODULE_NO_FORMS");?></div>
						</td>
					</tr>
					<?continue;?>
				<?endif;?>
				<?foreach($arTab["ITEMS"] as $groupCode => $arOptions):?>
					<?if($groupCode == "LINK"):?>
						<tr>
							<td colspan="2" style="width:100%;text-align:center;">
								<input type="submit" class="check_auth" value="<?=Loc::getMessage("ASPRO_MAX_MODULE_CHECK_AUTH");?>"/>
								<div><span class="response"></span></div>
							</td>
						</tr>
					<?endif;?>

					<tr class="heading"><td colspan="2"><?=Loc::getMessage("ASPRO_MAX_MODULE_".$groupCode."_AMO_CRM");?></td></tr>

					<?if($groupCode == "LINK" && !$bOAuthChecked):?>
						<tr>
							<td colspan="2" style="width:100%;text-align:center;">
								<div class="adm-info-message"><?=Loc::getMessage("ASPRO_MAX_MODULE_INTEGRATION_AMO_CRM");?></div>
							</td>
						</tr>
						<?continue;?>
					<?endif;?>

					<?foreach($arOptions["ITEMS"] as $optionCode => $arOption):?>
						<?if($arOption["TYPE"] === 'note'):?>
							<tr>
								<td colspan="2" style="width:100%;text-align:center;">
									<div class="adm-info-message"><?=$arOption['TITLE']?></div>
								</td>
							</tr>
							<?continue;?>
						<?endif;?>
						<?
						$bAuthCode = $optionCode === 'AUTH_CODE_AMO_CRM';
						$value = ($arOption["TYPE"] == "checkbox" ? "Y" : $arOption["VALUE"]);

						if($arOption["TYPE"] == "hidden"):?>
							<input type="<?=$arOption["TYPE"];?>" size="50" maxlength="255" value="" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>">
						<?else:?>
							<tr>
								<td class="adm-detail-content-cell-l" style="width:50%;<?=($arOption["HINT"] ? "vertical-align: top;padding-top: 7px;" : "");?>">
									<?=Loc::getMessage("ASPRO_MAX_MODULE_".$optionCode);?>
								</td>
								<td style="width:50%;">
									<?if($arOption["TYPE"] == "select"):?>
										<select name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>">
											<?if($arOption["VALUES"])
											{
												foreach($arOption["VALUES"] as $key => $arForm):?>
													<option <?=($key == $value ? "selected" : "");?> value="<?=$key?>">[<?=$arForm['ID'];?>] <?=$arForm['NAME'];?></option>
												<?endforeach;
											}?>
										</select>
									<?else:?>
										<input type="<?=$arOption["TYPE"];?>" <?=($arOption["TYPE"] == "checkbox" ? ($arOption["VALUE"] == "Y" ? "checked" : "") : "");?> size="60" maxlength="<?=($bAuthCode ? 1000 : 255)?>" value="<?=htmlspecialcharsbx($value)?>" name="<?=htmlspecialcharsbx($optionCode)."_".$optionsSiteID?>" <?=($bAuthCode && $bOAuthChecked ? 'readonly' : '')?>>
									<?endif;?>
									<?if($arOption["HINT"]):?>
										<br/><small style="color: #777;"><?=$arOption["HINT"];?></small>
									<?endif;?>
								</td>
							</tr>
							<?if(isset($arOption["DINAMIC_FORMS"]) && $arOption["DINAMIC_FORMS"]):?>
								<?if($arOption["VALUES"]):?>
									<tr>
										<td colspan="2">
											<?
											$aSiteTabs = array(
												array(
													"DIV" => "edit_forms_field_".$optionsSiteID,
													"TAB" => Loc::getMessage("ASPRO_MAX_MODULE_FIELDS_AMO_CRM"),
													"TITLE" => Loc::getMessage("ASPRO_MAX_MODULE_ALL_FIELDS_AMO_CRM"),
													"ICON" => "settings",
													"PAGE_TYPE" => "site_settings",
												),
												array(
													"DIV" => "edit_forms_result_".$optionsSiteID,
													"TAB" => Loc::getMessage("ASPRO_MAX_MODULE_RESULTS_AMO_CRM"),
													"TITLE" => Loc::getMessage("ASPRO_MAX_MODULE_ALL_RESULTS_AMO_CRM"),
													"ICON" => "settings",
													"PAGE_TYPE" => "site_settings",
												),
											);

											if($bOAuthChecked){
												$url = str_replace('#DOMAIN#', Option::get($moduleID, 'DOMAIN_AMO_CRM', '', $optionsSiteID), \Aspro\Functions\CAsproMaxCRM::AMO_CRM_PATH);
												$result_text = \Aspro\Functions\CAsproMaxCRM::query($url, "/private/api/v2/json/accounts/current/", array(), $arHeaders, false, true);
												$arResponse = Json::decode($result_text, true);
												if($arResponse)
												{
													if(isset($arResponse["response"]["account"]) && (isset($arResponse["response"]["account"]["custom_fields"]) && $arResponse["response"]["account"]["custom_fields"]))
													{
														$arCrmOptions = array();
														foreach($arResponse["response"]["account"]["custom_fields"] as $codeGroup => $arGroup)
														{
															if($codeGroup != "customers")
															{
																foreach($arGroup as $arProp)
																{
																	$arCrmOptions[$codeGroup][$arProp["id"]]["CODE"] = $arProp["code"];
																	if(isset($arProp["enums"]))
																	{
																		$arCrmOptions[$codeGroup][$arProp["id"]]["ENUMS"] = $arProp["enums"];
																		foreach($arProp["enums"] as $keyEnum => $enum)
																		{
																			\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["AMO_CRM"][$codeGroup]["PROPS"][$arProp["id"]."_".$keyEnum."_".$codeGroup] = (Loc::getMessage("AMO_CRM_FIELD_".$arProp["code"]."_".$enum) ? Loc::getMessage("AMO_CRM_FIELD_".$arProp["code"]."_".$enum) : $arProp["name"]."_".$enum);
																		}
																	}
																	else
																	{
																		\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["AMO_CRM"][$codeGroup]["PROPS"][$arProp["id"]."_".$codeGroup] = $arProp["name"];
																	}
																}
															}
														}
													}
													if($arCrmOptions):?>
														<input type="hidden" value="<?=urlencode(serialize($arCrmOptions));?>" name="CUSTOM_FIELD_AMO_CRM_<?=$optionsSiteID?>">
													<?endif;
												}
											}
											?>

											<?$siteTabControl = new CAdminViewTabControl("siteTabControl".$optionsSiteID, $aSiteTabs);
											$siteTabControl->Begin();?>

											<?$siteTabControl->BeginNextTab();?>
												<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
													<?foreach($arOption["VALUES"] as $key => $arForm):?>
														<?
														$arFields = array();
														$rsQuestions = CFormField::GetList($key, "ALL", $by = "id", $order = "desc", array("ACTIVE" => "Y"), $is_filtered);
														while($arQuestion = $rsQuestions->Fetch())
														{
															$arFields[$arQuestion["ID"]] = $arQuestion;
														}
														$arValueForm = Solution::unserialize(Option::get($moduleID, "AMO_CRM_FIELDS_MATCH_".$key, "", $optionsSiteID));
														?>
														<tr class="form_<?=$key;?>" <?=($key != $value ? "style='display: none;'" : '');?>>
															<td colspan="2">
																<table class="internal" style="width:100%;">
																	<thead>
																		<tr class="heading">
																			<td width="50%"><?=Loc::getMessage("CRM_FIELD_TABLE")?></td>
																			<td width="50%"><?=Loc::getMessage("FORM_FIELD_TABLE")?></td>
																			<td width="17"></td>
																		</tr>
																	</thead>
																	<tbody>
																		<?if($arValueForm)
																		{
																			foreach($arValueForm as $crm_field_id => $form_field_id):?>
																				<tr>
																					<td class="adm-detail-content-cell-l" style="width:50%;">
																						<select name="CRM_FIELD_<?=$optionsSiteID?>[<?=$key;?>][]" class="field_crm" style="width:300px;">
																							<option value=""><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																							<?foreach(\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["AMO_CRM"] as $groupCode => $arGroup):?>
																								<optgroup label="<?=Loc::getMessage($groupCode."_FIELD_CODE_AMO_CRM");?>">
																									<?foreach($arGroup["PROPS"] as $key2 => $text):?>
																										<option <?=($key2 == $crm_field_id ? "selected" : "");?> value="<?=$key2?>">
																											<?=($text ? $text : Loc::getMessage($key2."_AMO_CRM"));?>
																										</option>
																									<?endforeach;?>
																								</optgroup>
																							<?endforeach;?>
																						</select>
																					</td>
																					<td style="width:50%;">
																						<select name="CRM_FORM_FIELD_<?=$optionsSiteID?>[<?=$key;?>][]" class="field_form" style="width:300px;">
																							<option value=""><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																							<optgroup label="...">
																								<?foreach(\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["MAIN"] as $key2 => $text):?>
																									<option <?=($key2 == $form_field_id ? "selected" : "");?> value="<?=$key2?>"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_'.$key2)?></option>
																								<?endforeach;?>
																							</optgroup>
																							<optgroup label="...">
																								<?foreach($arFields as $key2 => $arQuestion):?>
																									<option <?=($key2 == $form_field_id ? "selected" : "");?> value="<?=$key2?>"><?=$arQuestion["TITLE"];?> (<?=$arQuestion["SID"];?>)</option>
																								<?endforeach;?>
																							</optgroup>
																						</select>
																					</td>
																					<td><a href="javascript:void(0)" title="<?=Loc::getMessage("DELETE_NODE")?>" class="form-action-button action-delete"></a></td>
																				</tr>
																			<?endforeach;
																		}?>
																		<tr>
																			<td class="adm-detail-content-cell-l" style="width:50%;">
																				<select name="CRM_FIELD_<?=$optionsSiteID?>[<?=$key;?>][]" class="field_crm" style="width:300px;">
																					<option value=""><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																					<?foreach(\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["AMO_CRM"] as $groupCode => $arGroup):?>
																						<optgroup label="<?=Loc::getMessage($groupCode."_FIELD_CODE_AMO_CRM");?>">
																							<?foreach($arGroup["PROPS"] as $key2 => $text):?>
																								<option value="<?=$key2?>">
																									<?=($text ? $text : Loc::getMessage($key2."_AMO_CRM"));?>
																								</option>
																							<?endforeach;?>
																						</optgroup>
																					<?endforeach;?>
																				</select>
																			</td>
																			<td style="width:50%;">
																				<select name="CRM_FORM_FIELD_<?=$optionsSiteID?>[<?=$key;?>][]" class="field_form" style="width:300px;">
																					<option value=""><?=GetMessage('FORM_FIELD_CRM_FIELDS_FORM_NAME_NO')?></option>
																					<optgroup label="...">
																						<?foreach(\Aspro\Functions\CAsproMaxCRM::$arCrmFileds["MAIN"] as $key2 => $text):?>
																							<option value="<?=$key2?>"><?=Loc::getMessage('FORM_FIELD_CRM_FIELDS_'.$key2)?></option>
																						<?endforeach;?>
																					</optgroup>
																					<optgroup label="...">
																						<?foreach($arFields as $key2 => $arQuestion):?>
																							<option value="<?=$key2?>"><?=$arQuestion["TITLE"];?> (<?=$arQuestion["SID"];?>)</option>
																						<?endforeach;?>
																					</optgroup>
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
											<?$siteTabControl->BeginNextTab();?>
												<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
													<?foreach($arOption["VALUES"] as $key => $arForm):?>
														<?$arFormResults = array();
														$rsFormResults = CFormResult::GetList($key, $by = 's_id', $order = 'asc', array(), $is_filtered, 'N', false);
														while($arFormResult = $rsFormResults->Fetch())
														{
															$arFormResults[] = $arFormResult;
														}
														$arValueForm = Solution::unserialize(Option::get($moduleID, "AMO_CRM_FIELDS_MATCH_".$key, "", $optionsSiteID));?>
														<tr class="form_<?=$key;?>" <?=($key != $value ? "style='display: none;'" : '');?>>
															<td colspan="2">
																<?if(!$arValueForm || !$bOAuthChecked):?>
																	<div class="adm-info-message"><?=Loc::getMessage("ASPRO_MAX_MODULE_NO_FORM_FIELD_MATCHING");?></div>
																<?elseif($arFormResults):?>
																	<table class="internal" style="width:100%;">
																		<thead>
																			<tr class="heading">
																				<td width="100%"><?=Loc::getMessage("FORM_RESULT_FIELD_TABLE")?></td>
																				<td width="17"></td>
																			</tr>
																		</thead>
																		<tbody>
																			<?foreach($arFormResults as $arFormResult):?>
																				<?$arStatus = Solution::unserialize(Option::get($moduleID, 'CRM_SEND_FORM_'.$arFormResult["ID"], 'a:0:{}', $optionsSiteID));
																				$bSend = (isset($arStatus["AMO_CRM"]) && $arStatus["AMO_CRM"]);?>
																				<tr>
																					<td>
																						<a href="/bitrix/admin/form_result_edit.php?lang=<?=LANGUAGE_ID;?>&WEB_FORM_ID=<?=$arForm["ID"];?>&RESULT_ID=<?=$arFormResult["ID"];?>&WEB_FORM_NAME=<?=$arForm["SID"];?>">
																							<?=$arFormResult["ID"];?>
																						</a>
																						<?=Loc::getMessage('FORM_RESULT_INFO', array("DATE_CREATE" => $arFormResult["DATE_CREATE"], "TIMESTAMP_X" => $arFormResult["TIMESTAMP_X"]))?> <span class="status_send <?=($bSend ? "success" : "error");?>"><?=($bSend ? Loc::getMessage('FORM_RESULT_SEND') : Loc::getMessage('FORM_RESULT_NO_SEND'))?></span></td>
																					<td>
																						<?if(!$bSend):?>
																							<a href="javascript:void(0)" title="<?=Loc::getMessage("SEND_CRM")?>" data-form_id="<?=$key;?>" data-result_id="<?=$arFormResult["ID"]?>" data-site_id="<?=$optionsSiteID;?>" class="form-action-button action-send"></a>
																						<?endif;?>
																					</td>
																				</tr>
																			<?endforeach;?>
																		</tbody>
																	</table>
																<?else:?>
																	<div class="adm-info-message"><?=Loc::getMessage("ASPRO_MAX_MODULE_NO_FORM_RESULTS");?></div>
																<?endif;?>
															</td>
														</tr>
													<?endforeach;?>
												</table>
											<?$siteTabControl->End();?>
										</td>
									</tr>
								<?endif;?>
							<?endif;?>
						<?endif;?>
					<?endforeach;?>
				<?endforeach;?>
			<?}
		}?>
		<?
		if($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) && check_bitrix_sessid())
		{
			if(strlen($Update) && strlen($_REQUEST["back_url_settings"]))
				LocalRedirect($_REQUEST["back_url_settings"]);
			else
				LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		}?>
			<?$tabControl->Buttons();?>

			<input <?if($RIGHT < "W") echo "disabled"?> type="submit" name="Apply" class="submit-btn adm-btn-save" value="<?=Loc::getMessage("ASPRO_MAX_MODULE_SAVE_OPTION")?>" title="<?=Loc::getMessage("ASPRO_MAX_MODULE_SAVE_OPTION")?>">
			<input type="submit" name="RestoreDefaults" title="<?=Loc::getMessage("ASPRO_MAX_MODULE_DELETE_OPTION")?>" onclick="confirm('<?=Loc::getMessage("ASPRO_MAX_MODULE_DELETE_OPTION_TITLE")?>')" value="<?=Loc::getMessage("ASPRO_MAX_MODULE_DELETE_OPTION_TEXT")?>">

			<script type="text/javascript">
				BX.message({
					"CRM_SEND": "<?=Loc::getMessage("FORM_RESULT_SEND")?>",
					"ASPRO_MAX_MODULE_LINK_COPY": "<?=Loc::getMessage("ASPRO_MAX_MODULE_LINK_COPY")?>",
					"ASPRO_MAX_MODULE_LINK_COPYED": "<?=Loc::getMessage("ASPRO_MAX_MODULE_LINK_COPYED")?>",
					"ASPRO_MAX_MODULE_LINK_COPY_ERROR": "<?=Loc::getMessage("ASPRO_MAX_MODULE_LINK_COPY_ERROR")?>",
				});

				$(document).ready(function(){
					var $btnCopyLink = $('.btn_copy_link');
					if($btnCopyLink.length){
						var copyLinkTimeout = false
						clipboard = new Clipboard('.btn_copy_link');
						clipboard.on('success', function(e){
						    $btnCopyLink.html('<i class="fa fa-check"></i>' + BX.message('ASPRO_MAX_MODULE_LINK_COPYED'))

							if(copyLinkTimeout){
								clearTimeout(copyLinkTimeout)
								copyLinkTimeout = false
							}

							copyLinkTimeout = setTimeout(function(){
								$btnCopyLink.html('<i class="fa fa-clipboard"></i>' + BX.message('ASPRO_MAX_MODULE_LINK_COPY'))
							}, 2000)

						    e.clearSelection()
						});

						clipboard.on('error', function(e){
						    alert(BX.message('ASPRO_MAX_MODULE_LINK_COPY_ERROR') + $btnCopyLink.attr('data-clipboard-text'))
						});
					}

					$('input.addbtn').on('click', function(){
						var _table = $(this).closest('.internal');
						$(_table.find('tbody tr:last').clone()).insertAfter(_table.find('tbody tr:last'));
					});

					$('.action-delete').on('click', function(){
						var _tr = $(this).closest('tr');
						_tr.remove();
					});

					$('.action-send').on('click', function(){
						var _this = $(this),
							tr = _this.closest('tr');
						if(_this.data('disabled') != 'disabled'){
							_this.attr('data-disabled', 'disabled');

							tr.find('.status_send').empty();

							$.ajax({
								type: 'POST',
								dataType: 'json',
								data: {
									'action': 'sendCrm',
									'sessid': $('input[name=sessid]').val(),
									'siteId': _this.data('site_id'),
									'formId': _this.data('form_id'),
									'resultId': _this.data('result_id')
								},
								success: function(data){
									if('response' in data){
										data = data.response;
									}

									if('error' in data){
										tr.find('.status_send').removeClass('success').addClass('error').text(data.error);
									}
									else{
										tr.find('.status_send').removeClass('error').addClass('success').text(BX.message("CRM_SEND"));
										_this.remove();
									}
								},
								error: function(data){
									window.console&&console.log(data);
									tr.find('.status_send').removeClass('success').addClass('error').text(data.responseText);
								},
								complete: function(){
									_this.removeAttr('data-disabled');
								}
							});
						}
					});

					$('input.check_auth').on('click', function(e){
						e.preventDefault();

						var _this = $(this),
							form = _this.closest('form');
						_this.attr('disabled', 'disabled');

						var siteId = form.find('input[name^=DOMAIN_AMO_CRM]').attr('name').replace(/.+_(.{2})$/, '$1');

						form.find('.response').empty();

						$.ajax({
							type: 'POST',
							dataType: 'json',
							data: {
								sessid: $('input[name=sessid]').val(),
								action: 'checkAuth',
								siteId: siteId,
								domain: form.find('input[name^=DOMAIN_AMO_CRM]').val(),
								clientSecret: form.find('input[name^=CLIENT_SECRET_AMO_CRM]').val(),
								clientId: form.find('input[name^=CLIENT_ID_AMO_CRM]').val(),
								authCode: form.find('input[name^=AUTH_CODE_AMO_CRM]').val()
							},
							success: function(data){
								if('response' in data){
									data = data.response;
								}

								if('error' in data){
									form.find('.response').removeClass('success').addClass('error').text(data.error);
									form.find('input[name^=AUTH_CODE_AMO_CRM]').prop('readonly', false);
								}
								else{
									form.find('.response').removeClass('error').addClass('success').text('ok');
									form.find('input[name^=AUTH_CODE_AMO_CRM]').prop('readonly', true);
								}
							},
							error: function(data){
								window.console&&console.log(data);
								form.find('.response').removeClass('success').addClass('error').text('error');
							},
							complete: function(){
								_this.removeAttr('disabled');
							}
						});
					});

					$('select[name^="WEB_FORM_AMO_CRM"]').on('change', function(){
						$('tr[class^="form_"]').hide();
						$('tr.form_'+$(this).val()).css('display','');
					});

					$('select[name^="WEB_FORM_AMO_CRM"]').change();
				});

			</script>
		</form>
		<?$tabControl->End();?>
	<?endif;?>
<?
}
else
{
	echo CAdminMessage::ShowMessage(Loc::getMessage('NO_RIGHTS_FOR_VIEWING'));
}?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>
