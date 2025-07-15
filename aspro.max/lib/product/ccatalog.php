<?php

namespace Aspro\Max\Product;

/* @global \CMain $APPLICATION */
use Bitrix\Catalog;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use CMaxRegionality as SolutionRegionality;

class CCatalog extends \CCatalogProduct
{
    public static $existPriceTypeDiscountsNew = false;

    public static function OnGetOptimalPriceRegion($intProductID, $quantity = 1, $arUserGroups = [], $renewal = 'N', $priceList = [], $siteID = false, $arDiscountCoupons = false)
    {
        global $APPLICATION, $arRegion;
        static $priceTypeCache = [];
        if (!$arRegion) {
            if (Main\Loader::includeModule('aspro.max')) {
                $arRegion = SolutionRegionality::getCurrentRegion(); // get current region from regionality module
            }
        }

        if ($arRegion) {
            static $resultCurrency, $arPricesID;

            $intProductID = (int) $intProductID;
            if ($intProductID <= 0) {
                $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_PRODUCT_ID_ABSENT'), 'NO_PRODUCT_ID');

                return false;
            }

            $quantity = (float) $quantity;
            if ($quantity <= 0) {
                $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_QUANTITY_ABSENT'), 'NO_QUANTITY');

                return false;
            }

            $intIBlockID = (int) \CIBlockElement::GetIBlockByID($intProductID);
            if ($intIBlockID <= 0) {
                $APPLICATION->ThrowException(
                    Loc::getMessage(
                        'BT_MOD_CATALOG_PROD_ERR_ELEMENT_ID_NOT_FOUND',
                        ['#ID#' => $intProductID]
                    ),
                    'NO_ELEMENT'
                );

                return false;
            }

            if (class_exists('\Bitrix\Sale\Internals\SiteCurrencyTable')) {
                $resultCurrency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteID ? $siteID : SITE_ID);
            }

            if ($resultCurrency === null) {
                $resultCurrency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
            }

            if (empty($resultCurrency)) {
                $APPLICATION->ThrowException(Loc::getMessage('BT_MOD_CATALOG_PROD_ERR_NO_BASE_CURRENCY'), 'NO_BASE_CURRENCY');

                return false;
            }

            if ($arPricesID === null) {
                $arPricesID = [];
                if ($arRegion['LIST_PRICES']) {
                    foreach ($arRegion['LIST_PRICES'] as $arPrice) {
                        if (is_array($arPrice)) {
                            if ($arPrice['CAN_BUY'] == 'Y') {
                                $arPricesID[] = $arPrice['ID'];
                            }
                        }
                    }
                }
                $strRegionPrices = isset($arRegion['LIST_PRICES']) && is_array($arRegion['LIST_PRICES']) ? reset($arRegion['LIST_PRICES']) : '';
                if (!$arPricesID && ($strRegionPrices == 'component' || $strRegionPrices == '')) {
                    if (!is_array($arUserGroups) && (int) $arUserGroups.'|' == (string) $arUserGroups.'|') {
                        $arUserGroups = [(int) $arUserGroups];
                    }

                    if (!is_array($arUserGroups)) {
                        $arUserGroups = [];
                    }

                    if (!in_array(2, $arUserGroups)) {
                        $arUserGroups[] = 2;
                    }
                    Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);

                    $cacheKey = 'U'.implode('_', $arUserGroups);
                    if (!isset($priceTypeCache[$cacheKey])) {
                        $priceTypeCache[$cacheKey] = [];
                        $priceIterator = Catalog\GroupAccessTable::getList([
                            'select' => ['CATALOG_GROUP_ID'],
                            'filter' => ['@GROUP_ID' => $arUserGroups, '=ACCESS' => Catalog\GroupAccessTable::ACCESS_BUY],
                            'order' => ['CATALOG_GROUP_ID' => 'ASC'],
                        ]);
                        while ($priceType = $priceIterator->fetch()) {
                            $priceTypeId = (int) $priceType['CATALOG_GROUP_ID'];
                            $priceTypeCache[$cacheKey][$priceTypeId] = $priceTypeId;
                            unset($priceTypeId);
                        }
                        unset($priceType, $priceIterator);
                    }
                    if (empty($priceTypeCache[$cacheKey])) {
                        return false;
                    }
                    $arPricesID = $priceTypeCache[$cacheKey];
                }
            }
            if ($arPricesID) {
                if (!isset($priceList) || !is_array($priceList)) {
                    $priceList = [];
                }

                /*if($arRegion['LIST_STORES'] && reset($arRegion['LIST_STORES']) != 'component') // check product quantity
                {
                    $quantity_stores = 0;
                    $arSelect = array('ID', 'PRODUCT_AMOUNT');
                    $arFilter = array(
                        'ID' => $arRegion['LIST_STORES'],
                        'PRODUCT_ID' => $intProductID,
                    );
                    $rsStore = CCatalogStore::GetList(array(), $arFilter, false, false, $arSelect);
                    while($arStore = $rsStore->Fetch())
                    {
                        $quantity_stores += $arStore['PRODUCT_AMOUNT'];
                    }
                    if(!$quantity_stores)
                        return false;
                }*/

                $arSelect = ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'];
                $arFilter = [
                    '=PRODUCT_ID' => $intProductID,
                    '@CATALOG_GROUP_ID' => $arPricesID,
                    [
                        'LOGIC' => 'OR',
                        '<=QUANTITY_FROM' => $quantity,
                        '=QUANTITY_FROM' => null,
                    ],
                    [
                        'LOGIC' => 'OR',
                        '>=QUANTITY_TO' => $quantity,
                        '=QUANTITY_TO' => null,
                    ],
                ];
                if (empty($priceList)) {
                    if (class_exists('\Bitrix\Catalog\PriceTable')) {
                        $iterator = Catalog\PriceTable::getList([
                            'select' => $arSelect,
                            'filter' => $arFilter,
                        ]);
                    } else {
                        $iterator = \CPrice::GetList([], $arFilter, false, false, $arSelect);
                    }
                    while ($row = $iterator->fetch()) {
                        $row['ELEMENT_IBLOCK_ID'] = $intIBlockID;
                        $priceList[] = $row;
                    }
                    unset($row);
                } else {
                    foreach (array_keys($priceList) as $priceIndex) {
                        $priceList[$priceIndex]['ELEMENT_IBLOCK_ID'] = $intIBlockID;
                    }
                    unset($priceIndex);
                }

                if (empty($priceList)) {
                    return false;
                }

                $iterator = \CCatalogProduct::GetVATInfo($intProductID);
                if ($vat = $iterator->Fetch()) {
                    $vat['RATE'] = (float) $vat['RATE'] * 0.01;
                } else {
                    $vat = ['RATE' => 0.0, 'VAT_INCLUDED' => 'N'];
                }
                unset($iterator);

                if (\CCatalogProduct::getUseDiscount()) {
                    if ($arDiscountCoupons === false) {
                        $arDiscountCoupons = \CCatalogDiscountCoupon::GetCoupons();
                    }
                }

                $boolDiscountVat = true;
                $isNeedDiscounts = \CCatalogProduct::getUseDiscount();
                $resultWithVat = Catalog\Product\Price\Calculation::isIncludingVat();

                if (static::$saleIncluded === null) {
                    static::initSaleSettingsNew();
                }
                $isNeedleToMinimizeCatalogGroup = static::isNeedleToMinimizeCatalogGroupNew($priceList);

                foreach ($priceList as $priceData) {
                    $priceData['VAT_RATE'] = $vat['RATE'];
                    $priceData['VAT_INCLUDED'] = $vat['VAT_INCLUDED'];

                    $currentPrice = $priceData['PRICE'];
                    if ($boolDiscountVat) {
                        if ($priceData['VAT_INCLUDED'] == 'N') {
                            $currentPrice *= (1 + $priceData['VAT_RATE']);
                        }
                    } else {
                        if ($priceData['VAT_INCLUDED'] == 'Y') {
                            $currentPrice /= (1 + $priceData['VAT_RATE']);
                        }
                    }

                    if ($priceData['CURRENCY'] != $resultCurrency) {
                        $currentPrice = \CCurrencyRates::ConvertCurrency($currentPrice, $priceData['CURRENCY'], $resultCurrency);
                    }
                    $currentPrice = roundEx($currentPrice, CATALOG_VALUE_PRECISION);

                    $result = [
                        'BASE_PRICE' => $currentPrice,
                        'COMPARE_PRICE' => $currentPrice,
                        'PRICE' => $currentPrice,
                        'CURRENCY' => $resultCurrency,
                        'DISCOUNT_LIST' => [],
                        'USE_ROUND' => true,
                        'RAW_PRICE' => $priceData,
                    ];
                    if ($isNeedDiscounts) {
                        $arDiscounts = \CCatalogDiscount::GetDiscount(
                            $intProductID,
                            $intIBlockID,
                            $priceData['CATALOG_GROUP_ID'],
                            $arUserGroups,
                            $renewal,
                            $siteID,
                            $arDiscountCoupons
                        );

                        $discountResult = \CCatalogDiscount::applyDiscountList($currentPrice, $resultCurrency, $arDiscounts);
                        unset($arDiscounts);
                        if ($discountResult === false) {
                            return false;
                        }
                        $result['PRICE'] = $discountResult['PRICE'];
                        $result['COMPARE_PRICE'] = $discountResult['PRICE'];
                        $result['DISCOUNT_LIST'] = $discountResult['DISCOUNT_LIST'];
                        unset($discountResult);
                    } elseif ($isNeedleToMinimizeCatalogGroup) {
                        $calculateData = $priceData;
                        $calculateData['PRICE'] = $currentPrice;
                        $calculateData['CURRENCY'] = $resultCurrency;
                        $possibleSalePrice = static::getPossibleSalePriceNew(
                            $intProductID,
                            $calculateData,
                            $quantity,
                            $siteID,
                            $arUserGroups,
                            $arDiscountCoupons
                        );

                        unset($calculateData);

                        if ($possibleSalePrice === null) {
                            return false;
                        }
                        $result['COMPARE_PRICE'] = $possibleSalePrice;
                        unset($possibleSalePrice);
                    }

                    if ($boolDiscountVat) {
                        if (!$resultWithVat) {
                            $result['PRICE'] /= (1 + $priceData['VAT_RATE']);
                            $result['COMPARE_PRICE'] /= (1 + $priceData['VAT_RATE']);
                            $result['BASE_PRICE'] /= (1 + $priceData['VAT_RATE']);
                        }
                    } else {
                        if ($resultWithVat) {
                            $result['PRICE'] *= (1 + $priceData['VAT_RATE']);
                            $result['COMPARE_PRICE'] *= (1 + $priceData['VAT_RATE']);
                            $result['BASE_PRICE'] *= (1 + $priceData['VAT_RATE']);
                        }
                    }

                    $result['UNROUND_PRICE'] = $result['PRICE'];
                    $result['UNROUND_BASE_PRICE'] = $result['BASE_PRICE'];
                    if (Catalog\Product\Price\Calculation::isComponentResultMode()) {
                        $result['BASE_PRICE'] = Catalog\Product\Price::roundPrice(
                            $priceData['CATALOG_GROUP_ID'],
                            $result['BASE_PRICE'],
                            $resultCurrency
                        );
                        $result['PRICE'] = Catalog\Product\Price::roundPrice(
                            $priceData['CATALOG_GROUP_ID'],
                            $result['PRICE'],
                            $resultCurrency
                        );
                        if (
                            empty($result['DISCOUNT_LIST'])
                            || Catalog\Product\Price\Calculation::compare($result['BASE_PRICE'], $result['PRICE'], '<=')
                        ) {
                            $result['BASE_PRICE'] = $result['PRICE'];
                        }
                        $result['COMPARE_PRICE'] = $result['PRICE'];
                    }

                    if (empty($minimalPrice) || $minimalPrice['COMPARE_PRICE'] > $result['COMPARE_PRICE']) {
                        $minimalPrice = $result;
                    } elseif (
                        $minimalPrice['COMPARE_PRICE'] == $result['COMPARE_PRICE']
                        && $minimalPrice['RAW_PRICE']['PRICE_SCALE'] > $result['RAW_PRICE']['PRICE_SCALE']
                    ) {
                        $minimalPrice = $result;
                    }

                    unset($currentPrice, $result);
                }
                unset($priceData);
                unset($vat);

                $discountValue = ($minimalPrice['BASE_PRICE'] > $minimalPrice['PRICE'] ? $minimalPrice['BASE_PRICE'] - $minimalPrice['PRICE'] : 0);

                $arResult = [
                    'PRICE' => $minimalPrice['RAW_PRICE'],
                    'RESULT_PRICE' => [
                        'PRICE_TYPE_ID' => $minimalPrice['RAW_PRICE']['CATALOG_GROUP_ID'],
                        'BASE_PRICE' => $minimalPrice['BASE_PRICE'],
                        'DISCOUNT_PRICE' => $minimalPrice['PRICE'],
                        'UNROUND_DISCOUNT_PRICE' => $minimalPrice['UNROUND_PRICE'],
                        'CURRENCY' => $resultCurrency,
                        'DISCOUNT' => $discountValue,
                        'PERCENT' => (
                            $minimalPrice['BASE_PRICE'] > 0 && $discountValue > 0
                            ? roundEx((100 * $discountValue) / $minimalPrice['BASE_PRICE'], CATALOG_VALUE_PRECISION)
                            : 0
                        ),
                        'VAT_RATE' => $minimalPrice['RAW_PRICE']['VAT_RATE'],
                        'VAT_INCLUDED' => $resultWithVat ? 'Y' : 'N', // $minimalPrice['RAW_PRICE']['VAT_INCLUDED']
                    ],
                    'DISCOUNT_PRICE' => $minimalPrice['PRICE'],
                    'DISCOUNT' => [],
                    'DISCOUNT_LIST' => [],
                    'PRODUCT_ID' => $intProductID,
                ];
                if (!empty($minimalPrice['DISCOUNT_LIST'])) {
                    reset($minimalPrice['DISCOUNT_LIST']);
                    $arResult['DISCOUNT'] = current($minimalPrice['DISCOUNT_LIST']);
                    $arResult['DISCOUNT_LIST'] = $minimalPrice['DISCOUNT_LIST'];
                }
                unset($minimalPrice);

                return $arResult;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    protected static function isNeedleToMinimizeCatalogGroupNew(array $priceList)
    {
        if (self::$saleIncluded === null) {
            self::initSaleSettings();
        }

        if (
            !self::$saleIncluded
            || !self::$useSaleDiscount
            || count($priceList) < 2
        ) {
            return false;
        }

        return self::$existPriceTypeDiscountsNew;
    }

    protected static function initSaleSettingsNew()
    {
        if (self::$saleIncluded === null) {
            self::$saleIncluded = Main\Loader::includeModule('sale');
        }
        if (self::$saleIncluded) {
            self::$useSaleDiscount = (string) Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
            if (self::$useSaleDiscount) {
                // TODO: replace runtime to reference after sale 17.5.2 will be stable
                $row = Sale\Internals\DiscountEntitiesTable::getList([
                    'select' => ['ID'],
                    'filter' => [
                        '=MODULE_ID' => 'catalog',
                        '=ENTITY' => 'PRICE',
                        '=FIELD_ENTITY' => 'CATALOG_GROUP_ID',
                        '=FIELD_TABLE' => 'CATALOG_GROUP_ID',
                        '=ACTIVE_DISCOUNT.ACTIVE' => 'Y',
                    ],
                    'runtime' => [
                        new Main\Entity\ReferenceField(
                            'ACTIVE_DISCOUNT',
                            'Bitrix\Sale\Internals\Discount',
                            ['=this.DISCOUNT_ID' => 'ref.ID'],
                            ['join_type' => 'LEFT']
                        ),
                    ],
                    'limit' => 1,
                ])->fetch();
                self::$existPriceTypeDiscountsNew = !empty($row);
                unset($row);
            }
        }
    }

    private static function getPossibleSalePriceNew($intProductID, array $priceData, $quantity, $siteID, array $userGroups, $coupons)
    {
        $possibleSalePrice = null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        if (empty($priceData)) {
            return $possibleSalePrice;
        }

        $isCompatibilityUsed = Sale\Compatible\DiscountCompatibility::isUsed();
        Sale\Compatible\DiscountCompatibility::stopUsageCompatible();

        $freezeCoupons = (empty($coupons) && is_array($coupons));

        if ($freezeCoupons) {
            Sale\DiscountCouponsManager::freezeCouponStorage();
        }

        /** @var Sale\Basket $basket */
        static $basket = null,
        /** @var Sale\BasketItem $basketItem */
        $basketItem = null;

        if ($basket !== null) {
            if ($basket->getSiteId() != $siteID) {
                $basket = null;
                $basketItem = null;
            }
        }
        if ($basket === null) {
            /** @var Sale\Basket $basketClassName */
            $basketClassName = $registry->getBasketClassName();

            $basket = $basketClassName::create($siteID);
            $basketItem = $basket->createItem('catalog', $intProductID);
        }

        $fields = [
            'PRODUCT_ID' => $intProductID,
            'QUANTITY' => $quantity,
            'LID' => $siteID,
            'PRODUCT_PRICE_ID' => $priceData['ID'],
            'PRICE' => $priceData['PRICE'],
            'BASE_PRICE' => $priceData['PRICE'],
            'DISCOUNT_PRICE' => 0,
            'CURRENCY' => $priceData['CURRENCY'],
            'CAN_BUY' => 'Y',
            'DELAY' => 'N',
            'PRICE_TYPE_ID' => (int) $priceData['CATALOG_GROUP_ID'],
        ];

        $basketItem->setFieldsNoDemand($fields);

        $discount = Sale\Discount::buildFromBasket($basket, new Sale\Discount\Context\UserGroup($userGroups));
        $discount->setExecuteModuleFilter(['all', 'catalog']);
        $discount->calculate();

        $calcResults = $discount->getApplyResult(true);

        if ($calcResults && !empty($calcResults['PRICES']['BASKET'])) {
            $possibleSalePrice = reset($calcResults['PRICES']['BASKET']);
            $possibleSalePrice = $possibleSalePrice['PRICE'];
        }

        if ($freezeCoupons) {
            Sale\DiscountCouponsManager::unFreezeCouponStorage();
        }
        $discount->setExecuteModuleFilter(['all', 'sale', 'catalog']);

        if ($isCompatibilityUsed === true) {
            Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
        }

        return $possibleSalePrice;
    }
}
