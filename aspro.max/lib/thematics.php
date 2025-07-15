<?
namespace Aspro\Max;

use CMax as Solution,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Main\IO\Directory,
	Bitrix\Main\SiteTable;

class Thematics {
	const REP_URL = 'https://theme.aspro.ru/';

	public static function clear (array $arParams = []) :bool {
		$zipDir = $arParams['zipDir'] ?? '';
		$arExcludeFiles = $arParams['arExcludeFiles'] ?? [];

		$defaultZipPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/tmp_zip_files';
		$zipDir = is_string($zipDir) && strlen($zipDir) ? $zipDir : $defaultZipPath;

		if (!is_dir($zipDir)) {
			@mkdir($zipDir, BX_DIR_PERMISSIONS, 1);
		}
		else {
			if ($arFiles = glob($zipDir.'/{,.}*', GLOB_NOSORT | GLOB_BRACE)) {
				$arExcludeFiles = array_merge(
					(array)$arExcludeFiles,
					[
						$zipDir.'/.',
						$zipDir.'/..',
					]
				);

				$arExcludeFiles = array_flip($arExcludeFiles);

				foreach ($arFiles as $file) {
					if (!isset($arExcludeFiles[$file])) {
						if (is_dir($file)) {
							Directory::deleteDirectory($file);
						}
						else {
							@unlink($file);
						}
					}
				}
			}
		}

		return true;
	}

	public static function copy (array $arParams = []) :bool {
		$zipDir = $arParams['zipDir'] ?? '';
		$targetDir = $arParams['targetDir'] ?? '';
		$moduleID = $arParams['moduleID'] ?? '';

		$defaultZipPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/tmp_zip_files/files/'.$moduleID;
		$zipDir = is_string($zipDir) && strlen($zipDir) ? $zipDir : $defaultZipPath;

		if (is_dir($zipDir) && is_string($targetDir) && strlen($targetDir)) {
			CopyDirFiles($zipDir, $targetDir, true, true, true);
		}

		return true;
	}

