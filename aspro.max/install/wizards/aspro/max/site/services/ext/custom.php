<?
use Aspro\Max\Thematics;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if (!defined('WIZARD_SITE_ID')) return;
if (!defined('WIZARD_ABSOLUTE_PATH')) return;
if (!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;
if (!defined('WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH')) return;
if (!isset($extModuleId)) return;
if (!isset($extModuleShortId)) return;
if (!isset($installExtModule)) return;

ob_start();
$errorMessage = '';

ob_get_clean();

if (strlen($errorMessage)) {
	$response = 'window.ajaxForm.ShowError(\''.CUtil::JSEscape($errorMessage).'\')';
	die("[response]".$response."[/response]");
}
else {
	// goto next step
	// echo 'OK. STAGE COMPLETED. SKIP THIS STEP TO CONTINUE INSTALLATION<br />';
	// die();
}