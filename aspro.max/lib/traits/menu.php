<?php

namespace Aspro\Max\Traits;

use Aspro\Functions\CAsproMax as SolutionFunctions;
use Bitrix\Main\Type\Collection;
use CMax as Solution;
use CMaxCache as SolutionCache;

trait Menu
{
    public static function replaceMenuChilds(array &$arResult, array $arParams)
    {
        global $APPLICATION;

        $arMegaLinks = $arMegaItems = [];

        $menuIblockId = SolutionCache::$arIBlocks[SITE_ID][Solution::partnerName.'_'.Solution::solutionName.'_catalog'][Solution::partnerName.'_'.Solution::solutionName.'_megamenu'][0];
        if ($menuIblockId) {
            $arMenuSections = SolutionCache::CIblockSection_GetList(
                [
                    'SORT' => 'ASC',
                    'ID' => 'ASC',
                    'CACHE' => [
                        'TAG' => SolutionCache::GetIBlockCacheTag($menuIblockId),
                        'GROUP' => ['DEPTH_LEVEL'],
                        'MULTI' => 'Y',
                    ],
                ],
                [
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y',
                    'IBLOCK_ID' => $menuIblockId,
                    '<=DEPTH_LEVEL' => $arParams['MAX_LEVEL'],
                ],
                false,
                [
                    'ID',
                    'NAME',
                    'IBLOCK_SECTION_ID',
                    'DEPTH_LEVEL',
                    'PICTURE',
                    'UF_MENU_LINK',
                    'UF_MEGA_MENU_LINK',
                    'UF_CATALOG_ICON',
                    'UF_MENU_BRANDS',
                    'UF_MENU_BANNER',
                ]
            );

            ksort($arMenuSections);

            if ($arMenuSections) {
                $cur_page = $APPLICATION->GetCurPage(true);
                $cur_page_no_index = $APPLICATION->GetCurPage(false);

                foreach ($arMenuSections as $depth => $arLinks) {
                    foreach ($arLinks as $arLink) {
                        $url = trim($arLink['UF_MEGA_MENU_LINK']);
                        $url = $url ?: trim($arLink['UF_MENU_LINK']);
                        if (
                            (
                                $depth == 1
                                && strlen($url)
                            )
                            || $depth > 1
                        ) {
                            $arMegaItem = [
                                'TEXT' => htmlspecialcharsbx($arLink['NAME']),
                                'NAME' => htmlspecialcharsbx($arLink['NAME']),
                                'LINK' => strlen($url) ? $url : 'javascript:;',
                                'SECTION_PAGE_URL' => strlen($url) ? $url : 'javascript:;',
                                'SELECTED' => false,
                                'PARAMS' => [
                                    'PICTURE' => $arLink['PICTURE'],
                                    'SORT' => $arLink['SORT'],
                                    'SECTION_ICON' => $arLink['UF_CATALOG_ICON'],
                                ],
                                'CHILD' => [],
                            ];

                            if ($arLink['PICTURE']) {
                                $arMegaItem['IMAGES']['src'] = \CFile::GetPath($arLink['PICTURE']);
                            }

                            if ($arLink['UF_MENU_BRANDS']) {
                                $arMegaItem['UF_MENU_BRANDS'] = $arMegaItem['PARAMS']['BRANDS'] = $arLink['UF_MENU_BRANDS'];
                            }

                            if ($arLink['UF_MENU_BANNER']) {
                                $arMegaItem['UF_MENU_BANNER'] = $arMegaItem['PARAMS']['BANNERS'] = $arLink['UF_MENU_BANNER'];
                            }

                            $arMegaItems[$arLink['ID']] = &$arMegaItem;

                            if ($depth > 1) {
                                if (strlen($url)) {
                                    $arMegaItem['SELECTED'] = \CMenu::IsItemSelected($url, $cur_page, $cur_page_no_index);
                                }

                                if ($arMegaItems[$arLink['IBLOCK_SECTION_ID']]) {
                                    $arMegaItems[$arLink['IBLOCK_SECTION_ID']]['IS_PARENT'] = 1;
                                    $arMegaItems[$arLink['IBLOCK_SECTION_ID']]['CHILD'][] = &$arMegaItems[$arLink['ID']];
                                }
                            } else {
                                $arMegaLinks[] = &$arMegaItems[$arLink['ID']];
                            }

                            unset($arMegaItem);
                        }
                    }
                }
            }
        }

        if ($arMegaLinks) {
            foreach ($arResult as $key => $arItem) {
                foreach ($arMegaLinks as $arLink) {
                    if ($arItem['LINK'] == $arLink['LINK']) {
                        if (
                            $arResult[$key]['PARAMS']['FROM_IBLOCK']
                            && !empty($arLink['CHILD'])
                        ) {
                            foreach ($arLink['CHILD'] as &$arLinkChild) {
                                $arLinkChild['PARAMS']['FROM_IBLOCK'] = 1;
                            }

                            unset($arLinkChild);
                        }
                        if (Solution::getFrontParametrValue('REPLACE_TYPE') === 'REPLACE') {
                            if ($arResult[$key]['PARAMS']['MEGA_MENU_CHILDS']) {
                                array_splice($arResult, $key, 1, $arLink['CHILD']);
                            } else {
                                $arResult[$key]['CHILD'] = &$arLink['CHILD'];
                                $arResult[$key]['IS_PARENT'] = (bool) $arLink['CHILD'];
                            }
                        } else {
                            if ($arResult[$key]['PARAMS']['MEGA_MENU_CHILDS']) {
                                if (array_key_exists('CHILD', $arResult[$key]) && $arResult[$key]['CHILD']) {
                                    $arLink['CHILD'] = static::compareMenuItems($arResult[$key]['CHILD'], $arLink['CHILD']);
                                }

                                array_splice($arResult, $key, 1, $arLink['CHILD']);
                            } else {
                                $arResult[$key]['CHILD'] = static::compareMenuItems($arResult[$key]['CHILD'], $arLink['CHILD']);
                                $arResult[$key]['IS_PARENT'] = (bool) $arResult[$key]['CHILD'];
                            }
                        }
                    }
                }
            }
        }
    }

