<? 
namespace Aspro\Max\Agents\Stickers;

use CMax as Solution;
use \Bitrix\Main\Config\Option;

class Novinka extends Base {

    public static function updateSticker(string $siteId, string $startElement = '0') {
        
        $stickerCode = Option::get(Solution::moduleID, "STICKER_NEW", 'NEW', $siteId);
        $step =  Option::get(Solution::moduleID, "COUNT_GOODS_STEP_NEW", '100', $siteId);
        $interval = Option::get(Solution::moduleID, "STICKER_NEW_TIME", '30', $siteId);

        $iblockId = static::getIBlockIDCatalog($siteId);
        $valueId = static::getValueId($siteId, $stickerCode);
       

        if(!$iblockId || !$valueId) return false;

        $rsItems = \CIBlockElement::GetList(["id" => "ASC"], ["IBLOCK_ID" => $iblockId, ">ID" => $startElement, "ACTIVE"=>"Y", "ACTIVE_DATE" => "Y"], false, ["nTopCount" => $step], ['ID', 'CREATED_DATE']);

        $arValuesUpdate = [];
        while ($arItem = $rsItems->GetNext()) {

            $createdDate = new \Bitrix\Main\Type\Date($arItem['CREATED_DATE'], 'Y.m.d');
            $dateInterval = $createdDate->getDiff(new \Bitrix\Main\Type\Date());
        
            $arValues = static::getPropertyValues($iblockId, $arItem['ID']);
            
            if ($interval > $dateInterval->days) {
                $arValuesUpdate[$arItem['ID']] = static::getAddStickers($valueId, $arValues);
            }  else {
                $arValuesUpdate[$arItem['ID']] = static::getDeleteStickers($valueId, $arValues);
            }

            $startElement = $arItem['ID'];
        }
        static::updateStickersValues($arValuesUpdate);

        return $startElement;
    }
    
}

