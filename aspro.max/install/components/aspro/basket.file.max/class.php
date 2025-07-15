<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Main\ORM\Data\Result,
	Bitrix\Sale\Basket,
	Bitrix\Sale\Fuser,
	Bitrix\Sale\Compatible\DiscountCompatibility,
	CMax as Solution,
	CMaxRegionality as Regionality;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class CAsproBasketFileMax extends CBitrixComponent{
	const FILE_NAME_DEFAULT = 'cart';
	const SAVE_TO_DIR_DEFAULT = '/upload/';
	const SOLUTION_SVG_LOGO_MARKER = '86V786H1789Zm30';
	const GET_HEADERS_TEMPLATE_FILE_NAME = 'get_headers.php';
	const GET_REQUIREMENTS_TEMPLATE_FILE_NAME = 'get_requirements.php';

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

		// BASE PARAMETERS
		$arParams['ACTION'] = isset($arParams['ACTION']) && $arParams['ACTION'] === 'SAVE' ? 'SAVE' : 'DOWNLOAD';

		if(
			isset($arParams['FILE_NAME']) &&
			strlen(trim($arParams['FILE_NAME']))
		){
			$arParams['FILE_NAME'] = trim($arParams['FILE_NAME']);
		}
		else{
			$arParams['FILE_NAME'] = static::FILE_NAME_DEFAULT;
		}
		$arParams['FILE_NAME'] = preg_replace('/.*\/([^\/]*)$/', '$1', $arParams['FILE_NAME']);

		if($arParams['ACTION'] === 'SAVE'){
			if(
				isset($arParams['SAVE_TO_DIR']) &&
				strlen(trim($arParams['SAVE_TO_DIR']))
			){
				$arParams['SAVE_TO_DIR'] = trim($arParams['SAVE_TO_DIR']);
			}
			else{
				$arParams['SAVE_TO_DIR'] = static::SAVE_TO_DIR_DEFAULT;
			}

			$arParams['SAVE_TO_DIR'] = preg_replace('/\/{2,}/', '/', '/'.$arParams['SAVE_TO_DIR'].'/');
		}
		else{
			unset($arParams['SAVE_TO_DIR']);
		}

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

		if(
			isset($arParams['REGION_ID']) &&
			intval(trim($arParams['REGION_ID'])) > 0
		){
			$arParams['REGION_ID'] = intval(trim($arParams['REGION_ID']));
		}
		else{
			$arParams['REGION_ID'] = static::getCurrentRegionId();
		}

		// VISUAL PARAMETERS
		$arParams['SHOW_ERRORS'] = isset($arParams['SHOW_ERRORS']) && $arParams['SHOW_ERRORS'] === 'N' ? 'N' : 'Y';

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
			'FILE_NAME' => array(),
			'SITE' => array(),
			'REGION' => array(),
			'STORES' => array(),
			'CONTACTS' => array(),
			'BASKET_ITEMS' => array(),
		);

		$this->arErrors =& $this->arResult['ERRORS'];

		return $arParams;
	}

	protected function raiseOnPrepareParamsEvent(&$arParams){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnPrepareBasketFileParams',
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
		$action = ($this->arParams['ACTION'] === 'SAVE' ? 'save' : 'download').'Action';
		if(is_callable(array($this, $action))){
			return $this->{$action}();
		}
	}

	protected function downloadAction(){
		if($this->includeModules()){
			DiscountCompatibility::stopUsageCompatible();
			$this->makeResult();
			DiscountCompatibility::revertUsageCompatible();
		}

		if($this->initComponentTemplate('', $this->getSiteTemplateId(), '')){
			$isTemplateAvailable = static::isTemplateAvailable($this->GetTemplate()->GetFolder());
			if(!$isTemplateAvailable){
				$this->setError(Loc::getMessage('BF_C_ERROR_TEMPLATE_IS_NOT_AVAILABLE'));
			}
		}
		else{
			$this->setError(Loc::getMessage('BF_C_ERROR_INIT_TEMPLATE'));
		}

		if(headers_sent()){
			$this->setError(Loc::getMessage('BF_C_ERROR_HEADERS_ALREADY_SENT'));
		}

		$content = $this->getContent();

		if($this->hasError()){
			echo $content;
		}
		else{
			$arHeaders = $this->getHeaders();

			$GLOBALS['APPLICATION']->RestartBuffer();

			if(
				$arHeaders &&
				is_array($arHeaders)
			){
				foreach($arHeaders as $key => $value){
					header($key.': '.$value);
				}
			}

			echo $content;
			flush();
			die();
		}

		return !$this->hasError();
	}

	protected function saveAction(){
		if($this->includeModules()){
			$this->makeResult();
		}

		if($this->initComponentTemplate('', $this->getSiteTemplateId(), '')){
			$isTemplateAvailable = static::isTemplateAvailable($this->GetTemplate()->GetFolder());
			if(!$isTemplateAvailable){
				$this->setError(Loc::getMessage('BF_C_ERROR_TEMPLATE_IS_NOT_AVAILABLE'));
			}
		}
		else{
			$this->setError(Loc::getMessage('BF_C_ERROR_INIT_TEMPLATE'));
		}

		$path2Save = realpath($_SERVER['DOCUMENT_ROOT'].str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->arParams['SAVE_TO_DIR']));
		if(!is_dir($path2Save)){
			if(!@mkdir($path2Save, $mode = BX_DIR_PERMISSIONS, $recursive = true)){
				$this->setError(Loc::getMessage('BF_C_ERROR_MK_DIR2SAVE'));
			}
		}

		$content = $this->getContent();

		if($this->hasError()){
			echo $content;
		}
		else{
			$file2Save = $path2Save.'/'.$this->arResult['FILE_NAME'];
			@file_put_contents($file2Save, $content);
		}

		return !$this->hasError();
	}

	protected function getContent(){
		define('IGNORE_EOL_OPT', true);

		ob_start();
		$this->includeComponentTemplate();
		$content = ob_get_clean();
		$content = is_string($content) ? $content : '';

		$this->raiseOnMakeContentEvent($content);

		return is_string($content) ? $content : '';
	}

	protected function raiseOnMakeContentEvent(&$content){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnMakeBasketFileContent',
				true
			) as $arEvent){
			\ExecuteModuleEventEx(
				$arEvent,
				array(
					'params' => $this->arParams,
					'result' => $this->arResult,
					&$content,
				)
			);
		}
	}

	protected function getHeaders(){
		$arHeaders = array();

		$fileGetHeaders = preg_replace('/\/{2,}/', '/', $_SERVER['DOCUMENT_ROOT'].'/'.$this->__template->GetFolder().'/'.static::GET_HEADERS_TEMPLATE_FILE_NAME);

		if(file_exists($fileGetHeaders)){
			$arParams = $this->arParams;
			$arResult = $this->arResult;

			$arHeaders = include $fileGetHeaders;
			$arHeaders = is_array($arHeaders) ? $arHeaders : array();
		}

		$this->raiseOnMakeHeadersEvent($arHeaders);

		return is_array($arHeaders) ? $arHeaders : array();
	}

	protected function raiseOnMakeHeadersEvent(&$arHeaders){
		foreach(
			\GetModuleEvents(
				Solution::moduleID,
				'OnMakeBasketFileHeaders',
				true
			) as $arEvent){
			\ExecuteModuleEventEx(
				$arEvent,
				array(
					'params' => $this->arParams,
					'result' => $this->arResult,
					&$arHeaders,
				)
			);
		}
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

	public function setFileNameExtension(string $extension){
		$fileName =& $this->arResult['FILE_NAME'];
		$extension = ltrim(trim($extension), '.');

		if(strlen($fileName)){
			if(strlen($extension)){
				if(strpos($fileName, '.') !== false){
					$fileName = preg_replace('/(.*\.)[^\.]*$/', '$1', $fileName).$extension;
				}
				else{
					$fileName .= '.'.$extension;
				}
			}
		}
		else{
			$fileName = '.'.$extension;
		}
	}

	protected function includeModules(){
		Loader::includeModule('fileman');

		if(!Loader::includeModule('sale')){
			$this->setError(Loc::getMessage('BF_C_ERROR_MODULE_SALE_NOT_INSTALL'));
		}

		if(!Loader::includeModule('currency')){
			$this->setError(Loc::getMessage('BF_C_ERROR_MODULE_CURRENCY_NOT_INSTALL'));
		}

		if(!Loader::includeModule('catalog')){
			$this->setError(Loc::getMessage('BF_C_ERROR_MODULE_CATALOG_NOT_INSTALL'));
		}

		if(!Loader::includeModule(Solution::moduleID)){
			$this->setError(Loc::getMessage('BF_C_ERROR_MODULE_SOLUTION_NOT_INSTALL', array('#SOLUTION_MODULE_ID#' => Solution::moduleID)));
		}

		return !$this->hasError();;
	}

	protected function makeResult(){
		$siteId =& $this->arParams['SITE_ID'];
		$userId =& $this->arParams['USER_ID'];
		$regionId =& $this->arParams['REGION_ID'];
		$arSite =& $this->arResult['SITE'];
		$arRegion =& $this->arResult['REGION'];
		$arStores =& $this->arResult['STORES'];
		$arContacts =& $this->arResult['CONTACTS'];
		$arBasketItems =& $this->arResult['BASKET_ITEMS'];

		// vendor path
		$this->arResult['VENDOR_PATH'] = Solution::getVendorsPath();

		// file name
		$this->arResult['FILE_NAME'] = $this->arParams['FILE_NAME'];

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
			return $this->setError(Loc::getMessage('BF_C_ERROR_SITE_ID'));
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
		if($regionId){
			if($arRegion = static::getRegion($regionId)){
				$arStores = static::getRegionStores($arRegion);
			}
			else{
				$regionId = false;
				$arStores = array();
			}
		}

		$bRegionContact = (Option::get(Solution::moduleID, 'SHOW_REGION_CONTACT', 'N', $siteId) === 'Y');

		// contacts
		$arContacts = array(
			'LOGO' => array(
				'SRC' => '',
				'SVG' => '',
			),
			'PHONE' => array(
				'TITLE' => '',
				'HREF' => '',
			),
			'EMAIL' => '',
			'ADDRESS' => '',
			'URL' => '',
		);

		// url
		$arContacts['URL'] = ($GLOBALS['APPLICATION']->IsHTTPs() ? 'https://' : 'http://').$arSite['SERVER_NAME'];

		// logo
		$arLogoImage = Solution::unserialize(Option::get(Solution::moduleID, 'LOGO_IMAGE', serialize(array()), $siteId));
		if($arLogoImage){
			$arContacts['LOGO']['SRC'] = \CFile::GetPath($arLogoImage[0]);
		}
		elseif(Solution::checkContentFile($svgSrc = $siteDir.'include/logo_svg.php')){
			$svgContent = @file_get_contents($_SERVER['DOCUMENT_ROOT'].$svgSrc);
			if(strpos($svgContent, static::SOLUTION_SVG_LOGO_MARKER) === false){
				// not default aspro logo svg
				$arContacts['LOGO']['SVG'] = $svgSrc;
			}
		}

		if(
			!strlen($arContacts['LOGO']['SRC']) &&
			!strlen($arContacts['LOGO']['SVG'])
		){
			$arTheme = Solution::GetFrontParametrsValues($siteId);
			if($arTheme['LOGO_IMAGE']){
				$arContacts['LOGO']['SRC'] = $arTheme['LOGO_IMAGE'];
			}
		}

		// phone
		if($arRegion && $bRegionContact){
			$iCountPhones = count($arRegion['PHONES']);
			if($iCountPhones){
				$arContacts['PHONE']['TITLE'] = $arRegion['PHONES'][0]['PHONE'];
				$arContacts['PHONE']['HREF'] = $arRegion['PHONES'][0]['HREF'];
				if(!strlen($arContacts['PHONE']['HREF'])){
					$arContacts['PHONE']['HREF'] = 'javascript:;';
				}
			}
		}
		else{
			if(
				$arRegion &&
				$iCountPhones = count($arRegion['PHONES'])
			){
				$arBackParametrs = Solution::GetBackParametrsValues($siteId);

				$arContacts['PHONE']['TITLE'] = $arBackParametrs['HEADER_PHONES_array_PHONE_VALUE_0'];
				$arContacts['PHONE']['HREF'] = $arBackParametrs['HEADER_PHONES_array_HREF_VALUE_0'];
				if(!strlen($arContacts['PHONE']['HREF'])){
					$arContacts['PHONE']['HREF'] = 'javascript:;';
				}
			}
			else{
				if($content = @file_get_contents($_SERVER['DOCUMENT_ROOT'].$siteDir.'include/contacts-site-phone-one.php')){
					$arContacts['PHONE']['TITLE'] = strip_tags($content, '');
					$arContacts['PHONE']['HREF'] = 'tel:'.preg_replace('/[^+\d]/', '', $arContacts['PHONE']['TITLE']);
				}
			}
		}

		// email
		if($arRegion && $bRegionContact){
			if($arRegion['PROPERTY_EMAIL_VALUE']){
				$arContacts['EMAIL'] = $arRegion['PROPERTY_EMAIL_VALUE'][0];
			}
		}
		else{
			if($content = @file_get_contents($_SERVER['DOCUMENT_ROOT'].$siteDir.'include/contacts-site-email.php')){
				$arContacts['EMAIL'] = strip_tags($content, '');
			}
		}

		// address
		if($arRegion && $bRegionContact){
			if($arRegion['PROPERTY_ADDRESS_VALUE']){
				$arContacts['ADDRESS'] = $arRegion['PROPERTY_ADDRESS_VALUE']['TEXT'];
			}
		}
		else{
			if($content = @file_get_contents($_SERVER['DOCUMENT_ROOT'].$siteDir.'include/contacts-site-address.php')){
				$arContacts['ADDRESS'] = strip_tags($content, '');
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

		// basket items
		$arBasketItems = array(
			'CAN_BUY' => array(),
			'DELAY' => array(),
			'SUBSCRIBE' => array(),
			'NOT_AVAILABLE' => array(),
		);

		if($basket){
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

			$arProducts = $arProductsIds = $arPriceTypes = $arPriceTypesIds = array();
			foreach($basket as $basketItem){
				$arProductsIds[] = $basketItem->getProductId();
				$arPriceTypesIds[] = $basketItem->getField('PRICE_TYPE_ID');
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
					'PREVIEW_PICTURE',
					'DETAIL_PICTURE',
					'DETAIL_PAGE_URL',
					'IBLOCK_SECTION_ID',
					'PROPERTY_CML2_LINK',
					'PROPERTY_CML2_ARTICLE',
				);

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
						if($arProduct['PREVIEW_PICTURE']){
							$arProduct['PREVIEW_PICTURE'] = \CFile::GetFileArray($arProduct['PREVIEW_PICTURE']);
						}

						if($arProduct['DETAIL_PICTURE']){
							$arProduct['DETAIL_PICTURE'] = \CFile::GetFileArray($arProduct['DETAIL_PICTURE']);
						}

						$imageId = $arProduct['PREVIEW_PICTURE'] ? $arProduct['PREVIEW_PICTURE']['ID'] : ($arProduct['DETAIL_PICTURE'] ? $arProduct['DETAIL_PICTURE']['ID'] : false);

						if($imageId){
							$arImage = \CFile::ResizeImageGet($imageId, array('width' => 150, 'height'=> 150), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, false);
							$src = $arImage['src'];
							$arProduct['PICTURE'] = array(
								'ID' => $imageId,
								'SRC' => $src,
								'SRC_ORIGINAL' => \CFile::GetPath($imageId),
							);
						}
						else{
							$arProduct['PICTURE'] = array();
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
							array(),
							'N',
							false,
							'',
							$siteId,
							$userId
						);

						$arProducts[$arProduct['ID']] = $arProduct;
					}
				}
			}

			if($arPriceTypesIds){
				$arPriceTypesIds = array_unique($arPriceTypesIds);

				$res = \Bitrix\Catalog\GroupTable::getList(array(
					'filter' => array(
						'ID' => $arPriceTypesIds
					),
					'select' => array(
						'ID',
						'NAME',
						'XML_ID',
					)
				));

				while($arPriceType = $res->fetch()){
					$arPriceTypes[$arPriceType['ID']] = $arPriceType;
				}

				$res = \Bitrix\Catalog\GroupLangTable::getList(array(
					'filter' => array(
						'LANG' => $languageId,
						'CATALOG_GROUP_ID' => $arPriceTypesIds
					),
					'select' => array(
						'CATALOG_GROUP_ID',
						'NAME',
					)
				));

				while($arPriceTypeLang = $res->fetch()){
					$arPriceTypes[$arPriceTypeLang['CATALOG_GROUP_ID']]['NAME'] = $arPriceTypeLang['NAME'];
				}
			}

			$link_services = array();
			foreach($basket as $basketItem){
				$pId = $basketItem->getProductId();
				$arProduct = $arProducts[$pId];
				$arProduct['NAME'] = $basketItem->getField('NAME');
				$arProduct['QUANTITY'] = $basketItem->getQuantity();
				$arProduct['MEASURE_NAME'] = $basketItem->getField('MEASURE_NAME');
				$arProduct['MEASURE_RATIO'] = $basketItem->getField('MEASURE_RATIO');
				$arProduct['QUANTITY_FORMATED'] = $arProduct['QUANTITY'].' '.$arProduct['MEASURE_NAME'];
				$arProduct['CURRENCY'] = $basketItem->getCurrency();
				$arProduct['PRICE'] = $basketItem->getPrice();
				$arProduct['PRICE_FORMATED'] = \CurrencyFormat($arProduct['PRICE'], $arProduct['CURRENCY']);
				$arProduct['BASE_PRICE'] = $basketItem->getBasePrice();
				$arProduct['BASE_PRICE_FORMATED'] = \CurrencyFormat($arProduct['BASE_PRICE'], $arProduct['CURRENCY']);
				$arProduct['DISCOUNT_PRICE'] = $basketItem->getDiscountPrice();
				$arProduct['DISCOUNT_PRICE_FORMATED'] = \CurrencyFormat($arProduct['DISCOUNT_PRICE'], $arProduct['CURRENCY']);
				$arProduct['FINAL_PRICE'] = $basketItem->getFinalPrice();
				$arProduct['FINAL_PRICE_FORMATED'] = CurrencyFormat($arProduct['FINAL_PRICE'], $arProduct['CURRENCY']);
				$arProduct['PRICE_TYPE'] = $basketItem->getField('PRICE_TYPE_ID');
				$arProduct['PRICE_TYPE_FORMATED'] = $arPriceTypes[$arProduct['PRICE_TYPE']]['NAME'];

				$arProperties = array();
				if($propertiesCollection = $basketItem->getPropertyCollection()){
					$arProperties = $propertiesCollection->getPropertyValues();
				}

				$block =
					$basketItem->canBuy() ?
						($basketItem->isDelay() ? 'DELAY' : 'CAN_BUY') :
						($basketItem->getField('SUBSCRIBE') === 'Y' ? 'SUBSCRIBE' : 'NOT_AVAILABLE' );

				$arItem = array(
					'PRODUCT' => $arProduct,
					'BASKET_PROPS' => $arProperties,
				);
				if($arProduct['PROPERTY_CML2_LINK_VALUE']){
					$arItem['MAIN_PRODUCT'] =& $arProducts[$arProduct['PROPERTY_CML2_LINK_VALUE']];
				}

				/*services*/
				if( isset($arItem["BASKET_PROPS"]) ){
					$arPropsByCode = array_column($arItem["BASKET_PROPS"], NULL , "CODE");
					$isServices = isset($arPropsByCode["ASPRO_BUY_PRODUCT_ID"]) && $arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"]>0;
					$services_info = array();
					if($isServices){
						$services_info['ID'] = $arItem['PRODUCT']["ID"];
						$services_info['QUANTITY'] = $arItem['PRODUCT']["QUANTITY"];
						$services_info['MEASURE_NAME'] = $arItem['PRODUCT']["MEASURE_NAME"];
						$services_info['QUANTITY_FORMATED'] = $arItem['PRODUCT']["QUANTITY_FORMATED"];
						$services_info['PRICE_FORMATED'] = $arItem['PRODUCT']["PRICE_FORMATED"];
						$services_info['FINAL_PRICE'] = $arItem['PRODUCT']["FINAL_PRICE"];
						$services_info['FINAL_PRICE_FORMATED'] = $arItem['PRODUCT']["FINAL_PRICE_FORMATED"];
						$services_info['CURRENCY'] = $arItem['PRODUCT']["CURRENCY"];
						$services_info['URL'] = $arItem['PRODUCT']['DETAIL_PAGE_URL'];
						$services_info['NAME'] = $arItem['PRODUCT']['NAME'];
						$link_services[$arPropsByCode["ASPRO_BUY_PRODUCT_ID"]["VALUE"]][$arItem['PRODUCT']["ID"]] = $services_info;
						$arItem['IS_SERVICES'] = 'Y';
					}
				}
				/**/

				$arBasketItems[$block][] = $arItem;
			}
			foreach($arBasketItems as $block => $arItems){
				foreach($arItems as $i => $arItem){
					if(is_array($link_services) && count($link_services) > 0){	
						if( isset($link_services[$arItem['PRODUCT']["ID"]]) ){
							$arBasketItems[$block][$i]['LINK_SERVICES'] = $link_services[$arItem['PRODUCT']["ID"]];
						}
					}
				}
			}
		}

		return !$this->hasError();
	}

	public static function getTemplateRequirements(string $templateFolder){
		$arRequirements = array();

		$fileGetRequirements = preg_replace('/\/{2,}/', '/', $_SERVER['DOCUMENT_ROOT'].'/'.$templateFolder.'/'.static::GET_REQUIREMENTS_TEMPLATE_FILE_NAME);

		if(file_exists($fileGetRequirements)){
			$arRequirements = include $fileGetRequirements;
			$arRequirements = is_array($arRequirements) ? $arRequirements : array();
		}

		return $arRequirements;
	}

	public static function isTemplateAvailable(string $templateFolder){
		$templatePath = preg_replace('/\/{2,}/', '/', $_SERVER['DOCUMENT_ROOT'].'/'.$templateFolder.'/');
		if(is_dir($templatePath)){
			$arRequirements = static::getTemplateRequirements($templateFolder);

			foreach($arRequirements as $arRequirement){
				if(
					is_array($arRequirement) &&
					$arRequirement &&
					array_key_exists('TITLE', $arRequirement) &&
					array_key_exists('PASSED', $arRequirement)
				){
					if(!$arRequirement['PASSED']){
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	public static function getTemplatesList($siteTemplatePath = ''){
		$arTemplates = array();

		$docRoot = realpath(__DIR__.'/../../../..');

		if(is_dir($componentTemplatesPath = __DIR__.'/templates/')){
			foreach((array)glob($componentTemplatesPath.'{,.}*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_BRACE) as $templatePath){	
				if(
					$templatePath !== $componentTemplatesPath.'.' &&
					$templatePath !== $componentTemplatesPath.'..' &&
					$templatePath !== $componentTemplatesPath.'.default'
				){
					$templateName = basename($templatePath);
					$templateFolder = str_replace(str_replace('/', DIRECTORY_SEPARATOR, $docRoot), '', $templatePath);

					$arTemplates[$templateName] = array(
						'TITLE' => $templateName,
						'REQUIREMENTS' => static::getTemplateRequirements($templateFolder),
					);

					if(!static::isTemplateAvailable($templateFolder)){
						$arTemplates[$templateName]['DISABLED'] = 'Y';
					}
				}
			}
		}

		if(!strlen($siteTemplatePath)){
			if(defined('SITE_TEMPLATE_PATH')){
				$siteTemplatePath = SITE_TEMPLATE_PATH;
			}
		}

		if(strlen($siteTemplatePath)){
			$siteTemplateTemplatesPath = $docRoot.$siteTemplatePath.'/components/'.Solution::partnerName.'/basket.file.max/';
			foreach((array)glob($siteTemplateTemplatesPath.'{,.}*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_BRACE) as $templatePath){
				if(
					$templatePath !== $siteTemplateTemplatesPath.'.' &&
					$templatePath !== $siteTemplateTemplatesPath.'..' &&
					$templatePath !== $siteTemplateTemplatesPath.'.default'
				){
					$templateName = basename($templatePath);
					$templateFolder = str_replace(str_replace('/', DIRECTORY_SEPARATOR, $docRoot), '', $templatePath);

					if(isset($arTemplates[$templateName])){
						unset($arTemplates[$templateName]);
					}

					$arTemplates[$templateName] = array(
						'TITLE' => $templateName,
						'REQUIREMENTS' => static::getTemplateRequirements($templateFolder),
					);

					if(!static::isTemplateAvailable($templateFolder)){
						$arTemplates[$templateName]['DISABLED'] = 'Y';
					}
				}
			}
		}

		return $arTemplates;
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
