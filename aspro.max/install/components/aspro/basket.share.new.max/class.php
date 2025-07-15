<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Main\ORM\Data\Result,
	Bitrix\Sale\Basket,
	Bitrix\Sale\Fuser,
	Bitrix\Sale\Compatible\DiscountCompatibility,
	CMax as Solution,
	CMaxRegionality as Regionality,
	Aspro\Max\ShareBasketTable,
	Aspro\Max\ShareBasketItemTable,
	chillerlan\QRCode\QRCode,
	chillerlan\QRCode\QROptions;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class CAsproBasketShareNewMax extends CBitrixComponent{
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

		// URL TEMPLATES PARAMETERS
		$arParams['DETAIL_URL_TEMPLATE'] = isset($arParams['DETAIL_URL_TEMPLATE']) ? trim($arParams['DETAIL_URL_TEMPLATE']) : '';

		// BASE PARAMETERS
		$arParams['SET_PAGE_TITLE'] = !isset($arParams['SET_PAGE_TITLE']) || $arParams['SET_PAGE_TITLE'] !== 'N' ? 'Y' : 'N';

		if(
			isset($arParams['SITE_ID']) &&
			strlen(trim($arParams['SITE_ID']))
		){
			$arParams['SITE_ID'] = trim($arParams['SITE_ID']);
		}
		else{
			$arParams['SITE_ID'] = $siteId;
		}

		if(
			isset($arParams['USER_ID']) &&
			intval(trim($arParams['USER_ID'])) > 0
		){
			$arParams['USER_ID'] = intval(trim($arParams['USER_ID']));
		}
		elseif(
			$GLOBALS['USER'] &&
			$GLOBALS['USER'] instanceof \CUser
		){
			$arParams['USER_ID'] = $GLOBALS['USER']->GetID();
		}
		else{
			$arParams['USER_ID'] = false;
		}

		$arParams['PATH_TO_SHARE_BASKET'] = isset($arParams['PATH_TO_SHARE_BASKET']) ? trim($arParams['PATH_TO_SHARE_BASKET']) : '';

		// VISUAL PARAMETERS
		$arParams['SHOW_SHARE_SOCIALS'] = isset($arParams['SHOW_SHARE_SOCIALS']) && $arParams['SHOW_SHARE_SOCIALS'] === 'N' ? 'N' : 'Y';
		if(isset($arParams['SHARE_SOCIALS']) && is_array($arParams['SHARE_SOCIALS'])){
			$arTmp = array_intersect(array('VKONTAKTE', 'FACEBOOK', 'ODNOKLASSNIKI', 'MOIMIR', 'TWITTER', 'VIBER', 'WHATSAPP', 'SKYPE', 'TELEGRAM'), $arParams['SHARE_SOCIALS']);
			$arParams['SHARE_SOCIALS'] = array_values($arTmp);
		}
		else{
			$arParams['SHARE_SOCIALS'] = array('VKONTAKTE', 'FACEBOOK', 'ODNOKLASSNIKI', 'TWITTER');
		}
		$arParams['SHOW_QRCODE'] = isset($arParams['SHOW_QRCODE']) && $arParams['SHOW_QRCODE'] === 'N' ? 'N' : 'Y';

		// MESSAGES PARAMETERS
		$arParams['USE_CUSTOM_MESSAGES'] = isset($arParams['USE_CUSTOM_MESSAGES']) && $arParams['USE_CUSTOM_MESSAGES'] === 'Y' ? 'Y' : 'N';
		if($arParams['USE_CUSTOM_MESSAGES'] === 'Y'){
			$arParams['MESS_TITLE'] = isset($arParams['MESS_TITLE']) ? trim($arParams['MESS_TITLE']) : '';
			$arParams['MESS_URL_COPY_HINT'] = isset($arParams['MESS_URL_COPY_HINT']) ? trim($arParams['MESS_URL_COPY_HINT']) : '';
			$arParams['MESS_URL_COPIED_HINT'] = isset($arParams['MESS_URL_COPIED_HINT']) ? trim($arParams['MESS_URL_COPIED_HINT']) : '';
			$arParams['MESS_URL_COPY_ERROR_HINT'] = isset($arParams['MESS_URL_COPY_ERROR_HINT']) ? trim($arParams['MESS_URL_COPY_ERROR_HINT']) : '';
			$arParams['MESS_SHARE_SOCIALS_TITLE'] = isset($arParams['MESS_SHARE_SOCIALS_TITLE']) ? trim($arParams['MESS_SHARE_SOCIALS_TITLE']) : '';
			$arParams['MESS_QR_CODE_HINT'] = isset($arParams['MESS_QR_CODE_HINT']) ? trim($arParams['MESS_QR_CODE_HINT']) : '';
		}
		else{
			unset(
				$arParams['MESS_TITLE'],
				$arParams['MESS_URL_COPY_HINT'],
				$arParams['MESS_URL_COPIED_HINT'],
				$arParams['MESS_URL_COPY_ERROR_HINT'],
				$arParams['MESS_SHARE_SOCIALS_TITLE'],
				$arParams['MESS_QR_CODE_HINT'],
			);
		}

		$this->arResult = array(
			'RAND' => randString(5),
			'ERRORS' => array(),
			'ID' => false,
			'URL' => false,
		);

		$this->arErrors =& $this->arResult['ERRORS'];

		return $arParams;
	}

	protected function raiseOnPrepareParamsEvent(&$arParams){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnPrepareBasketShareParams',
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
		$action = 'createAction';
		if(is_callable(array($this, $action))){
			$this->{$action}();
		}

		return $this->hasError() ? false : $this->arResult['ID'];
	}

	protected function createAction(){
		\Bitrix\Main\Data\StaticHtmlCache::getInstance()->markNonCacheable();

		if($this->includeModules()){
			DiscountCompatibility::stopUsageCompatible();
			$this->makeResult();
			DiscountCompatibility::revertUsageCompatible();
		}

		$this->includeComponentTemplate();

		if($this->arParams['SET_PAGE_TITLE'] === 'Y'){
			$GLOBALS['APPLICATION']->SetTitle(strlen($this->arParams['MESS_TITLE']) ? $this->arParams['MESS_TITLE'] : Loc::getMessage('BSN_C_MESS_TITLE_DEFAULT'));
		}

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

	protected function includeModules(){
		Loader::includeModule('fileman');

		if(!Loader::includeModule('sale')){
			$this->setError(Loc::getMessage('BSN_C_ERROR_MODULE_SALE_NOT_INSTALL'));
		}

		if(!Loader::includeModule('currency')){
			$this->setError(Loc::getMessage('BSN_C_ERROR_MODULE_CURRENCY_NOT_INSTALL'));
		}

		if(!Loader::includeModule('catalog')){
			$this->setError(Loc::getMessage('BSN_C_ERROR_MODULE_CATALOG_NOT_INSTALL'));
		}

		if(!Loader::includeModule(Solution::moduleID)){
			$this->setError(Loc::getMessage('BSN_C_ERROR_MODULE_SOLUTION_NOT_INSTALL', array('#SOLUTION_MODULE_ID#' => Solution::moduleID)));
		}

		return !$this->hasError();
	}

	protected function makeResult(){
		$siteId =& $this->arParams['SITE_ID'];
		$userId =& $this->arParams['USER_ID'];
		$arUser = $arSite = $arRegion = [];

		// site
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
			return $this->setError(Loc::getMessage('BSN_C_ERROR_SITE_ID'));
		}

		// user
		if($userId){
			if($arUser = \CUser::GetByID($userId)->Fetch()){

			}
			else{
				$userId = false;
				$arUser = array();
			}
		}

		// region
		if($bUseRegionality = Regionality::checkUseRegionality()){
			if($arRegion = Regionality::getCurrentRegion()){
				$regionId = $arRegion['ID'];
			}
		}

		// fuser
		if($userId){
			$fuserId = Fuser::getIdByUserId($userId);
		}
		else{
			$fuserId = Fuser::getId();
		}

		// basket
		$basket = Basket::loadItemsForFUser($fuserId, $siteId);
		if(!$basket){
			return $this->setError(Loc::getMessage('BSN_C_ERROR_BASKET_EMPTY'));
		}

		// get discounts
		$context = new \Bitrix\Sale\Discount\Context\Fuser($fuserId);
		$discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, $context);
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

		$arBasket = array(
			'USER_ID' => intval($userId),
			'SITE_ID' => $siteId,
		);

		if(
			isset($regionId) &&
			$regionId
		){
			$arBasket['REGION_ID'] = intval($regionId);
			$arBasket['REGION_NAME'] = $arRegion['NAME'];
		}

		$arProducts = $arProductsIds = $arSectionsIDs = $arSections = array();
		foreach($basket as $basketItem){
			if($basketItem->getField('SUBSCRIBE') === 'Y'){
				continue;
			}

			$arProductsIds[] = $basketItem->getProductId();
		}

		if(!$arProductsIds){
			return $this->setError(Loc::getMessage('BSN_C_ERROR_BASKET_EMPTY'));
		}

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

			foreach($arProductsIdsByIblockId as $iblockId => $arIblockProductsIds){
				$arSelect = array(
					'ID',
					'IBLOCK_ID',
					'IBLOCK_SECTION_ID',
					'PROPERTY_CML2_LINK',
					'PROPERTY_CML2_ARTICLE',
				);
	
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
				while($arProduct = $dbRes->Fetch()){
					if($arProductMeasureRatio = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($arProduct['ID'])){
						$arProduct = array_merge($arProduct, $arProductMeasureRatio[$arProduct['ID']]);
					}
	
					if(!$arProduct['RATIO']){
						$arProduct['RATIO'] = 1;
					}
					$arProduct['RATIO_IS_FLOAT'] = is_double($arProduct['RATIO']);
	
					$arProducts[$arProduct['ID']] = $arProduct;
					if($arProduct['IBLOCK_SECTION_ID']){
						$arSectionsIDs[] = $arProduct['IBLOCK_SECTION_ID'];
					}
				}
			}
		}

		if($arSectionsIDs){
			$dbRes = \CIBlockSection::GetList(
				array(),
				array('ID' => $arSectionsIDs),
				false,
				array(
					'ID',
					'NAME',
				),
				false
			);
			while($arSection = $dbRes->Fetch()){
				$arSections[$arSection['ID']] = $arSection;
			}
		}

		$arBasketItems = array();
		foreach($basket as $basketItem){
			if($basketItem->getField('SUBSCRIBE') === 'Y'){
				continue;
			}

			$pId = $basketItem->getProductId();

			$arItem = array(
				'QUANTITY' => $basketItem->getQuantity(),
				'DELAY' => $basketItem->isDelay(),
				'PRODUCT_ID' => $pId,
				'NAME' => $basketItem->getField('NAME'),
				'BASE_PRICE' => $basketItem->getBasePrice(),
				'PRICE' => $basketItem->getPrice(),
				'DISCOUNT_PRICE' => $basketItem->getDiscountPrice(),
				'FINAL_PRICE' => $basketItem->getFinalPrice(),
				'CURRENCY' => $basketItem->getCurrency(),
				'MEASURE_NAME' => $basketItem->getField('MEASURE_NAME'),
			);

			if($propertiesCollection = $basketItem->getPropertyCollection()){
				if($arProperties = $propertiesCollection->getPropertyValues()){
					foreach($arProperties as $arProperty){
						if(
							$arProperty['CODE'] !== 'CATALOG.XML_ID' &&
							$arProperty['CODE'] !== 'PRODUCT.XML_ID'
						){
							unset($arProperty['ID']);
							$arItem['BASKET_PROPS'][$arProperty['CODE']] = $arProperty;
						}
					}
				}
			}

			$arProduct =& $arProducts[$pId];

			// ratio
			$arItem['RATIO'] = $arProduct['RATIO'];

			// article
			if(strlen($arProduct['PROPERTY_CML2_ARTICLE_VALUE'])){
				$arItem['ARTICLE'] = $arProduct['PROPERTY_CML2_ARTICLE_VALUE'];
			}

			// main product
			if($arProduct['PROPERTY_CML2_LINK_VALUE']){
				$arProduct =& $arProducts[$arProduct['PROPERTY_CML2_LINK_VALUE']];

				// article from main product
				if(
					!strlen($arItem['ARTICLE']) &&
					strlen($arProduct['PROPERTY_CML2_ARTICLE_VALUE'])
				){
					$arItem['ARTICLE'] = $arProduct['PROPERTY_CML2_ARTICLE_VALUE'];
				}
			}

			// section
			if($arProduct['IBLOCK_SECTION_ID']){
				$arItem['SECTION_ID'] = intval($arProduct['IBLOCK_SECTION_ID']);

				if(isset($arSections[$arProduct['IBLOCK_SECTION_ID']])){
					$arItem['SECTION_NAME'] = $arSections[$arProduct['IBLOCK_SECTION_ID']]['NAME'];
				}
			}

			$arBasketItems[] = $arItem;
		}

		$hash = md5(serialize($arBasket).serialize($arBasketItems));

		$shareBasket = ShareBasketTable::getList(array(
            'filter' => array(
            	'=HASH' => $hash,
            	'=USER_ID' => $userId,
            	'=SITE_ID' => $siteId,
            ),
            'order' => array('ID' => 'DESC'),
            'limit' => 1,
            'select' => array('ID', 'CODE'),
        ))->fetchObject();

        if($shareBasket){
        	$this->arResult['ID'] = $shareBasket->getId();
        	$code = $shareBasket->getCode();
        }
        else{
			$code = static::generateCode();

			$arBasket = array_merge(
				$arBasket,
				array(
					'HASH' => $hash,
					'CODE' => $code,
					'PUBLIC' => true,
				)
			);

			if(
				$GLOBALS['USER'] &&
				$GLOBALS['USER'] instanceof \CUser
			){
				$arBasket['CREATED_BY'] = intval($GLOBALS['USER']->GetID());
			}

			$result = ShareBasketTable::add($arBasket);

			if($result->isSuccess()){
				$shareBasketId = $result->getId();
				$this->arResult['ID'] = $shareBasketId;
			}
			else{
				return $this->setError($result);
			}

			foreach($arBasketItems as $arItem){
				$arItem['BASKET_ID'] = $shareBasketId;

				$result = ShareBasketItemTable::add($arItem);

				if(!$result->isSuccess()){
					return $this->setError($result);
				}
			}
        }

        // path to sharebasket
		$this->arResult['URL'] = str_replace('#CODE#', $code, $this->arParams['DETAIL_URL_TEMPLATE']);
		$this->arResult['URL'] = ($GLOBALS['APPLICATION']->IsHTTPs() ? 'https://' : 'http://').$arSite['SERVER_NAME'].$this->arResult['URL'];

		if ($this->arParams['SHOW_QRCODE'] === 'Y') {
			// vendor path
			$vendorPath = Solution::getVendorsPath();
	
			require $vendorPath.'/php-qrcode/autoload.inc.php';
			if (class_exists('chillerlan\QRCode\QRCode')) {
				$options = new QROptions([
					'version'      => 5,
					'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
					'imageBase64'  => false,
					'addQuietzone' => true,
					'eccLevel'     => QRCode::ECC_L,
					'svgViewBoxSize' => 45,
					'svgOpacity'   => 1.0,
					'svgDefs'      => '<style>rect{shape-rendering:crispEdges}</style>',
				]);
				$qrcode = new QRCode($options);
				$this->arResult['QR_CODE'] = '<i class="svg inline">'.$qrcode->render($this->arResult['URL']).'</i>';
			}
		}

		return $this->hasError() ? false : $this->arResult['ID'];
	}

	protected static function generateCode(){
		do{
			$code = '';

			while(strlen($code) < ShareBasketTable::CODE_LENGTH){
				$rs = new \Bitrix\Main\Type\RandomSequence(time().(random_int(1, 10000) + strlen($code) * random_int(1, 10000)).__FILE__);
				$code .= preg_replace('/[^a-z0-9]/', '', $rs->randString(12));
			}

			$code = substr($code, 0, ShareBasketTable::CODE_LENGTH);

			$result = ShareBasketTable::getList(array(
	            'filter' => array('=CODE' => $code),
	            'limit' => 1,
	            'select' => array('ID'),
	        ));
		}
		while($result->fetch());

		return $code;
	}
}
