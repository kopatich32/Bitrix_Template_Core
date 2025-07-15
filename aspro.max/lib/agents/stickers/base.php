<?

namespace Aspro\Max\Agents\Stickers;

use CMax as Solution;
use \Bitrix\Main\Config\Option;

abstract class Base {

    protected static $iblockIdCatalog = '';


    protected static function getIBlockIDCatalog(string $siteId) {
        if(!static::$iblockIdCatalog) return Option::get(Solution::moduleID, 'CATALOG_IBLOCK_ID', '', $siteId);

        return static::$iblockIdCatalog;
    }

    abstract static function updateSticker(string $siteId, string $startElement = '0');

    public static function updateStickerMain($siteId)
    {
        $startElement = static::updateSticker($siteId, $startElement = '0');

        static::addOneMoreStepAgent($siteId, $startElement);
        return static::getNameClass().'::'.__FUNCTION__.'("'.$siteId. '");';
    }

    public static function updateStickerMore(string $siteId, string $startElement) 
    {
        $tmpElement = $startElement;
        $startElement = static::updateSticker($siteId, $startElement);

        if($startElement && $startElement !== $tmpElement) {
            return static::getNameClass().'::'.__FUNCTION__.'("'.$siteId. '", "' .$startElement.'");';
        } else {
            $agentName = static::getNameClass().'::'.__FUNCTION__.'("'.$siteId. '", "' .$tmpElement.'");';
            \CAgent::RemoveAgent($agentName);
        }
    }

    protected static function getIBlockID(string $code, string $siteId): string
    {
        $db = \CIBlock::GetList(
            array(),
            array(
                'ACTIVE' => 'Y',
                'CODE' => '%_' . $code,
                'LID' => $siteId,
            ),
        );

        while ($arIblock = $db->Fetch()) {
            if(!$arIblock) return '';
            return $arIblock['ID'];
        }
    }

    protected static function getValueId(string $siteId, string $externalId) 
    {
        $iblockIdCatalog = static::getIBlockIDCatalog($siteId);

        $rsResult = \CIBlockProperty::GetPropertyEnum("HIT", Array(), Array("IBLOCK_ID"=> $iblockIdCatalog, "EXTERNAL_ID"=>$externalId));
        if($arResult = $rsResult->GetNext())
        {
            return $arResult['ID'];
        }
    }

    protected static function getPropertyValues(string $iblockId, string $idElement) : array
    {
        $arValues = [];
    
        $rsResult = \CIBlockElement::GetProperty($iblockId, $idElement, [], ["CODE" => "HIT"]);
        while ($arResult = $rsResult->GetNext()) {
            $arValues[] = $arResult['VALUE'];
        }
    
        return $arValues;
    }

    protected static function getNameClass () {
        return static::class;
    }

    protected static function getAddStickers (string $valueId, array $arValues) : array
    {
        if(!in_array($valueId, $arValues)) {
            array_push($arValues, $valueId);
        }
        return $arValues;
    }

    protected static function getDeleteStickers (string $valueId, array $arValues) : array
    {
        if(in_array($valueId, $arValues)) {
            $key = array_search($valueId, $arValues);
            unset($arValues[$key]);
    
            if(!$arValues[$key]) {
                $arValues[$key] = '';
            } 
        }

        return $arValues;
    }

    protected static function updateStickersValues (array $arValuesUpdate)
    {
        if($arValuesUpdate) {
            foreach ($arValuesUpdate as $idElement => $values) {
                \CIBlockElement::SetPropertyValuesEx($idElement, false, array('HIT' =>  $values));
            }
        }
    }

    public static function add(string $siteId, string $day)
    {
        $agentName = static::getNameClass().'::updateStickerMain("'.$siteId.'");';
        \CAgent::AddAgent(
            $agentName, 
            Solution::moduleID,                          
            "N",                                  
            60*60*24*$day, 
            '',
			'Y',    
			\ConvertTimeStamp(time() + (60*1),'FULL'), 
            30);

    }

    protected static function addOneMoreStepAgent(string $siteId, string $id)
    {
        $agentName = static::getNameClass().'::updateStickerMore("'.$siteId. '", "' .$id.'");';

        \CAgent::AddAgent(
            $agentName, 
            Solution::moduleID,                          
            "N",                                  
            60,                          
            '',
            "Y",                                  
            \ConvertTimeStamp(time() + (60*1),'FULL'),
            30);
    }
    
}