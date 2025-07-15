<?
use Aspro\Max\Thematics;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;
if(!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;

ob_start();
$errorMessage = '';
$templateID = $wizard->GetVar('templateID');

$smartSeoModuleId = "aspro.smartseo";
$installSmartSeo = $wizard->GetVar("installSmartSeo");
$bInstallSmartSeo = $installSmartSeo === "Y" && !CModule::IncludeModule($smartSeoModuleId);
if(CModule::IncludeModule(ASPRO_MODULE_NAME) && $bInstallSmartSeo){
	try {
		$result = Thematics::download([
			'templateID' => 'smartseo',
			'moduleID' => $smartSeoModuleId,
		]);

		$arDownloadFile = $result['arDownloadFile'];
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
	if($arDownloadFile){
		// set response with percent stage
		$_SESSION['BX_next_LOCATION'] = 'Y';

		$arServices = WizardServices::GetServices($_SERVER['DOCUMENT_ROOT'].$wizard->GetPath(), '/site/services/');

		$arServiceID = array_keys($arServices);
		$lastService = array_pop($arServiceID);
		$stepsCount = $arServices[$lastService]['POSITION'];
		if(array_key_exists('STAGES', $arServices[$lastService]) && is_array($arServices[$lastService])){
			$stepsCount += count($arServices[$lastService]['STAGES']) - 1;
		}

		$stepsComplete = $arServices[$serviceID]['POSITION'];
		if(array_key_exists('STAGES', $arServices[$serviceID]) && is_array($arServices[$serviceID])){
			$stepsComplete += array_search($serviceStage, $arServices[$serviceID]['STAGES']) - 1;
		}

		$percent = round($stepsComplete / $stepsCount * 100);
		$status = GetMessage(
			'SERVICE_PREPARE_DATA_DOWNLOAD_PART',
			$result['arStatus']
		);

		$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$serviceID."', '".$serviceStage."', '".$status."');";
		die("[response]".$response."[/response]");
	}
	else{
		// no more files to download, go to next step

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

		// status is clear stage
		$status = GetMessage('SERVICE_PREPARE_DATA_UNZIP');

		$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
		die("[response]".$response."[/response]");
	}
}
