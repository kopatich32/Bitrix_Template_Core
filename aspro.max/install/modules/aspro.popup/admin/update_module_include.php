<?
use Bitrix\Main\Localization\Loc,
	Aspro\Popup\Thematics;

$moduleID = 'aspro.popup';
Bitrix\Main\Loader::includeModule($moduleID);

$templateId = 'popup';

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
$bReadOnly = $RIGHT < 'W';

// ajax result
$arResult = [];

$action = $_POST['action'] ?: '';
$step = $_POST['step'] ?: '';

$APPLICATION->RestartBuffer();

try {
	if ($RIGHT < 'R') {
		throw new \Exception(Loc::getMessage('ASPRO_UPDATE__NO_RIGHTS_FOR_VIEWING'));
	}

	if ($bReadOnly) {
		throw new \Exception(Loc::getMessage('ASPRO_UPDATE__ERROR_READ_ONLY'));
	}

	if (!check_bitrix_sessid()) {
		throw new \Exception(Loc::getMessage('ASPRO_UPDATE__ERROR_INVALID_SESSID'));
	}

	if ($action === 'download') {
		if ($step === 'check') {
			$arFiles = Thematics::check([
				'templateID' => $templateId,
				'moduleID' => $moduleID,
				'bSeparateModule' => true,
				'bUpdate' => true,
			]);

			$arResult = [
				'title' => Loc::getMessage('ASPRO_UPDATE__CLEAR'),
				'nextStep' => 'clear',
				'procent' => 10,
			];
		}
		elseif ($step === 'clear') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_UPDATE__DOWNLOAD'),
				'nextStep' => 'download',
				'procent' => 20,
			];
		}
		elseif ($step === 'download') {
			$result = Thematics::download([
				'templateID' => $templateId,
				'moduleID' => $moduleID,
			]);

			if ($result['arDownloadFile']) {
				$status = Loc::getMessage(
					'ASPRO_UPDATE__DOWNLOAD_PART',
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
					'title' => Loc::getMessage('ASPRO_UPDATE__UNZIP'),
					'nextStep' => 'unzip', 
					'procent' => 50,
				];
			}
		}
		elseif ($step === 'unzip') {
			$result = Thematics::unzip([
				'templateID' => $templateId,
				'moduleID' => $moduleID,
				'bUpdate' => true,
			]);

			if ($result['unZipFile']) {
				$status = Loc::getMessage(
					'ASPRO_UPDATE__UNZIP_PART',
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
					'title' => Loc::getMessage('ASPRO_UPDATE__CLEAR_FINAL'),
					'nextStep' => 'clear_final', 
					'procent' => 80,
				];
			}
		}
		elseif ($step === 'clear_final') {
			Thematics::clear();

			$arResult = [
				'title' => Loc::getMessage('ASPRO_UPDATE__FINISH'),
				'nextStep' => 'finish',
				'procent' => 100,
			];
		}
	}
	elseif ($action === 'check_updates') {
		$arFiles = Thematics::check([
			'templateID' => $templateId,
			'moduleID' => $moduleID,
			'bSeparateModule' => true,
			'bUpdate' => true,
		]);

		$arResult = [
			'title' => $arFiles ? Loc::getMessage('ASPRO_UPDATE__UPDATES_AVAILABLE', ['#COUNT_UPDATES#' => count($arFiles)]) : Loc::getMessage('ASPRO_UPDATE__NO_UPDATES_AVAILABLE'),
			'nextStep' => 'finish',
			'need_update' => (bool)$arFiles,
		];

	}
	elseif ($action === 'get_description') {
		$arDescriptions = Thematics::description([
			'moduleID' => $moduleID,
			'bSeparateModule' => true,
			'bUpdate' => true,
		]);
		$arDescriptions = is_array($arDescriptions) ? array_reverse($arDescriptions) : [];
		
		ob_start();
		foreach ($arDescriptions as $version => $description) {
			foreach ($description as $keyDesc => $valueDesc) {?>
				<div class="aspro-update__version-info">
					<div class="aspro-update__version-info__title">
						<?=Loc::getMessage('ASPRO_UPDATE__DESCRIPTION_VERSION').$version;?>
					</div>
					<div class="aspro-update__version-info__description">
						<?=$valueDesc;?>
						<div class="aspro-update__backup-alert"><?=Loc::getMessage('ASPRO_UPDATE__DESCRIPTION_ALERT')?></div>
					</div>
				</div>
			<?}
		}
		$htmlDescription = ob_get_clean();

		$arResult = [
			'content' => $htmlDescription,
		];

	}
	else {
		throw new \Exception(Loc::getMessage('ASPRO_UPDATE__ERROR_INVALID_ACTION'));
	}
}
catch (\Exception $e) {
	$arResult['errors'] = $e->getMessage();
	$arResult['title'] = Loc::getMessage('ASPRO_UPDATE__ERROR');
}

echo \Bitrix\Main\Web\Json::encode($arResult);
die();