	public static function check (array $arParams = []) :array {
		$templateID = $arParams['templateID'] ?? 'default';
		$moduleID = $arParams['moduleID'] ?? Solution::moduleID;
		$thematic = $arParams['thematic'] ?? 'universal';
		$bSeparateModule = $arParams['bSeparateModule'] ?? false;
		$bUpdate = $arParams['bUpdate'] ?? false;
		
		unset($_SESSION[$templateID]);

		if (!Loader::includeModule(Solution::moduleID)) {
			throw new \Exception('Failed to include the module '.Solution::moduleID);
		}

		$obModule = \CModule::CreateModuleObject(Solution::moduleID);
		if (!$obModule) {
			throw new \Exception('Failed to instantiate module '.Solution::moduleID);
		}
		
		$moduleClass = $obModule::moduleClass;
		$moduleToolsClass = $moduleClass.'Tools';
		$moduleVersion = $obModule->MODULE_VERSION;

		if (
			!class_exists($moduleClass) ||
			!class_exists($moduleToolsClass)
		) {
			throw new \Exception('Some required module '.Solution::moduleID.' classes are missing');
		}

		$arData = [
			'ACTION' => 'check',
			'CLIENT' => [
				'LICENSE_KEY' => '',
				'CMS_EDITION' => '',
				'PARTNER_ID' => '',
			],
			'MODULE' => [
				'SM_VERSION' => SM_VERSION,
				'MODULE_ID' => $moduleID,
				'MODULE_VERSION' => $bSeparateModule ? '100.0.0' : $moduleVersion,
				'CHARSET' => SITE_CHARSET,
				'LANGUAGE_ID' => LANGUAGE_ID,
				'THEMATIC' => $thematic,
			],
			'TIMESTAMP' => time(),
		];

		if (
			file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php') &&
			file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client_partner.php')
		) {
			include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php');

			$errorMessage = '';
			$arData['CLIENT']['LICENSE_KEY'] = \CUpdateClient::GetLicenseKey();
			if (strlen($errorMessage)) {
				throw new \Exception($errorMessage);
			}

			$arUpdateList = \CUpdateClient::GetUpdatesList($errorMessage, 'en', 'Y');

			$arClient = $arUpdateList['CLIENT'][0]['@'];
			if (isset($arClient) && is_array($arClient)) {
				$arData['CLIENT']['CMS_EDITION'] = $arClient['LICENSE'];
				$arData['CLIENT']['PARTNER_ID'] = $arClient['PARTNER_ID'];
			}
		}
		
		$key = $arData['CLIENT']['LICENSE_KEY'];
		$key = base64_encode($key);
		$arData = [
			'd' => $moduleToolsClass::___1596018847($arData, $key),
			'k' => $key,
		];

		$httpClient = new HttpClient();
		$httpClient->setTimeout(15);
		$httpClient->setStreamTimeout(15);

		// proxy
		if (
			$bUseProxy = strlen($proxyAddress = Option::get('main', 'update_site_proxy_addr')) &&
			strlen($proxyPort = Option::get('main', 'update_site_proxy_port'))
		) {
			$httpClient->setProxy(
				$proxyAddress,
				$proxyPort,
				Option::get('main', 'update_site_proxy_user'),
				Option::get('main', 'update_site_proxy_pass')
			);
		}

		if ($httpClient->query(HttpClient::HTTP_POST, static::REP_URL, $arData)) {
			// no request error
			$response = $httpClient->getResult();

			// get status of HTTP response
			$status = $httpClient->getStatus();

			// is OK?
			if ($status !== 200) {
				throw new \Exception('Bad response status');
			}

			$arResult = \Bitrix\Main\Web\Json::decode($response);

			if (
				!$arResult ||
				!is_array($arResult)
			) {
				throw new \Exception('Bad response');
			}

			if (strlen($arResult['ERROR'])) {
				throw new \Exception($arResult['ERROR']);
			}
			
			if (!is_array($arResult['FILES'])) {
				throw new \Exception('Bad response');
			}

			if ($bUpdate) {
				$arResult['FILES'] = array_filter($arResult['FILES'], function($fileItem) use ($moduleVersion) {
					$fileVersion = str_replace(['.common.zip', '.zip'], ['', ''], $fileItem['NAME']);
					return version_compare($moduleVersion, $fileVersion, '<');
				});
				$arResult['FILES'] = array_values($arResult['FILES']);
			}

			$_SESSION[$templateID] = [
				'FILES' => $arResult['FILES'],
				'DOWNLOADED_FILES' => [],
				'UNZIPED_FILES' => [],
			];

			return $arResult['FILES'];
		}
		else {
			throw new \Exception(implode('<br />', $httpClient->getError()));
		}
		
		return [];
	}

