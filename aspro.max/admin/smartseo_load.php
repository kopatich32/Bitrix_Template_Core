<?
use Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$moduleClass = 'CMax';
$moduleID = 'aspro.max';
\Bitrix\Main\Loader::includeModule($moduleID);
$smartSeoModuleId = "aspro.smartseo";
$bSmartSeoInstalled = \Bitrix\Main\Loader::includeModule($smartSeoModuleId);
$smartSeoModuleClass = str_replace('.', '_', $smartSeoModuleId);
$smartSeoTemplateId = "smartseo";

$linkToSmartSEO = '/bitrix/admin/aspro.smartseo_smartseo.php?route=filter_rules/list';

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_MAX_PAGE_TITLE'));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
CJSCore::Init(array('jquery3'));

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT < 'R'){
	echo CAdminMessage::ShowMessage(GetMessage('ASPRO_MAX_NO_RIGHTS_FOR_VIEWING'));
}

$bReadOnly = $RIGHT < 'W';

/////////////////////////
//check rights for obf files
$arTestFiles = [
    '/bitrix/modules/'. $moduleID .'/lib/thematics.php',
    '/bitrix/modules/'. $moduleID .'/admin/smartseo_load_include.php',
];

$bShowNotify = false;
foreach ($arTestFiles as $key => $file) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
    if(!is_readable($filePath)){
        $bShowNotify = true;
    }
}

if($bShowNotify){
    \CAdminNotify::Add(
        [
            "MESSAGE" => Loc::getMessage('ASPRO_SMARTSEO_OBF_FILES_ERROR'),
            "TAG" => 'aspro_smartseo',
            "MODULE_ID" => $moduleID,
        ]
    );
}
//////////////////

// ajax action

if(
	$_SERVER['REQUEST_METHOD'] === 'POST' &&
	isset($_POST['action']) &&
	in_array($_POST['action'], array('download', 'delete'))
){
	$APPLICATION->RestartBuffer();

	if(
		check_bitrix_sessid() &&
		!$bReadOnly
	){
		include('smartseo_load_include.php');
	}

	die();
}

?>
<?if($RIGHT >= 'R'):?>

	<?
	$arTabs = [
		[
			'DIV' => 'smartseo-setup',
			'TAB' => Loc::getMessage('ASPRO_SMARTSEO_SETUP_TAB'),
			'TITLE'=> Loc::getMessage('ASPRO_SMARTSEO_SETUP_TAB_TITLE'),
		]
	];
	$tabControl = new CAdminTabControl("tabControl", $arTabs);
	$tabControl->Begin();
	?>
	<div class="aspro-smartseo-download-admin-area">
		<form method="post" class="smartseo-download-form" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?foreach($arTabs as $key => $arTab){
				$arTabViewOptions = [
					'showTitle' => false,
					'className' => 'adm-detail-content-without-bg',
				];
				$tabControl->BeginNextTab($arTabViewOptions);?>
				<div class="" style="max-width:540px;">
					<div class="smartseo-info-block" <?if(!$bSmartSeoInstalled):?>style="display:none;"<?endif;?>>
						<?=Loc::getMessage('ASPRO_SMARTSEO_ALREADY_INSTALLED')?><br>
						<a class="smartseo-settings-link" href="<?=$linkToSmartSEO?>"><?=Loc::getMessage('ASPRO_SMARTSEO_OPTIONS_LINK')?></a>
						<br>
						<br>
					</div>
					<?if(!$bSmartSeoInstalled):?>
						<div class="download-smartseo-wrap">
							<?=Loc::getMessage('ASPRO_SMARTSEO_CAN_DOWNLOAD')?>
							<br>
							<br>
							<input type="button" class="download-smartseo submit-btn adm-btn-save" name="download-smartseo" value="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DOWNLOAD')?>" title="<?=Loc::getMessage('ASPRO_SMARTSEO_BUTTON_DOWNLOAD')?>">
						</div>
					<?endif;?>
					<div class="progress-download" style="display:none;">
						<div class="progress-download__title"><?=Loc::getMessage('ASPRO_SMARTSEO_CHECK')?></div>
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
			<script>
			$(document).ready(function(){
				function sendAction(action, step){
					if(
						action === 'download' || action === 'delete'
					){
						var $form = $('.smartseo-download-form');
						if($form.length){
							var data = {
								sessid: $form.find('input[name=sessid]').val(),
								action: action,
								step: step
							};

							$.ajax({
								type: 'POST',
								data: data,
								dataType: 'json',
								success: function(jsonData){
									if(jsonData){
										if(jsonData['errors']){
											console.log(jsonData['errors']);
											$('.download-errors .adm-info-message-title').html(jsonData['errors']);
											$('.download-errors').show();
											$('.progress-download').hide();
										} else {
											if(jsonData['nextStep'] && jsonData['nextStep'] !== 'finish'){
												let nextStep = jsonData['nextStep'];
												sendAction(action, nextStep);
											}
											if(jsonData['procent']){
												$('.progress-download__bar-inner').css('width', jsonData['procent'] + '%');
											}
											if(jsonData['title']){
												$('.progress-download__title').html(jsonData['title']);
											}
											if(action === "download" && jsonData['nextStep'] === 'finish'){
												$('.smartseo-info-block').show();
												$('.download-smartseo-wrap').hide();
                                                location.href = $('.smartseo-settings-link').attr('href');
											}
										}
									}
								},
								error: function(){

								}
							});
						}
					}
				}

				$(document).on('click', '.download-smartseo', function(){
					//$('.download-smartseo').prop('disabled', true);
					$('.download-smartseo-wrap').hide();
					$('.progress-download').show();
					sendAction('download', 'check');
				});
			});
			</script>
		</form>
	</div>
<?endif;?>

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>
