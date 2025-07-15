<?
use Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$module_id = 'aspro.popup';
$moduleAssetsPath = str_replace('.', '/', $module_id);

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_GROUP_RIGHTS__PAGE_TITLE'));
?>
<?if (Loader::includeModule($module_id)):?>
	<?
	// rights
	$RIGHT = $APPLICATION->GetGroupRight($moduleID);
	$bReadOnly = $RIGHT < 'W';
	?>
	<?if ($RIGHT >= 'R'):?>
		<?
		// css & js
		$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleAssetsPath.'/style.css');

		$arSites = [];
		$db_res = CSite::GetList(
			($by = 'id'),
			($sort = 'asc'), 
			['ACTIVE' => 'Y']
		);
		while ($res = $db_res->Fetch()) {
			$arSites[] = $res;
		}

		$arTabs = [];
		$arTabs[] = [
			'DIV' => 'edit',
			'TAB' => GetMessage('ASPRO_GROUP_RIGHTS__PAGE_TITLE'),
			'ICON' => 'settings',
			'PAGE_TYPE' => 'site_settings',
			'SITE_ID' => '',
		];

		$tabControl = new CAdminTabControl('tabControl', $arTabs);?>
		<form method="post" class="lite_options" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?$tabControl->Begin();?>

			<?$tabControl->BeginNextTab();?>
			<?require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");?>

			<?if ($REQUEST_METHOD === 'POST' && strlen($Update.$Apply.$RestoreDefaults) && check_bitrix_sessid()):?>
				<?if(strlen($Update) && strlen($_REQUEST['back_url_settings'])):?>
					<?LocalRedirect($_REQUEST['back_url_settings']);?>
				<?else:?>
					<?LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&lang='.urlencode(LANGUAGE_ID).'&back_url_settings='.urlencode($_REQUEST['back_url_settings'])."&".$tabControl->ActiveTabParam());?>
				<?endif;?>
			<?endif;?>

			<?$tabControl->Buttons();?>

			<input type="submit" <?=($RIGHT < 'W' ? 'disabled' : '')?> name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>">
			<input type="hidden" name="Update" value="Y">

			<?$tabControl->End();?>
		</form>
	<?else:?>
		<?=CAdminMessage::ShowMessage(Loc::getMessage('ASPRO_GROUP_RIGHTS__NO_RIGHTS_FOR_VIEWING'));?>
	<?endif;?>
<?else:?>
	<?=CAdminMessage::ShowMessage(Loc::getMessage('ASPRO_GROUP_RIGHTS__ERROR_MODULE'));?>
<?endif;?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>