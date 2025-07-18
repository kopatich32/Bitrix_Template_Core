<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = "CMax";
$moduleID = "aspro.max";
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule($moduleID);

use \Bitrix\Main\Config\Option;

$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT >= "R"){
	$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/".$moduleID."/style.css");
	$GLOBALS['APPLICATION']->SetTitle(GetMessage("MAX_PAGE_TITLE"));

	$by = "id";
	$sort = "asc";

	$arSites = array();
	$db_res = CSite::GetList($by, $sort, array("ACTIVE"=>"Y"));
	while($res = $db_res->Fetch()){
		$arSites[] = $res;
	}

	$arTabs[] = array(
		"DIV" => "edit".($key+1),
		"TAB" => GetMessage("MAIN_OPTIONS_STORES_TITLE"),
		"ICON" => "settings",
		"PAGE_TYPE" => "site_settings",
	);

	$tabControl = new CAdminTabControl("tabControl", $arTabs);?>

	<div class="adm-info-message"><?=GetMessage("MAX_MODULE_STORES_INFO");?></div>

	<?if($REQUEST_METHOD == "POST" && $RIGHT >= "W" && check_bitrix_sessid())
	{
		global $APPLICATION, $CACHE_MANAGER;
		if($_POST["Apply"])
		{
			if($_POST["EVENT_SYNC"] && $_POST["EVENT_SYNC"] == "Y")
				\Bitrix\Main\Config\Option::set($moduleID, "EVENT_SYNC", "Y");
			else
				\Bitrix\Main\Config\Option::set($moduleID, "EVENT_SYNC", "N");
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&save=Y&".$tabControl->ActiveTabParam());
		}
		if($_POST["Generate"])
		{
			if($_POST["IBLOCK_ID"])
			{
				$arCatalog = CCatalog::GetByID($_POST["IBLOCK_ID"]);
				if($arCatalog)
				{
					if(!$arCatalog["PRODUCT_IBLOCK_ID"])
					{
						$arItems = $arItems2 = array();
						$arFilter = array("IBLOCK_ID" => $_POST["IBLOCK_ID"]);
						if($_POST["SECTION_ID"])
						{
							$arFilter["SECTION_ID"] = $_POST["SECTION_ID"];
							$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
						}
						$rsItems = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID", "ACTIVE"));
						while($arItem = $rsItems->Fetch())
						{
							$arItems[$arItem["ID"]] = $arItem;
						}
						if($arItems)
						{
							$rsItems = CCatalogProduct::GetList(array(), array("ID" => array_keys($arItems)), false, false, array("WEIGHT", "ID"));
							while($arItem2 = $rsItems->Fetch())
							{
								$arItems2[] = $arItem2;
							}

							if($arItems2)
							{
								$obProduct = new CCatalogProduct();
								foreach($arItems2 as $arItem)
								{
									$_SESSION["CUSTOM_UPDATE"] = "Y";

									$obProduct->Update($arItem["ID"], array("WEIGHT" => $arItem["WEIGHT"]));

									unset($_SESSION["CUSTOM_UPDATE"]);
								}
							}
						}
						LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&success=Y&".$tabControl->ActiveTabParam());
					}
					else
					{
						echo CAdminMessage::ShowMessage(GetMessage("MAX_MODULE_NO_CATALOG_CAN_SELECT"));
					}
				}
				else
				{
					echo CAdminMessage::ShowMessage(GetMessage("MAX_MODULE_NO_CATALOG_IBLOCK_ID"));
				}
			}
			else
			{
				echo CAdminMessage::ShowMessage(GetMessage("MAX_MODULE_NO_IBLOCK_ID"));
			}
		}
		// $APPLICATION->RestartBuffer();
	}

	CJSCore::Init(array("jquery3"));
	?>
	<?if(!count($arSites)):?>
		<div class="adm-info-message-wrap adm-info-message-red">
			<div class="adm-info-message">
				<div class="adm-info-message-title"><?=GetMessage("ASPRO_MAX_NO_SITE_INSTALLED", array("#SESSION_ID#"=>bitrix_sessid_get()))?></div>
				<div class="adm-info-message-icon"></div>
			</div>
		</div>
	<?else:?>
		<?if(htmlspecialcharsbx($_REQUEST["success"])):?>
			<?echo CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("MAX_MODULE_SYNC_OK"), "TYPE" => "OK"));?>
		<?endif;?>
		<?if(htmlspecialcharsbx($_REQUEST["save"])):?>
			<?echo CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("MAX_MODULE_SAVE_OK"), "TYPE" => "OK"));?>
		<?endif;?>
		<?$tabControl->Begin();?>
			<form method="post" class="max_options" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
				<?=bitrix_sessid_post();?>
				<?$tabControl->BeginNextTab();?>
				<tr class="heading">
					<td colspan="2"><?=GetMessage("MAX_MODULE_SETTINGS");?></td>
				</tr>
				<tr>
					<td>
						<?=GetMessage("MAX_MODULE_EVENT_SYNC_TITLE");?>
					</td>
					<td style="width:50%;">
						<input type="checkbox" id="EVENT_SYNC" name="EVENT_SYNC" value="Y" <?=(\Bitrix\Main\Config\Option::get($moduleID, "EVENT_SYNC", "N") == "Y" ? "checked" : "");?> class="adm-designed-checkbox">
						<label class="adm-designed-checkbox-label" for="EVENT_SYNC" title=""></label>
					</td>
				</tr>
				<tr class="heading">
					<td colspan="2"><?=GetMessage("MAX_MODULE_PARAMS");?></td>
				</tr>
				<tr>
					<td>
						<?=GetMessage("MAX_MODULE_IBLOCK_ID");?>
					</td>
					<td style="width:50%;">
						<input name="IBLOCK_ID" value="<?=$_POST["IBLOCK_ID"];?>"/>

					</td>
				</tr>
				<tr>
					<td>
						<?=GetMessage("MAX_MODULE_IBLOCK_SECTION_ID");?>
					</td>
					<td style="width:50%;">
						<input name="SECTION_ID" value="<?=$_POST["SECTION_ID"];?>"/>

					</td>
				</tr>

				<?$tabControl->Buttons();?>
				<input <?if($RIGHT < "W") echo "disabled"?> type="submit" name="Apply" class="submit-btn adm-btn-save" value="<?=GetMessage("MAX_MODULE_SAVE")?>" title="<?=GetMessage("MAX_MODULE_SYNC_STORES")?>">
				<input <?if($RIGHT < "W") echo "disabled"?> type="submit" name="Generate" class="submit-btn adm-btn-save" value="<?=GetMessage("MAX_MODULE_SYNC_STORES")?>" title="<?=GetMessage("MAX_MODULE_SYNC_STORES")?>">
			</form>
		<?$tabControl->End();?>
	<?endif;?>
<?}
else
{
	echo CAdminMessage::ShowMessage(GetMessage('NO_RIGHTS_FOR_VIEWING'));
}?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>
