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
	// check module index file
    try {
        if (@file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$smartSeoModuleId.'/install/index.php')) {
            include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$smartSeoModuleId.'/install/index.php');

            $smartSeoModuleClass = str_replace('.', '_', $smartSeoModuleId);
            $obModuleSeo = new $smartSeoModuleClass;
            if( !$obModuleSeo->IsInstalled() ) {
                $obModuleSeo->DoInstall();
            }
        }
        else {
            throw new \Exception(GetMessage('ASPRO_SMARTSEO_ERROR_SETUP_INDEX'));
        }
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
    // echo 'OK. STAGE COMPLETED. SKIP THIS STEP TO CONTINUE INSTALLATION<br />';
    // die();
}
