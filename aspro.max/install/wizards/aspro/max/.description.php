<?
define('ASPRO_PARTNER_NAME', 'aspro');
define('ASPRO_MODULE_NAME_SHORT', 'max');
define('ASPRO_MODULE_NAME', ASPRO_PARTNER_NAME.'.'.ASPRO_MODULE_NAME_SHORT);
define('ASPRO_TEMPLATE_NAME', ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT);
define('ASPRO_REP_URL', 'https://theme.aspro.ru/');

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!defined('WIZARD_DEFAULT_SITE_ID') && !empty($_REQUEST['wizardSiteID'])){
	define('WIZARD_DEFAULT_SITE_ID', $_REQUEST['wizardSiteID']);
}

$arWizardDescription = array(
	'NAME' => GetMessage('PORTAL_WIZARD_NAME'),
	'DESCRIPTION' => GetMessage('PORTAL_WIZARD_DESC'),
	'VERSION' => '1.0.0',
	'START_TYPE' => 'WINDOW',
	'WIZARD_TYPE' => 'INSTALL',
	'IMAGE' => '/images/'.LANGUAGE_ID.'/solution.png',
	'PARENT' => 'wizard_sol',
	'TEMPLATES' => array(
        // array('SCRIPT' => 'wizard_sol')
        array('SCRIPT' => 'template.php', 'CLASS' => 'WizardTemplate'),
	),
	'STEPS' => array(
		'SelectSiteStep',
		'SelectTemplateStep',
		'SelectThematicStep',
		'SelectPresetStep',
		'ExtModulesStep',
	),
);

$arWizardDescription['STEPS'] = array_merge(
	$arWizardDescription['STEPS'],
	[
		'SiteSettingsStep',
		// 'ShopSettings',
		'PersonType',
		'PaySystem',
		'DataInstallStep',
		'FinishStep',
	]
);

if(defined('WIZARD_DEFAULT_SITE_ID')){
	unset($arWizardDescription['STEPS'][array_search('SelectSiteStep', $arWizardDescription['STEPS'])]);
}

if(LANGUAGE_ID !== 'ru'){
	unset($arWizardDescription['STEPS'][array_search('PersonType', $arWizardDescription['STEPS'])]);
}
