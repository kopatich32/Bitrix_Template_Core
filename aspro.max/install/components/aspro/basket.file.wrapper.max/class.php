<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Main\ORM\Data\Result,
	CMax as Solution,
	CMaxRegionality as Regionality;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class CAsproBasketFileWrapperMax extends CBitrixComponent{
	protected $action;
	protected $arErrors = array();

	public function onPrepareComponentParams($arParams){
		$this->raiseOnPrepareParamsEvent($arParams);

		if(isset($arParams['CUSTOM_SITE_ID'])){
			$this->setSiteId($arParams['CUSTOM_SITE_ID']);
		}

		if(isset($arParams['CUSTOM_LANGUAGE_ID'])){
			$this->setLanguageId($arParams['CUSTOM_LANGUAGE_ID']);
		}

		$siteId = $this->getSiteId();
		$languageId = $this->getLanguageId();

		// MESSAGES PARAMETERS
		$arParams['USE_CUSTOM_MESSAGES'] = isset($arParams['USE_CUSTOM_MESSAGES']) && $arParams['USE_CUSTOM_MESSAGES'] === 'Y' ? 'Y' : 'N';
		if($arParams['USE_CUSTOM_MESSAGES'] === 'Y'){
			$arParams['MESS_BASKET_TITLE'] = isset($arParams['MESS_BASKET_TITLE']) ? trim($arParams['MESS_BASKET_TITLE']) : '';
			$arParams['MESS_BASKET_CAN_BUY_ITEMS_TITLE'] = isset($arParams['MESS_BASKET_CAN_BUY_ITEMS_TITLE']) ? trim($arParams['MESS_BASKET_CAN_BUY_ITEMS_TITLE']) : '';
			$arParams['MESS_BASKET_DELAY_ITEMS_TITLE'] = isset($arParams['MESS_BASKET_DELAY_ITEMS_TITLE']) ? trim($arParams['MESS_BASKET_DELAY_ITEMS_TITLE']) : '';
			$arParams['MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE'] = isset($arParams['MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE']) ? trim($arParams['MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE']) : '';
		}
		else{
			unset(
				$arParams['MESS_BASKET_TITLE'],
				$arParams['MESS_BASKET_CAN_BUY_ITEMS_TITLE'],
				$arParams['MESS_BASKET_DELAY_ITEMS_TITLE'],
				$arParams['MESS_BASKET_NOT_AVAILABLE_ITEMS_TITLE']
			);
		}

		$this->arResult = array(
			'ERRORS' => array(),
			'TEMPLATE' => false,
			'ACTION' => false,
			'SITE_ID' => false,
			'USER_ID' => false,
			'REGION_ID' => false,
		);

		$this->arErrors =& $this->arResult['ERRORS'];

		return $arParams;
	}

	protected function raiseOnPrepareParamsEvent(&$arParams){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnPrepareBasketFileWrapperParams',
				true
			) as $arEvent){
			\ExecuteModuleEventEx(
				$arEvent,
				array(
					&$arParams,
				)
			);
		}
	}

	public function executeComponent(){
		$action = 'mainAction';
		if(is_callable(array($this, $action))){
			return $this->{$action}();
		}
	}

	protected function mainAction(){
		$this->makeResult();

		$this->includeComponentTemplate();

		return !$this->hasError();
	}

	public function hasError(){
		return boolval($this->arErrors);
	}

	public function setError($message){
		if($message instanceof Result){
			$errors = $message->getErrorMessages();
		}
		else{
			$errors = array($message);
		}

		foreach($errors as $error){
			if(!in_array($error, $this->arErrors, true)){
				$this->arErrors[] = $error;
			}
		}

		return false;
	}

	protected function makeResult(){
		$template =& $this->arResult['TEMPLATE'];
		$action =& $this->arResult['ACTION'];
		$siteId =& $this->arResult['SITE_ID'];
		$userId =& $this->arResult['USER_ID'];
		$regionId =& $this->arResult['REGION_ID'];

		// site
		$siteId = $this->getSiteId();

		// user
		if(
			$GLOBALS['USER'] &&
			$GLOBALS['USER'] instanceof \CUser
		){
			$userId = $GLOBALS['USER']->GetID();
		}

		// region
		if($bUseRegionality = Regionality::checkUseRegionality()){
			if($arRegion = Regionality::getCurrentRegion()){
				$regionId = $arRegion['ID'];
			}
		}

		// action
		$action = 'DOWNLOAD';

		// template
		if(Loader::includeModule(Solution::moduleID)){
			$template = Solution::GetFrontParametrValue('BASKET_FILE_DOWNLOAD_TEMPLATE', $siteId);
		}
		else{
			$template = 'xls';
		}

		return !$this->hasError();
	}

}
