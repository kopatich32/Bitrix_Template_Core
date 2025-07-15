<?php

namespace Aspro\Max\Product;

use Aspro\Functions\CAsproMax as SolutionFunctions;
use Aspro\Functions\CAsproMaxItem as SolutionItem;
use Aspro\Max\Property\IBInherited;
use CMax as Solution;
use CMaxCache as SolutionCache;
use CMaxEvents as SolutionEvents;
use CMaxRegionality as SolutionRegionality;

class Sku
{
    public array $params = [];
    public string $offerID;
    public string $maiItemID;
    public array $offerInfo = [];
    public array $mainElementInfo = [];
    public array $arCurrencyParams = [];
    public array $infoFromPost = [];
    public array $arCacheParams = [];
    public array $arResize = [];
    public array $arCacheParamsDetail = [];
    public array $offerInfoDetail = [];

    public function __construct(array $arPost)
    {
        $this->params = $arPost['PARAMS'];
        $this->params['SITE_ID'] = SITE_ID;
        $this->params['SHOW_ABSENT'] = true;
        $this->params['SKU_PROPERTY_ID'] = $arPost['PROPERTY_ID'];
        $this->params['SECTION_ID'] = $arPost['SECTION_ID'];
        $this->offerID = $arPost['SELECTED_OFFER_ID'];
        $this->maiItemID = $arPost['ID'];

        $this->params['IS_DETAIL'] = isset($arPost['PARAMS']['IS_DETAIL']) && $arPost['PARAMS']['IS_DETAIL'] === 'Y';

        $this->infoFromPost['ARTICLE_NAME'] = $arPost['ARTICLE_NAME'] ?: '';
        $this->infoFromPost['ARTICLE_VALUE'] = $arPost['ARTICLE_VALUE'] ?: '';
        $this->infoFromPost['PICTURE'] = $arPost['PICTURE'] ?: '';

        if($this->params['IS_DETAIL']) {
            $featureProps = \Bitrix\Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes($this->params['IBLOCK_ID'], ['CODE' => 'Y']);
        } else {
            $featureProps = \Bitrix\Iblock\Model\PropertyFeature::getListPageShowPropertyCodes($this->params['IBLOCK_ID'], ['CODE' => 'Y']);
        }
        if($featureProps) {
            $this->params['LIST_OFFERS_PROPERTY_CODE'] = $featureProps;
        }

        $this->params['STORES'] = array_diff((array) $this->params['STORES'], [], ['']);
        $this->params['PRICE_CODE'] = array_diff((array) $this->params['PRICE_CODE'], [], ['']);
    }

