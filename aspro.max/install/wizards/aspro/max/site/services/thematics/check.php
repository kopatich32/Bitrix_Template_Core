<?
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Config\Option;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
if(!defined('WIZARD_THEMATIC')) return;

ob_start();
$errorMessage = '';
$arData = array();

$templateID = $wizard->GetVar('templateID');
unset($_SESSION[$templateID]);

if(CModule::IncludeModule(ASPRO_MODULE_NAME)){
	if($obModule = CModule::CreateModuleObject(ASPRO_MODULE_NAME)){
		$moduleClass = $obModule::moduleClass;
		$moduleToolsClass = $moduleClass.'Tools';
		if(
			class_exists($moduleClass) &&
			class_exists($moduleToolsClass)
		){
			if(
				file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php') &&
				file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client_partner.php')
			){
				include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client.php');
				$arUpdateList = CUpdateClient::GetUpdatesList($errorMessage, 'en', 'Y');
				$arClient = $arUpdateList['CLIENT'][0]['@'];
				$arData = array(
					'ACTION' => 'check',
					'CLIENT' => array(
						'LICENSE_KEY' => $key = CUpdateClient::GetLicenseKey(),
						'CMS_EDITION' => '',
						'PARTNER_ID' => '',
					),
					'MODULE' => array(
						'SM_VERSION' => SM_VERSION,
						'MODULE_ID' => ASPRO_MODULE_NAME,
						'MODULE_VERSION' => $obModule->MODULE_VERSION,
						'CHARSET' => SITE_CHARSET,
						'LANGUAGE_ID' => LANGUAGE_ID,
						'THEMATIC' => WIZARD_THEMATIC,
					),
					'TIMESTAMP' => time(),
				);

				if(isset($arClient) && is_array($arClient)){
					$arData['CLIENT']['CMS_EDITION'] = $arClient['LICENSE'];
					$arData['CLIENT']['PARTNER_ID'] = $arClient['PARTNER_ID'];
				}
			}
		}
	}
}

if($arData){

	$key = base64_encode($key);
	$arData = array(
		'd' => $moduleToolsClass::___1596018847($arData, $key),
		'k' => $key,
	);

	try{
		$httpClient = new HttpClient();
		$httpClient->setTimeout(15);
		$httpClient->setStreamTimeout(15);

		// proxy
		if($bUseProxy = strlen($proxyAddress = Option::get('main', 'update_site_proxy_addr')) && strlen($proxyPort = Option::get('main', 'update_site_proxy_port'))){
			$httpClient->setProxy(
				$proxyAddress,
				$proxyPort,
				Option::get('main', 'update_site_proxy_user'),
				Option::get('main', 'update_site_proxy_pass')
			);
		}

		if($httpClient->query(HttpClient::HTTP_POST, ASPRO_REP_URL, $arData)){
			// no request error
			$response = $httpClient->getResult();

			// get status of HTTP response
			$status = $httpClient->getStatus();

			// is OK?
			if($status === 200){
				$arResult = \Bitrix\Main\Web\Json::decode($response);
				if($arResult && is_array($arResult)){
					if(strlen($arResult['ERROR'])){
						$errorMessage = $arResult['ERROR'];
					}
					else{
						if(is_array($arResult['FILES'])){
							$_SESSION[$templateID] = array(
								'FILES' => $arResult['FILES'],
								'DOWNLOADED_FILES' => array(),
								'UNZIPED_FILES' => array(),
							);
						}
						else{
							$errorMessage = 'Bad response';
						}
					}
				}
				else{
					$errorMessage = 'Bad response';
				}
			}
			else{
				$errorMessage = 'Bad response status';
			}
		}
		else{
			$errorMessage = implode('<br />', $httpClient->getError());
		}
	}
	catch(\Exception $e){
		$errorMessage = $e->getMessage();
	}
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

	// status is download stage
	$status = GetMessage('SERVICE_PREPARE_DATA_DOWNLOAD');

	$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."', '".$status."');";
	die("[response]".$response."[/response]");
}