	public static function download (array $arParams = []) :array {
		$templateID = $arParams['templateID'] ?? 'default';
		$zipDir = $arParams['zipDir'] ?? '';
		
		$defaultZipPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/tmp_zip_files';
		$zipDir = is_string($zipDir) && strlen($zipDir) ? $zipDir : $defaultZipPath;

		if (
			!$_SESSION[$templateID] || 
			!is_array($_SESSION[$templateID]) || 
			!is_array($_SESSION[$templateID]['FILES'])
		) {
			throw new \Exception('Bad last stage result`s data');
		}

		if (!@is_dir($zipDir)) {
			@mkdir($zipDir, BX_DIR_PERMISSIONS, 1);
		}

		$arDownloadFile = [];
		foreach ($_SESSION[$templateID]['FILES'] as $downloadIndex => $arFile) {
			$zipFile = $zipDir.'/'.$arFile['NAME'];
			if (file_exists($zipFile)) {
				if ($arFile['HASH'] !== sha1_file($zipFile)) {
					$arDownloadFile = $arFile;
					break;
				}
			}
			else {
				$arDownloadFile = $arFile;
				break;
			}
		}

		if ($arDownloadFile) {
			$zipFile = $zipDir.'/'.$arDownloadFile['NAME'];
			$tmpFile = preg_replace('/\.zip$/i', '.tmp', $zipFile);

			$arData = $arDownloadFile['URL'];
			$url = static::REP_URL.'?d='.$arData['d'].'&k='.urlencode($arData['k']);
			$arUrl = parse_url($url);

			$sDownloaded = $sStart = 0;
			$sBlock = 40960;
			$request = '';
			$bBody = $lContent = false;
			$bFinished = true;

			list($host, $port, $path, $arg) = [
				$arUrl['host'],
				strlen($arUrl['port']) ? $arUrl['port'] : ($arUrl['scheme'] === 'http' ? 80 : 443),
				$arUrl['path'],
				$arUrl['query']
			];

			if (
				$bUseProxy = strlen($proxyAddress = Option::get('main', 'update_site_proxy_addr')) &&
				strlen($proxyPort = Option::get('main', 'update_site_proxy_port'))
			) {
				$hostname = $proxyAddress;
			}
			else {
				$hostname = $host;
			}

			if (!in_array($arDownloadFile['NAME'], $_SESSION[$templateID]['DOWNLOADED_FILES'])) {
				$_SESSION[$templateID]['DOWNLOADED_FILES'][] = $arDownloadFile['NAME'];
				@unlink($tmpFile);
			}
			else {
				if (file_exists($tmpFile)) {
					$sStart = @filesize($tmpFile);
				}
			}

			$hFileFrom = @fsockopen(($arUrl['scheme'] === 'http' ? '' : 'ssl://').$host, $port, $error_id, $error_msg, 10);
			if (!$hFileFrom) {
				throw new \Exception('Can`t connect to '.$host.' ['.$error_id.'] '.$error_msg);
			}

			$hFileTo = @fopen($tmpFile, 'ab');
			if (!$hFileTo) {
				throw new \Exception('Can`t open tmp file for writing');
			}

			if (!$bUseProxy) {
				$request .= 'GET '.$path.($arg ? '?'.$arg : '')." HTTP/1.0\r\n";
				$request .= 'Host: '.$hostname."\r\n";
			}
			else {
				$request .= 'GET '.$url." HTTP/1.0\r\n";
				$request .= 'Host: '.$hostname."\r\n";
			}
			$request .= "Connection: close\r\n";
			$request .= "User-Agent: wizard-download/1.0\r\n";
			if ($sStart > 0) {
				$request .= 'Range: bytes='.$sStart."-\r\n";
			}
			$request .= "\r\n";

			@fwrite($hFileFrom, $request);

			$startLine = @fgets($hFileFrom, 4096);
			if ($startLine && preg_match_all('#^HTTP/1.\d?\s+(\d+)\s+#', $startLine, $arMatches)) {
				if (
					$arMatches[1][0] != 200 &&
					$arMatches[1][0] != 206
				) {
					throw new \Exception('Download error. Response: '.$startLine);
				}

				$maxTime = time() + 5;

				while (!feof($hFileFrom)) {
					if (!$bBody) {
						$header = @fgets($hFileFrom, 4096);
						$posColon = strpos($header, ':');
						$headerName = strtolower(trim(substr($header, 0, $posColon)));
						$headerVal = trim(substr($header, $posColon + 1));
						if ($headerName === 'content-length') {
							$lContent = doubleval($headerVal);
						}
						if ($header === "\r\n") {
							$bBody = true;
						}
					}
					else {
						if (time() >= $maxTime) {
							$bFinished = false;
							break;
						}

						$data = @fread($hFileFrom, $sBlock);

						$bJson = false;

						try {
							$arResult = \Bitrix\Main\Web\Json::decode($data);

							// data - is json array
							$bJson = true;
						}
						catch (\Exception $e) {
							// data - is string part of file
						}

						if ($bJson) {
							if ($arResult && is_array($arResult)) {
								if (strlen($arResult['ERROR'])) {
									throw new \Exception($arResult['ERROR']);
								}
								else {
									throw new \Exception('Unknown error');
								}
							}
							else {
								throw new \Exception('Bad response');
							}
						}
						else {
							$sDownloaded += strlen($data);
							if ($data === '') {
								break;
							}

							if (@fwrite($hFileTo, $data) === false) {
								throw new \Exception('Can`t write file. Check free disk space');
							}
						}
					}
				}
			}
			else {
				throw new \Exception('Download error. Bad response');
			}

			@fclose($hFileTo);

			@fclose($hFileFrom);

			if ($bFinished) {
				$zipHash = sha1_file($tmpFile);
				if ($zipHash != $arDownloadFile['HASH']) {
					throw new \Exception('File checksum does not match');
				}
				else {
					// delete old file
					@unlink($zipFile);

					// rename tmp file
					if (!@rename($tmpFile, $zipFile)) {
						throw new \Exception('Can`t rename tmp file');
					}
				}
			}
		}

		$arStatus = [];
		if ($arDownloadFile) {
			$sizeFile = $arDownloadFile['SIZE'];
			$sizeTmp = @filesize($tmpFile);

			$arStatus = [
				'#INDEX#' => $downloadIndex + 1 + ($bFinished ? ($downloadIndex + 2 <= count($_SESSION[$templateID]['FILES']) ? 1 : 0) : 0),
				'#MAX_INDEX#' => count($_SESSION[$templateID]['FILES']),
				'#TMP_SIZE#' => \CFile::FormatSize($sizeTmp),
				'#FILE_SIZE#' => \CFile::FormatSize($sizeFile),
			];
		}

		return [
			'arDownloadFile' => $arDownloadFile,
			'downloadIndex' => $downloadIndex,
			'arStatus' => $arStatus,
		];
	}

