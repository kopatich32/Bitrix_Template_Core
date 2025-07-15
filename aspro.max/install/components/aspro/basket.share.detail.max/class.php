<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Main\ORM\Data\Result,
	Bitrix\Sale\Basket,
	Bitrix\Sale\Fuser,
	Bitrix\Sale\Compatible\DiscountCompatibility,
	Bitrix\Main\Web\Json,
	CMax as Solution,
	CMaxRegionality as Regionality,
	Aspro\Max\ShareBasketTable,
	Aspro\Max\ShareBasketItemTable;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class CAsproBasketShareDetailMax extends CBitrixComponent{
	protected $action;
	protected $isAjax;
	protected $arCheckedItems;
	protected $sessionVar;
	protected $arSessionUserParams;
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

		// BASE PARAMETERS
		$arParams['CODE'] = isset($arParams['CODE']) ? trim($arParams['CODE']) : '';

		$arParams['SET_PAGE_TITLE'] = !isset($arParams['SET_PAGE_TITLE']) || $arParams['SET_PAGE_TITLE'] !== 'N' ? 'Y' : 'N';

		// VISUAL PARAMETERS
		$arParams['ACTUAL'] = isset($arParams['ACTUAL']) && $arParams['ACTUAL'] === 'N' ? 'N' : 'Y';
		$arParams['SHOW_VERSION_SWITCHER'] = isset($arParams['SHOW_VERSION_SWITCHER']) && $arParams['SHOW_VERSION_SWITCHER'] === 'N' ? 'N' : 'Y';
		$arParams['USE_COMPARE'] = isset($arParams['USE_COMPARE']) && $arParams['USE_COMPARE'] === 'N' ? 'N' : 'Y';
		$arParams['USE_DELAY'] = isset($arParams['USE_DELAY']) && $arParams['USE_DELAY'] === 'Y' ? 'Y' : 'N';
		if($arParams['USE_DELAY'] === 'Y'){
			if(Loader::includeModule(Solution::moduleID)){
				$arParams['USE_DELAY'] = Solution::GetFrontParametrValue('CATALOG_DELAY', $siteDir);
			}
		}

		$arParams['USE_FAST_VIEW'] = isset($arParams['USE_FAST_VIEW']) && $arParams['USE_FAST_VIEW'] === 'Y' ? 'Y' : 'N';
		if($arParams['USE_FAST_VIEW'] === 'Y'){
			if(Loader::includeModule(Solution::moduleID)){
				$arParams['USE_FAST_VIEW'] = (Solution::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL', $siteDir) === 'NO' ? 'N' : 'Y');
			}
		}

		$arParams['SHOW_ONE_CLICK_BUY'] = isset($arParams['SHOW_ONE_CLICK_BUY']) && $arParams['SHOW_ONE_CLICK_BUY'] === 'Y' ? 'Y' : 'N';
		$arParams['SHOW_STICKERS'] = isset($arParams['SHOW_STICKERS']) && $arParams['SHOW_STICKERS'] === 'Y' ? 'Y' : 'N';
		$arParams['SHOW_AMOUNT'] = isset($arParams['SHOW_AMOUNT']) && $arParams['SHOW_AMOUNT'] === 'N' ? 'N' : 'Y';
		$arParams['SHOW_OLD_PRICE'] = isset($arParams['SHOW_OLD_PRICE']) && $arParams['SHOW_OLD_PRICE'] === 'N' ? 'N' : 'Y';
		$arParams['SHOW_DISCOUNT_PERCENT'] = isset($arParams['SHOW_DISCOUNT_PERCENT']) && $arParams['SHOW_DISCOUNT_PERCENT'] === 'N' ? 'N' : 'Y';
		if($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y'){
			$arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] = isset($arParams['SHOW_DISCOUNT_PERCENT_NUMBER']) && $arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] === 'N' ? 'N' : 'Y';
		}
		else{
			unset($arParams['SHOW_DISCOUNT_PERCENT_NUMBER']);
		}

		$arParams['PRODUCT_PROPERTIES'] = (isset($arParams['PRODUCT_PROPERTIES']) && is_array($arParams['PRODUCT_PROPERTIES']) && $arParams['PRODUCT_PROPERTIES']) ? $arParams['PRODUCT_PROPERTIES'] : array(
			'CML2_ARTICLE',
		);

		// MESSAGES PARAMETERS
		$arParams['USE_CUSTOM_MESSAGES'] = isset($arParams['USE_CUSTOM_MESSAGES']) && $arParams['USE_CUSTOM_MESSAGES'] === 'Y' ? 'Y' : 'N';
		if($arParams['USE_CUSTOM_MESSAGES'] === 'Y'){
			$arParams['MESS_TITLE'] = isset($arParams['MESS_TITLE']) ? trim($arParams['MESS_TITLE']) : '';
			$arParams['MESS_SHOW_ACTUAL_PRICES'] = isset($arParams['MESS_SHOW_ACTUAL_PRICES']) ? trim($arParams['MESS_SHOW_ACTUAL_PRICES']) : '';
			$arParams['MESS_TOTAL_PRICE'] = isset($arParams['MESS_TOTAL_PRICE']) ? trim($arParams['MESS_TOTAL_PRICE']) : '';
			$arParams['MESS_ADD_TO_BASKET'] = isset($arParams['MESS_ADD_TO_BASKET']) ? trim($arParams['MESS_ADD_TO_BASKET']) : '';
			$arParams['MESS_REPLACE_BASKET'] = isset($arParams['MESS_REPLACE_BASKET']) ? trim($arParams['MESS_REPLACE_BASKET']) : '';
			$arParams['MESS_PRODUCT_ECONOMY'] = isset($arParams['MESS_PRODUCT_ECONOMY']) ? trim($arParams['MESS_PRODUCT_ECONOMY']) : '';
			$arParams['MESS_PRODUCT_NOT_EXISTS'] = isset($arParams['MESS_PRODUCT_NOT_EXISTS']) ? trim($arParams['MESS_PRODUCT_NOT_EXISTS']) : '';
		}
		else{
			unset(
				$arParams['MESS_TITLE'],
				$arParams['MESS_SHOW_ACTUAL_PRICES'],
				$arParams['MESS_TOTAL_PRICE'],
				$arParams['MESS_ADD_TO_BASKET'],
				$arParams['MESS_REPLACE_BASKET'],
				$arParams['MESS_PRODUCT_ECONOMY'],
				$arParams['MESS_PRODUCT_NOT_EXISTS']
			);
		}

		// 404 PARAMS
		$arParams['SET_STATUS_404'] = isset($arParams['SET_STATUS_404']) && $arParams['SET_STATUS_404'] === 'Y' ? 'Y' : 'N';
		$arParams['SHOW_404'] = isset($arParams['SHOW_404']) && $arParams['SHOW_404'] === 'Y' ? 'Y' : 'N';
		$arParams['MESSAGE_404'] = isset($arParams['MESSAGE_404']) ? trim($arParams['MESSAGE_404']) : '';
		$arParams['FILE_404'] = isset($arParams['FILE_404']) ? trim($arParams['FILE_404']) : '';

		// PRICES PARAMS
		$arParams['PRICE_CODE_IDS'] = isset($arParams['PRICE_CODE_IDS']) && is_array($arParams['PRICE_CODE_IDS']) ? $arParams['PRICE_CODE_IDS'] : array();
		$arParams['PRICE_CODE'] = isset($arParams['PRICE_CODE']) && is_array($arParams['PRICE_CODE']) ? $arParams['PRICE_CODE'] : array();

		// STICKERS PARAMS
		$arParams['FAV_ITEM'] = isset($arParams['FAV_ITEM']) ? trim($arParams['FAV_ITEM']) : 'FAVORIT_ITEM';
		$arParams['FINAL_PRICE'] = isset($arParams['FINAL_PRICE']) ? trim($arParams['FINAL_PRICE']) : 'FINAL_PRICE';
		$arParams['STIKERS_PROP'] = isset($arParams['STIKERS_PROP']) ? trim($arParams['STIKERS_PROP']) : 'HIT';
		$arParams['SALE_STIKER'] = isset($arParams['SALE_STIKER']) ? trim($arParams['SALE_STIKER']) : 'SALE_TEXT';

		$signer = new \Bitrix\Main\Security\Sign\Signer;
		$signedParams = $signer->sign(base64_encode(serialize($arParams)), explode(':', $this->{'__name'})[1]);

		$this->isAjax = $this->request->isPost() && $this->request['is_ajax_post'] === 'Y';
		$this->sessionVar = $this->{'__name'};
		$this->arCheckedItems = array();

		$this->arResult = array(
			'ERRORS' => array(),
			'SITE' => array(),
			'USER' => array(),
			'REGION' => array(),
			'STORES' => array(),
			'SHARE_BASKET' => array(),
			'BASKET_PAGE_URL' => '',
			'AJAX_URL' => $this->getPath().'/ajax.php',
			'IS_AJAX' => $this->isAjax ? 'Y' : 'N',
			'SIGNED_PARAMS' => $signedParams,
		);

		$this->arErrors =& $this->arResult['ERRORS'];

		return $arParams;
	}

	protected function raiseOnPrepareParamsEvent(&$arParams){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnPrepareBasketShareDetailParams',
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
		if($this->isAjax){
			$GLOBALS['APPLICATION']->RestartBuffer();
		}

		$this->initSessionUserParams();
		$this->initCheckedItems();

		$action = 'mainAction';
		if(is_callable(array($this, $action))){
			$this->{$action}();
		}

		if($this->isAjax){
			$GLOBALS['APPLICATION']->FinalActions();
			die();
		}
		else{
			return $this->hasError() ? false : $this->arResult['BASKET']['ID'];
		}
	}

	protected function mainAction(){
		\Bitrix\Main\Data\StaticHtmlCache::getInstance()->markNonCacheable();

		if($this->includeModules()){
			DiscountCompatibility::stopUsageCompatible();
			$this->makeResult();
			DiscountCompatibility::revertUsageCompatible();
		}

		$this->makeAjaxResult();

		$this->includeComponentTemplate();

		if($this->arParams['SET_PAGE_TITLE'] === 'Y'){
			$GLOBALS['APPLICATION']->SetTitle(strlen($this->arParams['MESS_TITLE']) ? $this->arParams['MESS_TITLE'] : Loc::getMessage('BSD_C_MESS_TITLE_DEFAULT'));
		}

		return $this->hasError() ? false : $this->arResult['BASKET']['ID'];
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

	protected function includeModules(){
		Loader::includeModule('fileman');

		if(!Loader::includeModule('sale')){
			$this->setError(Loc::getMessage('BSD_C_ERROR_MODULE_SALE_NOT_INSTALL'));
		}

		if(!Loader::includeModule('currency')){
			$this->setError(Loc::getMessage('BSD_C_ERROR_MODULE_CURRENCY_NOT_INSTALL'));
		}

		if(!Loader::includeModule('catalog')){
			$this->setError(Loc::getMessage('BSD_C_ERROR_MODULE_CATALOG_NOT_INSTALL'));
		}

		if(!Loader::includeModule(Solution::moduleID)){
			$this->setError(Loc::getMessage('BSD_C_ERROR_MODULE_SOLUTION_NOT_INSTALL', array('#SOLUTION_MODULE_ID#' => Solution::moduleID)));
		}

		return !$this->hasError();
	}

	protected function initSessionUserParams(){
		if(
			!isset($_SESSION[$this->sessionVar]) ||
			!is_array($_SESSION[$this->sessionVar])
		){
			$_SESSION[$this->sessionVar] = array();
		}

		if(
			$_SESSION[$this->sessionVar] &&
			$_SESSION[$this->sessionVar]['USER_PARAMS']
		){
			$this->arSessionUserParams = $_SESSION[$this->sessionVar]['USER_PARAMS'];

			if($this->arParams['SHOW_VERSION_SWITCHER'] === 'N'){
				$this->arSessionUserParams['ORIGINAL'] = $this->arParams['ACTUAL'] === 'N' ? 'Y' : 'N';
			}
		}
		else{
			$this->arSessionUserParams = array(
				'ORIGINAL' => $this->arParams['ACTUAL'] === 'N' ? 'Y' : 'N',
			);
		}

		$this->arSessionUserParams['ORIGINAL'] = $this->arSessionUserParams['ORIGINAL'] === 'Y' ? 'Y' : 'N';

		if($this->isAjax){
			$request =& $this->request;

			if($original = $request->get('ORIGINAL')){
				$this->arSessionUserParams['ORIGINAL'] = $original === 'Y' ? 'Y' : 'N';
			}
		}

		$_SESSION[$this->sessionVar]['USER_PARAMS'] = $this->arSessionUserParams;
	}

	protected function initCheckedItems(){
		if($this->isAjax){
			$request =& $this->request;
			$arChecked = $request->get('CHECKED');
			$arChecked = is_array($arChecked) ? $arChecked : array();
			foreach(
				(is_array($arChecked) ? $arChecked : array()) as $id => $value
			){
				$id = intval($id);

				if(
					$value === 'Y' &&
					$id > 0
				){
					$this->arCheckedItems[] = $id;
				}
			}
		}
	}

	protected function makeAjaxResult(){
		if($this->isAjax){
			$request =& $this->request;
			$action = $request->get('ACTION');

			if(
				$action === 'ADD2BASKET' ||
				$action === 'REPLACEBASKET'
			){
				if(!$this->hasError()){
					$siteId = $this->getSiteId();
					$userId = $this->arResult['USER'] ? $this->arResult['USER']['ID'] : false;

					if($userId){
						$fuserId = Fuser::getIdByUserId($userId);
					}
					else{
						$fuserId = Fuser::getId();
					}

					if($action === 'REPLACEBASKET'){
						\CSaleBasket::DeleteAll($fuserId, false);
					}

					$basket = Basket::loadItemsForFUser($fuserId, $this->getSiteId());

					if($this->arResult['SHARE_BASKET']['ITEMS']){
						if($this->arResult['SHARE_BASKET']['ITEMS']['MAIN']){
							foreach($this->arResult['SHARE_BASKET']['ITEMS']['MAIN'] as $arItem){
								if(
									$arItem['CHECKED'] === 'Y' &&
									isset($arItem['PRODUCT']) &&
									$arItem['PRODUCT'] &&
									$arItem['PRODUCT']['ID']
								){
									$arProperties = $arItem['BASKET_PROPS'] ? $arItem['BASKET_PROPS'] : array();

									if($basketItem = $basket->getExistsItem('catalog', $arItem['PRODUCT']['ID'], $arProperties)){
										$basketItem->setField('QUANTITY', $basketItem->getQuantity() + $arItem['PRODUCT']['QUANTITY']);
									}
									else{
										$basketItem = $basket->createItem('catalog', $arItem['PRODUCT']['ID']);
										$basketItem->setFields(array(
											'QUANTITY' => $arItem['PRODUCT']['QUANTITY'],
											'CURRENCY' => $arItem['PRODUCT']['CURRENCY'],
											'LID' => $siteId,
											'PRODUCT_PROVIDER_CLASS' => static::getCatalogProductProviderClass(),
										));

										if($arProperties){
											if($basketPropertyCollection = $basketItem->getPropertyCollection()){
												if(is_array($arProperties)){
													$basketPropertyCollection->setProperty($arProperties);
												}
											}
										}
									}
								}
							}

							$basket->save();
						}
					}
				}

				$arAjaxResult = array(
					'ERRORS' => $this->arErrors,
				);

				if(!$this->hasError()){
					$arAjaxResult['BASKET_PAGE_URL'] = $this->arResult['BASKET_PAGE_URL'];
				}

				echo Json::encode($arAjaxResult);
				$GLOBALS['APPLICATION']->FinalActions();
				die();
			}
		}
	}

	protected function makeResult(){
		$code =& $this->arParams['CODE'];
		$arSite =& $this->arResult['SITE'];
		$arUser =& $this->arResult['USER'];
		$arRegion =& $this->arResult['REGION'];
		$arStores =& $this->arResult['STORES'];
		$arShareBasket =& $this->arResult['SHARE_BASKET'];
		$basketPageUrl =& $this->arResult['BASKET_PAGE_URL'];

		// basket page
		$basketPageUrl = Solution::GetFrontParametrValue('BASKET_PAGE_URL', $this->getSiteId());
		if(!strlen($basketPageUrl)){
			$basketPageUrl = SITE_DIR.'basket/';
		}

		// share basket
		$shareBasket = ShareBasketTable::getList(array(
			'filter' => array('=CODE' => $code),
			'limit' => 1,
		))->fetchObject();

		if(!$shareBasket){
			return $this->set404Error();
		}

		// site
		$siteId = $this->getSiteId();
		if(strlen($siteId)){
			if($arSite = \CSite::GetByID($siteId)->Fetch()){
				$arSite['DIR'] = $siteDir = preg_replace('/\/{2,}/', '/', '/'.$arSite['DIR'].'/');

				// lang
				$languageId = $arSite['LANGUAGE_ID'];
			}
			else{
				$siteId = false;
				$arSite = array();
			}
		}

		if(!strlen($siteId)){
			return $this->setError(Loc::getMessage('BSD_C_ERROR_SITE_ID'));
		}

		$basketSiteId = $shareBasket->getSiteId();
		if(strlen($basketSiteId)){
			if($arShareBasketSite = \CSite::GetByID($basketSiteId)->Fetch()){
				$arShareBasketSite['DIR'] = $basketSiteDir = preg_replace('/\/{2,}/', '/', '/'.$arShareBasketSite['DIR'].'/');

				// lang
				$basketLanguageId = $arShareBasketSite['LANGUAGE_ID'];
			}
			else{
				$basketSiteId = false;
				$arShareBasketSite = array();
			}
		}

		if(
			$siteId !== $basketSiteId ||
			!strlen($basketSiteId)
		){
			return $this->set404Error();
		}

		// user
		if(
			$GLOBALS['USER'] &&
			$GLOBALS['USER'] instanceof \CUser
		){
			$userId = $GLOBALS['USER']->GetID();
		}

		if($userId){
			if($arUser = \CUser::GetByID($userId)->Fetch()){

			}
			else{
				$userId = false;
				$arUser = array();
			}
		}

		$basketUserId =& $shareBasket->getUserId();
		if($basketUserId){
			if($arShareBasketUser = \CUser::GetByID($basketUserId)->Fetch()){

			}
			else{
				$basketUserId = false;
				$arShareBasketUser = array();
			}
		}

		// public
		$bBasketPublic = $shareBasket->getPublic();
		if(!$bBasketPublic){
			if($userId != $basketUserId){
				return $this->setError(Loc::getMessage('BSD_C_ERROR_BASKET_HIDDEN'));
			}
		}

		$arShareBasket['SITE_ID'] = $basketSiteId;
		$arShareBasket['SITE'] = $arShareBasketSite;
		$arShareBasket['USER_ID'] = $basketUserId;
		$arShareBasket['USER'] = $arShareBasketUser;
		$arShareBasket['PUBLIC'] = $bBasketPublic;

		// id
		$arShareBasket['ID'] = $shareBasket->getId();

		// code
		$arShareBasket['CODE'] = $code;

		// date create
		$arShareBasket['DATE_CREATE'] = $shareBasket->getDateCreate();

		// created by
		$arShareBasket['CREATED_BY'] = $shareBasket->getCreatedBy();

		// region
		$regionId = false;
		if($bUseRegionality = Regionality::checkUseRegionality()){
			if($arRegion = Regionality::getCurrentRegion()){
				$regionId = $arRegion['ID'];
				$arStores = static::getRegionStores($arRegion);
			}
		}

		$arShareBasket['REGION_ID'] = $shareBasket->getRegionId();
		if($arShareBasket['REGION_ID']){
			if($arShareBasket['REGION'] = static::getRegion($arShareBasket['REGION_ID'])){
				$arShareBasket['STORES'] = static::getRegionStores($arShareBasket['REGION']);
			}
			else{
				$arShareBasket['REGION_ID'] = false;
				$arShareBasket['STORES'] = array();
			}
		}

		// share basket items
		$arShareBasket['ITEMS'] = array(
			'MAIN' => array(),
			'DELAY' => array(),
		);

		$shareBasketItems = $shareBasket->fillItems();
		if(!$shareBasketItems){
			return $this->setError(Loc::getMessage('BSN_C_ERROR_BASKET_EMPTY'));
		}

		$arProductsIds = $arProducts = $arSectionsIds = $arSections = $arPricesIds = array();
		foreach($shareBasketItems as $shareBasketItem){
			$arItem = $shareBasketItem->collectValues();

			if(
				intval($arItem['ID']) <= 0 ||
				floatval($arItem['QUANTITY']) <= 0 ||
				floatval($arItem['RATIO']) <= 0 ||
				intval($arItem['PRODUCT_ID']) <= 0 ||
				!strlen($arItem['NAME']) ||
				intval($arItem['SECTION_ID']) < 0 ||
				floatval($arItem['BASE_PRICE']) < 0 ||
				floatval($arItem['PRICE']) < 0 ||
				floatval($arItem['FINAL_PRICE']) < 0 ||
				!strlen($arItem['CURRENCY'])
			){
				return $this->setError(Loc::getMessage('BSD_C_ERROR_BASKET_BAD_ITEM'));
			}

			$arProperties = Solution::unserialize($shareBasketItem->getBasketProps());
			$arItem['BASKET_PROPS'] = is_array($arProperties) ? $arProperties : array();

			$arItem['RATIO_IS_FLOAT'] = is_double($arItem['RATIO']);

			if($arItem['RATIO_IS_FLOAT']){
				$arItem['QUANTITY'] = floatval($arItem['QUANTITY']);
			}
			else{
				$arItem['QUANTITY'] = intval($arItem['QUANTITY']);
			}

			$block = $arItem['DELAY'] ? 'DELAY' : 'MAIN';

			$arProductsIds[] = $arItem['PRODUCT_ID'];

			if($arItem['SECTION_ID']){
				$arSectionsIds[] = $arItem['SECTION_ID'];
			}

			$arItem['BASE_PRICE_FORMATED'] = CurrencyFormat($arItem['BASE_PRICE'], $arItem['CURRENCY']);
			$arItem['PRICE_FORMATED'] = CurrencyFormat($arItem['PRICE'], $arItem['CURRENCY']);
			$arItem['DISCOUNT_PRICE_FORMATED'] = CurrencyFormat($arItem['DISCOUNT_PRICE'], $arItem['CURRENCY']);
			$arItem['FINAL_PRICE_FORMATED'] = CurrencyFormat($arItem['FINAL_PRICE'], $arItem['CURRENCY']);

			$arShareBasket['ITEMS'][$block][] = $arItem;
		}

		$bIblockVersion18_6_200 = Solution::checkVersionModule('18.6.200', 'iblock');

		if($arProductsIds){
            // group products by iblocks
            $arProductsIdsByIblockId = array();
			$dbRes = \CIBlockElement::GetList(
				array(),
				array('ID' => $arProductsIds),
				false,
				false,
				array(
					'ID',
					'IBLOCK_ID',
				)
			);
			while($arProduct = $dbRes->Fetch()){
				if(!array_key_exists($arProduct['IBLOCK_ID'], $arProductsIdsByIblockId)){
					$arProductsIdsByIblockId[$arProduct['IBLOCK_ID']] = array($arProduct['ID']);
				}
				else{
					$arProductsIdsByIblockId[$arProduct['IBLOCK_ID']][] = $arProduct['ID'];
				}
			}

            foreach($arProductsIdsByIblockId as $iblockId => $arIblockProductsIds){
                $dbRes = \CIBlockElement::GetList(
                    array(),
                    array(
                        'ID' => $arIblockProductsIds,
                        'IBLOCK_ID' => $iblockId,
                    ),
                    false,
                    false,
                    array(
                        'ID',
                        'IBLOCK_ID',
                        'PROPERTY_CML2_LINK',
                    )
                );
                while($arProduct = $dbRes->Fetch()){
                    if($arProduct['PROPERTY_CML2_LINK_VALUE']){
                        $arProductsIds[] = $arProduct['PROPERTY_CML2_LINK_VALUE'];
                    }
                }
            }

            // group products by iblocks
            $arProductsIdsByIblockId = array();
			$dbRes = \CIBlockElement::GetList(
				array(),
				array('ID' => $arProductsIds),
				false,
				false,
				array(
					'ID',
					'IBLOCK_ID',
				)
			);
			while($arProduct = $dbRes->Fetch()){
				if(!array_key_exists($arProduct['IBLOCK_ID'], $arProductsIdsByIblockId)){
					$arProductsIdsByIblockId[$arProduct['IBLOCK_ID']] = array($arProduct['ID']);
				}
				else{
					$arProductsIdsByIblockId[$arProduct['IBLOCK_ID']][] = $arProduct['ID'];
				}
			}

			$arSelect = array(
				'ID',
				'IBLOCK_ID',
				'NAME',
				'PREVIEW_PICTURE',
				'DETAIL_PICTURE',
				'DETAIL_PAGE_URL',
				'IBLOCK_SECTION_ID',
				'PROPERTY_CML2_LINK',
				'PROPERTY_CML2_ARTICLE',
			);

			$arPricesIds = array();
			if(!$this->arParams['PRICE_CODE_IDS']){
				$dbRes = \CCatalogGroup::GetList(
					array('SORT' => 'ASC'),
					array('NAME' => $this->arParams['PRICE_CODE'])
				);
				while($arPriceType = $dbRes->Fetch()){
					$this->arParams['PRICE_CODE_IDS'][] = $arPriceType['ID'];
				}
			}
			if($this->arParams['PRICE_CODE_IDS']){
				foreach($this->arParams['PRICE_CODE_IDS'] as $priceId){
					$arPricesIds[] = $priceId;
				}
			}

			if($bIblockVersion18_6_200){
				$arSelect = array_merge(
					$arSelect,
					array(
						'TYPE',
						'AVAILABLE',
						'QUANTITY',
						'QUANTITY_RESERVED',
						'QUANTITY_TRACE',
						'CAN_BUY_ZERO',
					)
				);
			}

			$arExcludePropertiesCodes = array(
				'CML2_ARTICLE',
				'HIT',
				'IN_STOCK',
				'MINIMUM_PRICE',
				'MAXIMUM_PRICE',
				'YM_ELEMENT_ID',
				'EXTENDED_REVIEWS_COUNT',
				'EXTENDED_REVIEWS_RAITING',
				'FAVORIT_ITEM',
				'BIG_BLOCK',
				'vote_count',
				'vote_sum',
				'rating',
				'VIDEO_YOUTUBE',
				'POPUP_VIDEO',
				'FORUM_TOPIC_ID',
				'FORUM_MESSAGE_CNT',
				'SALE_TEXT',
				'HELP_TEXT',
				'BLOG_POST_ID',
				'BLOG_COMMENTS_CNT',
				'FAV_ITEM',
				'FINAL_PRICE',
				'STIKERS_PROP',
				'SALE_STIKER',
            );
            
            foreach($arProductsIdsByIblockId as $iblockId => $arIblockProductsIds){
                $dbRes = \CIBlockElement::GetList(
                    array(),
                    array(
                        'ID' => $arIblockProductsIds,
                        'IBLOCK_ID' => $iblockId,
                    ),
                    false,
                    false,
                    $arSelect
                );
                while($arProduct = $dbRes->GetNext()){
                    if($arProduct['IBLOCK_SECTION_ID']){
                        $arSectionsIds[] = $arProduct['IBLOCK_SECTION_ID'];
                    }

                    if($arProduct['PREVIEW_PICTURE']){
                        $arProduct['PREVIEW_PICTURE'] = \CFile::GetFileArray($arProduct['PREVIEW_PICTURE']);
                    }

                    if($arProduct['DETAIL_PICTURE']){
                        $arProduct['DETAIL_PICTURE'] = \CFile::GetFileArray($arProduct['DETAIL_PICTURE']);
                    }

                    if(
                        $bIblockVersion18_6_200 ||
                        $arProductCatalog = \CCatalogProduct::GetByID($arProduct['ID'])
                    ){
                        if($arProductCatalog){
                            $arProduct['TYPE'] = $arProductCatalog['TYPE'];
                            $arProduct['AVAILABLE'] = $arProductCatalog['AVAILABLE'];
                            $arProduct['QUANTITY'] = $arProductCatalog['QUANTITY'];
                            $arProduct['QUANTITY_RESERVED'] = $arProductCatalog['QUANTITY_RESERVED'];
                            $arProduct['QUANTITY_TRACE'] = $arProductCatalog['QUANTITY_TRACE'];
                            $arProduct['CAN_BUY_ZERO'] = $arProductCatalog['CAN_BUY_ZERO'];
                        }

                        if($arProductMeasureRatio = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($arProduct['ID'])){
                            $arProduct = array_merge($arProduct, $arProductMeasureRatio[$arProduct['ID']]);
                        }
                    }

                    if(!$arProduct['TYPE']){
                        $arProduct['TYPE'] = 1;
                    }

                    if(!$arProduct['RATIO']){
                        $arProduct['RATIO'] = 1;
                    }
                    $arProduct['RATIO_IS_FLOAT'] = is_double($arProduct['RATIO']);

                    $arProduct['TOTAL_COUNT'] = Solution::GetTotalCount(
                        array(
                            'ID' => $arProduct['ID'],
                            'CATALOG_QUANTITY' => $arProduct['QUANTITY'],
                            '~CATALOG_QUANTITY' => $arProduct['QUANTITY'],
                            'PRODUCT' => array(
                                'TYPE' => $arProduct['TYPE'],
                            ),
                        ),
                        array(
                            'USE_REGION' => $arRegion ? 'Y' : 'N',
                            'STORES' => $arStores,
                        )
                    );
                    unset($arProduct['QUANTITY'], $arProduct['~QUANTITY']);

                    $arProduct['QUANTITY_ARRAY'] = Solution::GetQuantityArray(
                        $arProduct['TOTAL_COUNT'],
                        array(
                            'ID' => $arProduct['ID'],
                        ),
                        $useStoreClick = 'N',
                        $bShowAjaxItems = $arProduct['TYPE'] != 2 && $arStores,
                        '',
                        $siteId,
                        $userId
                    );

                    $arProduct['PRICES'] = \Aspro\Functions\CAsproMax::getPriceList($arProduct['ID'], $arPricesIds, 1, true);

                    $arProduct['PROPERTIES'] = array();

                    if($this->arParams['PRODUCT_PROPERTIES']){
                        foreach($this->arParams['PRODUCT_PROPERTIES'] as $propertyCode){
                            if(
                                !in_array(
                                    $propertyCode,
                                    $arExcludePropertiesCodes
                                )
                            ){
                                $arProduct['PROPERTIES'][$propertyCode] = array();

                                $res = \CIBlockElement::GetProperty($arProduct['IBLOCK_ID'], $arProduct['ID'], 'sort', 'asc', array('CODE' => $propertyCode));
                                while($arValue = $res->GetNext()){
                                    if($arProduct['PROPERTIES'][$propertyCode]){

                                        $arProduct['PROPERTIES'][$propertyCode]['VALUE'] = (array)$arProduct['PROPERTIES'][$propertyCode]['VALUE'];
                                        $arProduct['PROPERTIES'][$propertyCode]['VALUE'][] = $arValue['VALUE'];
                                        $arProduct['PROPERTIES'][$propertyCode]['~VALUE'] = $arProduct['PROPERTIES'][$propertyCode]['VALUE'];

                                        $arProduct['PROPERTIES'][$propertyCode]['PROPERTY_VALUE_ID'] = (array)$arProduct['PROPERTIES'][$propertyCode]['PROPERTY_VALUE_ID'];
                                        $arProduct['PROPERTIES'][$propertyCode]['PROPERTY_VALUE_ID'][] = $arValue['PROPERTY_VALUE_ID'];
                                        $arProduct['PROPERTIES'][$propertyCode]['~PROPERTY_VALUE_ID'] = $arProduct['PROPERTIES'][$propertyCode]['PROPERTY_VALUE_ID'];

                                        $arProduct['PROPERTIES'][$propertyCode]['DESCRIPTION'] = (array)$arProduct['PROPERTIES'][$propertyCode]['DESCRIPTION'];
                                        $arProduct['PROPERTIES'][$propertyCode]['DESCRIPTION'][] = $arValue['DESCRIPTION'];
                                        $arProduct['PROPERTIES'][$propertyCode]['~DESCRIPTION'] = $arProduct['PROPERTIES'][$propertyCode]['DESCRIPTION'];

                                        if($arProduct['PROPERTIES'][$propertyCode]['VALUE_XML_ID']){
                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_ENUM'] = (array)$arProduct['PROPERTIES'][$propertyCode]['VALUE_ENUM'];
                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_ENUM'][] = $arValue['VALUE_ENUM'];
                                            $arProduct['PROPERTIES'][$propertyCode]['~VALUE_ENUM'] = $arProduct['PROPERTIES'][$propertyCode]['VALUE_ENUM'];

                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_XML_ID'] = (array)$arProduct['PROPERTIES'][$propertyCode]['VALUE_XML_ID'];
                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_XML_ID'][] = $arValue['VALUE_XML_ID'];
                                            $arProduct['PROPERTIES'][$propertyCode]['~VALUE_XML_ID'] = $arProduct['PROPERTIES'][$propertyCode]['VALUE_XML_ID'];

                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_SORT'] = (array)$arProduct['PROPERTIES'][$propertyCode]['VALUE_SORT'];
                                            $arProduct['PROPERTIES'][$propertyCode]['VALUE_SORT'][] = $arValue['VALUE_SORT'];
                                            $arProduct['PROPERTIES'][$propertyCode]['~VALUE_SORT'] = $arProduct['PROPERTIES'][$propertyCode]['VALUE_SORT'];
                                        }
                                    }
                                    else{
                                        $arProduct['PROPERTIES'][$propertyCode] = $arValue;
                                    }
                                }
                            }
                        }
                    }

                    // stickers
                    if($this->arParams['SHOW_STICKERS'] === 'Y'){
                        foreach(
                            array(
                                'FAV_ITEM',
                                'FINAL_PRICE',
                                'STIKERS_PROP',
                                'SALE_STIKER',
                            ) as $propertyCode){
                            if(strlen($this->arParams[$propertyCode])){
                                $arProduct['PROPERTIES'][$this->arParams[$propertyCode]] = array();

                                $res = \CIBlockElement::GetProperty($arProduct['IBLOCK_ID'], $arProduct['ID'], 'sort', 'asc', array('CODE' => $this->arParams[$propertyCode]));
                                while($arValue = $res->GetNext()){
                                    if($arValue['VALUE']){
                                        if($propertyCode === 'STIKERS_PROP'){
                                            $arProduct['PROPERTIES'][$this->arParams[$propertyCode]]['VALUE'][] = $arValue['VALUE_ENUM'];
                                            $arProduct['PROPERTIES'][$this->arParams[$propertyCode]]['VALUE_XML_ID'][] = $arValue['VALUE_XML_ID'];
                                        }
                                        else{
                                            $arProduct['PROPERTIES'][$this->arParams[$propertyCode]] = $arValue;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $arProducts[$arProduct['ID']] = $arProduct;
                }
            }
		}

		if($arSectionsIds){
			$dbRes = \CIBlockSection::GetList(
				array(),
				array('ID' => $arSectionsIds),
				false,
				array(
					'ID',
					'NAME',
					'SECTION_PAGE_URL',
				),
				false
			);
			while($arSection = $dbRes->GetNext()){
				$arSections[$arSection['ID']] = $arSection;
			}

			foreach($arProducts as &$arProduct){
				if($arProduct['IBLOCK_SECTION_ID']){
					if(array_key_exists($arProduct['IBLOCK_SECTION_ID'], $arSections)){
						$arProduct['SECTION'] = $arSections[$arProduct['IBLOCK_SECTION_ID']];
					}
				}
			}
			unset($arProduct);
		}

		foreach($arShareBasket['ITEMS'] as $block => &$arItems){
			foreach($arItems as &$arItem){
				$pId = $arItem['PRODUCT_ID'];
				$arItem['PRODUCT'] = array_key_exists($pId, $arProducts) ? $arProducts[$pId] : array();

				if($arItem['PRODUCT']){
					$arItem['CHECKED'] = !$this->isAjax || in_array($arItem['ID'], $this->arCheckedItems) ? 'Y' : 'N';

					if($arItem['PRODUCT']['PROPERTY_CML2_LINK_VALUE']){
						$arItem['MAIN_PRODUCT'] =& $arProducts[$arItem['PRODUCT']['PROPERTY_CML2_LINK_VALUE']];
					}
				}
				else{
					$arItem['CHECKED'] = 'N';
				}
			}
			unset($arItem);
		}
		unset($arItems);

		// original
		$this->arResult['IS_ORIGINAL'] = $this->arSessionUserParams['ORIGINAL'];

		// fuser
		if($userId){
			$fuserId = Fuser::getIdByUserId($userId);
		}
		else{
			$fuserId = Fuser::getId();
		}

		// basket
		$siteId = false;  // !!! not current !!!
		$basket = Basket::create($siteId);
		if(!$basket){
			return $this->setError(Loc::getMessage('BSD_C_ERROR_BASKET_CREATE'));
		}

		foreach($arShareBasket['ITEMS'] as $block => &$arItems){
			foreach($arItems as &$arItem){
				$pId = $arItem['PRODUCT_ID'];

				if($arItem['PRODUCT']){
					$arItem['PRODUCT']['QUANTITY'] = $arItem['QUANTITY'];

					if($arItem['PRODUCT']['RATIO_IS_FLOAT']){
						$arItem['PRODUCT']['QUANTITY'] = floatval($arItem['PRODUCT']['QUANTITY']);
					}
					else{
						$arItem['PRODUCT']['QUANTITY'] = intval($arItem['PRODUCT']['QUANTITY']);
					}

					if($arItem['PRODUCT']['QUANTITY'] > $arItem['PRODUCT']['RATIO']){
						$diff = fmod($arItem['PRODUCT']['QUANTITY'], $arItem['PRODUCT']['RATIO']);
						if($diff > 0){
							$arItem['PRODUCT']['QUANTITY'] -= $diff;
						}
					}
					else{
						$arItem['PRODUCT']['QUANTITY'] = $arItem['PRODUCT']['RATIO'];
					}

					$basketItem = $basket->createItem('catalog', $pId);
					$basketItem->setFields(array(
						'CURRENCY' => $arItem['CURRENCY'],
						'LID' => $siteId,
						'PRODUCT_PROVIDER_CLASS' => static::getCatalogProductProviderClass(),
					));

					$result = $basketItem->setField('QUANTITY', $arItem['PRODUCT']['QUANTITY']);

					while(!$result->isSuccess() && ($arItem['PRODUCT']['QUANTITY'] > $arItem['PRODUCT']['RATIO'])){
						if($arItem['PRODUCT']['QUANTITY'] > $arItem['PRODUCT']['RATIO']){
							$arItem['PRODUCT']['QUANTITY'] -= $arItem['PRODUCT']['RATIO'];

							$result = $basketItem->setField('QUANTITY', $arItem['PRODUCT']['QUANTITY']);
						}
						else{
							break;
						}
					}

					if($arItem['DELAY']){
						$basketItem->setField('DELAY', 'Y');
					}
				}
			}
			unset($arItem);
		}
		unset($arItems);

		$basket->save();

		// create order - it`s need to calculate basket without siteId
		$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::create($this->getSiteId(), $userId);
		$order->setBasket($basket);

		// get discounts
		$context = new \Bitrix\Sale\Discount\Context\Fuser($fuserId);
		$discounts = \Bitrix\Sale\Discount::buildFromOrder($order);
		$result = $discounts->calculate();

		if(!$result->isSuccess()){
			return $this->setError($result);
		}

		// apply discounts
		$result = $result->getData();
		if(isset($result['BASKET_ITEMS'])){
			$result = $basket->applyDiscount($result['BASKET_ITEMS']);
			if(!$result->isSuccess()){
				return $this->setError($result);
			}
		}

		$basketItems = $basket->getBasketItems();
		$link_services_in_basket = array();
		$i = 0;
		foreach($arShareBasket['ITEMS'] as $block => &$arItems){
			foreach($arItems as &$arItem){
				$pId = $arItem['PRODUCT_ID'];

				if($arProduct =& $arItem['PRODUCT']){
					$basketItem = $basketItems[$i++]; // !!! post inc !!

					if($basketItem){
						$arProduct['CURRENCY'] = $basketItem->getCurrency();
						$arProduct['QUANTITY'] = $basketItem->getQuantity();
						$arProduct['MEASURE_NAME'] = $basketItem->getField('MEASURE_NAME');
						$arProduct['BASE_PRICE'] = $basketItem->getBasePrice();
						$arProduct['BASE_PRICE_FORMATED'] = CurrencyFormat($arProduct['BASE_PRICE'], $arProduct['CURRENCY']);
						$arProduct['PRICE'] = $basketItem->getPrice();
						$arProduct['PRICE_FORMATED'] = CurrencyFormat($arProduct['PRICE'], $arProduct['CURRENCY']);
						$arProduct['DISCOUNT_PRICE'] = $basketItem->getDiscountPrice();
						$arProduct['DISCOUNT_PRICE_FORMATED'] = CurrencyFormat($arProduct['DISCOUNT_PRICE'], $arProduct['CURRENCY']);
						$arProduct['FINAL_PRICE'] = $basketItem->getFinalPrice();
						$arProduct['FINAL_PRICE_FORMATED'] = CurrencyFormat($arProduct['FINAL_PRICE'], $arProduct['CURRENCY']);
						$arProduct['CAN_BUY'] = $basketItem->canBuy();
					}
				}

				/*services*/
				/*fill buy services array */				
				if($arItem["BASKET_PROPS"]){
					$arPropsByCode = array_column($arItem["BASKET_PROPS"], NULL , "CODE");
					$isServices = isset($arPropsByCode["ASPRO_BUY_PRODUCT_ID"]) && $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"]>0;
					$services_info = array();
					if($isServices){
						$arItem['IS_SERVICES'] = 'Y';
						$arShareBasket['PRODUCT_WITH_SERVICES'][ $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"] ] = $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"];
						$services_info = $arItem;
						$services_info['PRODUCT'] = $arProduct;
					 	$link_services_in_basket[$arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"]][$arItem["PRODUCT_ID"]] = $services_info;
					}
				}
				/**/
				unset($arProduct);
			}
			unset($arItem);
		}
		unset($arItems);

		foreach($arShareBasket['ITEMS'] as $block => &$arItems){
			foreach($arItems as &$arItem){
				/*fill link services*/
				if(is_array($link_services_in_basket) && count($link_services_in_basket) > 0){
					if( isset($link_services_in_basket[$arItem["PRODUCT_ID"]]) ){
						$arItem["LINK_SERVICES"] = $link_services_in_basket[$arItem["PRODUCT_ID"]];
					}
				}
				/**/
			}
			unset($arItem);
		}
		unset($arItems);
		

		return !$this->hasError();
	}

	protected function set404Error(){
		\Bitrix\Iblock\Component\Tools::process404(
			'',
			true,
			$this->arParams['SET_STATUS_404'] === 'Y',
			$this->arParams['SHOW_404'] === 'Y',
			$this->arParams['FILE_404']
		);

		return $this->setError(strlen($this->arParams['MESSAGE_404']) ? $this->arParams['MESSAGE_404'] : Loc::getMessage('BSD_C_ERROR_BASKET_NOT_FOUND'));
	}

	protected static function getCatalogProductProviderClass(){
		$providerClass = 'CCatalogProductProvider';

		if(
			class_exists('\Bitrix\Catalog\Product\Basket') &&
			method_exists('\Bitrix\Catalog\Product\Basket', 'getDefaultProviderName')
		){
			$providerClass = \Bitrix\Catalog\Product\Basket::getDefaultProviderName();
		}

		return $providerClass;
	}

	public static function getCurrentRegionId(){
		$regionId = false;

		if($bUseRegionality = Regionality::checkUseRegionality()){
			if($arRegion = Regionality::getCurrentRegion()){
				$regionId = $arRegion['ID'];
			}
		}

		return $regionId;
	}

	public static function getRegion(int $regionId){
		$arRegion = array();

		$regionId = intval($regionId);
		if($regionId > 0){
			$arRegions = Regionality::getRegions();
			$arRegion = $arRegions[$regionId] ?? array();
		}

		return $arRegion;
	}

	public static function getRegionStores(array $arRegion){
		$arStores = array();

		if(
			array_key_exists('LIST_STORES', $arRegion) &&
			$arRegion['LIST_STORES']
		){
			if(reset($arRegion['LIST_STORES']) !== 'component'){
				$arStores = $arRegion['LIST_STORES'];
			}
		}

		if($arStores){
			foreach($arStores as $i => $store){
				if(!$store){
					unset($arStores[$i]);
				}
			}
			$arStores = array_values($arStores);
		}

		return $arStores;
	}
}
