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
	\Aspro\Max\Stores\Property,
    Aspro\Max\Stickers,
    CMax as Solution;

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if ($RIGHT >= "R") {
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

				$rsResult = CAgent::GetList(Array("ID" => "DESC"), array("NAME"=>'%::updateStickerMain("'.$optionsSiteID.'"%'));
				while ($arResult = $rsResult->GetNext())
				{
					if (CAgent::Delete($arResult['ID']));
				}

				$rsResult = CAgent::GetList(Array("ID" => "DESC"), array("NAME"=>'%::updateStickerMore("'.$optionsSiteID.'"%'));
				while ($arResult = $rsResult->GetNext())
				{
					if (CAgent::Delete($arResult['ID']));
				}

				if ($_POST["USE_AGENT_INTERVAL_SALE_" . $optionsSiteID]) {
					Option::set($moduleID, "USE_AGENT_INTERVAL_SALE", $_POST["USE_AGENT_INTERVAL_SALE_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["USE_STICKER_SALE_AUTO_" . $optionsSiteID] && $_POST["USE_STICKER_SALE_AUTO_" . $optionsSiteID] == "Y") {

					Aspro\Max\Agents\Stickers\Sale::add($optionsSiteID, Option::get($moduleID, 'USE_AGENT_INTERVAL_SALE', '1', $optionsSiteID));
					Option::set($moduleID, "USE_STICKER_SALE_AUTO", "Y", $optionsSiteID);
				} else {
					Option::set($moduleID, "USE_STICKER_SALE_AUTO", "N", $optionsSiteID);
				}

				if ($_POST["COUNT_GOODS_STEP_SALE_" . $optionsSiteID]) {
					Option::set($moduleID, "COUNT_GOODS_STEP_SALE", $_POST["COUNT_GOODS_STEP_SALE_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["STICKER_SALE_" . $optionsSiteID]) {
					Option::set($moduleID, "STICKER_SALE", $_POST["STICKER_SALE_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["USE_AGENT_INTERVAL_NEW_" . $optionsSiteID]) {
					Option::set($moduleID, "USE_AGENT_INTERVAL_NEW", $_POST["USE_AGENT_INTERVAL_NEW_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["USE_STICKER_NEW_AUTO_" . $optionsSiteID] && $_POST["USE_STICKER_NEW_AUTO_" . $optionsSiteID] == "Y") {

					Aspro\Max\Agents\Stickers\Novinka::add($optionsSiteID, Option::get($moduleID, 'USE_AGENT_INTERVAL_NEW', '1', $optionsSiteID));

					Option::set($moduleID, "USE_STICKER_NEW_AUTO", "Y", $optionsSiteID);
				} else {
					Option::set($moduleID, "USE_STICKER_NEW_AUTO", "N", $optionsSiteID);
				}

				if ($_POST["COUNT_GOODS_STEP_NEW_" . $optionsSiteID]) {
					Option::set($moduleID, "COUNT_GOODS_STEP_NEW", $_POST["COUNT_GOODS_STEP_NEW_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["STICKER_NEW_" . $optionsSiteID]) {
					Option::set($moduleID, "STICKER_NEW", $_POST["STICKER_NEW_" . $optionsSiteID], $optionsSiteID);
				}

				if ($_POST["STICKER_NEW_TIME_" . $optionsSiteID]) {
					Option::set($moduleID, "STICKER_NEW_TIME", $_POST["STICKER_NEW_TIME_" . $optionsSiteID], $optionsSiteID);
				}

			}

			LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&save=Y&" . $tabControl->ActiveTabParam());
		}

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

                    <?$arParams = array(
                        'STICKER_SALE' => array(
                            'TITLE' => GetMessage('STICKER_SALE_TITLE'),
                            'TYPE' => 'array',
                            'OPTIONS' => array(
                                'USE_STICKER_SALE_AUTO' => array(
                                    'TITLE' => GetMessage('USE_STICKER_SALE_AUTO_TITLE'),
                                    'TYPE' => 'checkbox',
                                    'DEFAULT' => 'N',
                                ),
								'USE_AGENT_INTERVAL_SALE' => array(
                                    'TITLE' => GetMessage('USE_AGENT_INTERVAL_SALE_TITLE'),
                                    'TYPE' => 'text',
									'PLACEHOLDER' => '1',
                                ),
								'COUNT_GOODS_STEP_SALE' => array(
                                    'TITLE' => GetMessage('COUNT_GOODS_STEP_SALE_TITLE'),
                                    'TYPE' => 'text',
                                    'PLACEHOLDER' => '100',
                                ),
                                'STICKER_SALE' => array(
                                    'TITLE' => GetMessage('STICKER_SALE_TITLE'),
                                    'TYPE' => 'selectbox',
                                    'TYPE_SELECT' => 'ENUM',
                                    'DEFAULT' => 'STOCK',
                                ),
                                'STICKER_SALE_NOTE' => array(
                                    'TITLE' => GetMessage('STICKER_SALE_NOTE_TITLE'),
                                    'TYPE' => 'note',
                                ),
                            ),
                        ),

                        'STICKER_NEW' => array(
                            'TITLE' => GetMessage('STICKER_NEW_TITLE'),
                            'TYPE' => 'array',
                            'OPTIONS' => array(
                                'USE_STICKER_NEW_AUTO' => array(
                                    'TITLE' => GetMessage('USE_STICKER_NEW_AUTO_TITLE'),
                                    'TYPE' => 'checkbox',
                                    'DEFAULT' => 'N',
                                ),
								'USE_AGENT_INTERVAL_NEW' => array(
                                    'TITLE' => GetMessage('USE_AGENT_INTERVAL_NEW_TITLE'),
                                    'TYPE' => 'text',
									'PLACEHOLDER' => '1',
                                ),
								'COUNT_GOODS_STEP_NEW' => array(
                                    'TITLE' => GetMessage('COUNT_GOODS_STEP_NEW_TITLE'),
                                    'TYPE' => 'text',
									'PLACEHOLDER' => '100',
                                ),
                                'STICKER_NEW' => array(
                                    'TITLE' => GetMessage('STICKER_NEW_TITLE'),
                                    'TYPE' => 'selectbox',
                                    'TYPE_SELECT' => 'ENUM',
                                    'DEFAULT' => 'NEW',
                                ),
								'STICKER_NEW_TIME' => array(
                                    'TITLE' => GetMessage('STICKER_NEW_TIME_TITLE'),
                                    'TYPE' => 'text',
									'PLACEHOLDER' => '30',
                                ),
								'STICKER_NEW_NOTE' => array(
                                    'TITLE' => GetMessage('STICKER_NEW_NOTE_TITLE'),
                                    'TYPE' => 'note',
                                    'ALIGN' => 'center',
                                ),
                            ),
                        ),
                    );
                    ?>
                    <?foreach($arParams as $params){?>
						<tr class="heading">
							<td colspan="2"><?=$params['TITLE']?></td>
						</tr>
						<?foreach($params['OPTIONS'] as $optionCode => $arOptions):?>
							<tr>
								<?
								$arTab['OPTIONS'][$optionCode] = Option::get($moduleID, $optionCode, '', $optionsSiteID);
								Solution::ShowAdminRow($optionCode, $arOptions, $arTab, [], true);?>
							</tr>
						<?endforeach;?>
                    <?}?>
				<? endif; ?>
			<? endforeach; ?>
			<? $tabControl->Buttons(); ?>
			<input <? if ($RIGHT < "W") echo "disabled" ?> type="submit" name="Apply" class="submit-btn adm-btn-save" value="<?= GetMessage("MAX_MODULE_SAVE") ?>" title="<?= GetMessage("MAX_MODULE_SAVE") ?>">
		</form>
		<? $tabControl->End(); ?>
	<? endif; ?>
<? } else {
	echo CAdminMessage::ShowMessage(GetMessage('NO_RIGHTS_FOR_VIEWING'));
} ?>
<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'); ?>
