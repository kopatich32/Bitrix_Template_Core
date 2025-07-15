<?
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

// change default extension of file
$this->__component->setFileNameExtension('.pdf');

// check reqiurements
if(!function_exists('mb_strlen')){
	$this->__component->setError(Loc::getMessage('BF_T_ERROR_NO_MBSTRING_EXTENSION'));
}

if(!class_exists('DOMDocument')){
	$this->__component->setError(Loc::getMessage('BF_T_ERROR_NO_DOM_EXTENSION'));
}

if(version_compare(PHP_VERSION, '7.1.0') < 0){
	$this->__component->setError(Loc::getMessage('BF_T_ERROR_PHP_VERSION'));
}
else{
	include_once $arResult['VENDOR_PATH'].'/dompdf/autoload.inc.php';
	if(!class_exists('Dompdf\Dompdf')){
		$this->__component->setError(Loc::getMessage('BF_T_ERROR_NO_DOMPDF_CLASS'));
	}
}
?>