    public function getResizeParams()
    {
        $arResize = [];
        $useResize = \Bitrix\Main\Config\Option::get(Solution::moduleID, 'USE_CUSTOM_RESIZE_LIST', 'N', $this->params['SITE_ID']) === 'Y';
        if($useResize) {
            $arIBlockFields = \CIBlock::GetFields($this->params['IBLOCK_ID_PARENT']);
            if($arIBlockFields['PREVIEW_PICTURE'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']) {
                if($arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['WIDTH'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['HEIGHT']) {
                    $arResize = ['WIDTH' => $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['WIDTH'], 'HEIGHT' => $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['HEIGHT']];
                }
            }
        }
        $this->arResize = $arResize;
    }

    public function getOfferInfo()
    {
        $arFilter = [
            'IBLOCK_ID' => $this->params['IBLOCK_ID'],
            '=ID' => $this->offerID,
        ];
        $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'];

        // get sku prices
        if($this->params['PRICE_CODE']) {
            $arPricesIDs = SolutionFunctions::getPricesID($this->params['PRICE_CODE'], true);
            if($arPricesIDs) {
                foreach($arPricesIDs as $priceID) {
                    $arSelect[] = 'CATALOG_GROUP_'.$priceID;
                }
            } else {
                $arSelect[] = 'CATALOG_QUANTITY';
            }
        }

        $rsElements = \CIBLockElement::GetList(['ID' => 'DESC'], $arFilter, false, false, $arSelect);
        if($obElement = $rsElements->GetNextElement()) {
            $arItem = $obElement->GetFields();

            $arItem['FIELDS'] = [];
            $arItem['PROPERTIES'] = $obElement->GetProperties();
            $arItem['DISPLAY_PROPERTIES'] = [];

            $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arItem['IBLOCK_ID'], $arItem['ID']);
            $arItem['IPROPERTY_VALUES'] = $ipropValues->getValues();

            foreach($this->params['LIST_OFFERS_PROPERTY_CODE'] as $pid) {
                $prop = &$arItem['PROPERTIES'][$pid];
                if(
                    (is_array($prop['VALUE']) && count($prop['VALUE']) > 0)
                    || (!is_array($prop['VALUE']) && strlen($prop['VALUE']) > 0)
                ) {
                    $arItem['DISPLAY_PROPERTIES'][$pid] = \CIBlockFormatProperties::GetDisplayValue($arItem, $prop, 'news_out');
                }
            }

            $this->modifyDisplayProperties($arItem['DISPLAY_PROPERTIES']);

            $this->offerInfo = $arItem;
        }
    }

    private function modifyDisplayProperties(&$arProperties)
    {
        \CIBlockPriceTools::clearProperties($arProperties, $this->params['OFFER_TREE_PROPS']);
        $arProperties = Solution::PrepareItemProps($arProperties);
        \Aspro\Max\LinkableProperty::resolve($arProperties, $this->params['IBLOCK_ID'], $this->params['SECTION_ID']);

        static::clearDisplayProps($arProperties);

        \Bitrix\Main\Type\Collection::sortByColumn($arProperties, [
            'SORT' => [SORT_NUMERIC, SORT_ASC],
            'ID' => [SORT_NUMERIC, SORT_ASC],
        ]
        );
    }

    public function getOfferInfoDetail()
    {
        $dopOfferInfo = [];
        $arFilter = [
            'IBLOCK_ID' => $this->params['IBLOCK_ID'],
            '=ID' => $this->offerID,
        ];

        $bShowSkuDescription = isset($this->params['SHOW_SKU_DESCRIPTION']) && $this->params['SHOW_SKU_DESCRIPTION'] === 'Y';
        $arSelect = ['ID', 'IBLOCK_ID'];
        if($bShowSkuDescription) {
            $arSelect[] = 'DETAIL_TEXT';
            $arSelect[] = 'PREVIEW_TEXT';
        }

        $rsElements = \CIBLockElement::GetList(['ID' => 'DESC'], $arFilter, false, false, $arSelect);
        if($obElement = $rsElements->GetNextElement()) {
            $offerFields = $obElement->GetFields();
            $dopOfferInfo['DETAIL_TEXT'] = $offerFields['DETAIL_TEXT'] ?? '';
            $dopOfferInfo['PREVIEW_TEXT'] = $offerFields['PREVIEW_TEXT'] ?? '';
            $dopOfferInfo['PROPERTIES'] = $obElement->GetProperties();
            $dopOfferInfo['DISPLAY_PROPERTIES'] = [];

            foreach($this->params['LIST_OFFERS_PROPERTY_CODE_DETAIL'] as $pid) {
                $prop = &$dopOfferInfo['PROPERTIES'][$pid];

                if(
                    (is_array($prop['VALUE']) && count($prop['VALUE']) > 0)
                    || (!is_array($prop['VALUE']) && strlen($prop['VALUE']) > 0)
                ) {
                    $dopOfferInfo['DISPLAY_PROPERTIES'][$pid] = \CIBlockFormatProperties::GetDisplayValue($dopOfferInfo, $prop, 'news_out');
                }
            }
        }
        $dopOfferInfo['PROPERTIES'] = [
            'POPUP_VIDEO' => $dopOfferInfo['PROPERTIES']['POPUP_VIDEO'],
            $this->params['OFFER_ADD_PICT_PROP'] => $dopOfferInfo['PROPERTIES'][$this->params['OFFER_ADD_PICT_PROP']],
            $this->params['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE'] => $dopOfferInfo['PROPERTIES'][$this->params['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE']],
        ];

        $this->modifyDisplayProperties($dopOfferInfo['DISPLAY_PROPERTIES']);

        $this->offerInfoDetail = $dopOfferInfo;
    }

    public function getMeasureRatio()
    {
        $this->offerInfo['CATALOG_MEASURE_RATIO'] = 1;
        $rsRatios = \CCatalogMeasureRatio::getList(
            [],
            ['=PRODUCT_ID' => $this->offerID],
            false,
            false,
            ['PRODUCT_ID', 'RATIO']
        );
        while ($arRatio = $rsRatios->Fetch()) {
            $intRatio = (int) $arRatio['RATIO'];
            $dblRatio = (float) $arRatio['RATIO'];
            $mxRatio = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
            if (abs($mxRatio) < CATALOG_VALUE_EPSILON) {
                $mxRatio = 1;
            } elseif ($mxRatio < 0) {
                $mxRatio = 1;
            }
            $this->offerInfo['CATALOG_MEASURE_RATIO'] = $mxRatio;
            $this->offerInfo['STEP_QUANTITY'] = $mxRatio;
        }
    }

    public function getItemPrices()
    {
        global $USER;
        $USER_ID = $USER->GetID();

        $arOffer = $this->offerInfo;

        $arOffer['CATALOG_QUANTITY'] = (
            $arOffer['CATALOG_QUANTITY'] > 0 && is_float($arOffer['CATALOG_MEASURE_RATIO'])
            ? (float) $arOffer['CATALOG_QUANTITY']
            : (int) $arOffer['CATALOG_QUANTITY']
        );

        $result = false;
        $minPrice = 0;
        $arOffer['PRICES_TYPE'] = \CIBlockPriceTools::GetCatalogPrices(false, $this->params['PRICE_CODE']);
        $arOffer['PRICES_ALLOW'] = \CIBlockPriceTools::GetAllowCatalogPrices($arOffer['PRICES_TYPE']);
        $bPriceVat = $this->params['PRICE_VAT_INCLUDE'] === true || $this->params['PRICE_VAT_INCLUDE'] === 'true';
        $arOffer['PRICES'] = \CIBlockPriceTools::GetItemPrices($arOffer['IBLOCK_ID'], $arOffer['PRICES_TYPE'], $arOffer, $bPriceVat, $this->arCurrencyParams, $USER_ID, $this->params['SITE_ID']);

        if($arOffer['PRICES']) {
            $arPriceTypeID = [];
            foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice) {
                if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']]) {
                    $arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
                    $arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
                }
                unset($arOffer['PRICES'][$priceKey]['MIN_PRICE']);

                if (empty($result)) {
                    $minPrice = (!$this->arCurrencyParams['CURRENCY_ID']
                        ? $arOfferPrice['DISCOUNT_VALUE']
                        : \CCurrencyRates::ConvertCurrency($arOfferPrice['DISCOUNT_VALUE'], $arOfferPrice['CURRENCY'], $this->arCurrencyParams['CURRENCY_ID'])
                    );
                    $result = $priceKey;
                } else {
                    $comparePrice = (!$this->arCurrencyParams['CURRENCY_ID']
                        ? $arOfferPrice['DISCOUNT_VALUE']
                        : \CCurrencyRates::ConvertCurrency($arOfferPrice['DISCOUNT_VALUE'], $arOfferPrice['CURRENCY'], $this->arCurrencyParams['CURRENCY_ID'])
                    );
                    if ($minPrice > $comparePrice && $arOfferPrice['CAN_BUY'] == 'Y') {
                        $minPrice = $comparePrice;
                        $result = $priceKey;
                    }
                }
            }
            if ($result) {
                $arOffer['PRICES'][$result]['MIN_PRICE'] = 'Y';
                $arOffer['MIN_PRICE'] = $arOffer['PRICES'][$result];
            }
            $arOffer['PRICE_MATRIX'] = '';

            if(function_exists('CatalogGetPriceTableEx')) {
                $arOffer['PRICE_MATRIX'] = CatalogGetPriceTableEx($arOffer['ID'], 0, $arPriceTypeID, 'Y', $this->arCurrencyParams);

                if(count($arOffer['PRICE_MATRIX']['ROWS']) <= 1) {
                    $arOffer['PRICE_MATRIX'] = '';
                } else {
                    $arOffer = array_merge($arOffer, Solution::formatPriceMatrix($arOffer));
                }
            }
        }