    public static function compareMenuItems(array $parentMenu, array $childMenu): array
    {
        $arMenuEnd = $childMenu;
        foreach ($parentMenu as &$parentLink) {
            foreach ($childMenu as $childKey => $childLink) {
                if (
                    $childLink['LINK'] == $parentLink['LINK']
                    || $childLink['LINK'] == $parentLink['SECTION_PAGE_URL']
                ) {
                    $parentLink['NAME'] = $parentLink['TEXT'] = $childLink['NAME'];

                    if ($childLink['PARAMS']['PICTURE'] && isset($parentLink['PARAMS']['PICTURE'])) {
                        $parentLink['PARAMS']['PICTURE'] = $childLink['PARAMS']['PICTURE'];
                    }

                    if ($childLink['PARAMS']['SORT'] && isset($parentLink['PARAMS']['SORT'])) {
                        $parentLink['PARAMS']['SORT'] = $childLink['PARAMS']['SORT'];
                    }

                    if ($childLink['UF_MENU_BANNER']) {
                        $parentLink['UF_MENU_BANNER'] = $parentLink['PARAMS']['BANNERS'] = $childLink['UF_MENU_BANNER'];
                    }

                    if ($childLink['UF_MENU_BRANDS']) {
                        $parentLink['UF_MENU_BRANDS'] = $parentLink['PARAMS']['BRANDS'] = $childLink['UF_MENU_BRANDS'];
                    }

                    if ($childLink['CHILD']) {
                        if ($parentLink['CHILD']) {
                            $parentLink['CHILD'] = static::compareMenuItems($parentLink['CHILD'], $childLink['CHILD']);
                        } else {
                            $parentLink['CHILD'] = $childLink['CHILD'];
                        }
                    }
                    unset($arMenuEnd[$childKey]);

                    if ($parentLink['CHILD'] && count($parentLink['CHILD']) > 1) {
                        Collection::sortByColumn($parentLink['CHILD'], 'PARAMS', fn ($params) => ($params['SORT'] ?? 500));
                    }
                }
            }
        }

        if ($arMenuEnd) {
            $parentMenu = array_merge($parentMenu, $arMenuEnd);
        }
        Collection::sortByColumn($parentMenu, 'PARAMS', fn ($params) => ($params['SORT'] ?? 500));
        unset($parentLink);

        return $parentMenu;
    }

