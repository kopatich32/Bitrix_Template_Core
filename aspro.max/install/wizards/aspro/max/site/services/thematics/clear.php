<?
use \Bitrix\Main\IO\Directory;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;
if(!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_IBLOCK_XML_ABSOLUTE_PATH')) return;

ob_start();
$errorMessage = '';

// remove from wizard other files in zip dir exclude thematic dir
$zipDir = dirname(WIZARD_THEMATIC_FILES_ABSOLUTE_PATH);
if(!is_dir($zipDir)){
	@mkdir($zipDir, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($zipDir.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$zipDir.'/.',
			$zipDir.'/..',
			WIZARD_THEMATIC_FILES_ABSOLUTE_PATH,
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

// remove from module other files in zip dir
$zipDirInModule = str_replace('/bitrix/wizards/', '/bitrix/modules/'.ASPRO_MODULE_NAME.'/install/wizards/', $zipDir);
if(!is_dir($zipDirInModule)){
	@mkdir($zipDirInModule, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($zipDirInModule.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$zipDirInModule.'/.',
			$zipDirInModule.'/..',
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

// remove from wizard all files in xml dir
$xmlDir = WIZARD_THEMATIC_IBLOCK_XML_ABSOLUTE_PATH;
if(!is_dir($xmlDir)){
	@mkdir($xmlDir, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($xmlDir.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$xmlDir.'/.',
			$xmlDir.'/..',
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

// remove from module all files in xml dir
$xmlDirInModule = str_replace('/bitrix/wizards/', '/bitrix/modules/'.ASPRO_MODULE_NAME.'/install/wizards/', WIZARD_THEMATIC_IBLOCK_XML_ABSOLUTE_PATH);
if(!is_dir($xmlDirInModule)){
	@mkdir($xmlDirInModule, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($xmlDirInModule.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$xmlDirInModule.'/.',
			$xmlDirInModule.'/..',
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

// remove from wizard all files in public dir
$publicDir = WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH;
if(!is_dir($publicDir)){
	@mkdir($publicDir, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($publicDir.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$publicDir.'/.',
			$publicDir.'/..',
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

// remove from module all files in public dir
$publicDirInModule = str_replace('/bitrix/wizards/', '/bitrix/modules/'.ASPRO_MODULE_NAME.'/install/wizards/', WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH);
if(!is_dir($publicDirInModule)){
	@mkdir($publicDirInModule, BX_DIR_PERMISSIONS, 1);
}
else{
	if($arFiles = glob($publicDirInModule.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
		$arExcludeFiles = array_flip(array(
			$publicDirInModule.'/.',
			$publicDirInModule.'/..',
		));

		foreach($arFiles as $file){
			if(!isset($arExcludeFiles[$file])){
				if(is_dir($file)){
					Directory::deleteDirectory($file);
				}
				else{
					@unlink($file);
				}
			}
		}
	}
}

$templateID = $wizard->GetVar('templateID');
if($_SESSION[$templateID] && is_array($_SESSION[$templateID]) && is_array($_SESSION[$templateID]['FILES'])){
	// remove from wizard other files in thematic zip dir
	$thematicDir = WIZARD_THEMATIC_FILES_ABSOLUTE_PATH;
	if(!is_dir($thematicDir)){
		@mkdir($thematicDir, BX_DIR_PERMISSIONS, 1);
	}
	else{
		if($arFiles = glob($thematicDir.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
			$arExcludeFiles = array_flip(array(
				$thematicDir.'/.',
				$thematicDir.'/..',
			));

			foreach($_SESSION[$templateID]['FILES'] as $arFile){
				$arExcludeFiles[$thematicDir.'/'.$arFile['NAME']] = count($arExcludeFiles);
			}

			foreach($arFiles as $file){
				if(!isset($arExcludeFiles[$file])){
					if(is_dir($file)){
						Directory::deleteDirectory($file);
					}
					else{
						@unlink($file);
					}
				}
			}
		}
	}

	// remove from module other files in thematic zip dir
	$thematicDirInModule = str_replace('/bitrix/wizards/', '/bitrix/modules/'.ASPRO_MODULE_NAME.'/install/wizards/', $thematicDir);
	if(!is_dir($thematicDirInModule)){
		@mkdir($thematicDirInModule, BX_DIR_PERMISSIONS, 1);
	}
	else{
		if($arFiles = glob($thematicDirInModule.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)){
			$arExcludeFiles = array_flip(array(
				$thematicDirInModule.'/.',
				$thematicDirInModule.'/..',
			));

			foreach($_SESSION[$templateID]['FILES'] as $arFile){
				$arExcludeFiles[$thematicDirInModule.'/'.$arFile['NAME']] = count($arExcludeFiles);
			}

			foreach($arFiles as $file){
				if(!isset($arExcludeFiles[$file])){
					if(is_dir($file)){
						Directory::deleteDirectory($file);
					}
					else{
						@unlink($file);
					}
				}
			}
		}
	}

	// copy thematic zip files to module
	foreach($_SESSION[$templateID]['FILES'] as $arFile){
		@copy($thematicDir.'/'.$arFile['NAME'], $thematicDirInModule.'/'.$arFile['NAME']);
	}
}
else{
	$errorMessage = 'Bad last stage result`s data';
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
	$status = GetMessage('SERVICE_PREPARE_DATA_UNZIP');

	$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
	die("[response]".$response."[/response]");
}
