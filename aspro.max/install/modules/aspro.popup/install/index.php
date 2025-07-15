<?php

IncludeModuleLangFile(__FILE__);

class aspro_popup extends CModule {
	const solutionName	= 'popup';
	const partnerName = 'aspro';

	var $MODULE_ID = 'aspro.popup';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';

	function __construct(){
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = GetMessage('ADM_ASPRO_POPUP_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('ADM_ASPRO_POPUP_MODULE_DESC');
		$this->PARTNER_NAME = GetMessage('ADM_ASPRO_POPUP_PARTNER');
		$this->PARTNER_URI = GetMessage('ADM_ASPRO_POPUP_PARTNER_URI');
	}

	function InstallEvents(){
		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ListUsersGroups', "OnIBlockPropertyBuildList");
		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ListWebForms', "OnIBlockPropertyBuildList");
		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ModalConditions', "OnIBlockPropertyBuildList");
		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ConditionType', "OnIBlockPropertyBuildList");
	}

	function UnInstallEvents(){
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ListUsersGroups', "OnIBlockPropertyBuildList");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ListWebForms', "OnIBlockPropertyBuildList");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ModalConditions', "OnIBlockPropertyBuildList");
		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", $this->MODULE_ID, 'Aspro\Popup\Property\ConditionType', "OnIBlockPropertyBuildList");
	}

	function InstallDB(){
		RegisterModule($this->MODULE_ID);
		include('iblock/types.php');
		include('iblock/marketings.php');

		COption::RemoveOption($this->MODULE_ID);
		$GLOBALS['APPLICATION']->DelGroupRight($this->MODULE_ID);
		COption::SetOptionString($this->MODULE_ID, 'GROUP_DEFAULT_RIGHT', $this->MODULE_GROUP_RIGHTS);
		$GLOBALS['APPLICATION']->SetGroupRight($this->MODULE_ID, 0, $this->MODULE_GROUP_RIGHTS);

		return true;
	}

	function UnInstallDB(){
		COption::RemoveOption($this->MODULE_ID);
		$GLOBALS['APPLICATION']->DelGroupRight($this->MODULE_ID);

		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	function InstallFiles(){
		CopyDirFiles(__DIR__.'/admin/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/admin/'.static::partnerName.'/'.static::solutionName, true);
		CopyDirFiles(__DIR__.'/components/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components', true, true);
		CopyDirFiles(__DIR__.'/tools/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/tools/'.static::partnerName.'/'.static::solutionName, true, true);

		CopyDirFiles(__DIR__.'/css/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/css/'.static::partnerName.'/'.static::solutionName, true, true);
		CopyDirFiles(__DIR__.'/js/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/js/'.static::partnerName.'/'.static::solutionName, true, true);
		CopyDirFiles(__DIR__.'/images/', $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/images/'.static::partnerName.'/'.static::solutionName, true, true);

		return true;
	}

	function UnInstallFiles(){
		DeleteDirFilesEx(BX_ROOT.'/admin/'.static::partnerName.'/'.static::solutionName.'/');
		DeleteDirFilesEx(BX_ROOT.'/css/'.static::partnerName.'/'.static::solutionName.'/');
		DeleteDirFilesEx(BX_ROOT.'/js/'.static::partnerName.'/'.static::solutionName.'/');
		DeleteDirFilesEx(BX_ROOT.'/images/'.static::partnerName.'/'.static::solutionName.'/');
		DeleteDirFilesEx(BX_ROOT.'/tools/'.static::partnerName.'/'.static::solutionName.'/');

		$this->UnInstallComponents();

		return true;
	}

	function UnInstallComponents() {
		DeleteDirFilesEx(BX_ROOT.'/components/'.static::partnerName.'/marketing.popup/');

		return true;
	}

	function DoInstall(){
		$this->InstallEvents();
		$this->InstallFiles();
		$this->InstallDB(false);
		
		// $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage('LITE_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$this->MODULE_ID.'/install/step.php');
	}

	function DoUninstall(){
		$this->UnInstallEvents();
		$this->UnInstallDB();
		$this->UnInstallFiles();

		// $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage('LITE_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$this->MODULE_ID.'/install/unstep.php');
	}
}