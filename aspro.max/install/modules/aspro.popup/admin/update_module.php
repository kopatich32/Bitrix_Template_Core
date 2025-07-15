<?
use Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$moduleID = 'aspro.popup';
$moduleAssetsPath = str_replace('.', '/', $moduleID);
\Bitrix\Main\Loader::includeModule($moduleID);

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_UPDATE__PAGE_TITLE'));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleAssetsPath.'/style.css');
CJSCore::Init(array('jquery'));

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);

/////////////////////////
//check rights for obf files
$arTestFiles = [
    '/bitrix/modules/'.$moduleID.'/lib/thematics.php',
    '/bitrix/modules/'.$moduleID.'/admin/update_module_include.php',
];

$bShowNotify = false;
foreach ($arTestFiles as $key => $file) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
    if (!is_readable($filePath)) {
        $bShowNotify = true;
    }
}

if ($bShowNotify) {
    \CAdminNotify::Add(
        [
            'MESSAGE' => Loc::getMessage('ASPRO_UPDATE__UPDATE_OBF_FILES_ERROR'),
            'TAG' => 'aspro_popup',
            'MODULE_ID' => $moduleID,
        ]
    );
}
//////////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
	// ajax action
	require 'update_module_include.php';
}
else {
	if ($RIGHT < 'R') {
		echo CAdminMessage::ShowMessage(GetMessage('ASPRO_UPDATE__NO_RIGHTS_FOR_VIEWING'));
	}
	else {
		$arTabs = [
			[
				'DIV' => 'tabs-update',
				'TAB' => Loc::getMessage('ASPRO_UPDATE__UPDATE_TAB'),
				'TITLE'=> Loc::getMessage('ASPRO_UPDATE__UPDATE_TAB_TITLE'),
			]
		];
		$tabControl = new CAdminTabControl('tabControl', $arTabs);
		$tabControl->Begin();
		?>
		<div class="aspro-update__admin-area">
			<form method="post" class="aspro-update__download-form" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
				<?=bitrix_sessid_post();?>
				<?foreach ($arTabs as $key => $arTab):?>
					<?
					$arTabViewOptions = [
						'showTitle' => false,
						'className' => 'adm-detail-content-without-bg',
					];
					$tabControl->BeginNextTab($arTabViewOptions);?>
					<div class="aspro-update__wrap" style="max-width:540px;">
						<div class="aspro-update__info">
							<div class="aspro-update__title">
								<?=Loc::getMessage('ASPRO_UPDATE__CHECK_UPDATES_AVAIBLE')?>
							</div>
							<div class="aspro-update__description" style="display: none;">
								<input type="button" class="aspro-update__description-button adm-btn" name="description-popup" value="<?=Loc::getMessage('ASPRO_UPDATE__BUTTON_DESCRIPTION')?>" title="<?=Loc::getMessage('ASPRO_UPDATE__BUTTON_DESCRIPTION')?>">
							</div>
						</div>

						<div class="aspro-update__admin-waiter"></div>

						<div class="aspro-update__download-wrap" style="display: none;">
							<div class="aspro-update__download-wrap__title">
								<?=Loc::getMessage('ASPRO_UPDATE__CAN_DOWNLOAD')?>
							</div>
							<div class="aspro-update__backup-alert"><?=Loc::getMessage('ASPRO_UPDATE__DESCRIPTION_ALERT')?></div>
							<br>
							<input type="button" class="aspro-update__download submit-btn adm-btn-save" name="download-popup" value="<?=Loc::getMessage('ASPRO_UPDATE__BUTTON_DOWNLOAD')?>" title="<?=Loc::getMessage('ASPRO_UPDATE__BUTTON_DOWNLOAD')?>">
						</div>
						
						<div class="aspro-update__progress-download" style="display:none;">
							<div class="aspro-update__progress-download__title"><?=Loc::getMessage('ASPRO_UPDATE__CHECK')?></div>
							<div class="aspro-update__progress-download__bar" >
								<div class="aspro-update__progress-download__bar-inner" style="width: 0%;"></div>
							</div>
						</div>
						<div class="adm-info-message-red">
							<div class="aspro-update__download-errors adm-info-message" style="display:none;">
								<div class="adm-info-message-title"></div>
								<div class="adm-info-message-icon"></div>
							</div>
						</div>
					</div>
				<?endforeach;?>
				<?$tabControl->End();?>
				<script>
				BX.message({
					'ASPRO_UPDATE__TITLE_DESCRIPTION': '<?=Loc::getMessage('ASPRO_UPDATE__TITLE_DESCRIPTION')?>',
				});

				$(document).ready(function() {
					function sendAction(action, step) {
						if (
							action === 'download' ||
							action === 'check_updates'
						) {
							var $form = $('.aspro-update__download-form');
							if ($form.length) {
								var data = {
									sessid: $form.find('input[name=sessid]').val(),
									action: action,
									step: step
								};
								
								$.ajax({
									type: 'POST',
									data: data,								
									dataType: 'json',
									success: function(jsonData) {
										if (jsonData) {
											if (jsonData['errors']) {
												console.log(jsonData['errors']);
												$('.aspro-update__download-errors .adm-info-message-title').html(jsonData['errors']);
												$('.aspro-update__download-errors').show();
												$('.aspro-update__progress-download').hide();
											}
											else {
												if (jsonData['nextStep'] && jsonData['nextStep'] !== 'finish') {
													let nextStep = jsonData['nextStep'];												
													sendAction(action, nextStep);
												}

												if (jsonData['procent']) {
													$('.aspro-update__progress-download__bar-inner').css('width', jsonData['procent'] + '%');
												}

												if (jsonData['title']) {
													if (action === "check_updates") {
														$('.aspro-update__title').html(jsonData['title']);
													}
													else{
														$('.aspro-update__progress-download__title').html(jsonData['title']);
													}
												}

												if (action === "download" && jsonData['nextStep'] === 'finish') {
													$('.aspro-update__download-wrap').hide();
												}

												if (action === "check_updates" && jsonData['need_update'] === true) {
													$('.aspro-update__download-wrap').show();
													$('.aspro-update__description').show();
												}
											}
										}

										$('.aspro-update__admin-waiter').hide();
									},
									error: function() {
									}
								});
							}
						}
						else if(action === 'get_description'){
							var $form = $('.aspro-update__download-form');
							if ($form.length) {
								var data = {
									sessid: $form.find('input[name=sessid]').val(),
									action: action,
									step: step
								};
								
								$.ajax({
									type: 'POST',
									data: data,								
									dataType: 'json',
									success: function(jsonData) {
										if (jsonData) {
											if (jsonData['errors']) {
												console.log(jsonData['errors']);											
												window.updatePopup.SetContent('<div class="aspro-update__description-error">'+jsonData['errors'] + '</div>');
											}
											else {
												if (typeof jsonData['content'] !== 'undefined' && typeof window.updatePopup !== 'undefined') {
													window.updatePopup.SetContent(jsonData['content']);
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

					$(document).on('click', '.aspro-update__download', function() {
						$('.aspro-update__download-wrap').hide();
						$('.aspro-update__progress-download').show();
						sendAction('download', 'check');
					});

					$(document).on('click', '.aspro-update__description-button', function(){
						if(typeof window.updatePopup === 'undefined'){
							window.updatePopup = new BX.CDialog({
								'title': BX.message('ASPRO_UPDATE__TITLE_DESCRIPTION'),
								'content': '<br><div class="aspro-update__admin-waiter"></div>',
								'width': 615,
								'height': 500,
								'draggable': true,
								'resizable': false,
								//'buttons': [BX.CDialog.btnClose]
							});

							window.updatePopup.DIV.classList.add('aspro-update__description-popup');
							let popupInner = window.updatePopup.DIV.querySelector('.bx-core-adm-dialog-content');
							if(popupInner){
								popupInner.classList.add('popup-scrollblock');
							}
							sendAction('get_description', '');
						}
						window.updatePopup.Show();
					});

					sendAction('check_updates', '');
				});
				</script>
			</form>
		</div>
		<?
	}
}
?>
<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');?>