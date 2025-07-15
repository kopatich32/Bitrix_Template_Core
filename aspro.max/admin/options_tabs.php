<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$moduleClass = "CMax";
$moduleID = "aspro.max";
global  $APPLICATION;
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options_tabs.php");

CModule::IncludeModule($moduleID);
IncludeModuleLangFile(__FILE__);

use \Bitrix\Main\Config\Option;
$APPLICATION->SetTitle(GetMessage('TABS_SETTINGS_TITLE'));
$APPLICATION->SetAdditionalCss("/bitrix/css/".$moduleID."/options_tabs.css");

$arSites = array();
$db_res = CSite::GetList($by, $sort, array("ACTIVE"=>"Y"));
while($res = $db_res->Fetch()){
	$arSites[] = $res;
}

if($REQUEST_METHOD == "POST" && strlen($Apply) && check_bitrix_sessid()){
	foreach($_POST as $key => $value) {
		if($key != 'Apply' && $key != 'sessid') {
			$arOption[] = $key;
		}
	}
	if($arOption) {
		for($i = 0; $i < count($arOption); $i++) {
			$optionTmp .= $arOption[$i];
			if($i != count($arOption)-1) {
				$optionTmp .= ',';
			}
		}
		COption::SetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', $optionTmp);
	}
}

$tabs = COption::GetOptionString($moduleID, 'TABS_FOR_VIEW_ASPRO_MAX', '');

?>

<style>
	.checkboxes_wrapper {
		display: flex;
		flex-direction: column;
		margin: 20px 0;
	}
	.checkbox_wrapper label {
		margin-top: -2px;
	}
</style>

<form class="aspro_options_tabs" method="post" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
	<?=bitrix_sessid_post();?>
	<?
	if($tabs) {
		$tabs = explode(',' , $tabs);
	}
	?>
	<div class="title"><?=GetMessage('TABS_SETTINGS_PREVIEW')?></div>

	<div class="checkboxes_wrapper">
		<?foreach($arSites as $site):
			if($tabs) {
				$value = (in_array($site['ID'],$tabs) ? 'checked' : '');
			}
		?>
			<div class="checkbox_wrapper">
				<input type="checkbox" id="<?=$site['ID']?>" name="<?=$site['ID']?>" <?=$value?> >
				<label for="<?=$site['ID']?>"><?=$site['ID']?></label>
			</div>

		<?endforeach;?>
	</div>

	<input type="submit" name="Apply">
</form>

<?
if($REQUEST_METHOD == "POST" && strlen($Apply) && check_bitrix_sessid())
{

	//LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"]));
}
?>

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>