    public static function getChilds($input, &$start = 0, $level = 0)
    {
        $childs = [];

        if (!$level) {
            $lastDepthLevel = 1;
            if (is_array($input)) {
                foreach ($input as $i => $arItem) {
                    if ($arItem['DEPTH_LEVEL'] > $lastDepthLevel) {
                        if ($i > 0) {
                            $input[$i - 1]['IS_PARENT'] = 1;
                        }
                    }
                    $lastDepthLevel = $arItem['DEPTH_LEVEL'];
                }
            }
        }

        for ($i = $start, $count = count($input); $i < $count; ++$i) {
            $item = $input[$i];
            if ($level > $item['DEPTH_LEVEL'] - 1) {
                break;
            } elseif (!empty($item['IS_PARENT'])) {
                ++$i;
                $item['CHILD'] = static::getChilds($input, $i, $level + 1);
                --$i;
            }
            $childs[] = $item;
        }

        $start = $i;

        if ($GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' && $GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y' && $GLOBALS['arRegion']) {
            if (is_array($childs)) {
                foreach ($childs as $i => $item) {
                    if ($item['PARAMS'] && isset($item['PARAMS']['LINK_REGION'])) {
                        if ($item['PARAMS']['LINK_REGION']) {
                            if (!in_array($GLOBALS['arRegion']['ID'], $item['PARAMS']['LINK_REGION'])) {
                                unset($childs[$i]);
                            }
                        } else {
                            unset($childs[$i]);
                        }
                    }
                }
            }
        }

        return $childs;
    }

