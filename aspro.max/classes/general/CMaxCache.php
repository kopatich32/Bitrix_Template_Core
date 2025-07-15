<?php

class CMaxCache
{
    public static $arIBlocks;
    public static $arIBlocksInfo;

    public static function CIBlock_GetList($arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $arFilter = [], $bIncCnt = false)
    {
        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $bIncCnt)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arOrder['CACHE']['GROUP'] ?? [],
                'MULTI' => $arOrder['CACHE']['MULTI'] ?? [],
                'RESULT' => $arOrder['CACHE']['RESULT'] ?? [],
            ];
            unset($arOrder['CACHE']);
            $dbRes = CIBlock::GetList($arOrder, $arFilter, $bIncCnt);
            while ($item = $dbRes->Fetch()) {
                if ($item['ID']) {
                    $item['LID'] = [];
                    $dbIBlockSites = CIBlock::GetSite($item['ID']);
                    while ($arIBlockSite = $dbIBlockSites->Fetch()) {
                        $item['LID'][] = $arIBlockSite['SITE_ID'];
                    }
                }
                $arRes[] = $item;
            }

            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CIBlockElement_GetList($arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'CACHE_GROUP' => [false], 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000, 'URL_TEMPLATE' => '']], $arFilter = [], $arGroupBy = false, $arNavStartParams = false, $arSelectFields = [])
    {
        // check filter by IBLOCK_ID === false
        if (array_key_exists('IBLOCK_ID', $arFilter = (array) $arFilter) && !$arFilter['IBLOCK_ID']) {
            return false;
        }

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);
        if (is_array($arSelectFields) && $arSelectFields) {
            $arSelectFields[] = 'ID';
        }
        $siteID = 's1';
        if (defined('SITE_ID')) {
            $siteID = SITE_ID;
        }
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, [$siteID], $arFilter, (array) $arGroupBy, (array) $arNavStartParams, (array) $arSelectFields)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arOrder['CACHE']['GROUP'] ?? [],
                'MULTI' => $arOrder['CACHE']['MULTI'] ?? [],
                'RESULT' => $arOrder['CACHE']['RESULT'] ?? [],
            ];
            $urlTemplate = $arOrder['CACHE']['URL_TEMPLATE'] ?? '';

            $bCanMultiSection = !isset($arOrder['CACHE']['CAN_MULTI_SECTION']) || $arOrder['CACHE']['CAN_MULTI_SECTION'] === 'Y';

            unset($arOrder['CACHE']);

            $dbRes = CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
            if ($arGroupBy === []) {
                // only count
                $arRes = $dbRes;
            } else {
                if (strlen($urlTemplate)) {
                    $dbRes->SetUrlTemplates($urlTemplate, '');
                }

                $arResultIDsIndexes = [];
                $bGetSectionIDsArray = (in_array('IBLOCK_SECTION_ID', (array) $arSelectFields) || !$arSelectFields);
                if ($bGetDetailPageUrlsArray = (in_array('DETAIL_PAGE_URL', $arSelectFields) || !$arSelectFields)) {
                    if ($arSelectFields) {
                        if (!in_array('IBLOCK_ID', $arSelectFields)) {
                            $arSelectFields[] = 'IBLOCK_ID';
                        }
                        if (!in_array('IBLOCK_SECTION_ID', $arSelectFields)) {
                            $arSelectFields[] = 'IBLOCK_SECTION_ID';
                        }
                        if (!in_array('ID', $arSelectFields)) {
                            $arSelectFields[] = 'ID';
                        }
                        if (!in_array('CANONICAL_PAGE_URL', $arSelectFields)) {
                            $arSelectFields[] = 'CANONICAL_PAGE_URL';
                        }
                    }
                    $bGetSectionIDsArray = true;
                }
                // fields and properties
                $arRes = static::_GetFieldsAndProps($dbRes, $arSelectFields, $bGetSectionIDsArray, $bCanMultiSection);
                if ($bGetDetailPageUrlsArray) {
                    $arBySectionID = $arNewDetailPageUrls = $arCanonicalPageUrls = $arByIBlock = [];
                    $FilterIblockID = $arFilter['IBLOCK_ID'] ?? false;
                    $FilterSectionID = $arFilter['SECTION_ID'] ?? false;
                    foreach ($arRes as $arItem) {
                        if ($IBLOCK_ID = (isset($arItem['IBLOCK_ID']) && $arItem['IBLOCK_ID'] ? $arItem['IBLOCK_ID'] : $FilterIblockID)) {
                            if ($arSectionIDs = ($arItem['IBLOCK_SECTION_ID'] ? $arItem['IBLOCK_SECTION_ID'] : $FilterSectionID)) {
                                if (!is_array($arSectionIDs)) {
                                    $arSectionIDs = [$arSectionIDs];
                                }
                                foreach ($arSectionIDs as $SID) {
                                    $arByIBlock[$IBLOCK_ID][$SID][] = $arItem['ID'];
                                }
                            }
                        } else {
                            $arNewDetailPageUrls[$arItem['ID']] = [$arItem['DETAIL_PAGE_URL']];
                            if (strlen($arItem['CANONICAL_PAGE_URL'])) {
                                $arCanonicalPageUrls[$arItem['ID']] = $arItem['CANONICAL_PAGE_URL'];
                            }
                        }
                    }

                    foreach ($arByIBlock as $IBLOCK_ID => $arIB) {
                        $arSectionIDs = $arSections = [];
                        foreach ($arIB as $SECTION_ID => $arIDs) {
                            $arSectionIDs[] = $SECTION_ID;
                        }
                        if ($arSectionIDs) {
                            $arSections = static::CIBlockSection_GetList(['CACHE' => ['TAG' => static::GetIBlockCacheTag($IBLOCK_ID), 'MULTI' => 'N', 'GROUP' => ['ID']]], ['ID' => $arSectionIDs], false, ['ID', 'CODE', 'EXTERNAL_ID', 'IBLOCK_ID']);
                        }
                        foreach ($arIB as $SECTION_ID => $arIDs) {
                            if ($arIDs) {
                                $rsElements = CIBlockElement::GetList([], ['ID' => $arIDs], false, false, ['ID', 'DETAIL_PAGE_URL', 'CANONICAL_PAGE_URL']);
                                $rsElements->SetUrlTemplates(static::$arIBlocksInfo[$IBLOCK_ID]['DETAIL_PAGE_URL']);
                                $rsElements->SetSectionContext($arSections[$SECTION_ID]);
                                while ($arElement = $rsElements->GetNext()) {
                                    $arNewDetailPageUrls[$arElement['ID']][$SECTION_ID] = $arElement['DETAIL_PAGE_URL'];
                                    if (strlen($arElement['CANONICAL_PAGE_URL'])) {
                                        $arCanonicalPageUrls[$arElement['ID']] = $arElement['CANONICAL_PAGE_URL'];
                                    }
                                }
                            }
                        }
                    }

                    foreach ($arRes as $i => $arItem) {
                        if (array_key_exists($arItem['ID'], $arNewDetailPageUrls) && count($arNewDetailPageUrls[$arItem['ID']]) > 1) {
                            if (isset($arCanonicalPageUrls[$arItem['ID']]) && strlen($arCanonicalPageUrls[$arItem['ID']])) {
                                $arRes[$i]['DETAIL_PAGE_URL'] = $arCanonicalPageUrls[$arItem['ID']];
                            } else {
                                $arRes[$i]['DETAIL_PAGE_URL'] = $arNewDetailPageUrls[$arItem['ID']];
                            }
                        }
                        unset($arRes[$i]['~DETAIL_PAGE_URL']);
                    }
                }

                if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                    $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
                }
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CIBlockSection_GetList($arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'CACHE_GROUP' => [false], 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000, 'URL_TEMPLATE' => '']], $arFilter = [], $bIncCnt = false, $arSelectFields = [], $arNavStartParams = false)
    {
        // check filter by IBLOCK_ID === false
        if (array_key_exists('IBLOCK_ID', $arFilter = (array) $arFilter) && !$arFilter['IBLOCK_ID']) {
            return false;
        }

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);
        if (is_array($arSelectFields) && $arSelectFields) {
            $arSelectFields[] = 'ID';
        }

        $siteID = 's1';
        if (defined('SITE_ID')) {
            $siteID = SITE_ID;
        }

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, [$siteID], (array) $bIncCnt, (array) $arNavStartParams, (array) $arSelectFields)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arOrder['CACHE']['GROUP'] ?? [],
                'MULTI' => $arOrder['CACHE']['MULTI'] ?? [],
                'RESULT' => $arOrder['CACHE']['RESULT'] ?? [],
            ];
            $urlTemplate = $arOrder['CACHE']['URL_TEMPLATE'] ?? '';
            unset($arOrder['CACHE']);

            $dbRes = CIBlockSection::GetList($arOrder, $arFilter, $bIncCnt, $arSelectFields, $arNavStartParams);

            if (strlen($urlTemplate)) {
                $dbRes->SetUrlTemplates('', $urlTemplate);
            }

            // fields and properties
            $arRes = static::_GetFieldsAndProps($dbRes, $arSelectFields);

            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CSaleBasket_GetList($arOrder = ['SORT' => 'ASC'], $arFilter = [], $arGroupBy = false, $arNavStartParams = false, $arSelectFields = [], $cacheTag = '', $cacheTime = 36000000, $cachePath = '')
    {
        CModule::IncludeModule('sale');
        if (!strlen($cacheTag)) {
            $cacheTag = '_notag';
        }
        if (!strlen($cachePath)) {
            $cachePath = '/'.__CLASS__.'/sale/CSaleBasket_GetList/'.$cacheTag.'/';
        }
        $obCache = new CPHPCache();
        $cacheID = 'CSaleBasket_GetList_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $arGroupBy, (array) $arNavStartParams, (array) $arSelectFields)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arGroupBy['GROUP'] ?? [],
                'MULTI' => $arGroupBy['MULTI'] ?? [],
                'RESULT' => $arSelectFields['RESULT'] ?? [],
            ];
            $arGroupBy = (isset($arGroupBy['BX']) ? $arGroupBy['BX'] : $arGroupBy);
            $dbRes = CSaleBasket::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
            if (in_array('DETAIL_PAGE_URL', $arSelectFields) === false) {
                while ($item = $dbRes->Fetch()) {
                    $arRes[] = $item;
                }
            } else {
                while ($item = $dbRes->GetNext()) {
                    $arRes[] = $item;
                }
            }

            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CCatalogStore_GetList($arOrder = ['SORT' => 'ASC'], $arFilter = [], $arGroupBy = false, $arNavStartParams = false, $arSelectFields = [], $cacheTag = '', $cacheTime = 36000000, $cachePath = '')
    {
        CModule::IncludeModule('catalog');
        if (!strlen($cacheTag)) {
            $cacheTag = '_notag';
        }
        if (!strlen($cachePath)) {
            $cachePath = '/'.__CLASS__.'/catalog/CCatalogStore_GetList/'.$cacheTag.'/';
        }
        $obCache = new CPHPCache();
        $cacheID = 'CCatalogStore_GetList_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $arGroupBy, (array) $arNavStartParams, (array) $arSelectFields)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = $dbRes = [];
            $arResultGroupBy = [
                'GROUP' => $arGroupBy['GROUP'] ?? [],
                'MULTI' => $arGroupBy['MULTI'] ?? [],
                'RESULT' => $arSelectFields['RESULT'] ?? [],
            ];
            $arGroupBy = (isset($arGroupBy['BX']) ? $arGroupBy['BX'] : $arGroupBy);

            if (CMax::checkVersionModule('17.0.4', 'catalog')) {
                $getListParams = $arRuntimeFields = [];
                $storeClass = empty($arSelectFields) ? '\Bitrix\Catalog\StoreTable' : '\Bitrix\Catalog\StoreProductTable';
                $oldFieldsList = [
                    'PRODUCT_AMOUNT' => 'AMOUNT',
                    'ID' => 'STORE_ID',
                    'ELEMENT_ID' => 'PRODUCT_ID',
                ];

                if (!empty($arFilter)) {
                    $getListParams['filter'] = $arFilter;

                    foreach ($arSelectFields as $arField) {
                        if (isset($oldFieldsList[$arField]) && !isset($arRuntimeFields[$arField])) {
                            $arRuntimeFields[$arField] = new Bitrix\Main\Entity\ExpressionField($arField, $oldFieldsList[$arField]);
                        }
                    }
                }
                if (!empty($arGroupBy)) {
                    $getListParams['group'] = $arGroupBy;
                }

                if (!empty($arOrder)) {
                    $getListParams['order'] = $arOrder;
                }

                if (!empty($arSelectFields)) {
                    $getListParams['select'] = $arSelectFields;
                    $getListParams['filter']['STORE.ACTIVE'] = 'Y';

                    foreach ($arSelectFields as $arField) {
                        if (isset($oldFieldsList[$arField]) && !isset($arRuntimeFields[$arField])) {
                            $arRuntimeFields[$arField] = new Bitrix\Main\Entity\ExpressionField($arField, $oldFieldsList[$arField]);
                        }
                    }
                }

                if (!empty($arRuntimeFields)) {
                    $getListParams['runtime'] = array_values($arRuntimeFields);
                }

                if (!empty($arNavStartParams)) {
                    if (isset($arNavStartParams['nPageSize'])) {
                        $getListParams['limit'] = $arNavStartParams['nPageSize'];
                    }

                    if (isset($arNavStartParams['iNumPage'])) {
                        $getListParams['offset'] = $arNavStartParams['iNumPage'];
                    }
                }

                $dbRes = $storeClass::getList($getListParams);
            } else {
                $dbRes = CCatalogStore::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
            }

            while ($item = $dbRes->Fetch()) {
                $arRes[] = $item;
            }

            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CIBlockSection_GetCount($arOrder = ['CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $arFilter = [])
    {
        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arRes = CIBlockSection::GetCount($arFilter);

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CIBlockElement_GetProperty($iblockID, $elementID, $arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $arFilter = [])
    {
        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $iblockID, (array) $elementID, (array) $arOrder, (array) $arFilter)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            unset($arOrder['CACHE']);
            $arRes = [];
            $dbRes = CIBlockElement::GetProperty($iblockID, $elementID, $arOrder, $arFilter);
            while ($item = $dbRes->Fetch()) {
                if ($item['VALUE']) {
                    $arRes[] = $item['VALUE'];
                }
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CIBlockPropertyEnum_GetList($arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $arFilter = [])
    {
        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('iblock', __FUNCTION__, $arOrder['CACHE']);

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            unset($arOrder['CACHE']);
            $arRes = [];
            $rsProp = CIBlockPropertyEnum::GetList($arOrder, $arFilter);

            while ($arProp = $rsProp->Fetch()) {
                if ($arProp['VALUE']) {
                    $arRes[$arProp['EXTERNAL_ID']] = $arProp['VALUE'];
                }
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CUser_GetList($arOrder = ['SORT' => 'ASC', 'CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $arFilter = [], $arParameters = [])
    {
        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('main', __FUNCTION__, $arOrder['CACHE']);

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $arParameters)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arOrder['CACHE']['GROUP'] ?? [],
                'MULTI' => $arOrder['CACHE']['MULTI'] ?? [],
                'RESULT' => $arOrder['CACHE']['RESULT'] ?? [],
            ];
            unset($arOrder['CACHE']);

            $dbRes = CUser::GetList($arOrder, $order = 'sort', $arFilter, $arParameters);

            while ($item = $dbRes->Fetch()) {
                $arRes[] = $item;
            }
            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CForm_GetList($by = ['CACHE' => ['MULTI' => 'Y', 'GROUP' => [], 'RESULT' => [], 'TAG' => '', 'PATH' => '', 'TIME' => 36000000]], $order = 'asc', $arFilter = [], &$is_filtered, $min_permission = 10)
    {
        CModule::IncludeModule('form');
        if (!is_array($by)) {
            $by = [$by];
        }
        if (!isset($by['CACHE'])) {
            $by['CACHE'] = [];
        }

        $arCache = $by['CACHE'];
        unset($by['CACHE']);
        $by = $by['by'];
        $is_filtered = false;

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('form', __FUNCTION__, $arCache);
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize((array) $arFilter).$by.$order.$min_permission);
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arCache['GROUP'] ?? [],
                'MULTI' => $arCache['MULTI'] ?? [],
                'RESULT' => $arCache['RESULT'] ?? [],
            ];

            $dbRes = CForm::GetList($by, $order, $arFilter, $is_filtered, $min_permission);
            while ($item = $dbRes->Fetch()) {
                $arRes[] = $item;
            }

            if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function CForumMessage_GetListEx($arOrder = ['SORT' => 'ASC'], $arFilter = [], $arGroupBy = false, $iNum = 0, $arSelectFields = [], $cacheTag = '', $cacheTime = 36000000, $cachePath = '')
    {
        CModule::IncludeModule('forum');
        if (!strlen($cacheTag)) {
            $cacheTag = '_notag';
        }
        if (!strlen($cachePath)) {
            $cachePath = '/'.__CLASS__.'/forum/CForumMessage_GetListEx/'.$cacheTag.'/';
        }
        $obCache = new CPHPCache();
        $cacheID = 'CForumMessage_GetListEx_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $arGroupBy, (array) $iNum, (array) $arSelectFields)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            $arRes = [];
            $arResultGroupBy = [
                'GROUP' => $arGroupBy['GROUP'] ?? [],
                'MULTI' => $arGroupBy['MULTI'] ?? [],
                'RESULT' => $arSelectFields['RESULT'] ?? [],
            ];
            $bCount = (isset($arGroupBy['BX']) ? $arGroupBy['BX'] : $arGroupBy);
            $dbRes = CForumMessage::GetListEx($arOrder, $arFilter, $bCount, $iNum, $arSelectFields);
            if ($bCount) {
                $arRes = $dbRes;
            } else {
                while ($item = $dbRes->Fetch()) {
                    $arRes[] = $item;
                }

                if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                    $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
                }
            }

            static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
        }

        return $arRes;
    }

    public static function GeoIp_GetGeoData($ipAddress, $languageId = 'ru', $arCache = ['TAG' => 'geoDataByIp', 'PATH' => '', 'TIME' => 36000000])
    {
        $arResult = [];

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('main', __FUNCTION__, $arCache);
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5($ipAddress.'_'.$languageId);
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arResult = $res['arRes'];
        } else {
            if (class_exists('\Bitrix\Main\Service\GeoIp\Manager')) {
                $obBitrixGeoIPResult = Bitrix\Main\Service\GeoIp\Manager::getDataResult($ipAddress, $languageId);
                if ($obBitrixGeoIPResult) {
                    if ($obResult = $obBitrixGeoIPResult->getGeoData()) {
                        $arResult = get_object_vars($obResult);
                        static::_SaveDataCache($obCache, $arResult, $cacheTag, $cachePath, $cacheTime, $cacheID);
                    }
                }
            }
        }

        return $arResult;
    }

    public static function SaleGeoIp_GetLocationCode($ipAddress, $languageId = 'ru', $arCache = ['TAG' => 'locationCodeByIp', 'PATH' => '', 'TIME' => 36000000])
    {
        $locationCode = '';

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('sale', __FUNCTION__, $arCache);
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5($ipAddress.'_'.$languageId);
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $locationCode = $res['arRes'];
        } else {
            if (
                class_exists('\Bitrix\Main\Service\GeoIp\Manager')
                && Bitrix\Main\Loader::includeModule('sale')
            ) {
                if ($locationCode = Bitrix\Sale\Location\GeoIp::getLocationCode($ipAddress, $languageId)) {
                    static::_SaveDataCache($obCache, $locationCode, $cacheTag, $cachePath, $cacheTime, $cacheID);
                }
            }
        }

        return $locationCode;
    }

    public static function SaleLocation_GetList($arOrder = ['SORT' => 'ASC'], $arFilter = [], $arSelectFields = [], $arExt = [], $arCache = ['MULTI' => 'Y', 'CACHE_GROUP' => [false], 'GROUP' => [], 'RESULT' => [], 'TAG' => 'locationsByFilter', 'PATH' => '', 'TIME' => 36000000])
    {
        $arRes = [];

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('sale', __FUNCTION__, $arCache);
        if (is_array($arSelectFields) && $arSelectFields) {
            $arSelectFields[] = 'ID';
        }

        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5(serialize(array_merge((array) $arOrder, (array) $arFilter, (array) $arSelectFields, (array) $arExt, (array) $arCache)));
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arRes = $res['arRes'];
        } else {
            if (Bitrix\Main\Loader::includeModule('sale')) {
                $arResultGroupBy = [
                    'GROUP' => $arCache['GROUP'] ?? [],
                    'MULTI' => $arCache['MULTI'] ?? [],
                    'RESULT' => $arCache['RESULT'] ?? [],
                ];

                $params = [];

                if (is_array($arOrder)) {
                    $params['order'] = $arOrder;
                }

                if (is_array($arFilter)) {
                    $params['filter'] = $arFilter;
                }

                if (is_array($arSelectFields)) {
                    $params['select'] = $arSelectFields;
                }

                if (is_array($arExt)) {
                    $params = array_merge($params, $arExt);
                }

                $res = Bitrix\Sale\Location\LocationTable::getList($params);
                while ($arLocation = $res->fetch()) {
                    $arRes[] = $arLocation;
                }

                if ($arResultGroupBy['MULTI'] || $arResultGroupBy['GROUP'] || $arResultGroupBy['RESULT']) {
                    $arRes = static::GroupArrayBy($arRes, $arResultGroupBy);
                }

                static::_SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID);
            }
        }

        return $arRes;
    }

    public static function SaleGeoIp_GetLocation($ipAddress, $languageId = 'ru', $arCache = ['TAG' => 'locationByIp', 'PATH' => '', 'TIME' => 12960000])
    {
        $arLocation = [];

        list($cacheTag, $cachePath, $cacheTime) = static::_InitCacheParams('sale', __FUNCTION__, $arCache);
        $obCache = new CPHPCache();
        $cacheID = __FUNCTION__.'_'.$cacheTag.md5($ipAddress.'_'.$languageId);
        if ($obCache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $obCache->GetVars();
            $arLocation = $res['arRes'];
        } else {
            if (
                class_exists('\Bitrix\Main\Service\GeoIp\Manager')
                && Bitrix\Main\Loader::includeModule('sale')
            ) {
                $locationId = Bitrix\Sale\Location\GeoIp::getLocationId($ipAddress, $languageId);
                if ($locationId) {
                    $arTmp = static::SaleLocation_GetList(
                        ['PARENTS.TYPE_ID' => 'desc'],
                        [
                            '=ID' => $locationId,
                            '=NAME.LANGUAGE_ID' => $languageId,
                            '=PARENTS.NAME.LANGUAGE_ID' => $languageId,
                        ],
                        [
                            'ID',
                            'CODE',
                            'CITY_NAME' => 'NAME.NAME',
                            'TYPE_ID',
                            'TYPE_CODE' => 'TYPE.CODE',
                            'PARENTS.ID',
                            'PARENTS.NAME',
                            'PARENTS.TYPE.CODE',
                        ]
                    );

                    if ($arTmp) {
                        foreach ($arTmp as $loc) {
                            if (!$arLocation) {
                                $arLocation = [
                                    'ID' => $loc['ID'],
                                    'CODE' => $loc['CODE'],
                                    'CITY_NAME' => $loc['CITY_NAME'],
                                    'TYPE_ID' => $loc['TYPE_ID'],
                                    'TYPE_CODE' => $loc['TYPE_CODE'],
                                    'PARENTS' => [],
                                ];
                            }

                            if (
                                $loc['SALE_LOCATION_LOCATION_PARENTS_ID']
                                && $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME']
                                && !isset($arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']])
                                && $loc['SALE_LOCATION_LOCATION_PARENTS_ID'] != $loc['ID']
                            ) {
                                $arLocation['PARENTS'][$loc['SALE_LOCATION_LOCATION_PARENTS_ID']] = [
                                    'ID' => $loc['SALE_LOCATION_LOCATION_PARENTS_ID'],
                                    'NAME' => $loc['SALE_LOCATION_LOCATION_PARENTS_NAME_NAME'],
                                    'TYPE_CODE' => $loc['SALE_LOCATION_LOCATION_PARENTS_TYPE_CODE'],
                                ];
                            }
                        }
                    }
                    static::_SaveDataCache($obCache, $arLocation, $cacheTag, $cachePath, $cacheTime, $cacheID);
                }
            }
        }

        return $arLocation;
    }

    private static function _MakeResultTreeArray($arParams, &$arItem, &$arItemResval, &$to)
    {
        if ($arParams['GROUP']) {
            $newto = $to;
            $FieldID = array_shift($arParams['GROUP']);
            $arFieldValue = (is_array($arItem[$FieldID]) ? $arItem[$FieldID] : [$arItem[$FieldID]]);

            foreach ($arFieldValue as $FieldValue) {
                if (!isset($to[$FieldValue])) {
                    $to[$FieldValue] = false;
                }
                $newto = &$to[$FieldValue];
                static::_MakeResultTreeArray($arParams, $arItem, $arItemResval, $newto);
            }
        } else {
            $arParams['MULTI'] = $arParams['MULTI'] ?? '';

            if ($arParams['MULTI'] == 'Y') {
                $to[] = $arItemResval;
            } elseif ($arParams['MULTI'] == 'YM') {
                if ($to) {
                    $to = array_merge((array) $to, (array) $arItemResval);
                } else {
                    $to = $arItemResval;
                }
            } else {
                $to = $arItemResval;
            }
        }
    }

    public static function GroupArrayBy($arItems, $arParams)
    {
        $arRes = [];
        $arParams['RESULT'] = array_diff((array) ($arParams['RESULT'] ?? []), [null]);
        $arParams['GROUP'] = array_diff((array) ($arParams['GROUP'] ?? []), [null]);
        $resultIDsCount = count($arParams['RESULT']);
        foreach ($arItems as $arItem) {
            $val = false;
            if ($resultIDsCount) {
                if ($resultIDsCount > 1) {
                    foreach ($arParams['RESULT'] as $ID) {
                        $val[$ID] = $arItem[$ID];
                    }
                } else {
                    $val = $arItem[current($arParams['RESULT'])];
                }
            } else {
                $val = $arItem;
            }
            static::_MakeResultTreeArray($arParams, $arItem, $val, $arRes);
        }

        return $arRes;
    }

    private static function _InitCacheParams($moduleName, $functionName, $arCache)
    {
        CModule::IncludeModule($moduleName);

        $cacheTag = isset($arCache['TAG']) && strlen($arCache['TAG']) ? $arCache['TAG'] : '_notag';
        $cachePath = isset($arCache['PATH']) && strlen($arCache['PATH']) ? $arCache['PATH'] : '/'.__CLASS__.'/'.$moduleName.'/'.$functionName.'/'.$cacheTag.'/';
        $cacheTime = ((isset($arCache['TIME']) && $arCache['TIME'] >= 0) ? $arCache['TIME'] : 36000000);

        return [$cacheTag, $cachePath, $cacheTime];
    }

    private static function _GetElementsSectionsArray($IDs)
    {
        $arSections = [];

        $resGroups = CIBlockElement::GetElementGroups($IDs, true, ['ID', 'IBLOCK_ELEMENT_ID']);
        while ($arGroup = $resGroups->Fetch()) {
            if (!isset($arSections[$arGroup['IBLOCK_ELEMENT_ID']])) {
                // element has only one section (or this section is first in list)
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']] = $arGroup['ID'];
            } elseif (is_array($arSections[$arGroup['IBLOCK_ELEMENT_ID']])) {
                // element has more than two sections, return array of sections
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']][] = $arGroup['ID'];
            } else {
                // element has two sections, return array of sections
                $arSections[$arGroup['IBLOCK_ELEMENT_ID']] = [
                    $arSections[$arGroup['IBLOCK_ELEMENT_ID']],
                    $arGroup['ID'],
                ];
            }
        }

        return $arSections;
    }

    private static function _GetFieldsAndProps($dbRes, $arSelectFields, $bGetSectionIDsArray = false, $bCanMultiSection = true)
    {
        $arRes = $arResultIDsIndexes = [];
        if ($arSelectFields && (in_array('DETAIL_PAGE_URL', $arSelectFields) === false && in_array('SECTION_PAGE_URL', $arSelectFields) === false)) {
            $func = 'Fetch';
        } else {
            $func = 'GetNext';
        }
        while ($item = $dbRes->$func()) {
            $existKey = $arResultIDsIndexes[$item['ID']] ?? null;
            if ($existKey !== null) {
                $existItem = &$arRes[$existKey];
                if ($bGetSectionIDsArray) {
                    unset($item['IBLOCK_SECTION_ID']);
                    unset($item['~IBLOCK_SECTION_ID']);
                }
                foreach ($item as $key => $val) {
                    if ($key == 'ID') {
                        continue;
                    }
                    if (isset($existItem[$key])) {
                        if (is_array($existItem[$key])) {
                            if (!in_array($val, $existItem[$key])) {
                                $existItem[$key][] = $val;
                            }
                        } else {
                            if ($existItem[$key] != $val) {
                                $existItem[$key] = [$existItem[$key], $val];
                            } else {
                                if (isset($item[$key.'_ID'])) {
                                    if ($item[$key.'_ID'] != $existItem[$key.'_ID']) {
                                        $existItem[$key] = [$existItem[$key], $val];
                                    }
                                }
                            }
                        }
                    } else {
                        $existItem[$key] = $val;
                    }
                }
            } else {
                if ($bGetSectionIDsArray) {
                    $item['IBLOCK_SECTION_ID_SELECTED'] = $item['IBLOCK_SECTION_ID'];
                    unset($item['~IBLOCK_SECTION_ID']);
                }
                if (in_array('ElementValues', $arSelectFields) && isset($item['IBLOCK_ID'])) {
                    $ipropValues = new Bitrix\Iblock\InheritedProperty\ElementValues($item['IBLOCK_ID'], $item['ID']);
                    $item['IPROPERTY_VALUES'] = $ipropValues->getValues();
                }
                if (in_array('SectionValues', $arSelectFields) && isset($item['IBLOCK_ID'])) {
                    $ipropValues = new Bitrix\Iblock\InheritedProperty\SectionValues($item['IBLOCK_ID'], $item['ID']);
                    $item['IPROPERTY_VALUES'] = $ipropValues->getValues();
                }
                $arRes[] = $item;
                $arResultIDsIndexes[$item['ID']] = count($arRes) - 1;
            }
        }

        if (
            $bGetSectionIDsArray
            && $bCanMultiSection
            && $arResultIDsIndexes
        ) {
            $arSections = static::_GetElementsSectionsArray(array_keys($arResultIDsIndexes));
            foreach ($arSections as $itemId => $arSectionsIds) {
                $existKey = $arResultIDsIndexes[$itemId] ?? null;

                if (isset($existKey)) {
                    $arRes[$existKey]['IBLOCK_SECTION_ID'] = $arSectionsIds;
                }
            }
        }

        return $arRes;
    }

    private static function _SaveDataCache($obCache, $arRes, $cacheTag, $cachePath, $cacheTime, $cacheID)
    {
        if (
            $cacheTime > 0 
            && Bitrix\Main\Config\Option::get('main', 'component_cache_on', 'Y') != 'N'
            && !\Bitrix\Main\Data\Cache::shouldClearCache()
        ) {
            $obCache->StartDataCache($cacheTime, $cacheID, $cachePath);

            if (strlen($cacheTag)) {
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache($cachePath);
                $CACHE_MANAGER->RegisterTag($cacheTag);
                $CACHE_MANAGER->EndTagCache();
            }

            $obCache->EndDataCache(['arRes' => $arRes]);
        }
    }

    public static function GetIBlockCacheTag($IBLOCK_ID)
    {
        if (!$IBLOCK_ID) {
            return false;
        } else {
            if (is_array($IBLOCK_ID)) {
                return implode(',', $IBLOCK_ID);
            } else {
                return @static::$arIBlocksInfo[$IBLOCK_ID]['CODE'].$IBLOCK_ID;
            }
        }
    }

    public static function GetUserCacheTag($id)
    {
        if (!$id) {
            return false;
        } else {
            return 'user_'.$id;
        }
    }

    public static function GetPropertyCacheTag($code)
    {
        if (!$code) {
            return false;
        } else {
            return 'property_'.$code;
        }
    }

    public static function ClearTagIBlock($arFields)
    {
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag('iblocks');

        if ($arFields['ID'] && Aspro\Max\Product\SkuTools::checkOfferIblock($arFields['ID'])) {
            $obCache = new CPHPCache();
            $obCache->CleanDir('', '/cache/CMaxCache/iblock/getSKUjs/');
        }
    }

    public static function ClearCacheByTag($tag)
    {
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag($tag);
    }

    public static function ClearTagByUser($arFields)
    {
        if ($arFields['ID']) {
            static::ClearCacheByTag(static::GetUserCacheTag($arFields['ID']));
        }
    }

    public static function ClearTagByProperty($arFields)
    {
        if ($arFields['CODE']) {
            static::ClearCacheByTag(static::GetPropertyCacheTag($arFields['CODE']));
        }
    }

    public static function ClearTagIBlockBeforeDelete($ID)
    {
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag('iblocks');
    }

    public static function ClearTagIBlockElement($arFields)
    {
        global $CACHE_MANAGER;
        if ($arFields['IBLOCK_ID']) {
            $code = @static::$arIBlocksInfo[$arFields['IBLOCK_ID']]['CODE'];

            if ($code === 'aspro_max_catalog' || $code === 'aspro_max_sku') {
                $CACHE_MANAGER->ClearByTag('elements_out_of_production');
                $CACHE_MANAGER->ClearByTag('elements_by_offer');
            }

            if ($arFields['ID'] && Aspro\Max\Product\SkuTools::checkOfferIblock($arFields['IBLOCK_ID'])) {
                static::ClearSKUjsCache($arFields['ID']);
            }

            if (defined('ADMIN_SECTION')) {
                if (static::$arIBlocks) {
                    foreach (static::$arIBlocks as $arSiteIbloks) {
                        foreach ($arSiteIbloks as $siteID => $arAllIbloks) {
                            foreach ($arAllIbloks as $key => $arIbloks) {
                                if (in_array($arFields['IBLOCK_ID'], $arIbloks)) {
                                    if (strpos($key, 'services') !== false) {
                                        CBitrixComponent::clearComponentCache('bitrix:menu', $siteID);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $CACHE_MANAGER->ClearByTag(static::GetIBlockCacheTag($arFields['IBLOCK_ID']));
        }
    }

    public static function ClearSKUjsCache($offerID)
    {
        $dirSkuCache = '/cache/CMaxCache/iblock/getSKUjs/element_'.$offerID;
        if (Bitrix\Main\Data\Cache::getCacheEngineType() === 'cacheenginefiles') {
            $absDirSkuCache = $_SERVER['DOCUMENT_ROOT'].'/bitrix'.$dirSkuCache;
            if (file_exists($absDirSkuCache)) {
                Bitrix\Main\IO\Directory::deleteDirectory($absDirSkuCache);
            }
        } else {
            $obCache = new CPHPCache();
            $obCache->CleanDir('', $dirSkuCache);
        }
    }

    public static function ClearTagIBlockSection($arFields)
    {
        global $CACHE_MANAGER;
        if ($arFields['IBLOCK_ID']) {
            $CACHE_MANAGER->ClearByTag(static::GetIBlockCacheTag($arFields['IBLOCK_ID']));
        }
    }

    public static function ClearTagIBlockProperty($arFields)
    {
        global $CACHE_MANAGER;
        if ($arFields['ID']) {
            $CACHE_MANAGER->ClearByTag('PROP_'.$arFields['ID']);
        }
    }

    public static function ClearTagIBlockSectionBeforeDelete($ID)
    {
        global $CACHE_MANAGER;
        if ($ID > 0) {
            if ($IBLOCK_ID = static::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'RESULT' => ['IBLOCK_ID']]], ['ID' => $ID], false, ['IBLOCK_ID'], true)) {
                $CACHE_MANAGER->ClearByTag(static::GetIBlockCacheTag($IBLOCK_ID));
            }
        }
    }
}

// initialize CMaxCache::$arIBlocks array
if (CMaxCache::$arIBlocks === null) {
    $arIBlocksTmp = CMaxCache::CIBlock_GetList(['CACHE' => ['TAG' => 'iblocks']], ['ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N']);
    CMaxCache::$arIBlocks = CMaxCache::GroupArrayBy($arIBlocksTmp, ['GROUP' => ['LID', 'IBLOCK_TYPE_ID', 'CODE'], 'MULTI' => 'Y', 'RESULT' => ['ID']]);
    CMaxCache::$arIBlocksInfo = CMaxCache::GroupArrayBy($arIBlocksTmp, ['GROUP' => ['ID']]);
}
