<?
use \Bitrix\Main\IO\Directory;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_SITE_ID')) return;
if(!defined('WIZARD_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;
if(!defined('WIZARD_THEMATIC_PUBLIC_ABSOLUTE_PATH')) return;

ob_start();
$errorMessage = '';

$templateID = $wizard->GetVar('templateID');
if($_SESSION[$templateID] && is_array($_SESSION[$templateID]) && is_array($_SESSION[$templateID]['FILES'])){
	$unZipFile = false;
	foreach($_SESSION[$templateID]['FILES'] as $unzipIndex => $arFile){
		if(!in_array($arFile['NAME'], $_SESSION[$templateID]['UNZIPED_FILES'])){
			$unZipFile = WIZARD_THEMATIC_FILES_ABSOLUTE_PATH.'/'.$arFile['NAME'];
			if(file_exists($unZipFile)){
				include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/zip.php';

				if(class_exists('CZip')){
					$siteFrom = CFileMan::__CheckSite(WIZARD_SITE_ID);
					$docRootFrom = CSite::GetSiteDocRoot($siteFrom);

					$zip = new CZip($unZipFile);
					$zip->SetOptions(
						array(
							'REMOVE_PATH'		=> $docRootFrom,
							'UNPACK_REPLACE'	=> true,
							'CHECK_PERMISSIONS' => false,
						)
					);
					$result = $zip->Unpack(WIZARD_ABSOLUTE_PATH);
					if(!$result){
						foreach($arErrors = $zip->GetErrors() as $arError){
							$errorMessage = (strlen($errorMessage) ? '<br />' : '').'['.$arError[0].'] '.$arError[1];
						}
					}

					if(!strlen($errorMessage)){
						$_SESSION[$templateID]['UNZIPED_FILES'][] = $arFile['NAME'];
					}
				}
				else{
					$errorMessage = 'CZip class not found';
				}
			}
			else{
				$errorMessage = 'File not exists ('.$unZipFile.')';
			}

			break;
		}
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
	if($unZipFile){
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
			'SERVICE_PREPARE_DATA_UNZIP_PART',
			array(
				'#INDEX#' => $unzipIndex + 1 + ($unzipIndex + 2 <= count($_SESSION[$templateID]['FILES']) ? 1 : 0),
				'#MAX_INDEX#' => count($_SESSION[$templateID]['FILES']),
			)
		);

		$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$serviceID."', '".$serviceStage."', '".$status."');";
		die("[response]".$response."[/response]");
	}
	else{
		// no more files to unzip, go to next step
		// echo 'OK. STAGE COMPLETED. SKIP THIS STEP TO CONTINUE INSTALLATION<br />';
		// die();
	}
}
