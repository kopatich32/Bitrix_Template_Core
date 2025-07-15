<?
namespace Aspro\Max\Components;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\SystemException,
	Bitrix\Main\Web\Json,
	CMax as Solution,
	CMaxRegionality as Regionality,
	CMaxCache as Cache,
	Aspro\Max\Components\RegionalityList;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class RegionalityListController extends \Bitrix\Main\Engine\Controller {
	public function configureActions(){
		return array(
			'getMainCities' => array(
				'prefilters' => array(),
			),
			'getLevelsAndCities' => array(
				'prefilters' => array(),
			),
			'searchCities' => array(
				'prefilters' => array(),
			),
		);
	}

	public function getMainCitiesAction($siteId, $lang, $url, $lastId){
		$this->includeModules();
		$this->checkSite($siteId);
		$this->checkLang($lang);
		$this->checkPage($lastId);

		Regionality::setSiteId($siteId);
		$regionsType = Solution::GetFrontParametrValue('REGIONALITY_TYPE', $siteId);

		\CBitrixComponent::includeComponentClass('aspro:regionality.list.max');
		$arMainCities = RegionalityList::getMainCities($url, $regionsType, $lang, $lastId);

		return $arMainCities;
	}

	public function getLevelsAndCitiesAction($siteId, $lang, $url, $level1, $level2){
		$this->includeModules();
		$this->checkSite($siteId);
		$this->checkLang($lang);
		$this->checkLevel($level1);
		$this->checkLevel($level2);

		Regionality::setSiteId($siteId);
		$regionsType = Solution::GetFrontParametrValue('REGIONALITY_TYPE', $siteId);

		\CBitrixComponent::includeComponentClass('aspro:regionality.list.max');
		$arLevelsAndCities = RegionalityList::getLevelsAndCities($url, $regionsType, $lang, $level1, $level2);

		return $arLevelsAndCities;
	}

	public function searchCitiesAction($siteId, $lang, $url, $term){
		$this->includeModules();
		$this->checkSite($siteId);
		$this->checkLang($lang);
		$this->checkPage($lastId);

		$term = iconv('UTF-8', SITE_CHARSET, $term);

		Regionality::setSiteId($siteId);
		$regionsType = Solution::GetFrontParametrValue('REGIONALITY_TYPE', $siteId);

		\CBitrixComponent::includeComponentClass('aspro:regionality.list.max');
		$arCities = RegionalityList::searchCities($term, $url, $regionsType, $lang);

		return [
			'cities' => $arCities,
		];
	}

	protected function includeModules(){
		if(!Loader::includeModule(Solution::moduleID)){
			throw new SystemException(Loc::getMessage('RLM_C_ERROR_MODULE_NOT_INSTALLED'));
		}
        if(!Loader::includeModule('sale')){
			throw new SystemException(Loc::getMessage('RLM_C_ERROR_MODULE_SALE_NOT_INSTALLED'));
		}
	}

	protected function checkSite($siteId){
		if(!$siteId){
			throw new SystemException(Loc::getMessage('RLM_C_ERROR_BAD_SITE_PARAMS'));
		}
	}

	protected function checkLang(&$lang){
		$lang = $lang ?: LANGUAGE_ID;
	}

	protected function checkPage(&$lastId){
		$lastId = intval($lastId) >= 0 ? intval($lastId) : 0;
	}

	protected function checkId(&$id){
		$id = intval($id) >= 0 ? intval($id) : 0;
	}

	protected function checkLevel(&$id){
		$id = trim($id);
		$id = strlen($id) ? $id : '';
	}
}
