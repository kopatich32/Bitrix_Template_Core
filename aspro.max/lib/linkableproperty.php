<?php

namespace Aspro\Max;

use Bitrix\Main\Localization\Loc;
use CMax as Solution;

Loc::loadMessages(__FILE__);

class LinkableProperty
{
    public static $smartFilterPathes = [];

    public static function resolve(array &$properties = [], $iblockId = 0, $sectionId = 0)
    {
        if (!self::isActivatedLinkableProperty()) {
            return;
        }

        $arSectionProperties = \CIBlockSectionPropertyLink::GetArray($iblockId, $sectionId);

        foreach ($properties as $code => $arProperty) {
            if (!$arProperty['VALUE']) {
                continue;
            }
            try {
                $property = LinkableProperty\Factory::create(array_merge($arProperty, (array) $arSectionProperties[$arProperty['ID']]));
                $properties[$code]['VALUE'] = $properties[$code]['DISPLAY_VALUE'] = $property->resolve(self::getSmartFilterPathBySeсtionId($sectionId));
            } catch (\Exception $e) {
            }
        }
    }

    public static function isActivatedLinkableProperty()
    {
        return Solution::GetBackParametrsValues(SITE_ID)['USE_LINKABLE_PROPERTY'] === 'Y';
    }

    public static function getSmartFilterPathBySeсtionId($sectionId)
    {
        if (self::$smartFilterPathes[$sectionId]) {
            return self::$smartFilterPathes[$sectionId];
        }

        $sectionList = \CIBlockSection::GetByID($sectionId);

        $sectionList->SetUrlTemplates(Solution::GetBackParametrsValues(SITE_ID)['LINKABLE_PROPERTY_SMART_FILTER_URL']);
        $section = $sectionList->GetNext();

        return self::$smartFilterPathes[$sectionId] = Solution::GetBackParametrsValues(SITE_ID)['CATALOG_PAGE_URL'].$section['DETAIL_PAGE_URL'];
    }
}
