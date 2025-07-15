<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$extModuleId = 'aspro.smartseo';
$installExtModule = $wizard->GetVar('installSmartSeo');

$extModuleShortId = str_replace('aspro.', '', $extModuleId);
$extModuleClass = str_replace('.', '_', $extModuleId);

require realpath(__DIR__.'/../ext/unzip.php');
