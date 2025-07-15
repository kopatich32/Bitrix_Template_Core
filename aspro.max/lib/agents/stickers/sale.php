<?

namespace Aspro\Max\Agents\Stickers;

use CMax as Solution;
use \Bitrix\Main\Config\Option;

class Sale extends Base
{

    public static function updateSticker(string $siteId, string $startElement = '0') {
    
        $iblockIdCatalog = static::getIBlockIDCatalog($siteId);
        $iblockIdStock = static::getIBlockID('stock', $siteId);
        $stickerCode = Option::get(Solution::moduleID, "STICKER_SALE", 'STOCK', $siteId);
        $valueId = static::getvalueId($siteId, $stickerCode);
        $arParams = [
			'IBLOCK_STOCK_ID' => $iblockIdStock,
			'IBLOCK_ID' => $iblockIdCatalog,
		];
        $step =  Option::get(Solution::moduleID, "COUNT_GOODS_STEP_SALE", '100', $siteId);

        if (!$iblockIdCatalog || !$iblockIdStock || !$valueId) return false;

        $rsItems = \CIBlockElement::GetList(["id" => "ASC"], ["IBLOCK_ID" => $iblockIdCatalog, ">ID" => $startElement, "ACTIVE" => "Y", "ACTIVE_DATE" => "Y"], false, ["nTopCount" => $step], ['ID', 'PROPERTY_LINK_SALE']);
        
        $arValuesUpdate = [];
        while ($arItem = $rsItems->GetNext()) {
            $arItem['DISPLAY_PROPERTIES']['STOCK']['VALUE'] = $arItem['PROPERTY_LINK_SALE_VALUE'];

            $arValues = static::getPropertyValues($iblockIdCatalog, $arItem['ID']);
            $arLinkedItems = \Aspro\Functions\CAsproMax::getLinkedItems($arItem, "STOCK", $arParams);
            if($arLinkedItems) {
                $arValuesUpdate[$arItem['ID']] = static::getAddStickers($valueId, $arValues);
            } else {
                $arValuesUpdate[$arItem['ID']] = static::getDeleteStickers($valueId, $arValues);
            }

            $startElement = $arItem['ID'];
        }
        static::updateStickersValues($arValuesUpdate);

        return $startElement;
    }
}
