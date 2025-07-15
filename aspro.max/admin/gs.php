<?
use Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Aspro\Max\GS;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

global $APPLICATION;
IncludeModuleLangFile(__FILE__);
// $APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$moduleClass = 'CMax';
$moduleID = 'aspro.max';
$bIncluded = \Bitrix\Main\Loader::includeModule($moduleID);
if (!$bIncluded) {
	CAdminMessage::ShowMessage(Loc::getMessage('ASPRO_MAX_MODULE_NOT_INCLUDED'));
}

// title
$APPLICATION->SetTitle(Loc::getMessage('ASPRO_MAX_PAGE_TITLE'));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleID.'/style.css');
CJSCore::Init(array('jquery3'));

if ($bIncluded) {
	// rights
	$RIGHT = $APPLICATION->GetGroupRight($moduleID);
	if ($RIGHT < 'R') {
		CAdminMessage::ShowMessage(Loc::getMessage('ASPRO_MAX_NO_RIGHTS_FOR_VIEWING'));
	}

	if ($RIGHT >= 'R') {
		$bReadOnly = $RIGHT < 'W';

		$bEnabled = GS::isEnabled();
		$bRegistered = GS::isRegistered();

		// ajax action
		if (
			$_SERVER['REQUEST_METHOD'] === 'POST' &&
			isset($_POST['action']) &&
			in_array($_POST['action'], array('register', 'enable', 'disable'))
		) {
			$APPLICATION->RestartBuffer();

			$error = $message = false;

			if (
				check_bitrix_sessid() &&
				!$bReadOnly
			) {
				if ($_POST['action'] === 'enable') {
					GS::enable();
				}
				elseif ($_POST['action'] === 'disable') {
					GS::disable();
				}

				if (
					$_POST['action'] === 'register' ||
					$_POST['action'] === 'enable'
				) {
					if (GS::register()) {
						$message = Loc::getMessage('GS_REGISTERED');

						$arData = GS::mkData(array('sites', 'options'));
						GS::sendData($arData);
					}
					else{
						$error = Loc::getMessage('GS_REGISTER_ERROR');
					}
				}
			}

			if (strlen($error)) {
				CAdminMessage::ShowMessage($error);
			}
			elseif (strlen($message)) {
				CAdminMessage::ShowNote($message);
			}

			die();
		}
		?>
		<div id="aspro_admin_area">
			<form method="post" class="next_options" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
				<?=bitrix_sessid_post();?>
				<div class="adm-info-message" style="max-width:540px;">
					<?=Loc::getMessage('GS_INFO')?>
					<br>
					<br>
					<input type="checkbox" id="gs_enabled" name="gs_enabled" class="adm-designed-checkbox" value="Y" <?=($bEnabled ? 'checked' : '')?> <?=($bReadOnly ? 'disabled' : '')?> />
					<label class="adm-designed-checkbox-label" for="gs_enabled" title=""></label>
					<?=Loc::getMessage('GS_LABEL')?>
				</div>
				<script>
				$(document).ready(function() {
					var bReadOnly = <?=($bReadOnly ? 1 : 0)?>;
					if (!bReadOnly) {
						function sendAction(action) {
							if (
								action === 'register' ||
								action === 'enable' ||
								action === 'disable'
							) {
								var $form = $('form.next_options');
								if ($form.length) {
									var data = {
										sessid: $form.find('input[name=sessid]').val(),
										action: action
									};

									if (action !== 'register') {
										$('#gs_enabled').prop('disabled', true);
									}

									$('#gs_register').prop('disabled', true).addClass('adm-btn-disabled');

									$.ajax({
										type: 'POST',
										data: data,
										success: function(html) {
											html = $.trim(html);
											if (html.length) {
												if ($('#gs_action_result').length) {
													$('#gs_action_result').html(html);
												}
												else{
													$('<div id="gs_action_result" style="display:none;">' + html + '</div>').insertAfter($form);
												}
											}
											else{
												$('#gs_action_result').remove();
											}
										},
										error: function() {
											var html = '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message"><div class="adm-info-message-title"><?=Loc::getMessage('GS_REQUEST_ERROR')?></div><div class="adm-info-message-icon"></div></div></div>';

											if ($('#gs_action_result').length) {
												$('#gs_action_result').html(html);
											}
											else{
												$('<div id="gs_action_result" style="display:none;">' + html + '</div>').insertAfter($form);
											}
										},
										complete: function() {
											if (action !== 'register') {
												$('#gs_enabled').prop('disabled', false);
											}

											$('#gs_register').prop('disabled', false).removeClass('adm-btn-disabled');

											$form.parent().find('.adm-info-message-red .adm-info-message-title').append('<br /><br /><a id="gs_register" class="adm-btn" title="<?=Loc::getMessage('GS_REGISTER_BUTTON_TITLE')?>"><?=Loc::getMessage('GS_REGISTER_BUTTON')?></a>');

											$('#gs_action_result').fadeIn();
										}
									});
								}
							}
						}

						var bEnabled = <?=($bEnabled ? 1 : 0)?>;
						var bRegistered = <?=($bRegistered ? 1 : 0)?>;

						// try to register or check registry if enabled
						if (bEnabled) {
							sendAction('register');
						}

						$(document).on('click', '#gs_register', function() {
							sendAction('register');
						});

						$(document).on('change', '#gs_enabled', function() {
							sendAction($(this).prop('checked') ? 'enable' : 'disable');
						});
					}
				});
				</script>
			</form>
		</div>
		<?
	}
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>
