<?
namespace Aspro\Max\Components;

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Web\Json,
    Bitrix\Main\SystemException,
    CMax as Solution,
    CMaxTools as SolutionTools;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class Developer extends \CBitrixComponent {
	const ASPRO_URL = 'https://partner.aspro.ru/tools/developer.php';

    public function onPrepareComponentParams($arParams){
    	if(isset($arParams['CUSTOM_SITE_ID'])){
			$this->setSiteId($arParams['CUSTOM_SITE_ID']);
		}

		if(isset($arParams['CUSTOM_LANGUAGE_ID'])){
			$this->setLanguageId($arParams['CUSTOM_LANGUAGE_ID']);
		}

        return $arParams;
    }

    protected function includeModules(){
        if(!Loader::includeModule(Solution::moduleID)){
            throw new SystemException(Loc::getMessage('DA_C_ERROR_MODULE_NOT_INSTALLED'));
        }
    }

    public function executeComponent(){
    	$isAjax = $this->request->isPost() && $this->request['mode'] === 'ajax' && $this->request['action'] === 'getDeveloper';
        $isAjaxBlockToggle = $this->request->isPost() && $this->request['BLOCK'] === 'FOOTER_TOGGLE_DEVELOPER' && $this->request['IS_AJAX'] === 'Y';

    	if($isAjax){
			$GLOBALS['APPLICATION']->RestartBuffer();
		}

        try{
            $this->includeModules();

			$siteId = $this->getSiteId();
            //$footerType = Solution::GetFrontParametrValue('FOOTER_TYPE', $siteId);
            if(Solution::GetFrontParametrValue('FOOTER_TOGGLE_DEVELOPER', $siteId) !== 'N'){
                $footerColor = $this->arParams["COLOR"];
				//$footerColor = strtolower(Solution::GetFrontParametrValue('FOOTER_COLOR_'.$footerType, $siteId));
				$themeViewColor = strtolower(Solution::GetFrontParametrValue('THEME_VIEW_COLOR', $siteId));
				
				$color = $themeViewColor === 'dark' ? $themeViewColor : $footerColor;

				if($themeViewColor === 'default'){
					$prefersColorScheme = $this->request['prefersColorScheme'] === 'dark' ? $this->request['prefersColorScheme'] : 'light';
					$color = $prefersColorScheme === 'dark' ? $prefersColorScheme : $footerColor;
				}

                $bShowPartner = Solution::GetFrontParametrValue('FOOTER_TOGGLE_DEVELOPER_PARTNER', $siteId) === 'Y';

				$arDeveloper = array();
                if($bShowPartner){
					$arConfig = array(
						'COLOR' => $color,
					);

                	if(
                        $isAjax ||
                        $isAjaxBlockToggle
                    ){
                		$arDeveloper = $this->getDeveloper($arConfig);
                	}
                	else{
	                	$arDeveloper = $this->getCachedDeveloper($arConfig);
                	}
                }

				$bShowDefault = true;
				if(
					$bShowPartner &&
					$arDeveloper &&
					$arDeveloper['PARTNER_ID']
				){
					$title = $arDeveloper['TITLE'] ?? '';
					$link = $arDeveloper['LINK'] ?? '';
					$logo = $arDeveloper['LOGO'] ?? '';

					$bShowDefault = !boolval($title || $link || $logo);
				}

                $signer = new \Bitrix\Main\Security\Sign\Signer;
				$signedParams = $signer->sign(base64_encode(serialize($this->arParams)), str_replace(':', '.', $this->getName()));

				$bNeedAjaxActualData = $bShowPartner && $bShowDefault && !$arDeveloper && !$isAjax && !$isAjaxBlockToggle;

                $this->arResult = array(
					'COLOR' => $color,
                	'SHOW_PARTNER' => $bShowPartner,
					'SHOW_DEFAULT' => $bShowDefault,
                	'DEVELOPER' => $arDeveloper,
                	'SIGNED_PARAMS' => $signedParams,
                	'IS_AJAX' => $isAjax || $isAjaxBlockToggle,
                	'NEED_AJAX' => $bNeedAjaxActualData,
                );

                if(
                	$this->StartResultCache(
                		$this->arParams['CACHE_TIME'],
                		serialize(
                			array(
                				($this->arParams['CACHE_GROUPS'] === 'N' ? false : $GLOBALS['USER']->GetGroups()),
                				$this->arResult
                			)
                		)
                	)
                ){
                	if($isAjax){
						$GLOBALS['APPLICATION']->RestartBuffer();
					}

	                $this->includeComponentTemplate();
				}
            }
        }
        catch(SystemException $e){
            // echo $e->getMessage();
        }

        return $this->arResult;
    }

    private function getLicenseKey(){
    	static $licenseKey;

    	if(!isset($licenseKey)){
	    	include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/license_key.php');
	    	$licenseKey = $LICENSE_KEY;
    	}

		return $licenseKey;
    }

    private function getCacheParams($licenseKey, $arConfig){
    	$cacheTag = 'max_developer';
    	$cachePath = str_replace('\\', DIRECTORY_SEPARATOR, __CLASS__);
    	$cacheTime = $this->arParams['CACHE_TIME'];
    	$cacheID = md5(serialize((array)$arConfig).$licenseKey);

    	return array($cacheTag, $cachePath, $cacheTime, $cacheID);
    }

    public function getCachedDeveloper($arConfig){
    	$arDeveloper = array();
    	$licenseKey = $this->getLicenseKey();

    	list($cacheTag, $cachePath, $cacheTime, $cacheID) = $this->getCacheParams($licenseKey, $arConfig);
    	$obCache = new \CPHPCache();
    	if($obCache->InitCache($cacheTime, $cacheID, $cachePath)){
    		$res = $obCache->GetVars();
			$arDeveloper = $res['arRes'];
    	}

    	return $arDeveloper;
    }

    public function getDeveloper($arConfig){
    	$arDeveloper = array();
    	$licenseKey = $this->getLicenseKey();

    	$arData = array(
			'LICENSE_KEY' => $licenseKey,
			'MODULE_ID' => Solution::moduleID,
			'LANGUAGE_ID' => $this->getLanguageId(),
		);

		$arData = array_merge($arData, (array)$arConfig);

		$key = base64_encode($licenseKey);
		$arData = array(
			'd' => SolutionTools::___1596018847($arData, $key),
			'k' => $key,
		);

		try{
			$http = new \Bitrix\Main\Web\HttpClient(
				array(
					'socketTimeout' => 5,
				)
			);
			$response = $http->post(self::ASPRO_URL, $arData);
		}
		catch(SystemException $e){
			// echo $e->getMessage();
			$response = '';
		}

		if(strlen($response)){
			try{
				$arDeveloper = Json::decode($response);

				if(
					$arDeveloper &&
					is_array($arDeveloper)
				){
					list($cacheTag, $cachePath, $cacheTime, $cacheID) = $this->getCacheParams($licenseKey, $arConfig);
					$obCache = new \CPHPCache();
					$obCache->StartDataCache($cacheTime, $cacheID, $cachePath);

					if($GLOBALS['CACHE_MANAGER']){
						$GLOBALS['CACHE_MANAGER']->StartTagCache($cachePath);
						$GLOBALS['CACHE_MANAGER']->RegisterTag($cacheID);
						$GLOBALS['CACHE_MANAGER']->EndTagCache();
					}

					$obCache->EndDataCache(
						array(
							'arRes' => $arDeveloper
						)
					);
				}
				else{
					$arDeveloper = array();
				}
			}
			catch(SystemException $e){
				// echo $e->getMessage();
				$arDeveloper = array();
			}
		}

		return $arDeveloper;
    }
}