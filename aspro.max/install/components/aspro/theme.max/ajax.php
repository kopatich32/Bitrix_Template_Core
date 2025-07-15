<?
namespace Aspro\Max\Components;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Web\Json,
	Bitrix\Main\SystemException,
	CMax as Solution,
	Aspro\Max\Preset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ThemeController extends \Bitrix\Main\Engine\Controller {
	const PRESET_MODULE_ID = 'aspro.sharepreset';
	const PRESET_FILE_EXTENSION = 'json';
	const PRESET_FILE_MAX_SIZE = 102400;

	public function configureActions() {
		return array(
			'exportToFile' => array(
				'prefilters' => array(),
			),
			'downloadFile' => array(
				'prefilters' => array(),
			),
			'importFromLink' => array(
				'prefilters' => array(),
			),
			'importFromPreset' => array(
				'prefilters' => array(),
			),
			'importFromFile' => array(
				'prefilters' => array(),
			),
		);
	}

	public function exportToFileAction($sessid, $siteId, $siteDir, $front, $blocks) {
		$this->includeModules();

		$this->checkSession($sessid);
		$this->checkSite($siteId, $siteDir);
		$this->checkCanRead($siteId, $siteDir, $front);

		$serverName = self::getServerName();

		$obModule = \CModule::CreateModuleObject(Solution::moduleID);
		$version = $obModule ? $obModule->MODULE_VERSION : '';

		$arExcludeBlockCodes = array();
		$arBlockCodes = explode(',', $blocks);

		$bSomeBlockToExportExists = false;
		foreach (Solution::$arParametrsList as $blockCode => $arBlock) {
			if ($arBlock['THEME'] === 'Y') {
				if (!in_array($blockCode, $arBlockCodes)) {
					$arExcludeBlockCodes[] = $blockCode;
				}
				else {
					$bSomeBlockToExportExists = true;
				}
			}
		}

		if (!$bSomeBlockToExportExists) {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_NO_BLOCKS_TO_EXPORT'));
		}

		$bFront = boolval($front);
		$arThemeParametrsValues = Preset::getThemeParametrsValues($bFront, $arExcludeBlockCodes, $siteId, $siteDir);
		$arPresetOptions = Preset::getPresetOptions($arThemeParametrsValues, $arExcludeBlockCodes);
		ksort($arPresetOptions);

		$hash = md5(Solution::moduleID.serialize($arThemeParametrsValues));
		$code = md5($serverName.Solution::moduleID.$hash);

		$_SESSION[$code] = array(
			'datetime' => date('Y-m-d H H:i:s', time()),
			'moduleId' => Solution::moduleID,
			'version' => $version,
			'serverName' => $serverName,
			'siteId' => $siteId,
			'fullset' => !boolval($arExcludeBlockCodes),
			'hash' => $hash,
			'options' => $arPresetOptions,
		);

		return array('code' => $code);
	}

	public function downloadFileAction($sessid, $siteId, $siteDir, $code) {
		try {
			$this->includeModules();

			$this->checkSession($sessid);
			$this->checkSite($siteId, $siteDir);

			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$serverName = $server->getServerName();
			$filename = $serverName.'.json';

			$GLOBALS['APPLICATION']->RestartBuffer();

			if (headers_sent()) {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_HEADERS_ALREADY_SENT'));
			}

			header('Content-Type: application/json; charset='.SITE_CHARSET);
			header('Content-Disposition: attachment; filename='.$filename);
			header('Expires: 0');
			header('Cache-Control: private');

			if (
				isset($_SESSION[$code]) &&
				is_array($_SESSION[$code])
			) {
				echo Json::encode($_SESSION[$code]);
				unset($_SESSION[$code]);
			}
			else {
				echo Json::encode(array());
			}
		}
		catch (SystemException $e) {
			echo $e->getMessage();
			?>
			<script>
			setTimeout(function() {
				location.href = '<?=($siteDir ?: '/')?>';
			}, 2000);
			</script>
			<?
		}

		\CMain::FinalActions();
		flush();
		die();
	}

	public function importFromLinkAction($sessid, $siteId, $siteDir, $front, $link) {
		$this->includeModules();

		$this->checkSession($sessid);
		$this->checkSite($siteId, $siteDir);
		$this->checkCanSave($siteId, $siteDir, $front);

		$link = trim($link);
		if (!strlen($link)) {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_BAD_LINK'));
		}

		if ($arUrl = parse_url($link)) {
			$scheme = in_array($arUrl['scheme'], array('http', 'https')) ? $arUrl['scheme'] : 'https';
			$actionUrl = $scheme.'://'.$arUrl['host'].(isset($arUrl['port']) ? ':'.$arUrl['port'] : '');
			$moduleName = str_replace('.', ':', self::PRESET_MODULE_ID);
			$moduleAction = 'getFromLink';
			$moduleActionFull = urlencode($moduleName.'.api.sharepreset.'.$moduleAction);
			$actionUrl .= '/bitrix/services/main/ajax.php?action='.$moduleActionFull;
		}
		else {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_BAD_URL_LINK'));
		}

		$arData = array(
			'moduleId' => Solution::moduleID,
			'link' => $link,
		);

		$http = new \Bitrix\Main\Web\HttpClient();
		$response = $http->post($actionUrl, $arData);

		if ($response) {

			try {
				$response = Json::decode($response);
			}
			catch (SystemException $e) {
				$response = false;
			}

			if (
				$response &&
				is_array($response) &&
				!$response['errors'] &&
				$response['data'] &&
				is_array($response['data']) &&
				$response['data']['preset'] &&
				is_array($response['data']['preset'])
			) {
				return $response['data'];
			}
			else {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_BAD_RESPONSE'));
			}
		}
		else {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_EMPTY_RESPONSE'));
		}
	}

	public function importFromPresetAction($sessid, $siteId, $siteDir, $front, $preset) {
		$this->includeModules();

		$this->checkSession($sessid);
		$this->checkSite($siteId, $siteDir);
		$this->checkCanSave($siteId, $siteDir, $front);

		try {
			$arPreset = Json::decode($preset);
		}
		catch (SystemException $e) {
			$arPreset = array();
		}

		if (
			$arPreset &&
			is_array($arPreset) &&
			$arPreset['themeParametrsValues'] &&
			is_array($arPreset['themeParametrsValues'])
		) {
			if (
				strlen($arPreset['moduleId']) &&
				$arPreset['moduleId'] !== Solution::moduleID
			) {
				throw new SystemException(
					Loc::getMessage(
						'TA_C_ERROR_LINK_ANOTHER_MODULE',
						array(
							'#MODULE_ID#' => Solution::moduleID
						)
					)
				);
			}

			$bFront = boolval($front);
			$arThemeParametrsValues = Preset::getThemeParametrsValues($bFront, $arExcludeBlockCodes = array(), $siteId, $siteDir);
			$arThemeParametrsValues = array_merge($arThemeParametrsValues, $arPreset['themeParametrsValues']);
			$arPresetOptions = Preset::getPresetOptions($arThemeParametrsValues, $arExcludeBlockCodes = array());
			$bFront ? Preset::setFrontPresetOptions($arPresetOptions, $siteId) : Preset::setBackPresetOptions($arPresetOptions, $siteId);
		}

		return array();
	}

	public function importFromFileAction($sessid, $siteId, $siteDir, $front) {
		$this->includeModules();

		$this->checkSession($sessid);
		$this->checkSite($siteId, $siteDir);
		$this->checkCanSave($siteId, $siteDir, $front);

		$file = $_FILES['file'] ?? false;

		if (
			$file &&
			is_array($file) &&
			strlen($file['name']) &&
			strlen($file['tmp_name'])
		) {
			if (file_exists($file['tmp_name'])) {
				if (is_dir($file['tmp_name'])) {
					throw new \Bitrix\Main\IO\InvalidPathException($file['tmp_name']);
				}
				else {
					// validate extension
					if (!preg_match('/\.'.self::PRESET_FILE_EXTENSION.'$/i', $file['name'])) {
						throw new SystemException(Loc::getMessage('TA_C_ERROR_VALIDATION_FILE_BAD_NAME'));
					}

					// validate size
					if ($file['size'] > self::PRESET_FILE_MAX_SIZE) {
						throw new SystemException(Loc::getMessage('TA_C_ERROR_VALIDATION_FILE_SIZE'));
					}

					$content = \Bitrix\Main\IO\File::getFileContents($file['tmp_name']);

					try {
						$arPreset = Json::decode($content);
					}
					catch (SystemException $e) {
						$arPreset = array();
					}

					if (
						$arPreset &&
						is_array($arPreset) &&
						$arPreset['options'] &&
						is_array($arPreset['options'])
					) {
						if (
							strlen($arPreset['moduleId']) &&
							$arPreset['moduleId'] !== Solution::moduleID
						) {
							throw new SystemException(
								Loc::getMessage(
									'TA_C_ERROR_FILE_ANOTHER_MODULE',
									array(
										'#MODULE_ID#' => Solution::moduleID
									)
								)
							);
						}

						$bFront = boolval($front);
						$arThemeParametrsValues = Preset::getThemeParametrsValues($bFront, $arExcludeBlockCodes = array(), $siteId, $siteDir);
						$arPresetOptions = Preset::getPresetOptions($arThemeParametrsValues, $arExcludeBlockCodes = array());
						$arPresetOptions = Preset::options_replace($arPresetOptions, $arPreset['options']);
						$bFront ? Preset::setFrontPresetOptions($arPresetOptions, $siteId) : Preset::setBackPresetOptions($arPresetOptions, $siteId);
					}
					else {
						throw new SystemException(Loc::getMessage('TA_C_ERROR_INVALID_FILE_FORMAT'));
					}
				}
			}
			else {
				throw new \Bitrix\Main\IO\InvalidPathException($file['tmp_name']);
			}
		}
		else {
			throw new \Bitrix\Main\ArgumentException(Loc::getMessage('TA_C_ERROR_BAD_FILE_FIELDS'));
		}

		return array();
	}

	protected function includeModules() {
		if (
			!Loader::includeModule(Solution::moduleID)
		) {
			throw new SystemException(
				Loc::getMessage(
					'TA_C_ERROR_MODULE_NOT_INSTALLED',
					array(
						'#MODULE_ID#' => Solution::moduleID
					)
				)
			);
		}
	}

	protected function checkSession($sessid) {
		if ($sessid !== bitrix_sessid()) {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_BAD_SESSID'));
		}
	}

	protected function checkSite($siteId, $siteDir) {
		if (
			!$siteId ||
			!$siteDir
		) {
			throw new SystemException(Loc::getMessage('TA_C_ERROR_BAD_SITE_PARAMS'));
		}
	}

	protected function checkCanRead($siteId, $siteDir, $front) {
		if ($front) {
			$arFrontParametrs = Solution::GetFrontParametrsValues($siteId, $siteDir, false);
			if ($arFrontParametrs['THEME_SWITCHER'] !== 'Y') {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_SWITCHER_NOT_ACTIVE'));
			}
		}
		else {
			$RIGHT = $GLOBALS['APPLICATION']->GetGroupRight(Solution::moduleID);
			if ($RIGHT < 'R') {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_INVALID_ACTION'));
			}
		}
	}

	protected function checkCanSave($siteId, $siteDir, $front) {
		if ($front) {
			$arFrontParametrs = Solution::GetFrontParametrsValues($siteId, $siteDir, false);
			if ($arFrontParametrs['THEME_SWITCHER'] !== 'Y') {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_SWITCHER_NOT_ACTIVE'));
			}
		}
		else {
			$RIGHT = $GLOBALS['APPLICATION']->GetGroupRight(Solution::moduleID);
			if ($RIGHT < 'W') {
				throw new SystemException(Loc::getMessage('TA_C_ERROR_INVALID_ACTION'));
			}
		}
	}

	protected static function isDemo() {
		$serverName = static::getServerName();

		return
			Loader::includeModule(self::PRESET_MODULE_ID) &&
			(
				strpos($serverName, Solution::solutionName.'-demo.ru') !== false ||
				strpos($serverName, 'dev.aspro.ru') !== false
			);
	}

	protected static function getServerName() {
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();

		return $serverName = $server->getServerName();
	}
}
