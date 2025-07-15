<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
// use $thematic for a list of different services
$thematic = isset($request['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) ? strtolower($request['__wiz_'.ASPRO_PARTNER_NAME.'_'.ASPRO_MODULE_NAME_SHORT.'_thematicCODE']) : 'universal';
switch ($thematic) {
    case 'universal':
        include('service_universal.php');
        break;
    case 'active':
        include('service_active.php');
        break;
    case 'home':
        include('service_home.php');
        break;
    case 'mebel':
        include('service_mebel.php');
        break;
    case 'moda':
        include('service_moda.php');
        break;
    case 'volt':
        include('service_volt.php');
        break;
    default:
        include('service_universal.php');
        break;
}
