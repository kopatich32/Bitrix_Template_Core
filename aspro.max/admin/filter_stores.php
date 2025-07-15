<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = "CMax";
$moduleID = "aspro.max";
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule($moduleID);


use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Localization\Loc,
	\Aspro\Max\Stores\Property;

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if ($RIGHT >= "R") {
	// ajax action
	if (
		$_SERVER['REQUEST_METHOD'] === 'POST' &&
		isset($_POST['action']) &&
		in_array($_POST['action'], array('sync_product', 'create_prop'))
	) {
		$APPLICATION->RestartBuffer();
		// sleep(5);
		// ajax result
		$arResult = [];
		try {
			if (
				check_bitrix_sessid()
			) {
				$siteId = $_POST["config"]["SITE_ID"] ?? 's1';
				$iblockId = \Bitrix\Main\Config\Option::get($moduleID, 'CATALOG_IBLOCK_ID', CMaxCache::$arIBlocks[$siteId]['aspro_max_catalog']['aspro_max_catalog'][0], $siteId);

				if ($iblockId) {

					if ($_POST['action'] === "sync_product") {
						$sectionId = intval($_POST["config"]["SECTION_ID"]);
						$arCatalog = CCatalog::GetByID($iblockId);

						if ($arCatalog) {
							$bUpdate = $_POST["step"] === 'update';
							if ($_POST["step"] === 'check') {
								$propCode = Property::getStoresFilterPropCode();

								$bPropExists = Property::checkPropStores($iblockId);
								if (!$bPropExists) {
									throw new \Exception(Loc::getMessage('ASPRO_PROP_NOT_EXIST', [
										"#PROP_CODE#" => $propCode,
										"#IBLOCK_ID#" => $iblockId,
									]));
								}

								$bHLExists = Property::checkHLStores();
								if (!$bHLExists) {
									throw new \Exception(Loc::getMessage('ASPRO_PROP_NO_HL_TABLE', ["#ASPRO_HL_TABLE#" => Property::STORES_HL_NAME]));
								}

								$bPropLinkHL = Property::checkPropTableStores($iblockId);
								if (!$bPropLinkHL) {
									throw new \Exception(Loc::getMessage('ASPRO_PROP_NOT_TABLE', [
										"#PROP_CODE#" => $propCode,
										"#IBLOCK_ID#" => $iblockId,
										"#ASPRO_HL_TABLE#" => Property::STORES_HL_NAME,
									]));
								}

								//check offers
								$offersIblockId = 0;
								$arCatalog = CCatalog::GetByID($iblockId);

								if ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
									$offersIblockId = $arCatalog["OFFERS_IBLOCK_ID"];

									$bPropOfferExists = Property::checkPropStores($offersIblockId);
									if (!$bPropOfferExists) {
										throw new \Exception(Loc::getMessage('ASPRO_PROP_NOT_EXIST', [
											"#PROP_CODE#" => $propCode,
											"#IBLOCK_ID#" => Loc::getMessage('ASPRO_SYNC_OFFERS_TITLE') . $offersIblockId,
										]));
									}

									$bPropOfferLinkHL = Property::checkPropTableStores($offersIblockId);
									if (!$bPropOfferLinkHL) {
										throw new \Exception(Loc::getMessage('ASPRO_PROP_NOT_TABLE', [
											"#PROP_CODE#" => $propCode,
											"#IBLOCK_ID#" => $offersIblockId,
											"#ASPRO_HL_TABLE#" => Property::STORES_HL_NAME,
										]));
									}
								}

								$arResult = [
									'bar_title' => Loc::getMessage('ASPRO_SYNC_HL_STORES'),
									'nextStep' => 'sync_stores',
								];
							} else if ($_POST["step"] === 'sync_stores') {

								Property::syncStores();

								$arResult = [
									'bar_title' => Loc::getMessage('ASPRO_SYNC_PRODUCT_PROP_STORES'),
									'nextStep' => 'update',
								];
							} else if ($bUpdate) {
								$arFilter = array("IBLOCK_ID" => $iblockId);
								if ($sectionId) {
									$arFilter["SECTION_ID"] = $sectionId;
									$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
								}

								$pageSize = intval($_POST["config"]["SYNC_PAGE_SIZE"] ?? 100);

								$pageNum = intval($_POST["config"]["SYNC_PAGE_NUM"] ?? 1);
								$arNavParams = false;
								if ($pageSize > 0) {
									$arNavParams = [
										"nPageSize" => $pageSize,
										"iNumPage" => $pageNum,
										"checkOutOfRange" => true
									];
								}

								$rsItems = CIBlockElement::GetList(["ID" => "ASC"], $arFilter, false, $arNavParams, array("ID", "ACTIVE"));
								$countElementsOnPage = 0;

								while ($arItem = $rsItems->Fetch()) {
									Property::setStoreFilterProp(["PRODUCT_ID" => $arItem["ID"], "FULL_CALC" => true]);
									$countElementsOnPage++;
								}

								if ($arNavParams && intval($countElementsOnPage) > 0) {
									$arResult = [
										//'title' => Loc::getMessage('ASPRO_SMARTSEO_CLEAR'),
										'nextStep' => 'update',
										'nextPage' => $pageNum + 1,
										'addValue' => $countElementsOnPage
									];
									if ($pageNum == 1) {
										$countAllElements = $rsItems->SelectedRowsCount();
										$arResult['maxValue'] = $countAllElements;
									}
								} else {
									$arResult = [
										'title' => Loc::getMessage('ASPRO_OPERATION_COMPLETE'),
										'nextStep' => 'finish',
									];

									//clear cache
									Property::clearPublicCache($siteId);
								}
							}
						} else {
							throw new \Exception(Loc::getMessage('MAX_MODULE_NO_CATALOG_IBLOCK_ID'));
						}
					} else if ($_POST['action'] === "create_prop") {
						$propCode = Property::getStoresFilterPropCode();
						$titleResponse = '';

						if (!Property::checkPropStores($iblockId)) {
							if (!Property::checkHLStores()) {
								Property::createHLBlockStores();
							}

							if (Property::checkHLStores()) {
								Property::createPropertyStores($iblockId);
							}
							$titleResponse .= Loc::getMessage('ASPRO_CREATE_PROP_STORES_COMPLETE', [
								"#PROP_CODE#" => $propCode,
								"#IBLOCK_ID#" => $iblockId,
							]);
						} else {
							$titleResponse .= Loc::getMessage('ASPRO_PROP_STORES_ALREADY_EXIST', [
								"#PROP_CODE#" => $propCode,
								"#IBLOCK_ID#" => $iblockId,
							]);
						}

						$offersIblockId = 0;
						$arCatalog = CCatalog::GetByID($iblockId);
						if ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
							$offersIblockId = $arCatalog["OFFERS_IBLOCK_ID"];

							if (!Property::checkPropStores($offersIblockId)) {
								if (!Property::checkHLStores()) {
									Property::createHLBlockStores();
								}

								if (Property::checkHLStores()) {
									Property::createPropertyStores($offersIblockId, false);
								}

								$titleResponse .= "<br>" . Loc::getMessage('ASPRO_CREATE_PROP_STORES_COMPLETE', [
									"#PROP_CODE#" => $propCode,
									"#IBLOCK_ID#" => $offersIblockId,
								]);
							} else {
								$titleResponse .= "<br>" . Loc::getMessage('ASPRO_PROP_STORES_ALREADY_EXIST', [
									"#PROP_CODE#" => $propCode,
									"#IBLOCK_ID#" => $offersIblockId,
								]);
							}
						}

						$arResult = [
							'title' => $titleResponse,
							'nextStep' => 'finish',
						];
					}
				} else {
					throw new \Exception(Loc::getMessage('MAX_MODULE_NO_IBLOCK_ID'));
				}
			}
		} catch (\Exception $e) {
			$arResult['errors'] = $e->getMessage();
			$arResult['title'] = Loc::getMessage('ASPRO_MAX_SYNC_ERROR');
		}


		echo \Bitrix\Main\Web\Json::encode($arResult);
		die();
	}


	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/" . $moduleID . "/style.css");
	$GLOBALS['APPLICATION']->SetTitle(GetMessage("MAX_PAGE_TITLE"));

	$by = "sort";
	$sort = "asc";

	$arSites = array();
	$db_res = CSite::GetList($by, $sort, array("ACTIVE" => "Y"));
	while ($res = $db_res->Fetch()) {
		$arSites[] = $res;
	}

	$arTabs = array();

	$arTabsForView = COption::GetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', '');
	if ($arTabsForView) {
		$arTabsForView = explode(',', $arTabsForView);
	}


	foreach ($arSites as $key => $arSite) {
		if ($arTabsForView) {
			if (in_array($arSite['ID'], $arTabsForView)) {
				$optionsSiteID = $arSite['ID'];

				$arTabs[] = array(
					'DIV' => 'edit' . ($key + 1),
					'TAB' => GetMessage('MAIN_OPTIONS_SITE_TITLE', array('#SITE_NAME#' => $arSite['NAME'], '#SITE_ID#' => $arSite['ID'])),
					'ICON' => 'settings',
					'PAGE_TYPE' => 'site_settings',
					'SITE_ID' => $arSite['ID'],
					'OPTIONS' => [],
				);
			}
		} else if (
			Option::get($moduleID, "SITE_INSTALLED", "N", $arSite["ID"]) == 'Y'
		) {
			$optionsSiteID = $arSite['ID'];

			$arTabs[] = array(
				'DIV' => 'edit' . ($key + 1),
				'TAB' => GetMessage('MAIN_OPTIONS_SITE_TITLE', array('#SITE_NAME#' => $arSite['NAME'], '#SITE_ID#' => $arSite['ID'])),
				'ICON' => 'settings',
				'PAGE_TYPE' => 'site_settings',
				'SITE_ID' => $arSite['ID'],
				'OPTIONS' => [],
			);
		}
	}

	$tabControl = new CAdminTabControl("tabControl", $arTabs); ?>

	<div class="adm-info-message"><?=GetMessage("MAX_MODULE_SYNC_TOP_NOTE");?></div>

	<? if ($REQUEST_METHOD == "POST" && $RIGHT >= "W" && check_bitrix_sessid()) {
		global $APPLICATION, $CACHE_MANAGER;
		if ($_POST["Apply"]) {
			foreach ($arTabs as $key => $arTab) {
				$optionsSiteID = $arTab['SITE_ID'];

				if ($_POST["USE_STORES_FILTER_" . $optionsSiteID] && $_POST["USE_STORES_FILTER_" . $optionsSiteID] == "Y") {
					if(Option::get($moduleID, "USE_STORES_FILTER", "N", $optionsSiteID) !== "Y"){
						Option::set($moduleID, "USE_STORES_FILTER", "Y", $optionsSiteID);

						//clear cache
						Property::clearPublicCache($optionsSiteID);
					}
				} else {
					if (Option::get($moduleID, "USE_STORES_FILTER", "N", $optionsSiteID) !== "N") {
						Option::set($moduleID, "USE_STORES_FILTER", "N", $optionsSiteID);

						//clear cache
						Property::clearPublicCache($optionsSiteID);
					}
				}

				if ($_POST["EVENT_SYNC_PRODUCT_STORES_" . $optionsSiteID] && $_POST["EVENT_SYNC_PRODUCT_STORES_" . $optionsSiteID] == "Y") {
					Option::set($moduleID, "EVENT_SYNC_PRODUCT_STORES", "Y", $optionsSiteID);
				} else {
					Option::set($moduleID, "EVENT_SYNC_PRODUCT_STORES", "N", $optionsSiteID);
				}
			}

			LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&save=Y&" . $tabControl->ActiveTabParam());
		}
		if ($_POST["Generate"]) {
		}
		// $APPLICATION->RestartBuffer();
	}

	CJSCore::Init(array("jquery3"));
	?>
	<? if (!count($arSites)) : ?>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?= GetMessage("ASPRO_MAX_NO_SITE_INSTALLED", array("#SESSION_ID#" => bitrix_sessid_get())) ?></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	<? else : ?>
		<? if (htmlspecialcharsbx($_REQUEST["success"])) : ?>
			<? echo CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("MAX_MODULE_SYNC_OK"), "TYPE" => "OK")); ?>
		<? endif; ?>
		<? if (htmlspecialcharsbx($_REQUEST["save"])) : ?>
			<? echo CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("MAX_MODULE_SAVE_OK"), "TYPE" => "OK")); ?>
		<? endif; ?>
		<? $tabControl->Begin(); ?>
		<a href="aspro.max_options_tabs.php" id="tabs_settings" target="_blank">
			<span>
				<?= GetMessage('TABS_SETTINGS') ?>
			</span>
		</a>
		<form method="post" class="max_options sync-stores-filter" enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>">
			<?= bitrix_sessid_post(); ?>
			<? foreach ($arTabs as $key => $arTab) : ?>
				<? $tabControl->BeginNextTab(["className" => "tab_site_" . $arTab['SITE_ID']]); ?>
				<? if ($arTab['SITE_ID']) : ?>
					<? $optionsSiteID = $arTab['SITE_ID']; ?>
					<tr class="heading">
						<td colspan="2"><?= GetMessage("MAX_MODULE_SETTINGS"); ?></td>
					</tr>
					<tr>
						<td>
							<?= GetMessage("MAX_MODULE_USE_STORES_FILTER_TITLE"); ?>
						</td>
						<td style="width:50%;">
							<input type="checkbox" id="USE_STORES_FILTER_<?= $optionsSiteID ?>" name="USE_STORES_FILTER_<?= $optionsSiteID ?>" value="Y" <?= (Option::get($moduleID, "USE_STORES_FILTER", "N", $optionsSiteID) == "Y" ? "checked" : ""); ?> class="adm-designed-checkbox">
							<label class="adm-designed-checkbox-label" for="USE_STORES_FILTER_<?= $optionsSiteID ?>" title=""></label>
						</td>
					</tr>
					<tr>
						<td>
							<?= GetMessage("MAX_MODULE_EVENT_SYNC_PRODUCT_STORES_TITLE"); ?>
						</td>
						<td style="width:50%;">
							<input type="checkbox" id="EVENT_SYNC_PRODUCT_STORES_<?= $optionsSiteID ?>" name="EVENT_SYNC_PRODUCT_STORES_<?= $optionsSiteID ?>" value="Y" <?= (Option::get($moduleID, "EVENT_SYNC_PRODUCT_STORES", "N", $optionsSiteID) == "Y" ? "checked" : ""); ?> class="adm-designed-checkbox">
							<label class="adm-designed-checkbox-label" for="EVENT_SYNC_PRODUCT_STORES_<?= $optionsSiteID ?>" title=""></label>
						</td>
					</tr>

					<tr>
						<td colspan="2"><div class="adm-info-message" style="margin:0 auto;"><?=GetMessage("MAX_MODULE_AUTO_SYNC_NOTE");?></div></td>
					</tr>

					<tr class="heading">
						<td colspan="2"><?= GetMessage("MAX_MODULE_SYNC_TITLE"); ?></td>
					</tr>
					<tr>
						<td>
							<?= GetMessage("MAX_MODULE_STORES_FILTER_PROP_CODE_TITLE"); ?>
						</td>
						<td style="width:50%;">
							<span><?= Option::get($moduleID, "STORES_FILTER_PROP_CODE", Property::STORES_PROP_CODE); ?></span>
						</td>
					</tr>
					<tr>
						<td>
							<?= GetMessage("MAX_MODULE_IBLOCK_ID"); ?>
						</td>
						<td style="width:50%;">
							<?
							$catalogIblockId = \Bitrix\Main\Config\Option::get($moduleID, 'CATALOG_IBLOCK_ID', CMaxCache::$arIBlocks[$optionsSiteID]['aspro_max_catalog']['aspro_max_catalog'][0], $optionsSiteID);

							$catalogTitle = "id {$catalogIblockId}";
							if ($catalogIblockId) {
								$dbCatalog = CIBlock::GetByID($catalogIblockId);
								if ($arCatalog = $dbCatalog->Fetch()) {
									$catalogTitle = "(" . $arCatalog["ID"] . ") " . $arCatalog["NAME"] . " [" . $arCatalog["CODE"] . "]";
								}
							}
							?>
							<span><?= $catalogTitle; ?></span>
						</td>
					</tr>

					<tr>
						<td>
							<?= GetMessage("MAX_MODULE_SYNC_STEP"); ?>
						</td>
						<td style="width:50%;">
							<input type="text" name="SYNC_PAGE_SIZE" value="50" />

						</td>
					</tr>

					<tr class="button-sync-wrap" data-site-id="<?= $optionsSiteID ?>">
						<td>
							<? //=GetMessage("MAX_MODULE_IBLOCK_SECTION_ID");
							?>
						</td>
						<td style="width:50%;">

							<?
							$propCode = Property::getStoresFilterPropCode();
							$arMessageError = [];

							$bHLExists = Property::checkHLStores();
							if (!$bHLExists) {
								Property::createHLBlockStores();
							}

							$bNeedCreateProp = false;
							$bShowSyncButton = true;

							$arCatalog = CCatalog::GetByID($catalogIblockId);
							$catalogName = "{$arCatalog['NAME']} ({$catalogIblockId})";

							$bPropExists = Property::checkPropStores($catalogIblockId);
							if (!$bPropExists) {
								$bNeedCreateProp = true;
								$arMessageError[] = Loc::getMessage('ASPRO_PROP_NOT_EXIST', [
									"#PROP_CODE#" => $propCode,
									"#IBLOCK_ID#" => $catalogName,
								]);
							} else {
								$bPropLinkHL = Property::checkPropTableStores($catalogIblockId);
								if (!$bPropLinkHL) {
									$arMessageError[] = Loc::getMessage('ASPRO_PROP_NOT_TABLE', [
										"#PROP_CODE#" => $propCode,
										"#IBLOCK_ID#" => $catalogName,
										"#ASPRO_HL_TABLE#" => Property::STORES_HL_NAME,
									]);
								}
							}

							//check offers
							$offersIblockId = 0;

							if ($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
								$offersIblockId = $arCatalog["OFFERS_IBLOCK_ID"];
								$arOffersCatalog = CCatalog::GetByID($offersIblockId);
								$offersName = "{$arOffersCatalog['NAME']} ({$offersIblockId})";
								$bPropOfferExists = Property::checkPropStores($offersIblockId);
								if (!$bPropOfferExists) {
									$bNeedCreateProp = true;
									$arMessageError[] = Loc::getMessage('ASPRO_PROP_NOT_EXIST', [
										"#PROP_CODE#" => $propCode,
										"#IBLOCK_ID#" => $offersName,
									]);
								} else {
									$bPropOfferLinkHL = Property::checkPropTableStores($offersIblockId);
									if (!$bPropOfferLinkHL) {
										$arMessageError[] = Loc::getMessage('ASPRO_PROP_NOT_TABLE', [
											"#PROP_CODE#" => $propCode,
											"#IBLOCK_ID#" => $offersName,
											"#ASPRO_HL_TABLE#" => Property::STORES_HL_NAME,
										]);
									}
								}
								if (!empty($arMessageError)) {
									$bShowSyncButton = false;
								}
								$strMessageError = implode('<br>', $arMessageError);
							}
							?>
							<? if (!empty($strMessageError)) : ?>
								<div class="adm-info-message-wrap create-prop-message">
									<div class="adm-info-message">
										<?= $strMessageError ?>
										<? if ($bNeedCreateProp) {
											echo '<br>' . Loc::getMessage('ASPRO_NEED_CREATE_PROP');
										} ?>
									</div>
								</div>
							<? endif; ?>
							<? if ($bNeedCreateProp) : ?>
								<input <? if ($RIGHT < "W") echo "disabled" ?> type="button" name="SYNC_ADD_PROP_STORES_ACTION" class="submit-btn adm-btn-save create-prop-button" value="<?= GetMessage("MAX_MODULE_SYNC_ADD_PROP_STORES") ?>" title="<?= GetMessage("MAX_MODULE_SYNC_ADD_PROP_STORES") ?>">
							<? endif; ?>

							<input <? if ($RIGHT < "W") echo "disabled" ?> type="button" name="SYNC_STORES_ACTION" class="submit-btn adm-btn-save sync-product-button" value="<?= GetMessage("MAX_MODULE_SYNC_STORES") ?>" title="<?= GetMessage("MAX_MODULE_SYNC_STORES") ?>" style="<?= $bShowSyncButton ? '' : 'display: none;' ?>">

							<? \Bitrix\Main\UI\Extension::load("ui.progressbar"); ?>

							<div class="progress-bar sync-stores" style="display: none; max-width: 500px; padding-top: 20px;">
								<div class="progress-bar__step-title" data-start-title="<?= Loc::getMessage('ASPRO_CHECK_PROP_EXIST') ?>"></div>
								<div class="progress-bar__content"></div>
							</div>

							<div class="sync-stores-message adm-info-message-wrap" style="display:none">
								<div class="adm-info-message">
									<div class="adm-info-message-title"></div>
									<div class="adm-info-message-icon"></div>
								</div>
							</div>
						</td>
					</tr>
				<? endif; ?>
			<? endforeach; ?>

			<script>
				$(document).ready(function() {
					function sendAction(action, step, config) {
						let outerWrap = $('.adm-detail-content');
						if (config['SITE_ID']) {
							outerWrap = $('.tab_site_' + config['SITE_ID']);
						}

						if (
							action === 'sync_product'
						) {
							var $form = $('.sync-stores-filter');
							if ($form.length) {
								var data = {
									sessid: $form.find('input[name=sessid]').val(),
									action: action,
									step: step,
									config: config
								};

								$.ajax({
									type: 'POST',
									data: data,
									dataType: 'json',
									success: function(jsonData) {
										if (jsonData) {
											if (jsonData['errors']) {
												showResponseMessage(jsonData['errors'], 'red', outerWrap);
												outerWrap.find('.progress-bar.sync-stores').hide();
												outerWrap.find('.sync-product-button').removeAttr('disabled');
											} else {
												if (jsonData['bar_title']) {
													outerWrap.find('.progress-bar__step-title').text(jsonData['bar_title']);
												}
												if (jsonData['maxValue']) {
													syncProductProgress.setMaxValue(+jsonData['maxValue']);
													syncProductProgress.update();
												}
												if (jsonData['nextStep'] === 'update' && jsonData['addValue']) {
													let newVal = syncProductProgress.getValue() + jsonData['addValue'];
													syncProductProgress.update(newVal);
												}
												if (jsonData['nextStep'] && jsonData['nextStep'] !== 'finish') {

													let nextStep = jsonData['nextStep'];
													if (jsonData['nextPage']) {
														config["SYNC_PAGE_NUM"] = jsonData['nextPage'];
													}

													sendAction(action, nextStep, config);
												}
												if (action === "sync_product" && jsonData['nextStep'] === 'finish') {
													showResponseMessage(jsonData['title'], 'green', outerWrap);
													outerWrap.find('.progress-bar.sync-stores').hide();
													outerWrap.find('.sync-product-button').removeAttr('disabled');
												}
											}
										}
									},
									error: function() {
										outerWrap.find('.sync-product-button').removeAttr('disabled');
									}
								});
							}
						} else if (action === 'create_prop') {
							var $form = $('.sync-stores-filter');
							if ($form.length) {
								var data = {
									sessid: $form.find('input[name=sessid]').val(),
									action: action,
									step: step,
									config: config
								};

								$.ajax({
									type: 'POST',
									data: data,
									dataType: 'json',
									success: function(jsonData) {
										if (jsonData) {
											if (jsonData['errors']) {
												showResponseMessage(jsonData['errors'], 'red', outerWrap);
												outerWrap.find('.create-prop-button').removeClass('disabled');
											} else {
												showResponseMessage(jsonData['title'], 'green', outerWrap);
												outerWrap.find('.sync-product-button').show();
												outerWrap.find('.create-prop-message').hide();
												outerWrap.find('.create-prop-button').hide();
											}
											outerWrap.find('.create-prop-button').removeAttr('disabled');
										}
									},
									error: function() {
										outerWrap.find('.create-prop-button').removeAttr('disabled');
									}
								});
							}
						}
					}

					function showResponseMessage(message, type, outerWrap) {
						if (!type) {
							type = 'green';
						}
						if (!outerWrap) {
							outerWrap = $('.adm-detail-content');
						}
						let messageWrap = outerWrap.find('.sync-stores-message');
						messageWrap.attr('class', 'sync-stores-message adm-info-message-wrap')
						messageWrap.find('.adm-info-message-title').html(message);
						messageWrap.addClass('adm-info-message-' + type);
						messageWrap.show();
					}

					$(document).on('click', '.sync-product-button', function() {
						this.setAttribute('disabled', true);
						let outerWrap = this.closest(".adm-detail-content");
						window.syncProductProgress = new BX.UI.ProgressBar({
							maxValue: 100,
							value: 0,
							statusType: BX.UI.ProgressBar.Status.COUNTER
						});

						let storesBarWrap = outerWrap.querySelector('.progress-bar.sync-stores');
						let storesBar = storesBarWrap.querySelector('.progress-bar__content');
						storesBar.innerHTML = "";
						storesBar.append(syncProductProgress.getContainer());

						let titleBar = storesBarWrap.querySelector('.progress-bar__step-title');
						titleBar.innerText = titleBar.getAttribute('data-start-title');
						syncProductProgress.update(0);
						outerWrap.querySelector('.sync-stores-message').style.display = "none";
						outerWrap.querySelector('.progress-bar.sync-stores').style.display = "block";

						let btnWrap = outerWrap.querySelector('.button-sync-wrap');
						let siteId = 's1';
						if (btnWrap) {
							siteId = btnWrap.getAttribute('data-site-id');
						}
						let config = {};
						config['SITE_ID'] = siteId;
						config['SYNC_PAGE_SIZE'] = outerWrap.querySelector('[name="SYNC_PAGE_SIZE"]').value;

						sendAction('sync_product', 'check', config);
					});

					$(document).on('click', '.create-prop-button', function() {
						this.setAttribute('disabled', true);
						let $form = this.closest(".adm-detail-content");
						let btnWrap = $form.querySelector('.button-sync-wrap');
						let siteId = 's1';
						if (btnWrap) {
							siteId = btnWrap.getAttribute('data-site-id');
						}

						let config = {};
						config['SITE_ID'] = siteId;
						sendAction('create_prop', 'create', config);
					});

				});
			</script>
			<? $tabControl->Buttons(); ?>
			<input <? if ($RIGHT < "W") echo "disabled" ?> type="submit" name="Apply" class="submit-btn adm-btn-save" value="<?= GetMessage("MAX_MODULE_SAVE") ?>" title="<?= GetMessage("MAX_MODULE_SYNC_STORES") ?>">
		</form>
		<? $tabControl->End(); ?>
	<? endif; ?>
<? } else {
	echo CAdminMessage::ShowMessage(GetMessage('NO_RIGHTS_FOR_VIEWING'));
} ?>
<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'); ?>
