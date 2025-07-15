<?
use Aspro\Max\Thematics;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;

ob_start();
$errorMessage = '';
$arData = array();

$templateID = $wizard->GetVar('templateID');
unset($_SESSION[$templateID]);

$smartSeoModuleId = "aspro.smartseo";
$installSmartSeo = $wizard->GetVar("installSmartSeo");
$bInstallSmartSeo = $installSmartSeo === "Y" && !CModule::IncludeModule($smartSeoModuleId);

if(CModule::IncludeModule(ASPRO_MODULE_NAME) && $bInstallSmartSeo){
	try {
		$arFiles = Thematics::check([
			'templateID' => 'smartseo',
			'moduleID' => $smartSeoModuleId,
			'bSeparateModule' => true,
		]);
	}
	catch (\Exception $e) {
		$errorMessage = $e->getMessage();
	}
}

ob_get_clean();

if(strlen($errorMessage)){
	$response = 'window.ajaxForm.ShowError(\''.CUtil::JSEscape($errorMessage).'\')';
	die("[response]".$response."[/response]");
}
else{
	// goto next step

	$arServices = WizardServices::GetServices($_SERVER['DOCUMENT_ROOT'].$wizard->GetPath(), '/site/services/');

	$arServiceID = array_keys($arServices);
	$lastService = array_pop($arServiceID);
	$stepsCount = $arServices[$lastService]['POSITION'];
	if(array_key_exists('STAGES', $arServices[$lastService]) && is_array($arServices[$lastService])){
		$stepsCount += count($arServices[$lastService]['STAGES']) - 1;
	}

	// get next step
	list($nextService, $nextServiceStage, $stepsComplete, $status) = $this->GetNextStep($arServices, $serviceID, $serviceStage);

	$percent = round($stepsComplete / $stepsCount * 100);

	// status is download stage
	$status = GetMessage('SERVICE_PREPARE_DATA_CLEAR');

	$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
	die("[response]".$response."[/response]");
}
