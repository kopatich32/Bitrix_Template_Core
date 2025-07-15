<?
use Bitrix\Main\Localization\Loc,
\Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__DIR__.'/module_load.php');
$APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$moduleClass = 'CMax';
$moduleID = 'aspro.max';
Loader::includeModule($moduleID);
$downloadModuleId = "aspro.popup";
$bModuleInstalled = Loader::includeModule($downloadModuleId);
$downloadModuleClass = str_replace('.', '_', $downloadModuleId);
$downloadTemplateId = "popup";
$moduleName = Loc::getMessage('ASPRO_MODULE_POPUP_NAME');

$linkToModule = '/bitrix/admin/aspro/popup/group_rights.php?lang='.urlencode(LANGUAGE_ID);

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_MODULE_PAGE_TITLE', ['#MODULE_NAME#' => $moduleName]));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleID.'/module_load.css');
CJSCore::Init(array('jquery3'));

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT < 'R'){
	echo CAdminMessage::ShowMessage(GetMessage('ASPRO_MAX_NO_RIGHTS_FOR_VIEWING'));
}

$bReadOnly = $RIGHT < 'W';

include('module_load_include.php');


?>
<?if($RIGHT >= 'R'):?>

	<?
	$arTabs = [
		[
			'DIV' => 'module-setup',
			'TAB' => Loc::getMessage('ASPRO_MODULE_SETUP_TAB'),
			'TITLE'=> Loc::getMessage('ASPRO_MODULE_SETUP_TAB_TITLE', ['#MODULE_NAME#' => $moduleName]),
		]
	];
	$tabControl = new CAdminTabControl("tabControl", $arTabs);
	$tabControl->Begin();
	?>
	<div class="aspro-module-download-admin-area">
		<form method="post" class="module-download-form" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?foreach($arTabs as $key => $arTab){
				$arTabViewOptions = [
					'showTitle' => false,
					'className' => 'adm-detail-content-without-bg',
				];
				$tabControl->BeginNextTab($arTabViewOptions);?>
				<div class="module-info-wrapper-outer">
					<div class="module-info-block" <?if(!$bModuleInstalled):?>style="display:none;"<?endif;?>>
						<?=Loc::getMessage('ASPRO_MODULE_ALREADY_INSTALLED', ['#MODULE_NAME#' => $moduleName])?><br>
						<a class="module-settings-link" href="<?=$linkToModule?>"><?=Loc::getMessage('ASPRO_MODULE_OPTIONS_LINK', ['#MODULE_NAME#' => $moduleName])?></a>
						<br>
						<br>
					</div>
					<?if(!$bModuleInstalled):?>
						<div class="download-module-wrap">
							<div class="module-install-text"><?=Loc::getMessage('ASPRO_MODULE_CAN_DOWNLOAD', ['#MODULE_NAME#' => $moduleName])?></div>
							<input type="button" class="download-module submit-btn adm-btn-save" name="download-module" value="<?=Loc::getMessage('ASPRO_MODULE_BUTTON_DOWNLOAD')?>" title="<?=Loc::getMessage('ASPRO_MODULE_BUTTON_DOWNLOAD')?>">
						</div>
					<?endif;?>
					<div class="progress-download" style="display:none;">
						<div class="progress-download__title"><?=Loc::getMessage('ASPRO_MODULE_CHECK')?></div>
						<div class="progress-download__bar" >
							<div class="progress-download__bar-inner" style="width: 0%;"></div>
						</div>
					</div>
					<div class="adm-info-message-red">
						<div class="download-errors adm-info-message" style="display:none;">
							<div class="adm-info-message-title"></div>
							<div class="adm-info-message-icon"></div>
						</div>
					</div>

				</div>
			<?
			}
			$tabControl->End();
			?>
		</form>
	</div>
<?endif;?>

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>