        $arOffer['CAN_BUY'] = \CIBlockPriceTools::CanBuy($arOffer['IBLOCK_ID'], $arOffer['PRICES_TYPE'], $arOffer);
        $this->offerInfo = $arOffer;
    }

    public function getMeasureName()
    {
        $arDefaultMeasure = \CCatalogMeasure::getDefaultMeasure(true, true);
        $this->offerInfo['CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
        $this->offerInfo['~CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];

        if (!isset($this->offerInfo['CATALOG_MEASURE'])) {
            $this->offerInfo['CATALOG_MEASURE'] = 0;
        }
        $this->offerInfo['CATALOG_MEASURE'] = (int) $this->offerInfo['CATALOG_MEASURE'];
        if ($this->offerInfo['CATALOG_MEASURE'] < 0) {
            $this->offerInfo['CATALOG_MEASURE'] = 0;
        }
        if ($this->offerInfo['CATALOG_MEASURE'] > 0) {
            $rsMeasures = \CCatalogMeasure::getList(
                [],
                ['=ID' => $this->offerInfo['CATALOG_MEASURE']],
                false,
                false,
                ['ID', 'SYMBOL_RUS']
            );
            while ($arMeasure = $rsMeasures->GetNext()) {
                $this->offerInfo['CATALOG_MEASURE_NAME'] = $arMeasure['SYMBOL_RUS'];
                $this->offerInfo['~CATALOG_MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
            }
        }
    }

    public function modifyIBInherited()
    {
        if($this->params['IBINHERIT_TEMPLATES']) {
            $arItemTmp = [
                'OFFERS' => [$this->offerInfo],
            ];
            IBInherited::modifyItemTemplates($this->params, $arItemTmp);
            $this->offerInfo = reset($arItemTmp['OFFERS']);
        }
    }

    public function getCurrencyParams()
    {
        $arCurrencyParams = [];
        if ($this->params['CONVERT_CURRENCY'] == 'Y') {
            if(\CModule::IncludeModule('currency')) {
                $arCurrencyInfo = \CCurrency::GetByID($this->params['CURRENCY_ID']);
                if (is_array($arCurrencyInfo) && !empty($arCurrencyInfo)) {
                    $arCurrencyParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
                }
            }
        }
        $this->arCurrencyParams = $arCurrencyParams;
    }

    public function getPropsGroup()
    {
        global $APPLICATION;

        if($this->params['IS_DETAIL']) {
            ob_start();
            $APPLICATION->IncludeComponent(
                'aspro:props.group.max',
                '',
                [
                    'DISPLAY_PROPERTIES' => $this->offerInfo['DISPLAY_PROPERTIES'],
                    'IBLOCK_ID' => $this->params['IBLOCK_ID'],
                    'SHOW_HINTS' => $this->params['SHOW_HINTS'],
                    'OFFERS_MODE' => 'Y',
                    'PROPERTIES_DISPLAY_TYPE' => $arPost['PARAMS']['PROPERTIES_DISPLAY_TYPE'],
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            $htmlProps = ob_get_clean();
            $this->offerInfo['PROPS_GROUP_HTML'] = $htmlProps;
        }
    }

    public function getGallery()
    {
        if($this->params['SHOW_GALLERY'] == 'Y') {
            $arItem = $this->offerInfo;
            if($this->params['IS_DETAIL']) {
                $arItem['DETAIL_PICTURE'] = $arItem['DETAIL_PICTURE_FIELD'];
            } else {
                $arItem['DETAIL_PICTURE'] = $arItem['PREVIEW_PICTURE_FIELD'];
            }

            $arItem['ALT_TITLE_GET'] = $this->params['ALT_TITLE_GET'];
            $arItem['GALLERY'] = Solution::getSliderForItemExt($arItem, $this->params['OFFER_ADD_PICT_PROP'], true);

            if($arItem['MAIN_ITEM'] && $this->params['ADD_DETAIL_TO_SLIDER'] == 'Y') {
                if(!$this->params['IS_DETAIL'] && $arItem['MAIN_ITEM']['PREVIEW_PICTURE']) {
                    $arItem['MAIN_ITEM']['DETAIL_PICTURE'] = $arItem['MAIN_ITEM']['PREVIEW_PICTURE'];
                }

                $arItem['MAIN_ITEM']['ALT_TITLE_GET'] = $this->params['ALT_TITLE_GET'];
                $mainItemGallery = Solution::getSliderForItemExt($arItem['MAIN_ITEM'], $this->params['ADD_PICT_PROP'], $this->params['ADD_DETAIL_TO_SLIDER'] == 'Y');

                if($this->params['IS_DETAIL'] && empty($mainItemGallery) && $arItem['MAIN_ITEM']['PREVIEW_PICTURE']) {
                    $mainItemGallery[] = \CFile::GetFileArray($arItem['MAIN_ITEM']['PREVIEW_PICTURE']);
                }
                $arItem['GALLERY'] = array_merge($arItem['GALLERY'], $mainItemGallery);
            }
            if($this->params['IS_DETAIL']) {
                $arItem['POPUP_VIDEO'] = (isset($arItem['PROPERTIES']['POPUP_VIDEO']) && $arItem['PROPERTIES']['POPUP_VIDEO']['VALUE'] ? $arItem['PROPERTIES']['POPUP_VIDEO']['VALUE'] : '');
                if($arItem['GALLERY']) {
                    foreach($arItem['GALLERY'] as $i => $arImage) {
                        if(isset($arImage['ID'])) {
                            $arItem['GALLERY'][$i]['BIG']['src'] = \CFile::GetPath($arImage['ID']);
                            $arItem['GALLERY'][$i]['BIG']['width'] = $arImage['WIDTH'];
                            $arItem['GALLERY'][$i]['BIG']['height'] = $arImage['HEIGHT'];
                            $arItem['GALLERY'][$i]['SMALL'] = \CFile::ResizeImageGet($arImage['ID'], ['width' => $this->params['GALLERY_WIDTH'], 'height' => $this->params['GALLERY_HEIGHT']], BX_RESIZE_IMAGE_PROPORTIONAL, true, []);
                            $arItem['GALLERY'][$i]['THUMB'] = \CFile::ResizeImageGet($arImage['ID'], ['width' => 52, 'height' => 52], BX_RESIZE_IMAGE_PROPORTIONAL, true, []);
                        }
                    }
                }
            } else {
                array_splice($arItem['GALLERY'], $this->params['MAX_GALLERY_ITEMS']);
                $arItem['GALLERY_HTML'] = SolutionItem::showSectionGallery(['ITEM' => $arItem, 'RETURN' => true, 'RESIZE' => $this->arResize]);
            }
            $this->offerInfo = $arItem;
        }
    }

    public function getAdditionalGalleryOffer()
    {
        $this->offerInfo['ADDITIONAL_GALLERY'] = [];
        if($this->params['USE_ADDITIONAL_GALLERY'] === 'Y') {
            $imgProp = $this->params['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE'];
            if(is_array($this->offerInfo['PROPERTIES'][$imgProp]['VALUE'])) {
                $this->offerInfo['ADDITIONAL_GALLERY'] = self::getAdditionalGallery($this->offerInfo, $this->offerInfo['PROPERTIES'][$imgProp]['VALUE']);
            }
        }
    }

    public static function getAdditionalGallery(array $arItem, array $arImages = []): array
    {
        $additionalGallery = [];
        if($arImages) {
            foreach($arImages as $img) {
                $arFile = \CFile::GetFileArray($img);

                $alt = ($arFile['DESCRIPTION'] ?: $arFile['ALT'] ?: $arItem['NAME']);
                $title = ($arFile['DESCRIPTION'] ?: $arFile['TITLE'] ?: $arItem['NAME']);

                if(isset($arItem['ALT_TITLE_GET']) && $arItem['ALT_TITLE_GET'] == 'SEO') {
                    $alt = ($arItem['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ?: $arItem['NAME']);
                    $title = ($arItem['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ?: $arItem['NAME']);
                }

                $arPhoto = [
                    'DETAIL' => $arFile,
                    'PREVIEW' => \CFile::ResizeImageGet($img, ['width' => 1500, 'height' => 1500], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true),
                    'THUMB' => \CFile::ResizeImageGet($img, ['width' => 60, 'height' => 60], BX_RESIZE_IMAGE_EXACT, true),
                    'TITLE' => $title,
                    'ALT' => $alt,
                ];
                $additionalGallery[] = $arPhoto;
            }
        }

        return $additionalGallery;
    }

    public function getMainItemInfo()
    {
        $arMainItem = [];
        $mainItemForOffers = [];
        $bShowSkuDescription = isset($this->params['SHOW_SKU_DESCRIPTION']) && $this->params['SHOW_SKU_DESCRIPTION'] === 'Y';

        if($this->maiItemID) {
            $arFilterTmp = ['ID' => $this->maiItemID, 'IBLOCK_ID' => $this->params['IBLOCK_ID_PARENT']];
            $arSelectTmp = ['ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PROPERTY_POPUP_VIDEO', 'PROPERTY_CML2_ARTICLE', 'PROPERTY_'.$this->params['ADD_PICT_PROP']];

            if($bShowSkuDescription) {
                $arSelectTmp[] = 'DETAIL_TEXT';
                $arSelectTmp[] = 'PREVIEW_TEXT';
            }

            if($this->params['USE_ADDITIONAL_GALLERY'] === 'Y') {
                $arSelectTmp[] = 'PROPERTY_'.$this->params['ADDITIONAL_GALLERY_PROPERTY_CODE'];
            }

            $arElement = SolutionCache::CIblockElement_GetList(['CACHE' => ['TAG' => SolutionCache::GetIBlockCacheTag($this->params['IBLOCK_ID_PARENT']), 'MULTI' => 'N']], $arFilterTmp, false, false, $arSelectTmp);

            if (
                isset($this->params['ALT_TITLE_GET'])
                && $this->params['ALT_TITLE_GET'] === 'SEO'
            ) {
                $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($this->params['IBLOCK_ID_PARENT'], $this->maiItemID);
                $arMainItem['IPROPERTY_VALUES'] = $ipropValues->getValues();
            }

            if(is_array($arElement) && !empty($arElement)) {
                $arMainItem['NAME'] = $arElement['NAME'];
                if(isset($arElement['PROPERTY_POPUP_VIDEO_VALUE'])) {
                    $arMainItem['POPUP_VIDEO'] = $arElement['PROPERTY_POPUP_VIDEO_VALUE'];
                }

                $arMainItem['DETAIL_PICTURE'] = $arElement['DETAIL_PICTURE'];
                $arMainItem['PREVIEW_PICTURE'] = $arElement['PREVIEW_PICTURE'];

                $postfix = '';
                if($this->params['SITE_ID'] && \Bitrix\Main\Config\Option::get(Solution::moduleID, 'HIDE_SITE_NAME_TITLE', 'N') == 'N') {
                    $dbRes = \CSite::GetByID($this->params['SITE_ID']);
                    $arSite = $dbRes->Fetch();
                    $postfix = ' - '.$arSite['SITE_NAME'];
                }

                $mainItemForOffers = [
                    'DETAIL_TEXT' => $bShowSkuDescription ? $arElement['DETAIL_TEXT'] : '',
                    'PREVIEW_TEXT' => $bShowSkuDescription ? $arElement['PREVIEW_TEXT'] : '',
                    'SHOW_SKU_DESCRIPTION' => $bShowSkuDescription ?? 'N',
                    'IS_DETAIL' => $this->params['IS_DETAIL'],
                    'NO_PHOTO' => SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg',
                    'ARTICLE' => $arElement['PROPERTY_CML2_ARTICLE_VALUE'],
                    'POSTFIX' => $postfix,
                    'PRODUCT_ID' => $arElement['ID'],
                    'ADDITIONAL_GALLERY' => [],
                    'POPUP_VIDEO' => $arElement['PROPERTY_POPUP_VIDEO_VALUE'],
                    'OID' => $this->params['SKU_DETAIL_ID'] ?? 'oid',
                ];

                if(!empty($arElement['PROPERTY_'.$this->params['ADD_PICT_PROP'].'_VALUE'])) {
                    $arMainItem['PROPERTIES'][$this->params['ADD_PICT_PROP']] = [
                        'PROPERTY_TYPE' => 'F',
                        'VALUE' => (array) $arElement['PROPERTY_'.$this->params['ADD_PICT_PROP'].'_VALUE'],
                    ];
                }

                if(!empty($arElement['PROPERTY_'.$this->params['ADDITIONAL_GALLERY_PROPERTY_CODE'].'_VALUE'])) {
                    $mainItemForOffers['ADDITIONAL_GALLERY'] = self::getAdditionalGallery($arMainItem, (array) $arElement['PROPERTY_'.$this->params['ADDITIONAL_GALLERY_PROPERTY_CODE'].'_VALUE']);
                }
            }
        }
        $this->mainElementInfo = $mainItemForOffers;
        $this->offerInfo['MAIN_ITEM'] = $arMainItem;
    }

    public function getPreviewPicture()
    {
        $offerPictures = \CIBlockPriceTools::getDoublePicturesForItem($this->offerInfo, $this->params['OFFER_ADD_PICT_PROP']);
        $this->offerInfo['OWNER_PICT'] = empty($offerPictures['PICT']);
        $this->offerInfo['PREVIEW_PICTURE_FIELD'] = $this->offerInfo['PREVIEW_PICTURE'] ?: $this->offerInfo['DETAIL_PICTURE'];
        $this->offerInfo['DETAIL_PICTURE_FIELD'] = $this->offerInfo['DETAIL_PICTURE'];
        $this->offerInfo['PREVIEW_PICTURE'] = false;
        $this->offerInfo['PREVIEW_PICTURE_SECOND'] = false;
        $this->offerInfo['SECOND_PICT'] = true;
        if (!$this->offerInfo['OWNER_PICT']) {
            if (empty($offerPictures['SECOND_PICT'])) {
                $offerPictures['SECOND_PICT'] = $offerPictures['PICT'];
            }
            $this->offerInfo['PREVIEW_PICTURE'] = $offerPictures['PICT'];
            $this->offerInfo['PREVIEW_PICTURE_SECOND'] = $offerPictures['SECOND_PICT'];
        }
    }

    public function getArticle()
    {
        if($this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']['DISPLAY_VALUE']) {
            $this->offerInfo['ARTICLE'] = ($this->params['IS_DETAIL'] ? '' : GetMessage('T_ARTICLE_COMPACT').': ').(is_array($this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']['DISPLAY_VALUE']) ? reset($this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']['DISPLAY_VALUE']) : $this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']['DISPLAY_VALUE']);
            $this->offerInfo['ARTICLE_NAME'] = $this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']['NAME'];
            unset($this->offerInfo['DISPLAY_PROPERTIES']['ARTICLE']);
        }
    }

    public function getDiscountTime()
    {
        if($this->params['SHOW_DISCOUNT_TIME'] == 'Y' && $this->params['SHOW_COUNTER_LIST'] != 'N') {
            global $USER;
            $arUserGroups = $USER->GetUserGroupArray();
            $active_to = '';
            $arDiscounts = \CCatalogDiscount::GetDiscountByProduct($this->offerInfo['ID'], $arUserGroups, 'N', [], $this->params['SITE_ID']);
            if($arDiscounts) {
                foreach($arDiscounts as $arDiscountOffer) {
                    if($arDiscountOffer['ACTIVE_TO']) {
                        $active_to = $arDiscountOffer['ACTIVE_TO'];
                        break;
                    }
                }
            }
            $this->offerInfo['DISCOUNT_ACTIVE'] = $active_to;
        }
    }

    public function getDataFromParams()
    {
        $this->offerInfo['DISPLAY_COMPARE'] = ($this->params['DISPLAY_COMPARE'] ? $this->params['DISPLAY_COMPARE'] : 'N');
        $this->offerInfo['DISPLAY_WISH_BUTTONS'] = ($this->params['DISPLAY_WISH_BUTTONS'] ? $this->params['DISPLAY_WISH_BUTTONS'] : 'N');
        $this->offerInfo['SHOW_OLD_PRICE'] = ($this->params['SHOW_OLD_PRICE'] == 'Y');
        $this->offerInfo['PRODUCT_QUANTITY_VARIABLE'] = $this->params['PRODUCT_QUANTITY_VARIABLE'];
        $this->offerInfo['SHOW_DISCOUNT_PERCENT'] = ($this->params['SHOW_DISCOUNT_PERCENT'] == 'Y');
        $this->offerInfo['SHOW_POPUP_PRICE'] = ($this->params['SHOW_POPUP_PRICE'] == 'Y');
        $this->offerInfo['SHOW_DISCOUNT_TIME_EACH_SKU'] = $this->params['SHOW_DISCOUNT_TIME_EACH_SKU'];
        $this->offerInfo['SHOW_MEASURE'] = ($this->params['SHOW_MEASURE'] == 'Y' ? 'Y' : 'N');
        $this->offerInfo['USE_PRICE_COUNT'] = $this->params['USE_PRICE_COUNT'];
        $this->offerInfo['SHOW_DISCOUNT_PERCENT_NUMBER'] = ($this->params['SHOW_DISCOUNT_PERCENT_NUMBER'] == 'Y');
        $this->offerInfo['SHOW_ARTICLE_SKU'] = $this->params['SHOW_ARTICLE_SKU'];
        $this->offerInfo['ARTICLE_SKU'] = ($this->params['SHOW_ARTICLE_SKU'] == 'Y' ? (isset($this->infoFromPost['ARTICLE_VALUE']) && $this->infoFromPost['ARTICLE_VALUE'] ? $this->infoFromPost['ARTICLE_NAME'].': '.$this->infoFromPost['ARTICLE_VALUE'] : '') : '');
    }

    public function formatPriceMatrix()
    {
        $this->offerInfo['ITEM_PRICES'] = [];

        if($this->offerInfo['PRICE_MATRIX']) {
            $rangeStart = (int) current(array_column($this->offerInfo['PRICE_MATRIX']['ROWS'], 'QUANTITY_FROM'));

            if ($rangeStart) {
                $minimalQty = $this->offerInfo['CATALOG_MEASURE_RATIO'] * ((int) ($rangeStart / $this->offerInfo['CATALOG_MEASURE_RATIO']));
                if ($minimalQty < $rangeStart) {
                    $minimalQty += $this->offerInfo['CATALOG_MEASURE_RATIO'];
                }
                $this->offerInfo['CONFIG']['MIN_QUANTITY_BUY'] = $minimalQty;
                $this->offerInfo['CONFIG']['SET_MIN_QUANTITY_BUY'] = true;
            }
            $this->offerInfo['PRICE_MATRIX_HTML'] = Solution::showPriceMatrix($this->offerInfo, $this->params, $this->offerInfo['MEASURE']);
            foreach($this->offerInfo['PRICE_MATRIX']['ROWS'] as $range => $arInterval) {
                $minimalPrice = null;
                foreach($this->offerInfo['PRICE_MATRIX']['MATRIX'] as $arPrice) {
                    if($arPrice[$range]) {
                        if($minimalPrice === null || $minimalPrice['DISCOUNT_PRICE'] > $arPrice[$range]['DISCOUNT_PRICE']) {
                            if($arPrice[$range]['PRICE'] > $arPrice[$range]['DISCOUNT_PRICE']) {
                                $arPrice[$range]['PERCENT'] = round((($arPrice[$range]['PRICE'] - $arPrice[$range]['DISCOUNT_PRICE']) / $arPrice[$range]['PRICE']) * 100);
                                $arPrice[$range]['DIFF'] = ($arPrice[$range]['PRICE'] - $arPrice[$range]['DISCOUNT_PRICE']);
                                $arPrice[$range]['PRINT_DIFF'] = \CCurrencyLang::CurrencyFormat($arPrice[$range]['PRICE'] - $arPrice[$range]['DISCOUNT_PRICE'], $arPrice[$range]['CURRENCY'], true);
                            }
                            $minimalPrice = $arPrice[$range];
                        }
                    }
                }
                $this->offerInfo['ITEM_PRICES'][$range] = $minimalPrice;
            }
        }
    }

    public function getButtons()
    {
        /* need for add basket props */
        $catalogIblockID = $this->params['IBLOCK_ID_PARENT'];
        $currentSKUIBlock = $this->offerInfo['IBLOCK_ID'];
        $this->offerInfo['IBLOCK_ID'] = $catalogIblockID ?? $this->offerInfo['IBLOCK_ID'];

        $basketButtonClass = isset($this->params['CART_CLASS']) ? $this->params['CART_CLASS'] : 'btn-exlg';
        $arAddToBasketData = Solution::GetAddToBasketArray($this->offerInfo, $this->offerInfo['TOTAL_COUNT'], $this->params['DEFAULT_COUNT'], $this->params['BASKET_URL'], $this->offerInfo['IS_DETAIL'], [], $basketButtonClass, $this->params);

        /* restore IBLOCK_ID */
        $this->offerInfo['IBLOCK_ID'] = $currentSKUIBlock;

        $this->offerInfo['IBLOCK_ID_PARENT'] = $catalogIblockID;

        $arAddToBasketData['HTML'] = str_replace('data-item', 'data-props="'.implode(';', (array) $this->params['OFFERS_CART_PROPERTIES']).'" data-item', $arAddToBasketData['HTML']);

        $this->offerInfo['PRICES_HTML'] = SolutionItem::showItemPrices($this->params, $this->offerInfo['PRICES'], $this->offerInfo['MEASURE'], $this->offerInfo['MIN_PRICE']['ID'], $this->params['SHOW_DISCOUNT_PERCENT_NUMBER'] == 'Y' ? 'N' : 'Y', false, true);
        $this->offerInfo['MAX_QUANTITY'] = $this->offerInfo['TOTAL_COUNT'];
        $this->offerInfo['STEP_QUANTITY'] = $this->offerInfo['CATALOG_MEASURE_RATIO'];
        $this->offerInfo['QUANTITY_FLOAT'] = is_double($this->offerInfo['CATALOG_MEASURE_RATIO']);
        $this->offerInfo['AVAILIABLE'] = Solution::GetQuantityArray([
            'totalCount' => $this->offerInfo['TOTAL_COUNT'],
            'arItemIDs' => [],
            'useStoreClick' => isset($this->params['USE_STORE_CLICK']) ? $this->params['USE_STORE_CLICK'] : 'N',
            'dataAmount' => $this->params['CATALOG_DETAIL_SHOW_AMOUNT_STORES'] !== 'Y' ? [] : [
                'ID' => $this->offerInfo['ID'],
                'STORES' => $this->params['STORES'],
                'IMMEDIATELY' => 'Y',
            ],
        ]);
        $this->offerInfo['CONFIG'] = $arAddToBasketData;
        $this->offerInfo['HTML'] = $arAddToBasketData['HTML'];

        $this->offerInfo['SHOW_ONE_CLICK_BUY'] = ($this->params['SHOW_ONE_CLICK_BUY'] ? $this->params['SHOW_ONE_CLICK_BUY'] : 'N');

        // for list view
        $this->params['IBLOCK_ID'] = $this->offerInfo['IBLOCK_ID'];
        $ocbButtonClass = isset($this->params['OCB_CLASS']) ? $this->params['OCB_CLASS'] : 'btn-sm';
        $this->offerInfo['ONE_CLICK_BUY_HTML'] = SolutionFunctions::showItemOCB($arAddToBasketData, $this->offerInfo, array_merge($this->params, ['IBLOCK_ID' => $catalogIblockID]), true, $ocbButtonClass);

        $this->offerInfo['CAN_BUY'] = ($this->params['USE_REGION'] == 'Y' ? $arAddToBasketData['CAN_BUY'] : $this->offerInfo['CAN_BUY']);
    }

    public function prepareToCache()
    {
        $arItem = $this->offerInfo;

        $arItem['PROPERTIES'] = [
            'POPUP_VIDEO' => $this->offerInfo['PROPERTIES']['POPUP_VIDEO'],
            $this->params['OFFER_ADD_PICT_PROP'] => $this->offerInfo['PROPERTIES'][$this->params['OFFER_ADD_PICT_PROP']],
        ];

        $this->offerInfo = [
            'ID' => $arItem['ID'],
            'IBLOCK_ID' => $arItem['IBLOCK_ID'],
            'NAME' => $arItem['~NAME'] ?? $arItem['NAME'],
            'PICTURE' => ($arItem['PREVIEW_PICTURE'] ? $arItem['PREVIEW_PICTURE']['SRC'] : ($arItem['DETAIL_PICTURE'] ? $arItem['DETAIL_PICTURE']['SRC'] : $this->infoFromPost['PICTURE'])),
            'PREVIEW_PICTURE_FIELD' => $arItem['PREVIEW_PICTURE_FIELD'] ?? [],
            'DETAIL_PICTURE_FIELD' => $arItem['DETAIL_PICTURE_FIELD'] ?? [],
            'CAN_BUY' => $arItem['CAN_BUY'],
            'MEASURE' => $arItem['CATALOG_MEASURE_NAME'],
            'CATALOG_MEASURE_RATIO' => $arItem['CATALOG_MEASURE_RATIO'],
            'CATALOG_QUANTITY_TRACE' => $arItem['CATALOG_QUANTITY_TRACE'],
            'CATALOG_CAN_BUY_ZERO' => $arItem['CATALOG_CAN_BUY_ZERO'],
            'DISCOUNT_ACTIVE' => $arItem['DISCOUNT_ACTIVE'],
            'CATALOG_SUBSCRIBE' => $arItem['CATALOG_SUBSCRIBE'],
            'ARTICLE' => $arItem['ARTICLE'],
            'ARTICLE_NAME' => $arItem['ARTICLE_NAME'],
            'PRICES' => $arItem['PRICES'],
            'PRICE_MATRIX' => $arItem['PRICE_MATRIX'],
            'PROPERTIES' => $arItem['PROPERTIES'],
            'DISPLAY_PROPERTIES' => $arItem['DISPLAY_PROPERTIES'],
            'URL' => $arItem['DETAIL_PAGE_URL'],
            'TOTAL_COUNT' => Solution::GetTotalCount($arItem, $this->params),
            'IPROPERTY_VALUES' => $arItem['IPROPERTY_VALUES'] ?? [],
            'PREVIEW_TEXT' => $arItem['PREVIEW_TEXT'] ?? '',
            'DETAIL_TEXT' => $arItem['DETAIL_TEXT'] ?? '',
            'MAIN_ITEM' => $arItem['MAIN_ITEM'],
            'MIN_PRICE' => $arItem['MIN_PRICE'],
        ];
    }

    public function getBaseCacheParams(): array
    {
        $arCacheParams = [];
        $arCacheParams[] = $this->offerID;
        $arCacheParams[] = $this->params['SITE_ID'];
        $arCacheParams[] = $this->params['IBLOCK_ID'];
        $arCacheParams[] = $this->params['IBLOCK_ID_PARENT'];
        $arCacheParams[] = $this->params['USE_REGION'];
        $arCacheParams[] = $this->params['OFFER_TREE_PROPS'];

        return $arCacheParams;
    }

    public function getCacheParams()
    {
        global $USER;

        $arCacheParams = $this->getBaseCacheParams();
        $arCacheParams[] = $this->params['PRICE_CODE'];
        $arCacheParams[] = $this->params['CONVERT_CURRENCY'];
        $arCacheParams[] = $this->params['CURRENCY_ID'];
        $arCacheParams[] = $this->params['ADD_PICT_PROP'];
        $arCacheParams[] = $this->params['OFFER_ADD_PICT_PROP'];
        $arCacheParams[] = $this->params['LIST_OFFERS_PROPERTY_CODE'];
        $arCacheParams[] = $this->params['SHOW_DISCOUNT_TIME'];
        $arCacheParams[] = $this->params['STORES'];

        if($this->params['CACHE_GROUPS'] !== 'N') {
            $arCacheParams[] = $USER->GetGroups();
        }

        $this->arCacheParams = $arCacheParams;
    }

    public function getCacheParamsDetail()
    {
        $arCacheParamsDetail = $this->getBaseCacheParams();
        $arCacheParamsDetail[] = $this->params['USE_ADDITIONAL_GALLERY'];
        $arCacheParamsDetail[] = $this->params['ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE'];
        $arCacheParamsDetail[] = $this->params['LIST_OFFERS_PROPERTY_CODE_DEATAIL'];
        $arCacheParamsDetail[] = $this->params['SHOW_SKU_DESCRIPTION'];

        $this->arCacheParamsDetail = $arCacheParamsDetail;
    }

    public function addOfferInfoDetail()
    {
        $this->offerInfo = array_merge($this->offerInfo, $this->offerInfoDetail);
    }

    public static function replaceRegionTags(array &$arInfo)
    {
        foreach ($arInfo as $keyInfo => $valInfo) {
            if(is_string($valInfo)) {
                $arInfo[$keyInfo] = SolutionEvents::replaceMarks($valInfo, SolutionRegionality::$arSeoMarks);
            } elseif(is_array($valInfo)) {
                static::replaceRegionTags($arInfo[$keyInfo]);
            }
        }
    }

    public function getRegionModification()
    {
        global $arRegion;
        $arRegion = SolutionRegionality::getCurrentRegion();
        Solution::setRegionSeoMarks();

        static::replaceRegionTags($this->offerInfo);
        static::replaceRegionTags($this->mainElementInfo);
    }

    public static function clearDisplayProps(array &$arProps)
    {
        $arProps = array_map(function ($arProp) {
            $arPropTmp = array_filter($arProp, function ($key) {
                $arNeedKeys = ['ID', 'SORT', 'NAME', 'HINT', 'DISPLAY_VALUE'];

                return in_array($key, $arNeedKeys, true);
            }, ARRAY_FILTER_USE_KEY);

            return $arPropTmp;
        }, $arProps);
    }

    public static function getChangeSku(array $arPost): array
    {
        global $USER;

        \Bitrix\Main\Loader::includeModule('sale');
        \Bitrix\Main\Loader::includeModule('catalog');

        $obSKU = new self($arPost);
        $obSKU->getCacheParams();
        ?>

        <?if($arPost['PARAMS']):?>
            <?php
            $obCache = new \CPHPCache();

            $cacheTag = 'element_'.$obSKU->offerID;
            $cacheID = 'getSKUjs'.$cacheTag.md5(serialize($obSKU->arCacheParams));
            $cachePath = '/CMaxCache/iblock/getSKUjs/'.$cacheTag.'/';
            $cacheTime = $arPost['PARAMS']['CACHE_TIME'];

            if(isset($arPost['CLEAR_CACHE']) && $arPost['CLEAR_CACHE'] === 'Y' && $USER->IsAdmin()) {
                SolutionCache::ClearSKUjsCache($obSKU->offerID);
            }

            if($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
                $res = $obCache->GetVars();
                $obSKU->offerInfo = $res['arOfferInfo'];
                $obSKU->arResize = $res['arResize'];
            } else {
                $obSKU->getCurrencyParams();

                $obSKU->getOfferInfo();

                $obSKU->getResizeParams();

                $obSKU->getPreviewPicture();

                $obSKU->getDiscountTime();

                $obSKU->getMeasureRatio();

                $obSKU->getItemPrices();

                $obSKU->getMeasureName();

                $obSKU->modifyIBInherited();

                /* save cache */
                $obSKU->prepareToCache();

                if(\Bitrix\Main\Config\Option::get('main', 'component_cache_on', 'Y') != 'N') {
                    $obCache->StartDataCache($cacheTime, $cacheID, $cachePath);
                    $obCache->EndDataCache(['arOfferInfo' => $obSKU->offerInfo, 'arResize' => $obSKU->arResize]);
                }
            }

        if($obSKU->params['IS_DETAIL']) {
            $obSKU->getCacheParamsDetail();

            $cacheIDDetail = 'getSKUjs'.$cacheTag.md5(serialize($obSKU->arCacheParamsDetail));
            if($obCache->InitCache($cacheTime, $cacheIDDetail, $cachePath)) {
                $res = $obCache->GetVars();
                $obSKU->offerInfoDetail = $res['arOfferInfoDetail'];
            } else {
                $obSKU->getOfferInfoDetail();

                if(\Bitrix\Main\Config\Option::get('main', 'component_cache_on', 'Y') != 'N') {
                    $obCache->StartDataCache($cacheTime, $cacheIDDetail, $cachePath);
                    $obCache->EndDataCache(['arOfferInfoDetail' => $obSKU->offerInfoDetail]);
                }
            }

            $obSKU->addOfferInfoDetail();
        }

        /* format items */
        if($obSKU->offerInfo) {
            $obSKU->getMainItemInfo();

            $obSKU->getRegionModification();

            $obSKU->getArticle();

            $obSKU->getButtons();

            $obSKU->getGallery();

            $obSKU->getAdditionalGalleryOffer();

            $obSKU->formatPriceMatrix();

            $obSKU->getDataFromParams();

            // for economy weight
            $obSKU->offerInfo['PROPERTIES'] = [];
            $obSKU->offerInfo['MAIN_ITEM'] = [];

            $obSKU->getPropsGroup();
        }

        ?>
        <?endif; ?>
        <?php
        $arRes = [
        'offer' => $obSKU->offerInfo,
        'mainItemForOffers' => $obSKU->params['IS_DETAIL'] ? $obSKU->mainElementInfo : [],
        ];

        return $arRes;
    }
}
