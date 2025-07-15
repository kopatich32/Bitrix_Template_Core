<?php

namespace Aspro\Max\Product;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class Price
{
    public static function isRangePriceMode(array $result = [])
    {
        return isset($result['ITEM_PRICE_MODE']) && $result['ITEM_PRICE_MODE'] === 'Q';
    }

    public static function resolveWhenEmptyPriceMatrix(array $result = [], array $element = [], array $params = [])
    {
        $arResult = [];
        $prices = $result['CAT_PRICES'] ?? $result['PRICES'];

        if (!Loader::includeModule('catalog')) {
            throw new SystemException('Error include catalog');
        }

        if (!$prices) {
            return $arResult;
        }

        if (!$element) {
            $element = $result;
        }

        $isEmptyPriceMatrix = isset($element['PRICE_MATRIX']) && !$element['PRICE_MATRIX'];
        if (
            $isEmptyPriceMatrix
            && !$element['PRICES']
        ) {
            $arFilter = [
                'IBLOCK_ID' => $element['IBLOCK_ID'],
                '=ID' => $element['ID'],
            ];
            $arSelect = array_map(fn ($item) => 'CATALOG_GROUP_'.$item['ID'], $prices);
            $arElement = \CIBLockElement::GetList(['ID' => 'DESC'], $arFilter, false, false, $arSelect)->Fetch();

            $arResult['PRICES'] = \CIBlockPriceTools::GetItemPrices(
                $element['IBLOCK_ID'],
                $prices,
                array_merge($result, $arElement),
                $params['PRICE_VAT_INCLUDE'],
                $result['CONVERT_CURRENCY']
            );
            if (!empty($arResult['PRICES'])) {
                $arResult['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arResult['PRICES']);
            }
        }

        return $arResult;
    }
}