    public static function getChilds2($input, &$start = 0, $level = 0)
    {
        static $arIblockItemsMD5 = [];

        if (!$level) {
            $lastDepthLevel = 1;
            if ($input && is_array($input)) {
                foreach ($input as $i => $arItem) {
                    if ($arItem['DEPTH_LEVEL'] > $lastDepthLevel) {
                        if ($i > 0) {
                            $input[$i - 1]['IS_PARENT'] = 1;
                        }
                    }
                    $lastDepthLevel = $arItem['DEPTH_LEVEL'];
                }
            }
        }

        $childs = [];
        $count = count($input);
        for ($i = $start; $i < $count; ++$i) {
            $item = $input[$i];
            if (!isset($item)) {
                continue;
            }
            if ($level > $item['DEPTH_LEVEL'] - 1) {
                break;
            } else {
                if (!empty($item['IS_PARENT'])) {
                    ++$i;
                    $item['CHILD'] = static::getChilds2($input, $i, $level + 1);
                    --$i;
                }

                $childs[] = $item;
            }
        }
        $start = $i;

        if (is_array($childs)) {
            foreach ($childs as $j => $item) {
                if ($item['PARAMS']) {
                    $md5 = md5($item['TEXT'].$item['LINK'].$item['SELECTED'].$item['PERMISSION'].$item['ITEM_TYPE'].$item['IS_PARENT'].serialize($item['ADDITIONAL_LINKS']).serialize($item['PARAMS']));
                    if (isset($arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']])) {
                        if (isset($arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']][$level]) || ($item['DEPTH_LEVEL'] === 1 && !$level)) {
                            unset($childs[$j]);
                            continue;
                        }
                    }
                    if (!isset($arIblockItemsMD5[$md5])) {
                        $arIblockItemsMD5[$md5] = [$item['PARAMS']['DEPTH_LEVEL'] => [$level => true]];
                    } else {
                        $arIblockItemsMD5[$md5][$item['PARAMS']['DEPTH_LEVEL']][$level] = true;
                    }
                }
            }
        }

        if ($GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' && $GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y' && $GLOBALS['arRegion']) {
            if (is_array($childs)) {
                foreach ($childs as $i => $item) {
                    if ($item['PARAMS'] && isset($item['PARAMS']['LINK_REGION'])) {
                        if ($item['PARAMS']['LINK_REGION']) {
                            if (!in_array($GLOBALS['arRegion']['ID'], $item['PARAMS']['LINK_REGION'])) {
                                unset($childs[$i]);
                            }
                        } else {
                            unset($childs[$i]);
                        }
                    }
                }
            }
        }

        if (!$level) {
            $arIblockItemsMD5 = [];
        }

        return $childs;
    }

    public static function getSectionChilds($PSID, &$arSections, &$arSectionsByParentSectionID, &$arItemsBySectionID, &$aMenuLinksExt, $bMenu = false)
    {
        if ($arSections && is_array($arSections)) {
            foreach ($arSections as $arSection) {
                if ($arSection['IBLOCK_SECTION_ID'] == $PSID) {
                    if (!$bMenu) {
                        $arItem = [$arSection['NAME'], $arSection['SECTION_PAGE_URL'], [], ['FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => $arSection['DEPTH_LEVEL'], 'IBLOCK_ID' => $arSection['IBLOCK_ID']]];
                        $arItem[3]['IS_PARENT'] = (isset($arItemsBySectionID[$arSection['ID']]) || isset($arSectionsByParentSectionID[$arSection['ID']]) ? 1 : 0);
                        if ($arSection['PICTURE']) {
                            $arItem[3]['PICTURE'] = $arSection['PICTURE'];
                        }
                        if ($arSection['UF_REGION'] ?? false) {
                            $arItem[3]['LINK_REGION'] = $arSection['UF_REGION'];
                        }
                        if ($arSection['UF_MENU_BANNER'] ?? false) {
                            $arItem[3]['BANNERS'] = $arSection['UF_MENU_BANNER'];
                        }
                        if ($arSection['UF_MENU_BRANDS'] ?? false) {
                            $arItem[3]['BRANDS'] = $arSection['UF_MENU_BRANDS'];
                        }
                        if ($arSection['UF_CATALOG_ICON'] ?? false) {
                            $arItem[3]['SECTION_ICON'] = $arSection['UF_CATALOG_ICON'];
                        }
                        $bCheck = $arItem[3]['IS_PARENT'];
                    } else {
                        $arItem = [
                            'TEXT' => $arSection['NAME'],
                            'LINK' => $arSection['SECTION_PAGE_URL'],
                            [],
                            'PARAMS' => ['FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => $arSection['DEPTH_LEVEL'], 'ID' => $arSection['ID'], 'IBLOCK_ID' => $arSection['IBLOCK_ID']],
                            'DEPTH_LEVEL' => $arSection['DEPTH_LEVEL'],
                        ];
                        $arItem['PARAMS']['IS_PARENT'] = $arItem['IS_PARENT'] = (isset($arItemsBySectionID[$arSection['ID']]) || isset($arSectionsByParentSectionID[$arSection['ID']]) ? 1 : 0);
                        if ($arSection['PICTURE']) {
                            $arItem['PARAMS']['PICTURE'] = $arSection['PICTURE'];
                        }
                        if ($arSection['UF_REGION']) {
                            $arItem['PARAMS']['LINK_REGION'] = $arSection['UF_REGION'];
                        }
                        if ($arSection['UF_MENU_BANNER']) {
                            $arItem['PARAMS']['BANNERS'] = $arSection['UF_MENU_BANNER'];
                        }
                        if ($arSection['UF_MENU_BRANDS']) {
                            $arItem['PARAMS']['BRANDS'] = $arSection['UF_MENU_BRANDS'];
                        }
                        if ($arSection['UF_CATALOG_ICON']) {
                            $arItem['PARAMS']['SECTION_ICON'] = $arSection['UF_CATALOG_ICON'];
                        }
                        $bCheck = $arItem['PARAMS']['IS_PARENT'];
                    }

                    $aMenuLinksExt[] = $arItem;
                    if ($bCheck) {
                        // subsections
                        static::getSectionChilds($arSection['ID'], $arSections, $arSectionsByParentSectionID, $arItemsBySectionID, $aMenuLinksExt, $bMenu);
                        // section elements
                        if ($arItemsBySectionID[$arSection['ID']] && is_array($arItemsBySectionID[$arSection['ID']])) {
                            foreach ($arItemsBySectionID[$arSection['ID']] as $arItem) {
                                if (is_array($arItem['DETAIL_PAGE_URL'])) {
                                    if (isset($arItem['CANONICAL_PAGE_URL'])) {
                                        $arItem['DETAIL_PAGE_URL'] = $arItem['CANONICAL_PAGE_URL'];
                                    } else {
                                        $arItem['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'][key($arItem['DETAIL_PAGE_URL'])];
                                    }
                                }

                                $arTmpLink = [];
                                if ($arItem['LINK_REGION']) {
                                    $arTmpLink['LINK_REGION'] = (array) $arItem['LINK_REGION'];
                                } elseif (array_key_exists('PROPERTY_LINK_REGION_VALUE', $arItem)) {
                                    $arTmpLink['LINK_REGION'] = (array) $arItem['PROPERTY_LINK_REGION_VALUE'];
                                }

                                if (!$bMenu) {
                                    $aMenuLinksExt[] = [$arItem['NAME'], $arItem['DETAIL_PAGE_URL'], [], array_merge(['FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => ($arSection['DEPTH_LEVEL'] + 1), 'IS_ITEM' => 1], $arTmpLink)];
                                } else {
                                    $aMenuLinksExt[] = [
                                        'TEXT' => $arItem['NAME'],
                                        'LINK' => $arItem['DETAIL_PAGE_URL'],
                                        [],
                                        'PARAMS' => array_merge(['FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => ($arSection['DEPTH_LEVEL'] + 1), 'IS_ITEM' => 1], $arTmpLink),
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getMenuChildsExt($arParams, &$aMenuLinksExt)
    {
        if ($handler = SolutionFunctions::getCustomFunc(__FUNCTION__)) {
            call_user_func_array($handler, [$arParams, &$aMenuLinksExt]);

            return;
        }

        $catalog_id = Solution::getFrontParametrValue('CATALOG_IBLOCK_ID');
        $bIsCatalog = $arParams['IBLOCK_ID'] == $catalog_id;

        $arParams['CATALOG_IBLOCK_ID'] = $catalog_id;
        $arParams['IS_CATALOG_IBLOCK'] = $bIsCatalog;

        // event for manipulation store quantity block
        foreach (GetModuleEvents(Solution::moduleID, 'BeforeAsproGetMenuChildsExt', true) as $arEvent) {
            ExecuteModuleEventEx($arEvent, [$arParams, &$aMenuLinksExt]);
        }

        $arSections = $arSectionsByParentSectionID = $arItemsBySectionID = [];
        if ($arParams['MENU_PARAMS']['MENU_SHOW_SECTIONS'] == 'Y') {
            $arSectionFilter = [
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                '<DEPTH_LEVEL' => (Solution::getFrontParametrValue('MAX_DEPTH_MENU') ?: 2),
            ];
            if (array_key_exists('SECTION_FILTER', $arParams) && $arParams['SECTION_FILTER']) {
                $arSectionFilter = array_merge($arSectionFilter, $arParams['SECTION_FILTER']);
            }

            $arSectionSelect = [
                'ID',
                'SORT',
                'ACTIVE',
                'IBLOCK_ID',
                'NAME',
                'SECTION_PAGE_URL',
                'DEPTH_LEVEL',
                'IBLOCK_SECTION_ID',
                'PICTURE',
                'UF_REGION',
            ];
            if ($bIsCatalog) {
                $arSectionSelect = array_merge($arSectionSelect, ['UF_MENU_BANNER', 'UF_MENU_BRANDS', 'UF_CATALOG_ICON']);
            }
            if (array_key_exists('SECTION_SELECT', $arParams) && $arParams['SECTION_SELECT']) {
                $arSectionSelect = array_merge($arSectionSelect, $arParams['SECTION_SELECT']);
            }

            $arSections = SolutionCache::CIBlockSection_GetList(['SORT' => 'ASC', 'ID' => 'ASC', 'CACHE' => ['TAG' => SolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y']], $arSectionFilter, false, $arSectionSelect);
            $arSectionsByParentSectionID = SolutionCache::GroupArrayBy($arSections, ['MULTI' => 'Y', 'GROUP' => ['IBLOCK_SECTION_ID']]);
        }

        if (!$bIsCatalog) {
            if ($arParams['MENU_PARAMS']['MENU_SHOW_ELEMENTS'] == 'Y') {
                $arElementFilter = [
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    'INCLUDE_SUBSECTIONS' => 'Y',
                ];

                $useGlobalActive = Solution::getFrontParametrValue('USE_SECTION_GLOBAL_ACTIVE') === 'Y';
                if ($useGlobalActive) {
                    $arElementFilter[] = [
                        'LOGIC' => 'OR',
                        [
                            'SECTION_ID' => false, // root elements
                        ],
                        [
                            '!SECTION_ID' => false, // elements in global active sections
                            'SECTION_GLOBAL_ACTIVE' => 'Y',
                        ],
                    ];
                }

                if (array_key_exists('ELEMENT_FILTER', $arParams) && $arParams['ELEMENT_FILTER']) {
                    $arElementFilter = array_merge($arElementFilter, $arParams['ELEMENT_FILTER']);
                }

                $arElementSelect = [
                    'ID',
                    'SORT',
                    'ACTIVE',
                    'IBLOCK_ID',
                    'NAME',
                    'DETAIL_PAGE_URL',
                    'DEPTH_LEVEL',
                    'IBLOCK_SECTION_ID',
                    'PROPERTY_LINK_REGION',
                ];

                if (array_key_exists('ELEMENT_SELECT', $arParams) && $arParams['ELEMENT_SELECT']) {
                    $arElementSelect = array_merge($arElementSelect, $arParams['ELEMENT_SELECT']);
                }

                $arItems = SolutionCache::CIBlockElement_GetList(['SORT' => 'ASC', 'ID' => 'DESC', 'CACHE' => ['TAG' => SolutionCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y']], $arElementFilter, false, false, $arElementSelect);

                // filter by region
                global $arRegion;
                if ($arItems) {
                    foreach ($arItems as $key => $arItem) {
                        $arTmpProp = [];
                        $rsPropRegion = \CIBlockElement::GetProperty($arItem['IBLOCK_ID'], $arItem['ID'], ['sort' => 'asc'], ['CODE' => 'LINK_REGION']);
                        while ($arPropRegion = $rsPropRegion->Fetch()) {
                            if ($arPropRegion['VALUE']) {
                                $arTmpProp[] = $arPropRegion['VALUE'];
                            }
                        }

                        $arItems[$key]['LINK_REGION'] = $arTmpProp;
                    }
                }

                if ($arParams['MENU_PARAMS']['MENU_SHOW_SECTIONS'] == 'Y') {
                    $arItemsBySectionID = SolutionCache::GroupArrayBy($arItems, ['MULTI' => 'Y', 'GROUP' => ['IBLOCK_SECTION_ID']]);
                }
            }
        }

        // event for manipulation store quantity block
        foreach (GetModuleEvents(Solution::moduleID, 'OnAsproGetMenuChildsExt', true) as $arEvent) {
            ExecuteModuleEventEx($arEvent, [$arParams, &$aMenuLinksExt]);
        }

        if ($arSections) {
            static::getSectionChilds(false, $arSections, $arSectionsByParentSectionID, $arItemsBySectionID, $aMenuLinksExt);
        }

        if (!$bIsCatalog) {
            if ($arItems && $arParams['MENU_PARAMS']['MENU_SHOW_SECTIONS'] != 'Y') {
                foreach ($arItems as $arItem) {
                    $arExtParam = ['FROM_IBLOCK' => 1, 'DEPTH_LEVEL' => 1];
                    if (isset($arItem['LINK_REGION'])) {
                        $arExtParam['LINK_REGION'] = $arItem['LINK_REGION'];
                    }

                    $aMenuLinksExt[] = [$arItem['NAME'], $arItem['DETAIL_PAGE_URL'], [], $arExtParam];
                }
            }
        }

        // event for manipulation store quantity block
        foreach (GetModuleEvents(Solution::moduleID, 'AfterAsproGetMenuChildsExt', true) as $arEvent) {
            ExecuteModuleEventEx($arEvent, [$arParams, &$aMenuLinksExt]);
        }
    }
}
