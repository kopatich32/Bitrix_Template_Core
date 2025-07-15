<?
use Aspro\Max\Thematics;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;
if(!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_IBLOCK_XML_ABSOLUTE_PATH')) return;

ob_start();
$errorMessage = '';

$smartSeoModuleId = "aspro.smartseo";
$installSmartSeo = $wizard->GetVar("installSmartSeo");
$bInstallSmartSeo = $installSmartSeo === "Y" && !CModule::IncludeModule($smartSeoModuleId);
if(CModule::IncludeModule(ASPRO_MODULE_NAME) && $bInstallSmartSeo){
	try {
		Thematics::clear();
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

	// status is unzip stage
	$status = GetMessage('SERVICE_PREPARE_DATA_DOWNLOAD');

	$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
	die("[response]".$response."[/response]");
}