	public static function unzip (array $arParams = []) :array {
		$templateID = $arParams['templateID'] ?? 'default';
		$zipDir = $arParams['zipDir'] ?? '';
		$siteID = $arParams['siteID'] ?? '';
		$bUpdate = $arParams['bUpdate'] ?? false;
		$moduleID = $arParams['moduleID'] ?? Solution::moduleID;
		
		$defaultZipPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.Solution::moduleID.'/tmp_zip_files';
		$zipDir = is_string($zipDir) && strlen($zipDir) ? $zipDir : $defaultZipPath;
		$unZipDir = $arParams['unZipDir'] ?? $zipDir.'/files';

		Loader::includeModule('fileman');

		if (
			!$_SESSION[$templateID] ||
			!is_array($_SESSION[$templateID]) ||
			!is_array($_SESSION[$templateID]['FILES'])
		) {
			throw new \Exception('Bad last stage result`s data');
		}
			
		$unZipFile = false;
		foreach ($_SESSION[$templateID]['FILES'] as $unzipIndex => $arFile) {
			if (!in_array($arFile['NAME'], $_SESSION[$templateID]['UNZIPED_FILES'])) {
				$unZipFile = $zipDir.'/'.$arFile['NAME'];

				if (!file_exists($unZipFile)) {
					throw new \Exception('File not exists ('.$unZipFile.')');
				}
					
				include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/zip.php';

				if (!class_exists('CZip')) {
					throw new \Exception('CZip class not found');
				}

				$siteFrom = \CFileMan::__CheckSite($siteID);
				$docRootFrom = SiteTable::getDocumentRoot($siteFrom === false ? null : $siteFrom);

				$zip = new \CZip($unZipFile);
				$zip->SetOptions(
					[
						'REMOVE_PATH'		=> $docRootFrom,
						'UNPACK_REPLACE'	=> true,
						'CHECK_PERMISSIONS' => false,
					]
				);
				$result = $zip->Unpack($unZipDir);
				if (!$result) {
					$errorMessage = '';
					foreach ($zip->GetErrors() as $arError) {
						$errorMessage = (strlen($errorMessage) ? '<br />' : '').'['.$arError[0].'] '.$arError[1];
					}
					if (strlen($errorMessage)) {
						throw new \Exception($errorMessage);
					}
				}

				$_SESSION[$templateID]['UNZIPED_FILES'][] = $arFile['NAME'];
				if ($bUpdate) {
					$updaterFile = $unZipDir.'/'.$moduleID.'/updater.php';
					$moduleDir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$moduleID;
					if (file_exists($updaterFile)) {
						include($updaterFile);
						@unlink($updaterFile);
					}

					static::copy([
						'targetDir' => $moduleDir,
						'moduleID' => $moduleID
					]);
				}

				break;
			}
		}

		$arStatus = [];
		if ($unZipFile) {
			$arStatus = [
				'#INDEX#' => $unzipIndex + 1 + ($unzipIndex + 2 <= count($_SESSION[$templateID]['FILES']) ? 1 : 0),
				'#MAX_INDEX#' => count($_SESSION[$templateID]['FILES']),
			];
		}

		return [
			'unZipFile' => $unZipFile,
			'unzipIndex' => $unzipIndex,
			'arStatus' => $arStatus,
		];
	}
}
