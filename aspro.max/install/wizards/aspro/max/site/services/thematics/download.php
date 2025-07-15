<?
use \Bitrix\Main\Config\Option;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;
if(!defined('WIZARD_THEMATIC_FILES_ABSOLUTE_PATH')) return;

ob_start();
$errorMessage = '';

$templateID = $wizard->GetVar('templateID');
if($_SESSION[$templateID] && is_array($_SESSION[$templateID]) && is_array($_SESSION[$templateID]['FILES'])){
	if(!@is_dir(WIZARD_THEMATIC_FILES_ABSOLUTE_PATH)){
		@mkdir(WIZARD_THEMATIC_FILES_ABSOLUTE_PATH, BX_DIR_PERMISSIONS, 1);
	}

	$arDownloadFile = array();
	foreach($_SESSION[$templateID]['FILES'] as $downloadIndex => $arFile){
		$zipFile = WIZARD_THEMATIC_FILES_ABSOLUTE_PATH.'/'.$arFile['NAME'];
		if(file_exists($zipFile)){
			if($arFile['HASH'] !== sha1_file($zipFile)){
				$arDownloadFile = $arFile;
				break;
			}
		}
		else{
			$arDownloadFile = $arFile;
			break;
		}
	}

	if($arDownloadFile){
		$zipFile = WIZARD_THEMATIC_FILES_ABSOLUTE_PATH.'/'.$arDownloadFile['NAME'];
		$tmpFile = preg_replace('/\.zip$/i', '.tmp', $zipFile);

		$arData = $arDownloadFile['URL'];
		$url = ASPRO_REP_URL.'?d='.$arData['d'].'&k='.urlencode($arData['k']);
		$arUrl = parse_url($url);

		$sDownloaded = $sStart = 0;
		$sBlock = 40960;
		$request = '';
		$bBody = $lContent = false;
		$bFinished = true;

		list($host, $port, $path, $arg) = array($arUrl['host'], strlen($arUrl['port']) ? $arUrl['port'] : ($arUrl['scheme'] === 'http' ? 80 : 443), $arUrl['path'], $arUrl['query']);

		if($bUseProxy = strlen($proxyAddress = Option::get('main', 'update_site_proxy_addr')) && strlen($proxyPort = Option::get('main', 'update_site_proxy_port'))){
			$hostname = $proxyAddress;
		}
		else{
			$hostname = $host;
		}

		if(!in_array($arDownloadFile['NAME'], $_SESSION[$templateID]['DOWNLOADED_FILES'])){
			$_SESSION[$templateID]['DOWNLOADED_FILES'][] = $arDownloadFile['NAME'];
			@unlink($tmpFile);
		}
		else{
			if(file_exists($tmpFile)){
				$sStart = @filesize($tmpFile);
			}
		}

		if($hFileFrom = @fsockopen(($arUrl['scheme'] === 'http' ? '' : 'ssl://').$host, $port, $error_id, $error_msg, 10)){
			if($hFileTo = @fopen($tmpFile, 'ab')){
				if(!$bUseProxy){
					$request .= 'GET '.$path.($arg ? '?'.$arg : '')." HTTP/1.0\r\n";
					$request .= 'Host: '.$hostname."\r\n";
				}
				else{
					$request .= 'GET '.$url." HTTP/1.0\r\n";
					$request .= 'Host: '.$hostname."\r\n";
				}
				$request .= "Connection: close\r\n";
				$request .= "User-Agent: wizard-download/1.0\r\n";
				if($sStart > 0){
					$request .= 'Range: bytes='.$sStart."-\r\n";
				}
				$request .= "\r\n";

				@fwrite($hFileFrom, $request);

				$startLine = @fgets($hFileFrom, 4096);
				if($startLine && preg_match_all('#^HTTP/1.\d?\s+(\d+)\s+#', $startLine, $arMatches)){
					if($arMatches[1][0] == 200 || $arMatches[1][0] == 206){
						$maxTime = time() + 5;

						while(!feof($hFileFrom)){
							if(!$bBody){
								$header = @fgets($hFileFrom, 4096);
								$posColon = strpos($header, ':');
								$headerName = strtolower(trim(substr($header, 0, $posColon)));
								$headerVal = trim(substr($header, $posColon + 1));
								if($headerName === 'content-length'){
									$lContent = doubleval($headerVal);
								}
								if($header === "\r\n"){
									$bBody = true;
								}
							}
							else{
								if(time() >= $maxTime){
									$bFinished = false;
									break;
								}

								$data = @fread($hFileFrom, $sBlock);

								$bJson = false;

								try{
									$arResult = \Bitrix\Main\Web\Json::decode($data);

									// data - is json array
									$bJson = true;
								}
								catch(\Exception $e){
									// data - is string part of file
								}

								if($bJson){
									if($arResult && is_array($arResult)){
										if(strlen($arResult['ERROR'])){
											$errorMessage = $arResult['ERROR'];
										}
										else{
											$errorMessage = 'Unknown error';
										}
									}
									else{
										$errorMessage = 'Bad response';
									}
								}
								else{
									$sDownloaded += strlen($data);
									if($data === ''){
										break;
									}

									if(@fwrite($hFileTo, $data) === false){
										$errorMessage = 'Can`t write file. Check free disk space';
									}
								}
							}
						}
					}
					else{
						$errorMessage = 'Download error. Response: '.$startLine;
					}
				}
				else{
					$errorMessage = 'Download error. Bad response';
				}

				@fclose($hFileTo);
			}
			else{
				$errorMessage = 'Can`t open tmp file for writing';
			}

			@fclose($hFileFrom);
		}
		else{
			$errorMessage = 'Can`t connect to '.$host.' ['.$error_id.'] '.$error_msg;
		}

		if(!strlen($errorMessage)){
			if($bFinished){
				$zipHash = sha1_file($tmpFile);
				if($zipHash != $arDownloadFile['HASH']){
					$errorMessage = 'File checksum does not match';
				}
				else{
					// delete old file
					@unlink($zipFile);

					// rename tmp file
					if(!@rename($tmpFile, $zipFile)){
						$errorMessage = 'Can`t rename file';
					}
				}
			}
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
	if($arDownloadFile){
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

		$sizeFile = $arDownloadFile['SIZE'];
		$sizeTmp = @filesize($tmpFile);

		$percent = round($stepsComplete / $stepsCount * 100);
		$status = GetMessage(
			'SERVICE_PREPARE_DATA_DOWNLOAD_PART',
			array(
				'#INDEX#' => $downloadIndex + 1 + ($bFinished ? ($downloadIndex + 2 <= count($_SESSION[$templateID]['FILES']) ? 1 : 0) : 0),
				'#MAX_INDEX#' => count($_SESSION[$templateID]['FILES']),
				'#TMP_SIZE#' => \CFile::FormatSize($sizeTmp),
				'#FILE_SIZE#' => \CFile::FormatSize($sizeFile),
			)
		);

		$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$serviceID."', '".$serviceStage."', '".$status."');";
		die("[response]".$response."[/response]");
	}
	else{
		// no more files to download, go to next step

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

		// status is clear stage
		$status = GetMessage('SERVICE_PREPARE_DATA_CLEAR');

		$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
		die("[response]".$response."[/response]");
	}
}
