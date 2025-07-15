<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc,
	CMax as Solution,
	Aspro\Max\Thematics;

$moduleID = Solution::moduleID;
Bitrix\Main\Loader::includeModule($moduleID);

$smartSeoTemplateId = 'smartseo';
$smartSeoModuleId = 'aspro.smartseo';
$smartSeoModuleClass = str_replace('.', '_', $smartSeoModuleId);


// ajax result
$arResult = [];

$action = $_POST['action'] ?: '';
$step = $_POST['step'] ?: '';

try {
	if ($action === 'download') {
		if ($step === 'check') {
			$arFiles = Thematics::check([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $smartSeoModuleId,
				'bSeparateModule' => true,
			]);

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_CLEAR'),
				'nextStep' => 'clear',
				'procent' => 10,
			];
		}
		elseif ($step === 'clear') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_DOWNLOAD'),
				'nextStep' => 'download',
				'procent' => 20,
			];
		}
		elseif ($step === 'download') {
			$result = Thematics::download([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $smartSeoModuleId,
			]);

			if ($result['arDownloadFile']) {
				$status = Loc::getMessage(
					'ASPRO_SMARTSEO_DOWNLOAD_PART',
					$result['arStatus']
				);

				$arResult = [
					'title' => $status,
					'nextStep' => 'download',
					'procent' => 20,
				];
			}
			else {
				$arResult = [
					'title' => Loc::getMessage('ASPRO_SMARTSEO_UNZIP'),
					'nextStep' => 'unzip',
					'procent' => 50,
				];
			}
		}
		elseif ($step === 'unzip') {
			$result = Thematics::unzip([
				'templateID' => $smartSeoTemplateId,
				'moduleID' => $smartSeoModuleId,
			]);

			if ($result['unZipFile']) {
				$status = Loc::getMessage(
					'ASPRO_SMARTSEO_UNZIP_PART',
					$result['arStatus']
				);

				$arResult = [
					'title' => $status,
					'nextStep' => 'unzip',
					'procent' => 50,
				];
			}
			else {
				$arResult = [
					'title' => Loc::getMessage('ASPRO_SMARTSEO_COPY'),
					'nextStep' => 'copy',
					'procent' => 80,
				];
			}
		}
		elseif ($step === 'copy') {
			$moduleDir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$smartSeoModuleId;

			Thematics::copy([
				'targetDir' => $moduleDir,
				'moduleID' => $smartSeoModuleId,
			]);

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_CLEAR_FINAL'),
				'nextStep' => 'clear_final',
				'procent' => 85,
			];
		}
		elseif ($step === 'clear_final') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_SMARTSEO_SETUP'),
				'nextStep' => 'setup',
				'procent' => 90,
			];
		}
		elseif ($step === 'setup') {
			// check module index file
			$indexFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$smartSeoModuleId.'/install/index.php';

			if (@file_exists($indexFile)) {
				include_once($indexFile);

				$obModuleSeo = new $smartSeoModuleClass;
				if (!$obModuleSeo->IsInstalled()) {
					$obModuleSeo->DoInstall();
					$arResult = [
						'title' => Loc::getMessage('ASPRO_SMARTSEO_FINISH'),
						'nextStep' => 'finish',
						'procent' => 100,
					];
				}
			}
			else {
				throw new \Exception(Loc::getMessage('ASPRO_SMARTSEO_ERROR_SETUP_INDEX'));
			}
		}
	}
}
catch (\Exception $e) {
	$arResult['errors'] = $e->getMessage();
	$arResult['title'] = Loc::getMessage('ASPRO_SMARTSEO_ERROR');
}

echo \Bitrix\Main\Web\Json::encode($arResult);
die();
