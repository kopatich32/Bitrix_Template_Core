<?
/**
 * Aspro:Max
 * @copyright 2019 Aspro
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

define('HELP_FILE', 'settings/wizard_list.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/wizard.php');

IncludeModuleLangFile(__FILE__);
$moduleName = 'aspro.max';
$errorMessage = '';

if($GLOBALS['USER']->IsAdmin() && $GLOBALS['USER']->CanDoOperation('edit_php')){
	if(\Bitrix\Main\Loader::includeModule($moduleName)){
		if($obModule = CModule::CreateModuleObject($moduleName)){
			$moduleClass = $obModule::moduleClass;
			$moduleClassPreset = $obModule::moduleClassPreset;
			$moduleTitle = $obModule->MODULE_NAME;
			$solutionName = $obModule::solutionName;
			$partnerName = $obModule::partnerName;
		}

		if(strlen($moduleClassPreset) && class_exists($moduleClassPreset)){
			$arWizardsIds = array();
			$arWizards = CWizardUtil::GetWizardList(false, true);
			if(is_array($arWizards)){
				$arWizardsIds = array_column($arWizards, 'ID');
			}

			$bInstalled = in_array($partnerName.':'.$solutionName, $arWizardsIds);
			$bExists = $bInstalled || in_array($moduleName.':'.$partnerName.':'.$solutionName, $arWizardsIds);

			if($bExists){
				if(strlen($thematic = isset($_REQUEST['thematic']) ? strval(htmlspecialcharsbx($_REQUEST['thematic'])) : false)){
					$arThematics = $moduleClassPreset::$arThematicsList;
					if(isset($arThematics[$thematic])){
						if(strlen($preset = isset($_REQUEST['preset']) ? intval($_REQUEST['preset']) : false)){
							$arPresets = $moduleClassPreset::$arPresetsList;
							if(isset($arPresets[$preset])){
								if(in_array($preset, $arThematics[$thematic]['PRESETS']['LIST'])){
									if($bGetForm = isset($_POST['action']) && $_POST['action'] === 'getform'){
										$arSites = array();
										$dbRes = CSite::GetList($by = 'sort', $order = 'desc', array('ACTIVE' => 'Y'));
										while($arSite = $dbRes->Fetch()){
											$arSites[] = $arSite;
										}

										$thematicTitle = $arThematics[$thematic]['TITLE'];
										?>
										<div class="title"><?=GetMessage('PREPARE_WIZARD_TITLE', array('#THEMATIC#' => htmlspecialcharsbx($thematicTitle), '#MODULE_NAME#' => $moduleTitle))?></div>
										<blockquote><?=GetMessage('PREPARE_WIZARD_NOTE')?></blockquote>
										<form action="<?=$_SERVER['REQUEST_URI']?>" name="wizard" method="POST" enctype="application/x-www-form-urlencoded">
											<input type="hidden" name="thematic" value="<?=$thematic?>" />
											<input type="hidden" name="preset" value="<?=$preset?>" />
											<input type="hidden" name="createSite" value="N" />
											<div class="variants">
												<div class="variant active">
													<div class="checkbox"></div>
													<div class="subtitle"><?=GetMessage('PREPARE_WIZARD_INSTALL_TO_CURRENT_SITE_TITLE')?></div>
													<div class="note"><?=GetMessage('PREPARE_WIZARD_INSTALL_TO_CURRENT_SITE_NOTE')?></div>
													<div class="form-control">
														<div class="label_block">
															<label><?=GetMessage('PREPARE_WIZARD_SELECT_SITE')?></label>
															<select class="required" name="siteId">
															<?foreach($arSites as $arSite):?>
																<option value="<?=$arSite['LID']?>">[<?=$arSite['LID']?>] <?=$arSite['NAME']?></option>
															<?endforeach;?>
															</select>
														</div>
													</div>
												</div>
												<div class="variant">
													<div class="checkbox"></div>
													<div class="subtitle"><?=GetMessage('PREPARE_WIZARD_INSTALL_TO_NEW_SITE_TITLE')?></div>
													<div class="note"><?=GetMessage('PREPARE_WIZARD_INSTALL_TO_NEW_SITE_NOTE')?></div>
													<div class="row">
														<div class="col-md-4">
															<div class="form-control">
																<div class="label_block">
																	<label><?=GetMessage('PREPARE_WIZARD_ID')?><span class="star">*</span></label>
																	<input type="text" name="siteNewID" value="" placeholder="s2" />
																</div>
															</div>
														</div>
														<div class="col-md-8">
															<div class="form-control">
																<div class="label_block">
																	<label><?=GetMessage('PREPARE_WIZARD_DIRECTORY')?><span class="star">*</span></label>
																	<input type="text" name="siteDir" value="" placeholder="/site_s2/" />
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="actions">
												<div class="btn btn-default white" data-action="close"><?=GetMessage('PREPARE_WIZARD_BACK')?></div><input type="submit" class="btn btn-default" value="<?=GetMessage('PREPARE_WIZARD_INSTALL')?>" />
											</div>
										</form>
										<script>
										$(document).ready(function(){
											/*$('.style-switcher .contents.wizard').mCustomScrollbar({
												mouseWheel: {
													scrollAmount: 150,
													preventDefault: true
												}
											});*/

											$('.style-switcher .contents.wizard .variant').click(function(){
												$('.style-switcher .contents.wizard label.error').remove();
												$('.style-switcher .contents.wizard .error').removeClass('error');
												$(this).find('select,input').addClass('required');
												$(this).addClass('active').siblings().removeClass('active').find('select,input').removeClass('required');
												$('.style-switcher .contents.wizard input[name=createSite]').val($(this).index() ? 'Y' : 'N');
											});

											$('.style-switcher .contents.wizard .btn[data-action=close]').click(function(){
												$('.style-switcher .contents.wizard').removeClass('active');
											});

                                            BX.Aspro.Loader.addExt('validate').then(() => {
                                                $('.style-switcher .contents.wizard form').validate({
                                                    highlight: function(element){
                                                        $(element).parent().addClass('error');
                                                    },
                                                    unhighlight: function(element){
                                                        $(element).parent().removeClass('error');
                                                    },
                                                    submitHandler: function(form){
                                                        if($('.style-switcher .contents.wizard form').valid()){
                                                            setTimeout(function() {
                                                                $(form).find('btn').prop('disabled', true);
                                                            }, 300);

                                                            var data = $(form).serializeArray();
                                                            $.ajax({
                                                                url: $(form).attr('action'),
                                                                type: 'POST',
                                                                data: data,
                                                                success: function(response){
                                                                    var data = false;
                                                                    try{
                                                                        data = $.parseJSON(response);

                                                                        if(typeof data === 'object' && data.URL){
                                                                            location.href = data.URL;
                                                                        }
                                                                    }
                                                                    catch(e){
                                                                        // here response as html
                                                                        $('.style-switcher .contents.wizard').html(response);
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    },
                                                    errorPlacement: function( error, element ){
                                                        error.insertBefore(element);
                                                    }
                                                });
                                            });

										});
										</script>
										<?
									}
									else{
										$arResult = array(
											'ERROR' => &$errorMessage,
										);

										$bCreateSite = isset($_REQUEST['createSite']) ? $_REQUEST['createSite'] === 'Y' : false;

										if(strlen($siteId = $bCreateSite ? (isset($_REQUEST['siteNewID']) ? strval(htmlspecialcharsbx($_REQUEST['siteNewID'])) : false) : (isset($_REQUEST['siteId']) ? strval(htmlspecialcharsbx($_REQUEST['siteId'])) : false))){
											$dbRes = CSite::GetList($by = 'sort', $order = 'asc', array());
											while($arSite = $dbRes->Fetch()){
												$arSites[$arSite['LID']] = $arSite;
											}

											if($bCreateSite){
												if(!isset($arSites[$siteId])){
													if(strlen($siteDir = isset($_REQUEST['siteDir']) ? strval(htmlspecialcharsbx($_REQUEST['siteDir'])) : false)){
														$arResult['URL'] = '/bitrix/admin/wizard_install.php?lang='.LANGUAGE_ID.'&wizardName='.($bInstalled ? '' : $moduleName.':').str_replace('.', ':', $moduleName).'&siteId='.$siteId.'&createSite=Y&siteDir='.$siteDir.'&templateID='.$partnerName.'_'.$solutionName.'&thematic='.$thematic.'&preset='.$preset.'&'.bitrix_sessid_get();
													}
													else{
														$errorMessage = GetMessage('EMPTY_SITE_DIR');
													}
												}
												else{
													$errorMessage = GetMessage('SITE_IS_ALLREADY_EXISTS', array(
														'#SITE_ID#' => $siteId,
													));
												}
											}
											else{
												if(isset($arSites[$siteId])){
													$arResult['URL'] = '/bitrix/admin/wizard_install.php?lang='.LANGUAGE_ID.'&wizardName='.($bInstalled ? '' : $moduleName.':').str_replace('.', ':', $moduleName).'&siteId='.$siteId.'&createSite=N&templateID='.$partnerName.'_'.$solutionName.'&thematic='.$thematic.'&preset='.$preset.'&'.bitrix_sessid_get();
												}
												else{
													$errorMessage = GetMessage('SITE_IS_NOT_EXISTS', array(
														'#SITE_ID#' => $siteId,
													));
												}
											}
										}
										else{
											$errorMessage = GetMessage('EMPTY_SITE_ID');
										}

										if(!strlen($errorMessage)){
											echo \Bitrix\Main\Web\Json::encode($arResult);
										}
									}
								}
								else{
									$errorMessage = GetMessage('BAD_THEMATIC_PRESET', array(
										'#THEMATIC#' => $thematic,
										'#PRESET#' => $preset,
									));
								}
							}
							else{
								$errorMessage = GetMessage('BAD_PRESET', array(
									'#PRESET#' => $preset,
								));
							}
						}
						else{
							$errorMessage = GetMessage('EMPTY_PRESET');
						}
					}
					else{
						$errorMessage = GetMessage('BAD_THEMATIC', array(
							'#THEMATIC#' => $thematic,
						));
					}
				}
				else{
					$errorMessage = GetMessage('EMPTY_THEMATIC');
				}
			}
			else{
				$errorMessage = GetMessage('BAD_MODULE_WIZARD', array(
					'#MODULE_ID#' => $moduleName,
				));
			}
		}
		else{
			$errorMessage = GetMessage('BAD_MODULE_CLASS', array(
				'#MODULE_ID#' => $moduleName,
			));
		}
	}
	else{
		$errorMessage = GetMessage('MODULE_REQUIRED', array(
			'#MODULE_ID#' => $moduleName,
		));
	}
}
else{
	$errorMessage = GetMessage('WIZARD_ACCESS_DENIED');
}

if(strlen($errorMessage)){
	?>
	<div class="alert alert-danger" role="alert"><?=$errorMessage?></div>
	<div class="actions">
		<div class="btn btn-default white" data-action="close"><?=GetMessage('PREPARE_WIZARD_BACK')?></div>
	</div>
	<script>
	$(document).ready(function(){
		/*$('.style-switcher .contents.wizard').mCustomScrollbar({
			mouseWheel: {
				scrollAmount: 150,
				preventDefault: true
			}
		});*/

		$('.style-switcher .contents.wizard .btn[data-action=close]').click(function(){
			$('.style-switcher .contents.wizard').removeClass('active');
		});
	});
	</script>
	<?
}
