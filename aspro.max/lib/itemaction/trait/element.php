<?php

namespace Aspro\Max\Itemaction\Trait;

use Bitrix\Main\Loader;

trait Element
{
    protected static function getElement(int $id): array
    {
        Loader::includeModule('iblock');

        $arItem = \CIBlockElement::GetByID($id)->Fetch();

        return $arItem ?: [];
    }

    protected static function modifyItem(int &$id, int &$iblockId, array &$arItem)
    {
        if (Loader::includeModule('catalog')) {
            $mxResult = \CCatalogSku::GetProductInfo($id);
            if (is_array($mxResult)) {
                $iblockId = $mxResult['IBLOCK_ID'];
            }
        }
    }
}
